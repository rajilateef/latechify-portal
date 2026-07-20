<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();          // short catalog description
            $table->longText('long_description')->nullable();  // "About This Course"
            $table->string('icon')->default('Code');
            $table->string('level')->nullable();
            $table->string('duration')->nullable();
            $table->string('schedule')->nullable();
            $table->string('start_date')->nullable();
            $table->unsignedInteger('price_physical')->default(0);
            $table->unsignedInteger('price_online')->default(0);
            $table->decimal('rating', 2, 1)->nullable();
            $table->boolean('popular')->default(false);
            $table->boolean('featured')->default(false);
            $table->string('popular_feature')->nullable();
            $table->json('tags')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
        });

        Schema::create('course_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('week');
            $table->string('title');
            $table->unsignedInteger('estimated_hours')->default(12);
            $table->boolean('is_detailed')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('course_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_module_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('duration')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('topic_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_topic_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['video', 'document', 'quiz', 'exercise'])->default('document');
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('course_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('course_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('included')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_features');
        Schema::dropIfExists('course_faqs');
        Schema::dropIfExists('topic_resources');
        Schema::dropIfExists('course_topics');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('course_highlights');
        Schema::dropIfExists('courses');
    }
};
