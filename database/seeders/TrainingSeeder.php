<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\ClassCompletion;
use App\Models\CourseMaterial;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\MaterialFile;
use App\Models\MaterialFolder;
use App\Models\TrainingClass;
use App\Models\TrainingCourse;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TrainingSeeder extends Seeder
{
    public function run(): void
    {
        // ── Programs → Courses → Classes → Resources ──
        $blueprint = [
            'Frontend Development' => [
                'duration' => 16,
                'fee'      => 250000,
                'siwes'    => 120000,
                'desc'     => 'Become a job-ready frontend engineer — from version control to building interactive React apps.',
                'courses'  => [
                    ['Git & GitHub', 'GitBranch', 4],
                    ['HTML', 'Code', 10],
                    ['CSS', 'Palette', 8],
                    ['JavaScript', 'Braces', 12],
                    ['React', 'Atom', 10],
                ],
            ],
            'Backend Development' => [
                'duration' => 16,
                'fee'      => 200000,
                'siwes'    => 100000,
                'desc'     => 'Build robust server-side applications and APIs with PHP, Laravel and databases.',
                'courses'  => [
                    ['PHP Fundamentals', 'Code', 8],
                    ['Databases & SQL', 'Database', 6],
                    ['Laravel', 'Server', 12],
                ],
            ],
        ];

        $programs = [];
        foreach ($blueprint as $name => $spec) {
            $program = TrainingProgram::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $spec['desc'], 'duration_weeks' => $spec['duration'], 'fee' => $spec['fee'], 'siwes_fee' => $spec['siwes'] ?? 0, 'is_active' => true, 'sort_order' => count($programs) + 1],
            );
            $programs[$name] = $program;

            foreach ($spec['courses'] as $ci => [$courseName, $icon, $classCount]) {
                $course = TrainingCourse::updateOrCreate(
                    ['training_program_id' => $program->id, 'slug' => Str::slug($courseName)],
                    ['name' => $courseName, 'icon' => $icon, 'description' => "Master {$courseName} through hands-on classes.", 'sort_order' => $ci + 1],
                );

                for ($n = 1; $n <= $classCount; $n++) {
                    $class = TrainingClass::updateOrCreate(
                        ['training_course_id' => $course->id, 'sort_order' => $n],
                        ['title' => "{$courseName} — Class {$n}", 'description' => "Class {$n}: concepts, live demo and a practical exercise."],
                    );

                    // Each class gets a slide deck plus additional link resources
                    // (admins can also upload files to the private disk).
                    $class->resources()->delete();
                    $class->resources()->createMany([
                        ['title' => 'Class slides', 'category' => 'Slides', 'type' => 'link',
                            'description' => "The deck used in class {$n} — walk back through the live demo at your own pace.",
                            'url' => 'https://docs.google.com/presentation/d/DEMO/edit', 'sort_order' => 1],
                        ['title' => 'Reference notes', 'category' => 'Reading', 'type' => 'link',
                            'description' => 'Official documentation for the concepts covered in this class.',
                            'url' => 'https://developer.mozilla.org/', 'sort_order' => 2],
                        ['title' => 'Practice exercises', 'category' => 'Exercise', 'type' => 'link',
                            'description' => 'Work through these before the next class to lock the concepts in.',
                            'url' => 'https://www.frontendmentor.io/challenges', 'sort_order' => 3],
                    ]);
                }
            }
        }

        // ── Composite (bundle) program: Fullstack = Frontend + Backend ──
        $fullstack = TrainingProgram::updateOrCreate(
            ['slug' => 'fullstack-development'],
            ['name' => 'Fullstack Development', 'description' => 'The complete path — combines the Frontend and Backend programs into one bundle.',
                'duration_weeks' => 28, 'fee' => 400000, 'siwes_fee' => 200000, 'is_active' => true, 'sort_order' => 3],
        );
        $fullstack->components()->sync([
            $programs['Frontend Development']->id => ['sort_order' => 1],
            $programs['Backend Development']->id  => ['sort_order' => 2],
        ]);
        $programs['Fullstack Development'] = $fullstack;

        // ── Assignments (one per Frontend course, pinned to its first class) ──
        $fe = $programs['Frontend Development'];
        foreach ($fe->courses as $i => $course) {
            $firstClass = $course->classes()->first();
            Assignment::updateOrCreate(
                ['training_course_id' => $course->id, 'title' => "{$course->name} practical task"],
                [
                    'training_class_id' => $firstClass?->id,
                    'instructions'      => "Complete the {$course->name} exercise covered in class. Submit a link to your work (GitHub / CodePen) or upload your files.",
                    'due_at'            => now()->addDays(($i + 1) * 3),
                    'max_score'         => 100,
                    'is_published'      => true,
                    'sort_order'        => $i + 1,
                ],
            );
        }

        // ── Demo student (admin-created account) ──
        $student = User::updateOrCreate(
            ['email' => 'student@latechify.test'],
            [
                'name'       => 'Demo Student',
                'phone'      => '08000000000',
                'bio'        => 'Aspiring frontend engineer learning at Latechify.',
                'password'   => Hash::make('password'),
                'is_admin'   => false,
                'is_student' => true,
                'is_active'  => true,
            ],
        );

        // Active enrolment in Frontend Development, started 4 weeks ago, with a part-paid fee.
        $enrollment = Enrollment::updateOrCreate(
            ['user_id' => $student->id, 'training_program_id' => $fe->id],
            [
                'status'      => 'active',
                'fee_amount'  => $fe->fee,
                'started_at'  => now()->subWeeks(4),
                'ends_at'     => now()->subWeeks(4)->addWeeks($fe->duration_weeks),
                'approved_at' => now()->subWeeks(4),
            ],
        );

        // Two part-payments → leaves an outstanding balance.
        $admin = User::where('is_admin', true)->first();
        $enrollment->payments()->delete();
        $enrollment->payments()->createMany([
            ['user_id' => $student->id, 'amount' => 100000, 'method' => 'bank_transfer', 'reference' => 'TRF-0001', 'paid_at' => now()->subWeeks(4), 'recorded_by' => $admin?->id],
            ['user_id' => $student->id, 'amount' => 50000, 'method' => 'cash', 'reference' => null, 'paid_at' => now()->subWeeks(1), 'recorded_by' => $admin?->id],
        ]);

        // Mark the first 6 classes of the program complete for the student.
        foreach (array_slice($fe->classIds(), 0, 6) as $classId) {
            ClassCompletion::updateOrCreate(
                ['user_id' => $student->id, 'training_class_id' => $classId],
                ['completed_at' => now()->subDays(rand(1, 20)), 'marked_by' => $admin?->id],
            );
        }

        // A graded submission on the Git & GitHub assignment.
        $gitAssignment = Assignment::whereHas('course', fn ($q) => $q->where('name', 'Git & GitHub'))->first();
        if ($gitAssignment) {
            AssignmentSubmission::updateOrCreate(
                ['assignment_id' => $gitAssignment->id, 'user_id' => $student->id],
                [
                    'link'         => 'https://github.com/demo/first-repo',
                    'content'      => 'Here is my repository with the branching exercise completed.',
                    'status'       => 'graded',
                    'score'        => 88,
                    'feedback'     => 'Great work! Clean commit history. Next time, add a descriptive README.',
                    'submitted_at' => now()->subDays(10),
                    'graded_at'    => now()->subDays(8),
                    'graded_by'    => $admin?->id,
                ],
            );
        }

        // ── Live sessions + attendance ──
        $htmlCourse = $fe->courses->firstWhere('name', 'HTML');
        $sessions = [
            ['title' => 'Kickoff & environment setup', 'starts_at' => now()->subWeeks(3)->setTime(18, 0), 'ends_at' => now()->subWeeks(3)->setTime(19, 30), 'att' => 'present', 'course' => null],
            ['title' => 'HTML deep-dive workshop', 'starts_at' => now()->subDays(5)->setTime(18, 0), 'ends_at' => now()->subDays(5)->setTime(19, 30), 'att' => 'late', 'course' => $htmlCourse?->id],
            ['title' => 'Live Q&A: CSS layouts', 'starts_at' => now()->addDays(2)->setTime(18, 0), 'ends_at' => now()->addDays(2)->setTime(19, 30), 'att' => null, 'course' => null],
            ['title' => 'JavaScript project clinic', 'starts_at' => now()->addDays(6)->setTime(18, 0), 'ends_at' => now()->addDays(6)->setTime(19, 30), 'att' => null, 'course' => null],
        ];
        foreach ($sessions as $s) {
            $session = LiveSession::updateOrCreate(
                ['training_program_id' => $fe->id, 'title' => $s['title']],
                [
                    'training_course_id' => $s['course'],
                    'description'        => 'Interactive live session with your instructor.',
                    'starts_at'          => $s['starts_at'],
                    'ends_at'            => $s['ends_at'],
                    'join_url'           => 'https://meet.google.com/demo-session',
                    'location'           => 'Online (Google Meet)',
                ],
            );

            if ($s['att']) {
                Attendance::updateOrCreate(
                    ['live_session_id' => $session->id, 'user_id' => $student->id],
                    ['status' => $s['att'], 'marked_by' => $admin?->id],
                );
            }
        }

        // ── Media library: every course gets a folder with MANY documents ──
        // Structure: Program folder → per-course subfolder → several docs (files + links),
        // each attached to its course so enrolled trainees see many materials per course.
        $gitCourse = $fe->courses->firstWhere('name', 'Git & GitHub');
        $disk = \Illuminate\Support\Facades\Storage::disk('local');

        $feFolder = MaterialFolder::updateOrCreate(['name' => 'Frontend Development', 'parent_id' => null], ['sort_order' => 1]);

        // course name => [ [title, kind(file|link), body-or-url], ... ]
        $library = [
            'Git & GitHub' => [
                ['Git basics cheatsheet', 'file', 'init, clone, add, commit, push, pull, status, log — the everyday commands.'],
                ['GitHub workflow guide', 'file', 'Fork → branch → commit → pull request → review → merge.'],
                ['Branching & merging explained', 'file', 'Feature branches, fast-forward vs 3-way merge, resolving conflicts.'],
                ['Pull request checklist', 'file', 'Small PRs, clear title, linked issue, tests pass, request a reviewer.'],
                ['Pro Git (free book)', 'link', 'https://git-scm.com/book/en/v2'],
                ['GitHub Docs', 'link', 'https://docs.github.com/en'],
            ],
            'HTML' => [
                ['HTML tags cheatsheet', 'file', 'Common tags: div, span, h1-h6, p, a, img, ul, ol, li, form, input. Semantic: header, nav, main, section, article, footer.'],
                ['Semantic HTML guide', 'file', 'Use the right element for the job — improves accessibility and SEO.'],
                ['Forms & inputs reference', 'file', 'input types, labels, validation attributes, fieldset & legend.'],
                ['MDN: HTML elements reference', 'link', 'https://developer.mozilla.org/en-US/docs/Web/HTML/Element'],
                ['Web accessibility basics (W3C)', 'link', 'https://www.w3.org/WAI/fundamentals/'],
            ],
            'CSS' => [
                ['CSS selectors cheatsheet', 'file', 'Type, class, id, attribute, pseudo-classes, combinators and specificity.'],
                ['Flexbox guide', 'file', 'flex-direction, justify-content, align-items, flex-grow/shrink/basis.'],
                ['CSS Grid guide', 'file', 'grid-template-columns/rows, gap, areas, and responsive tracks.'],
                ['Responsive design & media queries', 'file', 'Mobile-first breakpoints, fluid units, and container widths.'],
                ['MDN: CSS reference', 'link', 'https://developer.mozilla.org/en-US/docs/Web/CSS/Reference'],
            ],
            'JavaScript' => [
                ['JavaScript basics notes', 'file', 'Variables, types, operators, conditionals, loops and functions.'],
                ['Arrays & objects reference', 'file', 'map, filter, reduce, find; object destructuring and spread.'],
                ['DOM manipulation guide', 'file', 'querySelector, events, creating/updating/removing nodes.'],
                ['Async JS: promises & fetch', 'file', 'Promises, async/await, and calling APIs with fetch().'],
                ['JavaScript.info tutorial', 'link', 'https://javascript.info/'],
            ],
            'React' => [
                ['React components & props', 'file', 'Function components, JSX, props and composition.'],
                ['Hooks cheatsheet', 'file', 'useState, useEffect, useMemo, useRef — rules of hooks.'],
                ['State management basics', 'file', 'Lifting state up, context, and when to reach for a store.'],
                ['React official docs', 'link', 'https://react.dev/learn'],
            ],
        ];

        $categoryFor = function (string $title, string $kind): string {
            $t = strtolower($title);

            return match (true) {
                $kind === 'link'                 => 'Reading',
                str_contains($t, 'cheatsheet')   => 'Cheatsheet',
                str_contains($t, 'checklist')    => 'Checklist',
                str_contains($t, 'reference')    => 'Reference',
                str_contains($t, 'guide')        => 'Guide',
                default                          => 'Notes',
            };
        };

        $folderSort = 1;
        foreach ($library as $courseName => $docs) {
            $course = $fe->courses->firstWhere('name', $courseName);
            if (! $course) {
                continue;
            }

            $folder = MaterialFolder::updateOrCreate(
                ['name' => $courseName, 'parent_id' => $feFolder->id],
                ['sort_order' => $folderSort++],
            );

            $order = 1;
            foreach ($docs as [$title, $kind, $payload]) {
                if ($kind === 'file') {
                    $path = 'material-library/'.\Illuminate\Support\Str::slug($courseName.' '.$title).'.txt';
                    $disk->put($path, $title."\n".str_repeat('=', min(strlen($title), 60))."\n".$payload."\n");
                    $file = MaterialFile::updateOrCreate(
                        ['title' => $title],
                        ['material_folder_id' => $folder->id, 'kind' => 'file', 'disk' => 'local',
                            'file_path' => $path, 'file_name' => basename($path), 'uploaded_by' => $admin?->id],
                    );
                } else {
                    $file = MaterialFile::updateOrCreate(
                        ['title' => $title],
                        ['material_folder_id' => $folder->id, 'kind' => 'link', 'url' => $payload, 'uploaded_by' => $admin?->id],
                    );
                }

                CourseMaterial::updateOrCreate(
                    ['training_course_id' => $course->id, 'title' => $title],
                    ['material_file_id' => $file->id, 'category' => $categoryFor($title, $kind),
                        'access' => 'enrolled', 'is_published' => true, 'sort_order' => $order++, 'uploaded_by' => $admin?->id],
                );
            }
        }

        // A restricted document on Git & GitHub — only the demo student is granted access.
        if ($gitCourse) {
            $disk->put('material-library/git-advanced.txt',
                "Advanced Git (bonus)\n====================\nrebase -i, cherry-pick, reflog, bisect, worktrees.\n");
            $bonusFile = MaterialFile::updateOrCreate(
                ['title' => 'Advanced Git guide'],
                ['material_folder_id' => MaterialFolder::where('name', 'Git & GitHub')->value('id'), 'kind' => 'file', 'disk' => 'local',
                    'file_path' => 'material-library/git-advanced.txt', 'file_name' => 'git-advanced.txt', 'uploaded_by' => $admin?->id],
            );
            $bonus = CourseMaterial::updateOrCreate(
                ['training_course_id' => $gitCourse->id, 'title' => 'Bonus: Advanced Git guide'],
                ['material_file_id' => $bonusFile->id, 'description' => 'Extra material shared with selected trainees only.',
                    'category' => 'Guide', 'access' => 'selected', 'is_published' => true, 'sort_order' => 99, 'uploaded_by' => $admin?->id],
            );
            $bonus->allowedUsers()->syncWithoutDetaching([$student->id]);
        }

        // ── Fullstack (bundle) demo student — proves composite access to both programs' courses ──
        $fsStudent = User::updateOrCreate(
            ['email' => 'fullstack@latechify.test'],
            [
                'name'       => 'Fullstack Student',
                'phone'      => '08011112222',
                'bio'        => 'Doing the combined Fullstack bundle.',
                'password'   => Hash::make('password'),
                'is_student' => true,
                'is_active'  => true,
            ],
        );
        $fsEnrolment = Enrollment::updateOrCreate(
            ['user_id' => $fsStudent->id, 'training_program_id' => $fullstack->id],
            ['status' => 'active', 'type' => 'it_siwes', 'fee_amount' => 300000, 'fee_note' => 'IT/SIWES bundle rate',
                'started_at' => now()->subWeeks(3), 'ends_at' => now()->addWeeks(25), 'approved_at' => now()->subWeeks(3)],
        );
        $fsEnrolment->payments()->delete();
        $fsEnrolment->payments()->create(['user_id' => $fsStudent->id, 'amount' => 250000, 'method' => 'bank_transfer', 'reference' => 'FS-TRF-001', 'paid_at' => now()->subWeeks(2)]);
        // Complete a few classes across BOTH included programs so progress aggregates.
        $fsCompleted = collect($fullstack->effectiveClassIds())->take(9);
        ClassCompletion::where('user_id', $fsStudent->id)->delete();
        foreach ($fsCompleted as $classId) {
            ClassCompletion::updateOrCreate(
                ['user_id' => $fsStudent->id, 'training_class_id' => $classId],
                ['completed_at' => now()->subDays(3), 'marked_by' => $admin?->id],
            );
        }

        // ── Announcements (auto-notify active trainees via the model hook) ──
        Announcement::updateOrCreate(
            ['title' => 'Welcome to the Latechify Training Portal'],
            ['body' => "We're excited to have you. Check your dashboard for your program progress, and reach out to your mentor anytime.", 'training_program_id' => null, 'is_published' => true, 'published_at' => now()->subDays(6)],
        );
        Announcement::updateOrCreate(
            ['title' => 'Frontend cohort: live project week'],
            ['body' => 'Next week is project week — you will build and deploy a landing page. Slides for each class are available under your courses.', 'training_program_id' => $fe->id, 'is_published' => true, 'published_at' => now()->subDays(2)],
        );

        // Checkout sells courses but enrols into programs, so every marketing course
        // needs one. This links the obvious pairs and creates any that are missing —
        // without it a fresh install has nothing to sell.
        \Illuminate\Support\Facades\Artisan::call('portal:sync-programs');
    }
}
