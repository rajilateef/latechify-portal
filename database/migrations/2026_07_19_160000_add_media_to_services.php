<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Slider/gif images shown inside the service card (auto-rotating).
            $table->json('images')->nullable()->after('description');
            // Whether the media sits above ('top') or below ('bottom') the text.
            $table->string('media_position')->default('bottom')->after('images');
            // Optional accent backdrop for the media panel: plain | blue | dark.
            $table->string('media_accent')->nullable()->after('media_position');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['images', 'media_position', 'media_accent']);
        });
    }
};
