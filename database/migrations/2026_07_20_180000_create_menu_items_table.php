<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('type')->default('link');   // link | services | courses
            $table->string('url')->nullable();          // href for links / base link for dropdowns
            $table->string('icon')->nullable();         // optional lucide icon (PascalCase)
            $table->boolean('highlight')->default(false); // render in the primary/accent colour
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
