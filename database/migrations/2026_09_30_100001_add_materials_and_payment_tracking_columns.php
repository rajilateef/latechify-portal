<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Class slides / materials: richer metadata + a private-disk option so uploads
        // can be served through the portal's permission check instead of a public URL.
        Schema::table('class_resources', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('category')->nullable()->after('type');   // Slides, Notes, Reading…
            $table->string('disk')->default('public')->after('category');
            $table->string('file_name')->nullable()->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_name');
            $table->boolean('is_published')->default(true)->after('url');
        });

        // Online fee payments need the gateway's transaction id, kept unique so the
        // same Monnify transaction can never be banked twice.
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->string('transaction_reference')->nullable()->unique()->after('reference');
            $table->json('meta')->nullable()->after('note');
        });

        // Let a marketing course point at the portal program it actually enrols into,
        // so "Checkout" from a course page knows what it is selling.
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('training_program_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', fn (Blueprint $table) => $table->dropConstrainedForeignId('training_program_id'));

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropUnique(['transaction_reference']);
            $table->dropColumn(['transaction_reference', 'meta']);
        });

        Schema::table('class_resources', fn (Blueprint $table) => $table->dropColumn([
            'description', 'category', 'disk', 'file_name', 'file_size', 'is_published',
        ]));
    }
};
