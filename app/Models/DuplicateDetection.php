<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateDetection extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'document_id',
        'original_document_type',
        'original_document_id',
        'detection_method',
        'similarity_score',
        'status',
        'resolution_action',
        'detection_metadata',
        'detected_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'similarity_score' => 'decimal:4',
        'detection_metadata' => 'array',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user who detected the duplicate
     */
    public function detectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'detected_by');
    }

    /**
     * Get the user who resolved the duplicate
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the original document (polymorphic)
     */
    public function originalDocument()
    {
        return $this->morphTo('original_document', 'original_document_type', 'original_document_id');
    }

    /**
     * Get the duplicate document (polymorphic)
     */
    public function duplicateDocument()
    {
        return $this->morphTo('duplicate_document', 'document_type', 'document_id');
    }

    /**
     * Scope for pending duplicates
     */
    public function scopePending($query)
    {
        return $query->where('status', 'detected');
    }

    /**
     * Scope for resolved duplicates
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for high similarity duplicates
     */
    public function scopeHighSimilarity($query, float $threshold = 0.95)
    {
        return $query->where('similarity_score', '>=', $threshold);
    }

    /**
     * Mark duplicate as resolved
     */
    public function markAsResolved(string $action, int $userId): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution_action' => $action,
            'resolved_by' => $userId,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark duplicate as ignored
     */
    public function markAsIgnored(int $userId): void
    {
        $this->update([
            'status' => 'ignored',
            'resolved_by' => $userId,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Get confidence level based on similarity score
     */
    public function getConfidenceLevelAttribute(): string
    {
        return match (true) {
            $this->similarity_score >= 0.95 => 'high',
            $this->similarity_score >= 0.80 => 'medium',
            $this->similarity_score >= 0.60 => 'low',
            default => 'very_low'
        };
    }

    /**
     * Get human readable detection method
     */
    public function getDetectionMethodLabelAttribute(): string
    {
        return match ($this->detection_method) {
            'file_hash' => 'File Hash (Exact Match)',
            'file_size_metadata' => 'File Size + Metadata',
            'content_similarity' => 'Content Similarity',
            'metadata' => 'Metadata Only',
            default => ucfirst(str_replace('_', ' ', $this->detection_method))
        };
    }
}