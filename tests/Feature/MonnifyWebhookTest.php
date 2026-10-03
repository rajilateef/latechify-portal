<?php

namespace Tests\Feature;

use App\Models\CampRegistration;
use App\Models\CheckoutOrder;
use App\Models\Course;
use App\Models\MonnifyWebhookEvent;
use App\Models\Setting;
use App\Services\Monnify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonnifyWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected string $secret = 'TEST_SECRET_KEY_123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Setting::put('monnify_api_key', 'MK_TEST_KEY', 'payment');
        Setting::put('monnify_secret_key', $this->secret, 'payment');
        Setting::put('monnify_contract_code', '1234567890', 'payment');
    }

    /* ── Helpers ── */

    /** A realistically-shaped Monnify transaction-completion payload. */
    protected function payload(string $reference, int $amount, string $txRef = 'MNFY|TX|TEST|001'): array
    {
        return [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => [
                'product'              => ['reference' => $reference, 'type' => 'WEB_SDK'],
                'transactionReference' => $txRef,
                'paymentReference'     => $reference,
                'paidOn'               => now()->toIso8601String(),
                'paymentStatus'        => 'PAID',
                'paymentMethod'        => 'CARD',
                'currency'             => 'NGN',
                'amountPaid'           => $amount,
                'totalPayable'         => $amount,
                'customer'             => ['email' => 'buyer@example.com', 'name' => 'Ada Lovelace'],
            ],
        ];
    }

    /** POST a payload with a correctly computed monnify-signature header. */
    protected function deliver(array $payload, ?string $signature = null, string $url = '/webhooks/monnify')
    {
        $body = json_encode($payload);

        return $this->call(
            'POST', $url, [], [], [],
            [
                'CONTENT_TYPE'            => 'application/json',
                'HTTP_ACCEPT'             => 'application/json',
                'HTTP_MONNIFY_SIGNATURE'  => $signature ?? hash_hmac('sha512', $body, $this->secret),
            ],
            $body,
        );
    }

    protected function campRegistration(): CampRegistration
    {
        return CampRegistration::create([
            'full_name' => 'Camp Kid', 'email' => 'kid@example.com', 'phone' => '08012345678',
            'age_group' => '13-17', 'track' => 'Web Development', 'mode' => 'virtual',
            'experience' => 'none', 'amount' => 50000, 'payment_method' => 'monnify', 'status' => 'pending',
            'payment_reference' => Monnify::reference('CAMP', 1),
            'transaction_reference' => 'MNFY|TX|CAMP|001',
        ]);
    }

    protected function order(int $amount = 250000): CheckoutOrder
    {
        $course = Course::where('slug', 'frontend-web-development')->firstOrFail();

        return CheckoutOrder::create([
            'kind'                  => 'enrolment',
            'full_name'             => 'Ada Lovelace',
            'email'                 => 'buyer@example.com',
            'phone'                 => '08011111111',
            'course_id'             => $course->id,
            'course_name'           => $course->title,
            'training_program_id'   => $course->training_program_id,
            'program_name'          => $course->trainingProgram?->name,
            'format'                => 'online',
            'amount'                => $amount,
            'payment_method'        => 'monnify',
            'payment_reference'     => Monnify::reference('CHK', 1),
            'transaction_reference' => 'MNFY|TX|TEST|001',
            'status'                => 'pending',
        ]);
    }

    /* ── Signature is recorded, not enforced ── */

    public function test_an_unsigned_delivery_is_accepted_and_recorded_as_unsigned(): void
    {
        $order = $this->order();

        $this->postJson('/webhooks/monnify', $this->payload($order->payment_reference, 250000))
            ->assertOk();

        $event = MonnifyWebhookEvent::latest('id')->firstOrFail();
        $this->assertFalse($event->signature_valid, 'The missing signature should still be recorded.');
        $this->assertSame('checkout_order', $event->handler, 'It is still routed to the right record.');
    }

    public function test_a_correctly_signed_delivery_is_marked_as_signed(): void
    {
        $order = $this->order();

        $this->deliver($this->payload($order->payment_reference, 250000))->assertOk();

        $this->assertTrue(MonnifyWebhookEvent::latest('id')->firstOrFail()->signature_valid);
    }

    public function test_a_forged_signature_is_accepted_but_recorded_as_unsigned(): void
    {
        $order = $this->order();

        $this->deliver($this->payload($order->payment_reference, 250000), str_repeat('a', 128))->assertOk();

        $this->assertFalse(MonnifyWebhookEvent::latest('id')->firstOrFail()->signature_valid);
    }

    /**
     * The security guarantee that replaces the signature gate: no unverified call —
     * signed, unsigned or forged — can ever mark a payment as received, because the
     * handler re-checks the transaction with Monnify instead of believing the body.
     */
    public function test_an_unsigned_delivery_still_cannot_mark_a_payment_as_paid(): void
    {
        $order = $this->order();

        $this->postJson('/webhooks/monnify', $this->payload($order->payment_reference, 250000))
            ->assertOk();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame('failed', MonnifyWebhookEvent::latest('id')->firstOrFail()->status);
    }

    public function test_a_forged_delivery_cannot_mark_a_camp_registration_as_paid(): void
    {
        $registration = $this->campRegistration();

        $this->postJson('/webhooks/monnify', $this->payload($registration->payment_reference, 50000, 'MNFY|TX|CAMP|001'))
            ->assertOk();

        $this->assertSame('pending', $registration->fresh()->status);
    }

    /* ── Routing and logging ── */

    public function test_every_delivery_is_recorded(): void
    {
        $order = $this->order();

        $this->deliver($this->payload($order->payment_reference, 250000))->assertOk();

        $event = MonnifyWebhookEvent::latest('id')->firstOrFail();
        $this->assertTrue($event->signature_valid);
        $this->assertSame('SUCCESSFUL_TRANSACTION', $event->event_type);
        $this->assertSame($order->payment_reference, $event->payment_reference);
        $this->assertSame(250000, $event->amount_paid);
        $this->assertNotEmpty($event->payload);
    }

    public function test_a_non_payment_event_is_acknowledged_but_ignored(): void
    {
        $order = $this->order();

        $payload = $this->payload($order->payment_reference, 250000);
        $payload['eventType'] = 'SUCCESSFUL_DISBURSEMENT';

        $this->deliver($payload)->assertOk();

        $this->assertSame('ignored', MonnifyWebhookEvent::latest('id')->firstOrFail()->status);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_an_unknown_reference_is_acknowledged_not_errored(): void
    {
        // Monnify retries anything that isn't 2xx, so an unmatched call must still 200.
        $this->deliver($this->payload('LATECHIFY-CHK-999-NOPE', 1000))->assertOk();

        $event = MonnifyWebhookEvent::latest('id')->firstOrFail();
        $this->assertSame('unmatched', $event->status);
    }

    public function test_the_legacy_webhook_urls_reach_the_same_handler(): void
    {
        foreach (['/checkout/webhook', '/summer-coding-camp/payment/webhook'] as $url) {
            $this->deliver($this->payload('LATECHIFY-CHK-888-OLD', 1000), null, $url)->assertOk();
        }

        $this->assertSame(2, MonnifyWebhookEvent::where('payment_reference', 'LATECHIFY-CHK-888-OLD')->count());
    }

    public function test_a_camp_reference_is_routed_to_the_camp_registration(): void
    {
        $registration = $this->campRegistration();

        $this->deliver($this->payload($registration->payment_reference, 50000, 'MNFY|TX|CAMP|001'))->assertOk();

        $event = MonnifyWebhookEvent::latest('id')->firstOrFail();
        $this->assertSame('camp_registration', $event->handler);
        $this->assertSame($registration->id, $event->handled_id);
    }

    public function test_a_checkout_reference_is_routed_to_the_order(): void
    {
        $order = $this->order();

        $this->deliver($this->payload($order->payment_reference, 250000))->assertOk();

        $event = MonnifyWebhookEvent::latest('id')->firstOrFail();
        $this->assertSame('checkout_order', $event->handler);
        $this->assertSame($order->id, $event->handled_id);
    }

    /* ── It never trusts the payload ── */

    public function test_the_webhook_does_not_confirm_on_the_payload_alone(): void
    {
        $order = $this->order();

        // Monnify is unreachable in tests, so server-side verification cannot succeed —
        // the order must stay unpaid even though the body claims PAID.
        $this->deliver($this->payload($order->payment_reference, 250000))->assertOk();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame('failed', MonnifyWebhookEvent::latest('id')->firstOrFail()->status);
    }

    public function test_an_already_paid_order_is_acknowledged_without_rework(): void
    {
        $order = $this->order();
        $order->markPaid(['paymentStatus' => 'PAID']);

        $this->deliver($this->payload($order->payment_reference, 250000))->assertOk();

        $event = MonnifyWebhookEvent::latest('id')->firstOrFail();
        $this->assertSame('handled', $event->status);
        $this->assertStringContainsString('Already confirmed', $event->message);
    }

    public function test_repeated_deliveries_are_idempotent(): void
    {
        $order = $this->order();
        $order->markPaid(['paymentStatus' => 'PAID']);
        $paidAt = $order->fresh()->paid_at;

        for ($i = 0; $i < 3; $i++) {
            $this->deliver($this->payload($order->payment_reference, 250000))->assertOk();
        }

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertEquals($paidAt, $order->fresh()->paid_at, 'Re-delivery must not move paid_at.');
        $this->assertSame(3, MonnifyWebhookEvent::where('status', 'handled')->count());
    }
}
