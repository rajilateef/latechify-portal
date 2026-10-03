<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->unsignedInteger('fee')->default(0)->after('duration_weeks');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            // Per-enrolment fee (defaults from the program on approval so it can be overridden per trainee).
            $table->unsignedInteger('fee_amount')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('avatar_url');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });

        Schema::table('announcements', function (Blueprint $table) {
            // Set when trainees have been notified so publishing again does not re-notify.
            $table->timestamp('notified_at')->nullable()->after('published_at');
        });

        // Link verifiable certificates to trainees/programs (columns nullable so existing rows are unaffected).
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('training_program_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->after('training_program_id')->constrained()->nullOnDelete();
            $table->string('file_path')->nullable()->after('grade');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('training_program_id');
            $table->dropConstrainedForeignId('enrollment_id');
            $table->dropColumn('file_path');
        });
        Schema::table('announcements', fn (Blueprint $table) => $table->dropColumn('notified_at'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['bio', 'last_login_at']));
        Schema::table('enrollments', fn (Blueprint $table) => $table->dropColumn('fee_amount'));
        Schema::table('training_programs', fn (Blueprint $table) => $table->dropColumn('fee'));
    }
};
