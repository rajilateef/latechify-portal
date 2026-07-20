<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            // Also float this advert as a dismissible card near the navbar.
            $table->boolean('show_floating')->default(false)->after('placement');
        });
    }

    public function down(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->dropColumn('show_floating');
        });
    }
};
