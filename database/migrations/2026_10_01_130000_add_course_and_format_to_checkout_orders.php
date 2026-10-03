<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Checkout now sells a COURSE in a chosen class format (the axis the site
        // prices on). The training program it enrols into is derived from the course
        // and still drives portal access.
        Schema::table('checkout_orders', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('training_program_id')->constrained()->nullOnDelete();
            $table->string('course_name')->nullable()->after('program_name');  // snapshot
            $table->enum('format', ['online', 'physical'])->default('online')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('checkout_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
            $table->dropColumn(['course_name', 'format']);
        });
    }
};
