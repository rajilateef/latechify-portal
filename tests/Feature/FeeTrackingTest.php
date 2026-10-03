<?php

namespace Tests\Feature;

use App\Models\CheckoutOrder;
use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\User;
use App\Services\FeeLedger;
use App\Services\Monnify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeTrackingTest extends TestCase
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

    protected function enrollment(): Enrollment
    {
        return $this->student()->activeEnrollments()->firstOrFail();
    }

    /* ── What the trainee sees ── */

    public function test_the_fees_page_shows_the_balance_and_payment_history(): void
    {
        $enrollment = $this->enrollment();

        $this->actingAs($this->student())
            ->get(route('portal.fees'))
            ->assertOk()
            ->assertSee('Frontend Development')
            ->assertSee(number_format($enrollment->feeAmount()))
            ->assertSee(number_format($enrollment->outstanding()))
            ->assertSee('Payment history');
    }

    public function test_every_recorded_payment_issues_a_receipt_the_trainee_can_download(): void
    {
        $payment = FeePayment::create([
            'enrollment_id' => $this->enrollment()->id,
            'user_id'       => $this->student()->id,
            'amount'        => 10000,
            'method'        => 'cash',
            'paid_at'       => now(),
        ]);

        $this->assertNotNull($payment->receipt);

        $this->actingAs($this->student())
            ->get(route('receipts.show', $payment->receipt))
            ->assertOk();
    }

    /* ── Paying a balance online ── */

    public function test_a_trainee_cannot_pay_more_than_they_owe(): void
    {
        $enrollment = $this->enrollment();

        $this->actingAs($this->student())
            ->post(route('portal.fees.pay', $enrollment), ['amount' => $enrollment->outstanding() + 5000])
            ->assertSessionHasErrors('amount');
    }

    public function test_a_trainee_cannot_pay_towards_someone_elses_enrolment(): void
    {
        $intruder = User::create([
            'name' => 'Intruder', 'email' => 'intruder@example.com',
            'password' => bcrypt('secret123'), 'is_student' => true, 'is_active' => true,
        ]);

        $this->actingAs($intruder)
            ->post(route('portal.fees.pay', $this->enrollment()), ['amount' => 1000])
            ->assertForbidden();
    }

    public function test_a_verified_top_up_lands_on_the_ledger_and_reduces_the_balance(): void
    {
        $enrollment = $this->enrollment();
        $before = $enrollment->outstanding();

        $order = $this->topupOrder(25000);
        $order->markPaid(['paymentStatus' => 'PAID', 'amountPaid' => 25000]);

        $payment = app(FeeLedger::class)->recordFromOrder($order, $enrollment, $this->student());

        $this->assertSame(25000, $payment->amount);
        $this->assertSame('monnify', $payment->method);
        $this->assertNotNull($payment->receipt);
        $this->assertSame($before - 25000, $enrollment->fresh()->outstanding());
    }

    public function test_a_replayed_top_up_is_not_credited_twice(): void
    {
        $enrollment = $this->enrollment();
        $order = $this->topupOrder(25000);
        $order->markPaid();

        $ledger = app(FeeLedger::class);
        $ledger->recordFromOrder($order, $enrollment, $this->student());
        $second = $ledger->recordFromOrder($order, $enrollment, $this->student());

        $this->assertNull($second, 'The same transaction must never be banked twice.');
        $this->assertSame(1, FeePayment::where('reference', $order->payment_reference)->count());
    }

    /* ── Enrolment-level tracking maths ── */

    public function test_enrolment_tracks_fee_paid_and_outstanding(): void
    {
        $enrollment = $this->enrollment();
        $fee = $enrollment->feeAmount();

        $this->assertSame($fee - $enrollment->amountPaid(), $enrollment->outstanding());
        $this->assertFalse($enrollment->isFullyPaid());

        FeePayment::create([
            'enrollment_id' => $enrollment->id,
            'user_id'       => $enrollment->user_id,
            'amount'        => $enrollment->outstanding(),
            'method'        => 'bank_transfer',
            'paid_at'       => now(),
        ]);

        $enrollment = $enrollment->fresh();
        $this->assertSame(0, $enrollment->outstanding());
        $this->assertTrue($enrollment->isFullyPaid());
        $this->assertSame(100, $enrollment->paymentPercent());
    }

    protected function topupOrder(int $amount): CheckoutOrder
    {
        $enrollment = $this->enrollment();

        return CheckoutOrder::create([
            'kind'                  => 'fee_topup',
            'full_name'             => $this->student()->displayName(),
            'email'                 => $this->student()->email,
            'phone'                 => '08000000000',
            'training_program_id'   => $enrollment->training_program_id,
            'program_name'          => $enrollment->program->name,
            'type'                  => $enrollment->type,
            'amount'                => $amount,
            'payment_method'        => 'monnify',
            'status'                => 'pending',
            'user_id'               => $this->student()->id,
            'enrollment_id'         => $enrollment->id,
            'payment_reference'     => Monnify::reference('FEE', 1),
            'transaction_reference' => 'MNFY|TEST|'.uniqid(),
        ]);
    }
}
