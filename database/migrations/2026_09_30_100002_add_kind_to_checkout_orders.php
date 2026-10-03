<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_orders', function (Blueprint $table) {
            // 'enrolment'  — a website purchase; the portal is created on super-admin confirmation.
            // 'fee_topup'  — an existing trainee paying down a balance; banked as soon as it verifies.
            $table->enum('kind', ['enrolment', 'fee_topup'])->default('enrolment')->after('uuid')->index();
        });
    }

    public function down(): void
    {
        Schema::table('checkout_orders', fn (Blueprint $table) => $table->dropColumn('kind'));
    }
};
