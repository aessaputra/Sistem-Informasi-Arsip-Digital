<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lampiran_surat', function (Blueprint $table) {
            $table->id();
            $table->string('surat_type'); // 'masuk' or 'keluar'
            $table->bigInteger('surat_id');
            $table->string('nama_file_asli');
            $table->string('file_path');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index('surat_type', 'idx_lampiran_type');
            $table->index(['surat_type', 'surat_id'], 'idx_lampiran_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lampiran_surat');
    }
};
