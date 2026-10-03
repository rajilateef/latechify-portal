<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\TrainingProgram;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Checkout sells TrainingPrograms (a portal enrolment needs one), while the
 * marketing site advertises Courses. Any active course without a program is
 * therefore unbuyable. This links the two — matching an existing program by
 * name where possible, and creating one from the course's own details otherwise.
 */
class SyncCourseProgramsCommand extends Command
{
    protected $signature = 'portal:sync-programs
                            {--dry-run : Show what would change without writing anything}';

    protected $description = 'Give every active course a portal training program so it can be sold at checkout';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $linked = $created = 0;

        $courses = Course::where('is_active', true)->orderBy('sort_order')->get();

        if ($courses->isEmpty()) {
            $this->warn('No active courses found.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($courses as $course) {
            if ($course->training_program_id && $course->trainingProgram) {
                $rows[] = [$course->title, $course->trainingProgram->name, 'already linked'];

                continue;
            }

            if ($program = $this->matchExisting($course)) {
                $dry || $course->update(['training_program_id' => $program->id]);
                $linked++;
                $rows[] = [$course->title, $program->name, 'linked to existing'];

                continue;
            }

            $program = $this->buildProgram($course, $dry);
            $dry || $course->update(['training_program_id' => $program->id]);
            $created++;
            $rows[] = [$course->title, $program->name, 'program created'];
        }

        $this->table(['Course', 'Portal program', 'Action'], $rows);

        $this->newLine();
        $this->info(($dry ? '[dry run] ' : '')."Linked {$linked}, created {$created}.");

        if ($created > 0) {
            $this->warn('New programs carry the course fee and duration but have NO courses/classes yet —');
            $this->warn('add their syllabus under Admin → Training Portal → Programs before enrolling anyone.');
        }

        return self::SUCCESS;
    }

    /** Find a program that clearly corresponds to this course (slug or normalised name). */
    protected function matchExisting(Course $course): ?TrainingProgram
    {
        $normalise = fn (string $v) => preg_replace('/[^a-z0-9]/', '', strtolower(
            // "Frontend Web Development" and "Frontend Development" should meet.
            str_ireplace([' web ', ' app ', ' and '], ' ', $v)
        ));

        $target = $normalise($course->title);

        return TrainingProgram::all()->first(
            fn (TrainingProgram $p) => $normalise($p->name) === $target || $p->slug === $course->slug
        );
    }

    /** Create a program seeded from the course's own advertised details. */
    protected function buildProgram(Course $course, bool $dry): TrainingProgram
    {
        $attributes = [
            'name'           => $course->title,
            'slug'           => $this->uniqueSlug($course->slug ?: Str::slug($course->title)),
            'description'    => $course->description ?: $course->subtitle,
            'duration_weeks' => $this->weeksFrom($course->duration),
            // The advertised online price is the standard fee; SIWES falls back to it until set.
            'fee'            => (int) ($course->price_online ?: $course->price_physical),
            'siwes_fee'      => 0,
            // Carry any promotion across so the site and checkout agree on the price.
            'discount_fee'   => $course->discountFor('online') ?? $course->discountFor('physical'),
            'is_active'      => true,
            'sort_order'     => (int) $course->sort_order,
        ];

        return $dry ? new TrainingProgram($attributes) : TrainingProgram::create($attributes);
    }

    protected function uniqueSlug(string $slug): string
    {
        $base = $slug;
        $i = 2;

        while (TrainingProgram::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** "8 weeks" / "3 months" / "12" → a week count, defaulting to 12. */
    protected function weeksFrom(?string $duration): int
    {
        if (! $duration || ! preg_match('/(\d+)/', $duration, $m)) {
            return 12;
        }

        $n = (int) $m[1];

        return str_contains(strtolower($duration), 'month') ? $n * 4 : max(1, $n);
    }
}
