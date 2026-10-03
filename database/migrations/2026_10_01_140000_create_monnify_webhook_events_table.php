<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every webhook Monnify delivers, recorded before we act on it. Without this
        // a payment that never confirmed is undiagnosable — you cannot tell whether
        // Monnify called at all, was rejected, or matched nothing.
        Schema::create('monnify_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->nullable()->index();      // SUCCESSFUL_TRANSACTION, …
            $table->string('payment_reference')->nullable()->index();
            $table->string('transaction_reference')->nullable()->index();
            $table->string('payment_status')->nullable();
            $table->unsignedBigInteger('amount_paid')->nullable();

            $table->boolean('signature_valid')->default(false);

            // handled | ignored | unmatched | rejected | failed
            $table->string('status')->default('received')->index();
            $table->string('handler')->nullable();                   // checkout_order | camp_registration
            $table->unsignedBigInteger('handled_id')->nullable();
            $table->text('message')->nullable();

            $table->json('payload')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monnify_webhook_events');
    }
};
