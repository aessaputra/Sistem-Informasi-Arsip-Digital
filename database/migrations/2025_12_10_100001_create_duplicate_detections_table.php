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
        Schema::create('duplicate_detections', function (Blueprint $table) {
            $table->id();
            $table->string('document_type'); // 'surat_masuk' or 'surat_keluar'
            $table->bigInteger('document_id');
            $table->string('original_document_type');
            $table->bigInteger('original_document_id');
            $table->string('detection_method'); // 'file_hash', 'content_hash', 'metadata'
            $table->decimal('similarity_score', 5, 4); // 0.0000 to 1.0000
            $table->string('status'); // 'detected', 'resolved', 'ignored'
            $table->string('resolution_action')->nullable(); // 'replace', 'skip', 'force_save'
            $table->json('detection_metadata')->nullable();
            $table->foreignId('detected_by')->constrained('users');
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['document_type', 'document_id'], 'idx_duplicate_document');
            $table->index(['original_document_type', 'original_document_id'], 'idx_duplicate_original');
            $table->index('status', 'idx_duplicate_status');
            $table->index('similarity_score', 'idx_duplicate_similarity');
            $table->index('created_at', 'idx_duplicate_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duplicate_detections');
    }
};