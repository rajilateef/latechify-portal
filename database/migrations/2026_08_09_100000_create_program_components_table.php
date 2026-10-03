<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A composite program (e.g. Fullstack) "includes" one or more other programs
        // (e.g. Frontend, Backend). Students enrolled in the composite get access to all of them.
        Schema::create('program_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();   // the composite
            $table->foreignId('component_program_id')->constrained('training_programs')->cascadeOnDelete(); // the included program
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['training_program_id', 'component_program_id'], 'prog_components_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_components');
    }
};
