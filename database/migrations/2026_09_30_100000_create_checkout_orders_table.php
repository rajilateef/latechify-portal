<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A website checkout for a training program. Paid online via Monnify (or by
        // transfer), then confirmed by the super admin — confirmation is what creates
        // the trainee's portal account + enrolment.
        Schema::create('checkout_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();            // public handle (no id enumeration)

            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone');

            $table->foreignId('training_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('program_name')->nullable();  // snapshot, survives program deletion
            $table->enum('type', ['full_time', 'it_siwes'])->default('full_time');

            $table->unsignedInteger('amount')->default(0);
            $table->string('currency', 8)->default('NGN');
            $table->enum('payment_method', ['monnify', 'transfer'])->default('monnify');

            // Our reference (always LATECHIFY-prefixed) and Monnify's own transaction id.
            $table->string('payment_reference')->nullable()->unique();
            $table->string('transaction_reference')->nullable()->index();

            $table->enum('status', ['pending', 'paid', 'confirmed', 'rejected'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();

            // Set when the super admin confirms and the portal is provisioned.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();

            $table->text('note')->nullable();        // from the applicant
            $table->string('admin_note')->nullable();
            $table->json('meta')->nullable();        // verified gateway payload
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_orders');
    }
};
