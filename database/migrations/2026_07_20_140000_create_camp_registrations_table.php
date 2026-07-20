<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camp_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('age_group')->nullable();
            $table->string('track')->nullable();
            $table->string('experience')->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('amount')->default(0);
            $table->string('payment_method')->default('manual'); // manual | monnify
            $table->string('status')->default('pending');         // pending | paid | cancelled
            $table->string('payment_reference')->nullable();      // our unique ref sent to Monnify
            $table->string('transaction_reference')->nullable();  // Monnify's transactionReference
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camp_registrations');
    }
};
