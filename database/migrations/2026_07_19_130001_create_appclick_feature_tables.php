<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "Why Choose Us" benefits
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('Sparkles');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Accreditations, partners and alumni employers (logo strips / marquee)
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->enum('category', ['accreditation', 'partner', 'employer'])->default('partner');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Certificate verification
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_id')->unique();
            $table->string('student_name');
            $table->string('course_name');
            $table->date('issue_date')->nullable();
            $table->string('grade')->nullable();
            $table->enum('status', ['valid', 'revoked'])->default('valid');
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('category')->default('Software Engineering')->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('category');
        });
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('benefits');
    }
};
