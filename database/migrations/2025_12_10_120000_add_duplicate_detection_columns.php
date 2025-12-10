<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds duplicate detection columns to existing surat tables.
     * It safely checks if columns exist before adding them.
     */
    public function up(): void
    {
        // Add columns to surat_masuk table if they don't exist
        if (Schema::hasTable('surat_masuk')) {
            Schema::table('surat_masuk', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_masuk', 'file_hash')) {
                    $table->string('file_hash', 64)->nullable()->index();
                }
                if (!Schema::hasColumn('surat_masuk', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable();
                }
                if (!Schema::hasColumn('surat_masuk', 'is_duplicate')) {
                    $table->boolean('is_duplicate')->default(false);
                }
                if (!Schema::hasColumn('surat_masuk', 'duplicate_metadata')) {
                    $table->json('duplicate_metadata')->nullable();
                }
            });
        }

        // Add columns to surat_keluar table if they don't exist
        if (Schema::hasTable('surat_keluar')) {
            Schema::table('surat_keluar', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_keluar', 'file_hash')) {
                    $table->string('file_hash', 64)->nullable()->index();
                }
                if (!Schema::hasColumn('surat_keluar', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable();
                }
                if (!Schema::hasColumn('surat_keluar', 'is_duplicate')) {
                    $table->boolean('is_duplicate')->default(false);
                }
                if (!Schema::hasColumn('surat_keluar', 'duplicate_metadata')) {
                    $table->json('duplicate_metadata')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: We don't drop columns in down() to prevent data loss
        // If you need to remove these columns, create a separate migration
    }
};
