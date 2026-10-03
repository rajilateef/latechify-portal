<?php

namespace Tests\Feature;

use App\Filament\Resources\ReceiptResource;
use App\Mail\ReceiptMail;
use App\Models\FeePayment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReceiptTest extends TestCase
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

    public function test_a_receipt_is_auto_issued_for_every_payment(): void
    {
        $this->assertGreaterThan(0, Receipt::count());
        $this->assertSame(FeePayment::count(), Receipt::count());

        $receipt = Receipt::first();
        $this->assertStringStartsWith('RCP-', $receipt->receipt_number);
        $this->assertNotNull($receipt->amount);
        $this->assertNotEmpty($receipt->amountInWords());
    }

    public function test_recording_a_new_payment_creates_a_receipt(): void
    {
        $enrollment = $this->student()->activeEnrollments()->first();
        $before = Receipt::count();

        $payment = $enrollment->payments()->create([
            'user_id' => $enrollment->user_id, 'amount' => 25000, 'method' => 'cash', 'paid_at' => now(),
        ]);

        $this->assertSame($before + 1, Receipt::count());
        $this->assertNotNull($payment->fresh()->receipt);
    }

    public function test_student_can_download_their_own_receipt_as_pdf(): void
    {
        $receipt = Receipt::where('user_id', $this->student()->id)->firstOrFail();

        $response = $this->actingAs($this->student())->get(route('receipts.download', $receipt));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_a_student_cannot_access_someone_elses_receipt(): void
    {
        $receipt = Receipt::where('user_id', $this->student()->id)->firstOrFail();

        $other = User::create([
            'name' => 'Other', 'email' => 'other@example.com', 'password' => Hash::make('secret123'),
            'is_student' => true, 'is_active' => true,
        ]);

        $this->actingAs($other)->get(route('receipts.show', $receipt))->assertForbidden();
    }

    public function test_admin_can_email_a_receipt_to_the_student(): void
    {
        Mail::fake();
        $receipt = Receipt::whereNotNull('payer_email')->firstOrFail();

        ReceiptResource::emailReceipt($receipt);

        Mail::assertSent(ReceiptMail::class);
        $this->assertNotNull($receipt->fresh()->emailed_at);
    }
}
