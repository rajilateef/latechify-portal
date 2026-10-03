<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('fee_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot of the details at issue time (immutable even if the source records change).
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('program_name')->nullable();
            $table->string('description');
            $table->unsignedBigInteger('amount');
            $table->string('currency')->default('NGN');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('fee_total')->default(0);
            $table->unsignedBigInteger('amount_paid_total')->default(0); // cumulative paid after this payment
            $table->unsignedBigInteger('balance')->default(0);           // outstanding after this payment

            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->enum('status', ['issued', 'void'])->default('issued');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
