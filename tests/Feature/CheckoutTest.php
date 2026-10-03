<?php

namespace Tests\Feature;

use App\Mail\PortalCredentialsMail;
use App\Models\CheckoutOrder;
use App\Models\Course;
use App\Models\FeePayment;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Services\Monnify;
use App\Services\PortalProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
    }

    protected function program(): TrainingProgram
    {
        return TrainingProgram::where('slug', 'frontend-development')->firstOrFail();
    }

    protected function course(): Course
    {
        return Course::where('slug', 'frontend-web-development')->firstOrFail();
    }

    /* ── Reference format ── */

    public function test_payment_references_are_always_latechify_prefixed(): void
    {
        $this->assertStringStartsWith('LATECHIFY-CHK-', Monnify::reference('CHK', 12));
        $this->assertStringStartsWith('LATECHIFY-FEE-', Monnify::reference('FEE', 3));
        $this->assertStringStartsWith('LATECHIFY-CAMP-', Monnify::reference('CAMP', 9));
    }

    /* ── Checkout page ── */

    public function test_checkout_page_lists_the_sellable_courses(): void
    {
        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Frontend Web Development')
            ->assertSee('Checkout');
    }

    public function test_checkout_offers_an_online_physical_format_toggle(): void
    {
        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Class format')
            ->assertSee('On-campus')
            ->assertSee('Online');
    }

    public function test_checkout_creates_an_order_with_a_prefixed_reference(): void
    {
        $course = $this->course();

        // No Monnify credentials in the test env, so it falls back to bank transfer.
        Livewire::test(\App\Livewire\CheckoutForm::class)
            ->set('full_name', 'Ada Lovelace')
            ->set('email', 'ada@example.com')
            ->set('phone', '08011111111')
            ->set('course', $course->slug)
            ->set('format', 'online')
            ->set('payment_method', 'transfer')
            ->call('submit');

        $order = CheckoutOrder::where('email', 'ada@example.com')->firstOrFail();

        $this->assertSame('pending', $order->status);
        $this->assertSame('enrolment', $order->kind);
        $this->assertSame('online', $order->format);
        $this->assertSame($course->id, $order->course_id);
        $this->assertSame($course->training_program_id, $order->training_program_id);
        $this->assertSame($course->payablePriceFor('online'), $order->amount);
        $this->assertStringStartsWith('LATECHIFY-', $order->payment_reference);
    }

    public function test_the_chosen_format_sets_the_price(): void
    {
        $course = $this->course();

        Livewire::test(\App\Livewire\CheckoutForm::class, ['selectedSlug' => $course->slug])
            ->set('format', 'online')
            ->assertSet('format', 'online')
            ->set('full_name', 'Grace Hopper')
            ->set('email', 'physical@example.com')
            ->set('phone', '08022222222')
            ->set('format', 'physical')
            ->set('payment_method', 'transfer')
            ->call('submit');

        $order = CheckoutOrder::where('email', 'physical@example.com')->firstOrFail();

        $this->assertSame('physical', $order->format);
        $this->assertSame($course->payablePriceFor('physical'), $order->amount);
        $this->assertNotSame($course->payablePriceFor('online'), $order->amount);
    }

    public function test_the_bank_transfer_page_shows_the_reference_to_quote(): void
    {
        $order = $this->paidOrder(status: 'pending');

        $this->get(route('checkout.transfer', $order))
            ->assertOk()
            ->assertSee($order->payment_reference)
            ->assertSee($order->itemName());
    }

    public function test_every_sellable_course_is_offered_at_checkout(): void
    {
        $response = $this->get('/checkout')->assertOk();

        foreach (Course::active()->get() as $course) {
            if ($course->trainingProgram?->is_active) {
                $response->assertSee($course->title, false);
            }
        }
    }

    public function test_a_course_slug_in_the_url_preselects_that_course(): void
    {
        $course = Course::active()->whereNotNull('training_program_id')->orderByDesc('id')->firstOrFail();

        Livewire::test(\App\Livewire\CheckoutForm::class, ['selectedSlug' => $course->slug])
            ->assertSet('course', $course->slug);
    }

    public function test_a_legacy_program_slug_still_resolves_to_its_course(): void
    {
        $course = $this->course();

        Livewire::test(\App\Livewire\CheckoutForm::class, ['selectedSlug' => $course->trainingProgram->slug])
            ->assertSet('course', $course->slug);
    }

    public function test_a_course_with_no_portal_program_is_not_sellable(): void
    {
        $course = $this->course();
        $course->update(['training_program_id' => null]);

        // It stays browsable in the nav/catalog — it just can't be bought.
        $sellable = Livewire::test(\App\Livewire\CheckoutForm::class)->instance()->courses();

        $this->assertNull($sellable->firstWhere('slug', $course->slug));
    }

    /* ── Gateway availability ── */

    public function test_online_payment_is_offered_once_monnify_is_configured(): void
    {
        $this->configureMonnify();

        Livewire::test(\App\Livewire\CheckoutForm::class)
            ->assertSet('payment_method', 'monnify');

        $this->get('/checkout')->assertOk()->assertDontSee('Unavailable');
    }

    public function test_checkout_falls_back_to_transfer_and_says_so_when_monnify_is_unconfigured(): void
    {
        // No credentials seeded in the test env.
        Livewire::test(\App\Livewire\CheckoutForm::class)
            ->assertSet('payment_method', 'transfer');

        $this->get('/checkout')->assertOk()->assertSee('Unavailable');
    }

    protected function configureMonnify(): void
    {
        \App\Models\Setting::put('monnify_api_key', 'MK_TEST_KEY', 'payment');
        \App\Models\Setting::put('monnify_secret_key', 'MK_TEST_SECRET', 'payment');
        \App\Models\Setting::put('monnify_contract_code', '1234567890', 'payment');
    }

    /* ── Format carries through from the page the visitor came from ── */

    public function test_course_links_carry_the_chosen_format(): void
    {
        $course = $this->course();

        $this->assertStringContainsString('format=online', $course->checkoutUrl('online'));
        $this->assertStringContainsString('format=physical', $course->checkoutUrl('physical'));
        $this->assertStringNotContainsString('format=', $course->checkoutUrl());
    }

    public function test_the_courses_page_links_each_button_to_its_own_format(): void
    {
        // The href is HTML-escaped (& → &amp;), so assert on the query parameters.
        $this->get('/courses')
            ->assertOk()
            ->assertSee('format=online', false)
            ->assertSee('format=physical', false);
    }

    public function test_the_pricing_page_links_each_tab_to_its_own_format(): void
    {
        $this->get('/pricing')
            ->assertOk()
            ->assertSee('format=online', false)
            ->assertSee('format=physical', false);
    }

    public function test_the_course_page_lets_the_visitor_pick_a_format_before_checkout(): void
    {
        $course = $this->course();

        $this->get('/courses/'.$course->slug)
            ->assertOk()
            ->assertSee('Class format')
            ->assertSee('format=online', false)
            ->assertSee('format=physical', false);
    }

    public function test_a_format_in_the_url_is_preselected_at_checkout(): void
    {
        $course = $this->course();

        Livewire::test(\App\Livewire\CheckoutForm::class, [
            'selectedSlug'   => $course->slug,
            'selectedFormat' => 'physical',
        ])->assertSet('format', 'physical')->assertSet('course', $course->slug);

        Livewire::test(\App\Livewire\CheckoutForm::class, [
            'selectedSlug'   => $course->slug,
            'selectedFormat' => 'online',
        ])->assertSet('format', 'online');
    }

    public function test_an_invalid_format_falls_back_to_online(): void
    {
        Livewire::test(\App\Livewire\CheckoutForm::class, ['selectedFormat' => 'nonsense'])
            ->assertSet('format', 'online');
    }

    public function test_the_checkout_page_honours_the_format_query_parameter(): void
    {
        $course = $this->course();
        $course->update(['price_online' => 250000, 'price_physical' => 350000]);

        $this->get(route('checkout', ['course' => $course->slug, 'format' => 'physical']))
            ->assertOk()
            ->assertSee('350,000');
    }

    /* ── Super-admin confirmation creates the portal ── */

    public function test_confirming_a_paid_order_provisions_the_portal(): void
    {
        $order = $this->paidOrder();

        $result = app(PortalProvisioner::class)->confirm($order, User::where('is_admin', true)->first());

        $user = $result['user'];
        $enrollment = $result['enrollment']->fresh();

        $this->assertTrue($user->is_student);
        $this->assertTrue($user->is_active);
        $this->assertSame('grace@example.com', $user->email);

        $this->assertSame('active', $enrollment->status);
        $this->assertSame($this->program()->id, $enrollment->training_program_id);
        $this->assertNotNull($enrollment->started_at);

        // The money paid is on the ledger, with a receipt.
        $payment = $enrollment->payments()->first();
        $this->assertSame($order->amount, $payment->amount);
        $this->assertSame('monnify', $payment->method);
        $this->assertSame($order->payment_reference, $payment->reference);
        $this->assertNotNull($payment->receipt);

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame($user->id, $order->fresh()->user_id);

        Mail::assertSent(PortalCredentialsMail::class, fn ($mail) => $mail->hasTo('grace@example.com'));
    }

    public function test_the_new_trainee_can_sign_in_to_the_portal_after_confirmation(): void
    {
        $order = $this->paidOrder();

        $result = app(PortalProvisioner::class)->confirm($order);

        $this->post(route('portal.login.attempt'), [
            'email'    => 'grace@example.com',
            'password' => $result['password'],
        ])->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs($result['user']);
    }

    public function test_confirming_twice_does_not_double_credit_the_fee(): void
    {
        $order = $this->paidOrder();
        $provisioner = app(PortalProvisioner::class);

        $provisioner->confirm($order);
        $provisioner->confirm($order->fresh());

        $this->assertSame(1, FeePayment::where('reference', $order->payment_reference)->count());
    }

    public function test_an_unpaid_order_does_not_create_a_portal_by_itself(): void
    {
        $order = $this->paidOrder(status: 'pending');

        $this->assertFalse($order->isPaid());
        $this->assertNull(User::where('email', 'grace@example.com')->first());
    }

    public function test_confirmation_reuses_an_existing_account_instead_of_duplicating_it(): void
    {
        $existing = User::where('email', 'student@latechify.test')->firstOrFail();
        $order = $this->paidOrder();
        $order->update(['email' => $existing->email]);

        $result = app(PortalProvisioner::class)->confirm($order->fresh());

        $this->assertSame($existing->id, $result['user']->id);
        $this->assertNull($result['password'], 'An existing account keeps its own password.');
        $this->assertSame(1, User::where('email', $existing->email)->count());
    }

    /* Webhook behaviour is covered end to end in MonnifyWebhookTest. */

    /* ── Helpers ── */

    protected function paidOrder(string $status = 'paid'): CheckoutOrder
    {
        $program = $this->program();
        $course = $this->course();

        return CheckoutOrder::create([
            'kind'                  => 'enrolment',
            'full_name'             => 'Grace Hopper',
            'email'                 => 'grace@example.com',
            'phone'                 => '08022222222',
            'course_id'             => $course->id,
            'course_name'           => $course->title,
            'training_program_id'   => $program->id,
            'program_name'          => $program->name,
            'format'                => 'online',
            'type'                  => 'full_time',
            'amount'                => $course->payablePriceFor('online'),
            'payment_method'        => 'monnify',
            'payment_reference'     => Monnify::reference('CHK', 1),
            'transaction_reference' => 'MNFY|TEST|'.uniqid(),
            'status'                => $status,
            'paid_at'               => $status === 'paid' ? now() : null,
        ]);
    }
}
