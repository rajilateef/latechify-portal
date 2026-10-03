<?php

namespace Tests\Feature;

use App\Models\CourseMaterial;
use App\Models\TrainingCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompositeProgramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function fullstackStudent(): User
    {
        return User::where('email', 'fullstack@latechify.test')->firstOrFail();
    }

    protected function frontendStudent(): User
    {
        return User::where('email', 'student@latechify.test')->firstOrFail();
    }

    protected function courseIn(string $programSlug): TrainingCourse
    {
        return TrainingCourse::whereHas('program', fn ($q) => $q->where('slug', $programSlug))->firstOrFail();
    }

    public function test_bundle_student_can_open_courses_from_both_included_programs(): void
    {
        $frontend = $this->courseIn('frontend-development');
        $backend = $this->courseIn('backend-development');

        $this->actingAs($this->fullstackStudent())
            ->get(route('portal.course', $frontend))->assertOk()->assertSee($frontend->name);

        $this->actingAs($this->fullstackStudent())
            ->get(route('portal.course', $backend))->assertOk()->assertSee($backend->name);
    }

    public function test_bundle_program_page_groups_both_programs(): void
    {
        $fullstack = \App\Models\TrainingProgram::where('slug', 'fullstack-development')->firstOrFail();

        $this->actingAs($this->fullstackStudent())
            ->get(route('portal.program', $fullstack))
            ->assertOk()
            ->assertSee('Frontend Development')
            ->assertSee('Backend Development')
            ->assertSee('Included program');
    }

    public function test_bundle_progress_aggregates_across_included_programs(): void
    {
        $enrollment = $this->fullstackStudent()->activeEnrollments()->first();
        $fullstack = \App\Models\TrainingProgram::where('slug', 'fullstack-development')->firstOrFail();

        // Total classes span both programs, not just the (empty) bundle itself.
        $this->assertSame($fullstack->effectiveClassesCount(), $enrollment->totalClasses());
        $this->assertGreaterThan(50, $enrollment->totalClasses());
        $this->assertSame(9, $enrollment->completedClassesCount());
    }

    public function test_bundle_student_can_access_an_included_programs_material(): void
    {
        $material = CourseMaterial::where('title', 'HTML tags cheatsheet')->firstOrFail(); // a Frontend material

        $this->assertTrue($material->canBeAccessedBy($this->fullstackStudent()));

        $this->actingAs($this->fullstackStudent())
            ->get(route('portal.materials.download', $material))->assertOk();
    }

    public function test_a_single_program_student_still_cannot_access_another_program(): void
    {
        $backend = $this->courseIn('backend-development');

        // The Frontend-only student is NOT in a bundle → no Backend access.
        $this->actingAs($this->frontendStudent())
            ->get(route('portal.course', $backend))->assertForbidden();
    }
}
