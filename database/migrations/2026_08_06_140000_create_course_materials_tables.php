<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Course-level documents / media, stored on the PRIVATE disk and served only through a permission check.
        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['file', 'link', 'video', 'image'])->default('file');
            $table->string('category')->nullable();          // Slides, Notes, Reading, Brief…
            $table->string('disk')->default('local');         // private by default
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();          // original filename for downloads
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime')->nullable();
            $table->string('url')->nullable();                // for link/video
            $table->enum('access', ['enrolled', 'selected'])->default('enrolled');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // When access = 'selected', only these trainees may open the material.
        Schema::create('course_material_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['course_material_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_material_user');
        Schema::dropIfExists('course_materials');
    }
};
