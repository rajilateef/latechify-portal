<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\TrainingClass;
use App\Models\TrainingCourse;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $enrollments = $user->activeEnrollments()->with('program.components')->get();
        $programIds = $user->accessibleProgramIds(); // includes component programs of any bundle

        // Assignments in the student's programs they haven't submitted yet.
        $submittedIds = $user->submissions()->pluck('assignment_id')->all();
        $dueAssignments = Assignment::published()
            ->whereHas('course', fn ($q) => $q->whereIn('training_program_id', $programIds))
            ->whereNotIn('id', $submittedIds)
            ->with('course')
            ->orderByRaw('due_at is null, due_at asc')
            ->limit(5)->get();

        $overall = $enrollments->count()
            ? (int) round($enrollments->avg(fn (Enrollment $e) => $e->progressPercent()))
            : 0;

        return view('portal.dashboard', [
            'user'            => $user,
            'enrollments'     => $enrollments,
            'announcements'   => Announcement::published()->visibleTo($user)->limit(4)->get(),
            'upcomingSessions'=> LiveSession::upcoming()->whereIn('training_program_id', $programIds)->with('program')->limit(4)->get(),
            'dueAssignments'  => $dueAssignments,
            'activity'        => $user->classCompletions()->with('trainingClass.course')->latest('completed_at')->limit(6)->get(),
            'stats'           => [
                'programs'    => $enrollments->count(),
                'progress'    => $overall,
                'attendance'  => $user->attendanceRate(),
                'outstanding' => $user->totalOutstanding(),
                'certificates'=> $user->certificates()->count(),
                'dueCount'    => $dueAssignments->count(),
            ],
        ]);
    }

    public function program(TrainingProgram $program)
    {
        $this->assertEnrolled($program);

        $user = auth()->user();
        $completedIds = $user->completedClassIds();

        // For a composite program, group courses by their source program (self + included programs).
        $groups = $program->programGroup()
            ->map(fn (TrainingProgram $p) => [
                'program' => $p,
                'courses' => $p->courses()->with('classes')->get(),
            ])
            ->filter(fn ($g) => $g['courses']->isNotEmpty())
            ->values();

        $effectiveClassIds = $program->effectiveClassIds();
        $total = count($effectiveClassIds);
        $done = count(array_intersect($effectiveClassIds, $completedIds));

        return view('portal.program', [
            'program'      => $program,
            'groups'       => $groups,
            'isComposite'  => $program->isComposite(),
            'completedIds' => $completedIds,
            'enrollment'   => $this->enrollment($program),
            'stats'        => ['done' => $done, 'total' => $total, 'pct' => $total ? (int) round($done / $total * 100) : 0],
        ]);
    }

    public function course(TrainingCourse $course)
    {
        $course->load('program', 'classes.resources', 'assignments');
        $this->assertEnrolled($course->program);

        $materials = $course->materials()->published()->orderBy('sort_order')->get()
            ->filter(fn ($m) => $m->canBeAccessedBy(auth()->user()))->values();

        return view('portal.course', [
            'course'       => $course,
            'completedIds' => auth()->user()->completedClassIds(),
            'materials'    => $materials,
        ]);
    }

    public function classShow(TrainingClass $class)
    {
        $class->load('course.program', 'resources', 'assignments');
        $this->assertEnrolled($class->course->program);

        return view('portal.class', [
            'class'     => $class,
            'completed' => auth()->user()->hasCompletedClass($class->id),
        ]);
    }

    public function announcements()
    {
        return view('portal.announcements', [
            'announcements' => Announcement::published()->visibleTo(auth()->user())->paginate(10),
        ]);
    }

    public function schedule()
    {
        $user = auth()->user();
        $programIds = $user->accessibleProgramIds();

        return view('portal.schedule', [
            'upcoming'   => LiveSession::upcoming()->whereIn('training_program_id', $programIds)->with('program', 'course')->get(),
            'past'       => LiveSession::past()->whereIn('training_program_id', $programIds)->with('program', 'course')->limit(20)->get(),
            'attendance' => $user->attendances()->with('liveSession')->get()->keyBy('live_session_id'),
            'rate'       => $user->attendanceRate(),
        ]);
    }

    public function fees()
    {
        $user = auth()->user();

        return view('portal.fees', [
            'enrollments'  => $user->activeEnrollments()->with('program', 'payments.receipt')->get(),
            'outstanding'  => $user->totalOutstanding(),
            'canPayOnline' => app(\App\Services\Monnify::class)->isConfigured(),
        ]);
    }

    public function certificates()
    {
        $user = auth()->user();

        return view('portal.certificates', [
            'certificates' => $user->certificates()->with('program')->get(),
            'enrollments'  => $user->activeEnrollments()->with('program')->get(),
            'badges'       => $this->badgesFor($user),
        ]);
    }

    public function enroll()
    {
        $user = auth()->user();
        $takenIds = $user->enrollments()->pluck('training_program_id');

        return view('portal.enroll', [
            'available' => TrainingProgram::active()->whereNotIn('id', $takenIds)->get(),
            'pending'   => $user->enrollments()->where('status', 'pending')->with('program')->get(),
        ]);
    }

    public function enrollStore(Request $request)
    {
        $data = $request->validate([
            'training_program_id' => 'required|exists:training_programs,id',
            'type'                => 'nullable|in:full_time,it_siwes',
        ]);

        $user = auth()->user();

        if ($user->enrollments()->where('training_program_id', $data['training_program_id'])->exists()) {
            return back()->with('notice', 'You have already requested or joined that program.');
        }

        Enrollment::create([
            'user_id'             => $user->id,
            'training_program_id' => $data['training_program_id'],
            'type'                => $data['type'] ?? 'full_time',
            'status'              => 'pending',
        ]);

        return back()->with('success', 'Enrolment request submitted — an administrator will review and approve it.');
    }

    /* ── helpers ── */

    /** Computed achievement badges (no storage — derived from the student's activity). */
    protected function badgesFor($user): array
    {
        $completed = $user->classCompletions()->count();
        $graded = $user->submissions()->where('status', 'graded')->count();
        $rate = $user->attendanceRate();

        return array_values(array_filter([
            $completed >= 1 ? ['icon' => 'Rocket', 'label' => 'Getting started', 'desc' => 'Completed your first class'] : null,
            $completed >= 10 ? ['icon' => 'Flame', 'label' => 'On a roll', 'desc' => '10 classes completed'] : null,
            $graded >= 1 ? ['icon' => 'ClipboardCheck', 'label' => 'Submitted', 'desc' => 'Graded on an assignment'] : null,
            $rate !== null && $rate >= 80 ? ['icon' => 'CalendarCheck', 'label' => 'Reliable', 'desc' => '80%+ attendance'] : null,
            $user->certificates()->exists() ? ['icon' => 'Award', 'label' => 'Certified', 'desc' => 'Earned a certificate'] : null,
        ]));
    }

    protected function enrollment(TrainingProgram $program): ?Enrollment
    {
        return auth()->user()->activeEnrollments()->where('training_program_id', $program->id)->first();
    }

    protected function assertEnrolled(TrainingProgram $program): void
    {
        // Access via a direct enrolment OR a composite (bundle) program that includes this one.
        abort_unless(auth()->user()->canAccessProgram($program->id), 403, 'You do not have access to this program.');
    }
}
