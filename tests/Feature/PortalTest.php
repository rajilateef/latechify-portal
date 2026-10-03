<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\CourseMaterial;
use App\Models\Enrollment;
use App\Models\TrainingClass;
use App\Models\TrainingCourse;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalTest extends TestCase
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

    public function test_guest_is_redirected_to_portal_login(): void
    {
        $this->get('/portal')->assertRedirect(route('portal.login'));
    }

    public function test_active_student_sees_their_program_on_dashboard(): void
    {
        $this->actingAs($this->student())
            ->get('/portal')
            ->assertOk()
            ->assertSee('Frontend Development')
            ->assertSee('Classes completed');
    }

    public function test_inactive_student_cannot_sign_in(): void
    {
        User::create([
            'name' => 'Inactive', 'email' => 'inactive@example.com',
            'password' => Hash::make('secret123'), 'is_student' => true, 'is_active' => false,
        ]);

        $this->post(route('portal.login.attempt'), ['email' => 'inactive@example.com', 'password' => 'secret123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_student_can_open_a_class_in_their_program(): void
    {
        $class = TrainingClass::whereHas('course.program', fn ($q) => $q->where('slug', 'frontend-development'))->first();

        $this->actingAs($this->student())
            ->get(route('portal.class', $class))
            ->assertOk()
            ->assertSee('Slides &amp; materials', false);
    }

    public function test_student_cannot_open_a_class_they_are_not_enrolled_in(): void
    {
        $class = TrainingClass::whereHas('course.program', fn ($q) => $q->where('slug', 'backend-development'))->first();

        $this->actingAs($this->student())
            ->get(route('portal.class', $class))
            ->assertForbidden();
    }

    public function test_student_can_request_enrolment_in_another_program(): void
    {
        $backend = TrainingProgram::where('slug', 'backend-development')->first();

        $this->actingAs($this->student())
            ->post(route('portal.enroll.store'), ['training_program_id' => $backend->id])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student()->id,
            'training_program_id' => $backend->id,
            'status' => 'pending',
        ]);
    }

    public function test_dashboard_shows_fees_and_attendance_stats(): void
    {
        $this->actingAs($this->student())
            ->get('/portal')
            ->assertOk()
            ->assertSee('Outstanding')
            ->assertSee('Attendance')
            ->assertSee('₦100,000', false);
    }

    public function test_student_can_submit_an_assignment(): void
    {
        $student = $this->student();
        $assignment = Assignment::whereHas('course', fn ($q) => $q->where('training_program_id',
            TrainingProgram::where('slug', 'frontend-development')->value('id')))
            ->where('title', 'like', 'HTML%')->first();

        $this->actingAs($student)->get(route('portal.assignments.show', $assignment))->assertOk();

        $this->actingAs($student)
            ->post(route('portal.assignments.submit', $assignment), ['link' => 'https://github.com/demo/html-task'])
            ->assertRedirect(route('portal.assignments.show', $assignment));

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'status'        => 'submitted',
            'link'          => 'https://github.com/demo/html-task',
        ]);
    }

    public function test_student_cannot_access_assignment_outside_their_program(): void
    {
        $backendCourse = TrainingCourse::whereHas('program', fn ($q) => $q->where('slug', 'backend-development'))->first();
        $assignment = Assignment::create([
            'training_course_id' => $backendCourse->id,
            'title'              => 'Backend-only task',
            'max_score'          => 100,
            'is_published'       => true,
        ]);

        $this->actingAs($this->student())
            ->get(route('portal.assignments.show', $assignment))
            ->assertForbidden();
    }

    public function test_schedule_and_fees_pages_render(): void
    {
        $this->actingAs($this->student())->get(route('portal.schedule'))
            ->assertOk()->assertSee('HTML deep-dive workshop');

        $this->actingAs($this->student())->get(route('portal.fees'))
            ->assertOk()->assertSee('₦100,000', false);
    }

    public function test_student_can_update_profile(): void
    {
        $this->actingAs($this->student())
            ->put(route('portal.profile.update'), ['name' => 'Updated Name', 'phone' => '08011112222'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $this->student()->id, 'name' => 'Updated Name']);
    }

    public function test_student_can_change_password(): void
    {
        $this->actingAs($this->student())
            ->put(route('portal.profile.password'), [
                'current_password'      => 'password',
                'password'              => 'new-secret-123',
                'password_confirmation' => 'new-secret-123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secret-123', $this->student()->fresh()->password));
    }

    public function test_notifications_and_certificates_pages_render(): void
    {
        $this->actingAs($this->student())->get(route('portal.notifications'))->assertOk();
        $this->actingAs($this->student())->get(route('portal.certificates'))->assertOk()->assertSee('achievements');
    }

    public function test_course_page_lists_accessible_materials(): void
    {
        $html = TrainingCourse::where('name', 'HTML')->firstOrFail();

        $this->actingAs($this->student())
            ->get(route('portal.course', $html))
            ->assertOk()
            ->assertSee('Course documents')
            ->assertSee('HTML tags cheatsheet');
    }

    public function test_enrolled_student_can_download_an_open_material(): void
    {
        $material = CourseMaterial::where('title', 'HTML tags cheatsheet')->firstOrFail();

        $this->actingAs($this->student())
            ->get(route('portal.materials.download', $material))
            ->assertOk();
    }

    public function test_non_enrolled_student_cannot_download_material(): void
    {
        $material = CourseMaterial::where('title', 'HTML tags cheatsheet')->firstOrFail();
        $stranger = User::create([
            'name' => 'Stranger', 'email' => 'stranger@example.com',
            'password' => Hash::make('secret123'), 'is_student' => true, 'is_active' => true,
        ]);

        $this->actingAs($stranger)
            ->get(route('portal.materials.download', $material))
            ->assertForbidden();
    }

    public function test_restricted_material_only_serves_selected_trainees(): void
    {
        $bonus = CourseMaterial::where('title', 'Bonus: Advanced Git guide')->firstOrFail();
        $cheat = CourseMaterial::where('title', 'HTML tags cheatsheet')->firstOrFail();

        // The demo student is on the allow-list.
        $this->actingAs($this->student())->get(route('portal.materials.download', $bonus))->assertOk();

        // Another trainee, enrolled in the same program but NOT on the allow-list.
        $fe = TrainingProgram::where('slug', 'frontend-development')->firstOrFail();
        $other = User::create([
            'name' => 'Other Trainee', 'email' => 'other-trainee@example.com',
            'password' => Hash::make('secret123'), 'is_student' => true, 'is_active' => true,
        ]);
        Enrollment::create([
            'user_id' => $other->id, 'training_program_id' => $fe->id, 'status' => 'active',
            'started_at' => now(), 'ends_at' => now()->addWeeks(16), 'approved_at' => now(),
        ]);

        // Blocked from the restricted file, but allowed the open one.
        $this->actingAs($other)->get(route('portal.materials.download', $bonus))->assertForbidden();
        $this->actingAs($other)->get(route('portal.materials.download', $cheat))->assertOk();
    }
}
