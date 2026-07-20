<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_registrations', function (Blueprint $table) {
            $table->string('mode')->default('physical')->after('track'); // physical | virtual
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        // Backfill uuids for any existing rows.
        foreach (\App\Models\CampRegistration::whereNull('uuid')->get() as $reg) {
            $reg->update(['uuid' => (string) Str::uuid()]);
        }
    }

    public function down(): void
    {
        Schema::table('camp_registrations', function (Blueprint $table) {
            $table->dropColumn(['mode', 'uuid']);
        });
    }
};
