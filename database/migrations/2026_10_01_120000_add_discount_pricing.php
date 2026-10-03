<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Promotional pricing. The existing price columns stay the "list" price (shown
        // struck through); when a discount is set it becomes the price actually charged.
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('discount_price_online')->nullable()->after('price_online');
            $table->unsignedInteger('discount_price_physical')->nullable()->after('price_physical');
        });

        // Checkout bills the training program, so the discount has to live here too —
        // otherwise the site would advertise a price it never charges.
        Schema::table('training_programs', function (Blueprint $table) {
            $table->unsignedInteger('discount_fee')->nullable()->after('fee');
            $table->unsignedInteger('discount_siwes_fee')->nullable()->after('siwes_fee');
        });
    }

    public function down(): void
    {
        Schema::table('training_programs', fn (Blueprint $table) => $table->dropColumn(['discount_fee', 'discount_siwes_fee']));
        Schema::table('courses', fn (Blueprint $table) => $table->dropColumn(['discount_price_online', 'discount_price_physical']));
    }
};
