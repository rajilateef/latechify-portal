<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A training program / track (e.g. "Frontend Development").
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_weeks')->default(12);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // A course / subject within a program (e.g. "HTML", "CSS", "Git").
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // A class / lesson within a course (e.g. "HTML Class 1: Structure").
        Schema::create('training_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Slides / materials for a class — an uploaded file OR an external link.
        Schema::create('class_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_class_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('file');   // file | link | video
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // A student's enrolment in a program.
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending | active | completed | cancelled
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'training_program_id']);
        });

        // A student's completion of a class (set by the super admin).
        Schema::create('class_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_class_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'training_class_id']);
        });

        // Portal announcements (global, or targeted to one program).
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('class_completions');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('class_resources');
        Schema::dropIfExists('training_classes');
        Schema::dropIfExists('training_courses');
        Schema::dropIfExists('training_programs');
    }
};
