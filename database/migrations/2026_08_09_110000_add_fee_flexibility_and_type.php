<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            // Separate default price for IT / SIWES trainees (falls back to `fee` when 0).
            $table->unsignedInteger('siwes_fee')->default(0)->after('fee');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('type', ['full_time', 'it_siwes'])->default('full_time')->after('training_program_id');
            $table->string('fee_note')->nullable()->after('fee_amount'); // e.g. "family discount"
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', fn (Blueprint $table) => $table->dropColumn(['type', 'fee_note']));
        Schema::table('training_programs', fn (Blueprint $table) => $table->dropColumn('siwes_fee'));
    }
};
