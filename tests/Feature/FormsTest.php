<?php

namespace Tests\Feature;

use App\Livewire\CampRegistrationForm;
use App\Livewire\ContactForm;
use App\Livewire\VerifyCertificate;
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

    /* ── The apply/Paystack flow was retired on 2026-10-01 — it now redirects into Checkout. ── */

    public function test_the_retired_apply_url_redirects_to_checkout(): void
    {
        $this->get('/apply')->assertRedirect(route('checkout'));
    }

    public function test_the_retired_apply_url_carries_the_course_through_to_checkout(): void
    {
        $course = Course::where('slug', 'frontend-web-development')->firstOrFail();

        $this->get('/apply?course='.$course->slug)
            ->assertRedirect(route('checkout', ['course' => $course->slug]));
    }

    public function test_an_unknown_course_on_the_retired_url_still_lands_on_checkout(): void
    {
        $this->get('/apply?course=does-not-exist')->assertRedirect(route('checkout'));
    }

    public function test_no_new_applications_can_be_created(): void
    {
        $this->assertFalse(\App\Filament\Resources\ApplicationResource::canCreate());
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

    public function test_an_unverified_camp_webhook_cannot_mark_a_registration_paid(): void
    {
        // The endpoint accepts unsigned calls, but confirmation is re-checked with
        // Monnify — see MonnifyWebhookTest for the full contract.
        $this->postJson('/summer-coding-camp/payment/webhook', [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => ['paymentReference' => 'LATECHIFY-CAMP-1-ABC', 'paymentStatus' => 'PAID', 'amountPaid' => 70000],
        ])->assertOk();

        $this->assertDatabaseMissing('camp_registrations', ['status' => 'paid']);
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
