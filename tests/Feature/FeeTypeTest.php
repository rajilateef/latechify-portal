<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function student(): User
    {
        return User::where('email', 'student@latechify.test')->firstOrFail();
    }

    protected function backend(): TrainingProgram
    {
        return TrainingProgram::where('slug', 'backend-development')->firstOrFail();
    }

    public function test_program_returns_a_different_default_fee_per_trainee_type(): void
    {
        $fe = TrainingProgram::where('slug', 'frontend-development')->firstOrFail();

        $this->assertSame(250000, $fe->feeForType('full_time'));
        $this->assertSame(120000, $fe->feeForType('it_siwes'));

        // IT/SIWES falls back to the full-time fee when no SIWES price is set.
        $p = TrainingProgram::create(['name' => 'Solo', 'slug' => 'solo-prog', 'duration_weeks' => 4, 'fee' => 50000, 'siwes_fee' => 0]);
        $this->assertSame(50000, $p->feeForType('it_siwes'));
    }

    public function test_enrolment_fee_uses_type_default_but_a_custom_amount_wins(): void
    {
        $enrolment = Enrollment::create([
            'user_id' => $this->student()->id, 'training_program_id' => $this->backend()->id,
            'type' => 'it_siwes', 'status' => 'active',
        ]);

        // No explicit amount → program's IT/SIWES default.
        $this->assertSame(100000, $enrolment->feeAmount());

        // A custom per-trainee amount (e.g. a relative/discount) always wins.
        $enrolment->update(['fee_amount' => 60000, 'fee_note' => 'family discount']);
        $this->assertSame(60000, $enrolment->fresh()->feeAmount());
    }

    public function test_student_can_self_enrol_choosing_the_it_siwes_type(): void
    {
        $this->actingAs($this->student())
            ->post(route('portal.enroll.store'), ['training_program_id' => $this->backend()->id, 'type' => 'it_siwes'])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student()->id,
            'training_program_id' => $this->backend()->id,
            'type' => 'it_siwes',
            'status' => 'pending',
        ]);
    }

    public function test_seeded_bundle_demo_is_it_siwes_with_a_custom_fee(): void
    {
        $fs = User::where('email', 'fullstack@latechify.test')->firstOrFail();
        $enrolment = $fs->activeEnrollments()->first();

        $this->assertSame('it_siwes', $enrolment->type);
        $this->assertSame('IT / SIWES', $enrolment->typeLabel());
        $this->assertSame(300000, $enrolment->feeAmount());
    }
}
