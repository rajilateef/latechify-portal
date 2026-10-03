<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\TrainingProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DiscountPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function course(): Course
    {
        return Course::where('slug', 'frontend-web-development')->firstOrFail();
    }

    /* ── Course pricing maths ── */

    public function test_a_course_without_a_discount_charges_its_list_price(): void
    {
        $course = $this->course();

        $this->assertFalse($course->hasDiscountFor('online'));
        $this->assertNull($course->discountPercentFor('online'));
        $this->assertSame($course->priceFor('online'), $course->payablePriceFor('online'));
    }

    public function test_a_discounted_course_reports_the_saving(): void
    {
        $course = $this->course();
        $course->update(['price_online' => 250000, 'discount_price_online' => 200000]);

        $this->assertTrue($course->hasDiscountFor('online'));
        $this->assertSame(200000, $course->payablePriceFor('online'));
        $this->assertSame(250000, $course->priceFor('online'));
        $this->assertSame(20, $course->discountPercentFor('online'));
    }

    public function test_a_discount_per_format_is_independent(): void
    {
        $course = $this->course();
        $course->update([
            'price_online'            => 250000,
            'price_physical'          => 350000,
            'discount_price_online'   => 200000,
            'discount_price_physical' => null,
        ]);

        $this->assertTrue($course->hasDiscountFor('online'));
        $this->assertFalse($course->hasDiscountFor('physical'));
        $this->assertSame(350000, $course->payablePriceFor('physical'));
    }

    public function test_a_nonsensical_discount_is_ignored(): void
    {
        $course = $this->course();

        // Zero, and anything at or above the list price, must not be treated as a discount.
        foreach ([0, 250000, 300000] as $bad) {
            $course->update(['price_online' => 250000, 'discount_price_online' => $bad]);

            $this->assertFalse($course->fresh()->hasDiscountFor('online'), "₦{$bad} should not count as a discount");
            $this->assertSame(250000, $course->fresh()->payablePriceFor('online'));
        }
    }

    /* ── What the pages actually show ── */

    public function test_the_courses_page_strikes_through_the_old_price(): void
    {
        $this->course()->update(['price_online' => 250000, 'discount_price_online' => 200000]);

        $this->get('/courses')
            ->assertOk()
            ->assertSee('line-through', false)
            ->assertSee('200,000')
            ->assertSee('250,000');
    }

    public function test_the_pricing_page_shows_both_prices(): void
    {
        $this->course()->update(['price_physical' => 350000, 'discount_price_physical' => 280000]);

        $this->get('/pricing')
            ->assertOk()
            ->assertSee('280,000')
            ->assertSee('350,000')
            ->assertSee('Save 20%');
    }

    public function test_the_course_page_shows_the_discounted_tuition(): void
    {
        $course = $this->course();
        $course->update(['price_physical' => 350000, 'discount_price_physical' => 280000]);

        $this->get('/courses/'.$course->slug)
            ->assertOk()
            ->assertSee('280,000')
            ->assertSee('350,000');
    }

    public function test_an_undiscounted_course_page_shows_no_strikethrough_price(): void
    {
        $course = $this->course();
        $course->update(['discount_price_physical' => null, 'discount_price_online' => null]);

        $this->get('/courses/'.$course->slug)->assertOk()->assertDontSee('line-through', false);
    }

    /* ── Checkout has to charge the discounted fee ── */

    public function test_program_fee_falls_to_the_discount_when_set(): void
    {
        $program = TrainingProgram::where('slug', 'frontend-development')->firstOrFail();
        $program->update(['fee' => 250000, 'discount_fee' => 200000]);

        $this->assertSame(250000, $program->listFeeForType('full_time'));
        $this->assertSame(200000, $program->feeForType('full_time'));
        $this->assertSame(20, $program->discountPercentForType('full_time'));
    }

    public function test_siwes_inherits_the_full_time_discount_only_when_it_has_no_fee_of_its_own(): void
    {
        $program = TrainingProgram::where('slug', 'frontend-development')->firstOrFail();

        $program->update(['fee' => 250000, 'discount_fee' => 200000, 'siwes_fee' => 0, 'discount_siwes_fee' => null]);
        $this->assertSame(200000, $program->fresh()->feeForType('it_siwes'), 'No SIWES fee → inherits the full-time discount');

        $program->update(['siwes_fee' => 120000]);
        $this->assertSame(120000, $program->fresh()->feeForType('it_siwes'), 'Own SIWES fee → full-time discount does not leak in');

        $program->update(['discount_siwes_fee' => 90000]);
        $this->assertSame(90000, $program->fresh()->feeForType('it_siwes'), 'Own SIWES discount wins');
    }

    public function test_checkout_charges_the_courses_discounted_price(): void
    {
        $course = $this->course();
        $course->update(['price_online' => 250000, 'discount_price_online' => 200000]);

        Livewire::test(\App\Livewire\CheckoutForm::class, ['selectedSlug' => $course->slug])
            ->set('format', 'online')
            ->set('full_name', 'Ada Lovelace')
            ->set('email', 'ada@example.com')
            ->set('phone', '08011111111')
            ->set('payment_method', 'transfer')
            ->call('submit');

        $order = \App\Models\CheckoutOrder::where('email', 'ada@example.com')->firstOrFail();

        $this->assertSame(200000, $order->amount, 'The order must bill the promotional price, not the list price.');
    }

    public function test_each_format_is_billed_at_its_own_discounted_price(): void
    {
        $course = $this->course();
        $course->update([
            'price_online'            => 250000,
            'price_physical'          => 350000,
            'discount_price_online'   => 200000,
            'discount_price_physical' => 300000,
        ]);

        Livewire::test(\App\Livewire\CheckoutForm::class, ['selectedSlug' => $course->slug])
            ->set('format', 'physical')
            ->set('full_name', 'Grace Hopper')
            ->set('email', 'grace@example.com')
            ->set('phone', '08022222222')
            ->set('payment_method', 'transfer')
            ->call('submit');

        $order = \App\Models\CheckoutOrder::where('email', 'grace@example.com')->firstOrFail();

        $this->assertSame('physical', $order->format);
        $this->assertSame(300000, $order->amount);
    }

    public function test_checkout_shows_the_courses_saving(): void
    {
        $course = $this->course();
        $course->update(['price_online' => 250000, 'discount_price_online' => 200000]);

        $this->get(route('checkout', ['course' => $course->slug, 'format' => 'online']))
            ->assertOk()
            ->assertSee('200,000')
            ->assertSee('250,000')
            ->assertSee('You save');
    }

    public function test_a_confirmed_discounted_order_bills_the_trainee_the_promotional_price(): void
    {
        $course = $this->course();
        $course->update(['price_online' => 250000, 'discount_price_online' => 200000]);

        Livewire::test(\App\Livewire\CheckoutForm::class, ['selectedSlug' => $course->slug])
            ->set('format', 'online')
            ->set('full_name', 'Alan Turing')
            ->set('email', 'alan@example.com')
            ->set('phone', '08033333333')
            ->set('payment_method', 'transfer')
            ->call('submit');

        $order = \App\Models\CheckoutOrder::where('email', 'alan@example.com')->firstOrFail();
        $order->markPaid();

        $result = app(\App\Services\PortalProvisioner::class)->confirm($order->fresh());
        $enrollment = $result['enrollment']->fresh();

        // The enrolment is billed what they were quoted, so nothing is left "outstanding".
        $this->assertSame(200000, $enrollment->feeAmount());
        $this->assertSame(200000, $enrollment->amountPaid());
        $this->assertSame(0, $enrollment->outstanding());
    }
}
