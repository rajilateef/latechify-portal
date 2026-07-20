<?php

namespace Tests\Feature;

use App\Livewire\ApplyForm;
use App\Livewire\CampRegistrationForm;
use App\Livewire\ContactForm;
use App\Livewire\VerifyCertificate;
use App\Models\Application;
use App\Models\CampRegistration;
use App\Models\ContactMessage;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_contact_form_saves_message(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('message', 'I would like more information about your courses please.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $this->assertDatabaseHas('contact_messages', [
            'email'  => 'jane@example.com',
            'status' => 'new',
        ]);
    }

    public function test_apply_form_creates_application_and_redirects_to_bank_transfer_when_paystack_unconfigured(): void
    {
        $course = Course::where('slug', 'frontend-web-development')->first();

        Livewire::test(ApplyForm::class, ['selectedSlug' => $course->slug, 'classFormat' => 'online'])
            ->set('full_name', 'John Applicant')
            ->set('email', 'john@example.com')
            ->set('phone', '08012345678')
            ->set('education', 'bachelor')
            ->set('experience', 'beginner')
            ->set('motivation', 'I am very motivated to transition into a tech career and build products.')
            ->set('heard_about', 'social')
            ->set('payment_method', 'paystack')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(); // Paystack unconfigured → falls back to bank transfer

        $app = Application::where('email', 'john@example.com')->first();
        $this->assertNotNull($app);
        $this->assertSame($course->id, $app->course_id);
        $this->assertSame($course->price_online, $app->price);
        $this->assertSame('pending', $app->status);
    }

    public function test_apply_form_validates_required_fields(): void
    {
        Livewire::test(ApplyForm::class)
            ->set('full_name', '')
            ->set('email', 'not-an-email')
            ->set('motivation', 'too short')
            ->call('submit')
            ->assertHasErrors(['full_name', 'email', 'motivation']);
    }

    public function test_camp_registration_manual_creates_registration_and_redirects(): void
    {
        Livewire::test(CampRegistrationForm::class)
            ->set('full_name', 'Camp Kid')
            ->set('email', 'kid@example.com')
            ->set('phone', '08012345678')
            ->set('age_group', '13-17')
            ->set('track', 'Web Development (HTML, CSS, JS)')
            ->set('mode', 'virtual')
            ->set('experience', 'none')
            ->set('payment_method', 'manual')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect();

        $reg = CampRegistration::where('email', 'kid@example.com')->first();
        $this->assertNotNull($reg);
        $this->assertSame('manual', $reg->payment_method);
        $this->assertSame('virtual', $reg->mode);
        $this->assertSame('pending', $reg->status);
        // Virtual mode uses the virtual fee, and every registration gets a uuid.
        $this->assertSame((int) setting('camp_fee_virtual', 0), (int) $reg->amount);
        $this->assertNotNull($reg->uuid);
    }

    public function test_camp_webhook_rejects_unsigned_requests(): void
    {
        $this->postJson('/summer-coding-camp/payment/webhook', [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => ['paymentReference' => 'CAMP-1-ABC', 'paymentStatus' => 'PAID', 'amountPaid' => 70000],
        ])->assertStatus(401);
    }

    public function test_camp_registration_validates_required_fields(): void
    {
        Livewire::test(CampRegistrationForm::class)
            ->set('full_name', '')
            ->set('email', 'not-an-email')
            ->set('track', '')
            ->call('submit')
            ->assertHasErrors(['full_name', 'email', 'track', 'age_group', 'experience']);
    }

    public function test_certificate_verification(): void
    {
        Livewire::test(VerifyCertificate::class)
            ->set('certificate_id', 'LAT-2025-0001')
            ->call('verify')
            ->assertSet('searched', true)
            ->assertOk();

        Livewire::test(VerifyCertificate::class)
            ->set('certificate_id', 'NOPE-000')
            ->call('verify')
            ->assertSet('searched', true);
    }
}
