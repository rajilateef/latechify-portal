<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Folders for the private media library (nestable).
        Schema::create('material_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('material_folders')->nullOnDelete();
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->default('heroicon-o-folder');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Reusable library files/media (stored on the PRIVATE disk).
        Schema::create('material_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_folder_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->enum('kind', ['file', 'image', 'link', 'video'])->default('file');
            $table->string('disk')->default('local');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // A course material can now point at a reusable library file.
        Schema::table('course_materials', function (Blueprint $table) {
            $table->foreignId('material_file_id')->nullable()->after('training_course_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('course_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('material_file_id');
        });
        Schema::dropIfExists('material_files');
        Schema::dropIfExists('material_folders');
    }
};
