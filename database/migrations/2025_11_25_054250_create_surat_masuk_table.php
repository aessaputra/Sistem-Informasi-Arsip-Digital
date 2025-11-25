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
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_surat')->index('idx_sm_tanggal');
            $table->string('nomor_surat')->index('idx_sm_nomor');
            $table->string('perihal');
            $table->string('dari');
            $table->string('kepada');
            $table->date('tanggal_surat_masuk');
            $table->dateTime('jam_input');
            $table->foreignId('petugas_input_id')->constrained('users')->index('idx_sm_petugas');
            $table->foreignId('klasifikasi_surat_id')->constrained('klasifikasi_surat')->index('idx_sm_klasifikasi');
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuk');
    }
};
