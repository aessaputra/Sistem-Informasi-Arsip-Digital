<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds composite indexes to optimize report queries that filter by
     * tanggal_surat and klasifikasi_surat_id.
     */
    public function up(): void
    {
        // Add index to surat_masuk table
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->index(['tanggal_surat', 'klasifikasi_surat_id'], 'idx_surat_masuk_report');
        });

        // Add index to surat_keluar table
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->index(['tanggal_surat', 'klasifikasi_surat_id'], 'idx_surat_keluar_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropIndex('idx_surat_masuk_report');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropIndex('idx_surat_keluar_report');
        });
    }
};
