<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package')->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->enum('class_format', ['online', 'physical'])->default('online');
            $table->enum('payment_method', ['paystack', 'transfer'])->default('paystack');
            $table->string('education')->nullable();
            $table->string('experience')->nullable();
            $table->text('motivation')->nullable();
            $table->string('heard_about')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'accepted', 'rejected', 'paid'])->default('pending');
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['paystack', 'bank_transfer'])->default('paystack');
            $table->unsignedInteger('amount')->default(0);
            $table->string('currency')->default('NGN');
            $table->string('reference')->nullable()->index();
            $table->enum('status', ['pending', 'success', 'failed', 'verified', 'rejected'])->default('pending');
            $table->string('transaction_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('receipt')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied'])->default('new');
            $table->timestamps();
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('service')->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'done'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('applications');
    }
};
