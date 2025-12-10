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
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_surat')->index('idx_sk_tanggal');
            $table->string('nomor_surat')->unique('idx_sk_nomor'); // Unique index implies index
            $table->string('perihal');
            $table->string('tujuan');
            $table->string('dari');
            $table->date('tanggal_keluar');
            $table->dateTime('jam_input');
            $table->foreignId('petugas_input_id')->constrained('users')->index('idx_sk_petugas');
            $table->foreignId('klasifikasi_surat_id')->constrained('klasifikasi_surat')->index('idx_sk_klasifikasi');
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_hash', 64)->nullable()->index('idx_sk_file_hash');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->boolean('is_duplicate')->default(false);
            $table->json('duplicate_metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};
