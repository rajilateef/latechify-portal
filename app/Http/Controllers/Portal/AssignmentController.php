<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $programIds = $user->activeProgramIds();

        $assignments = Assignment::published()
            ->whereHas('course', fn ($q) => $q->whereIn('training_program_id', $programIds))
            ->with(['course.program', 'submissions' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByRaw('due_at is null, due_at asc')
            ->get();

        return view('portal.assignments.index', [
            'groups' => $assignments->groupBy(fn (Assignment $a) => $a->course->program->name),
            'user'   => $user,
        ]);
    }

    public function show(Assignment $assignment)
    {
        $this->assertAccess($assignment);

        return view('portal.assignments.show', [
            'assignment' => $assignment->load('course.program', 'trainingClass'),
            'submission' => $assignment->submissionFor(auth()->user()),
        ]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $this->assertAccess($assignment);
        $user = auth()->user();

        $data = $request->validate([
            'content' => 'nullable|string|max:20000',
            'link'    => 'nullable|url|max:2048',
            'file'    => 'nullable|file|max:20480|mimes:pdf,zip,doc,docx,png,jpg,jpeg,txt,ppt,pptx',
        ]);

        if (empty($data['content']) && empty($data['link']) && ! $request->hasFile('file')) {
            return back()->withErrors(['content' => 'Add a written response, a link, or a file before submitting.']);
        }

        $existing = $assignment->submissionFor($user);
        $payload = [
            'content'      => $data['content'] ?? null,
            'link'         => $data['link'] ?? null,
            'status'       => 'submitted',
            'submitted_at' => now(),
            'score'        => null,
            'feedback'     => null,
            'graded_at'    => null,
            'graded_by'    => null,
        ];

        if ($request->hasFile('file')) {
            $payload['file_path'] = $request->file('file')->store('assignments/submissions', 'public');
        }

        if ($existing) {
            $existing->update($payload);
        } else {
            $assignment->submissions()->create($payload + ['user_id' => $user->id]);
        }

        return redirect()->route('portal.assignments.show', $assignment)
            ->with('success', 'Your work has been submitted. You will be notified once it is graded.');
    }

    protected function assertAccess(Assignment $assignment): void
    {
        $programId = $assignment->course?->training_program_id;
        abort_unless(
            $assignment->is_published && in_array($programId, auth()->user()->activeProgramIds()),
            403,
            'This assignment is not available to you.'
        );
    }
}
