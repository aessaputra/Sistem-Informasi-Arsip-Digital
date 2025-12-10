<?php

namespace App\DTOs;

use Illuminate\Database\Eloquent\Model;

class DuplicateDetectionResult
{
    public function __construct(
        public readonly bool $isDuplicate,
        public readonly ?Model $existingDocument = null,
        public readonly float $similarityScore = 0.0,
        public readonly string $detectionMethod = '',
        public readonly string $fileHash = '',
        public readonly int $fileSize = 0,
        public readonly array $metadata = [],
        public readonly ?string $errorMessage = null
    ) {}

    public function toArray(): array
    {
        return [
            'is_duplicate' => $this->isDuplicate,
            'existing_document' => $this->existingDocument?->toArray(),
            'similarity_score' => $this->similarityScore,
            'detection_method' => $this->detectionMethod,
            'file_hash' => $this->fileHash,
            'file_size' => $this->fileSize,
            'metadata' => $this->metadata,
            'error_message' => $this->errorMessage,
        ];
    }

    public function hasError(): bool
    {
        return !is_null($this->errorMessage);
    }

    public function isHighConfidence(): bool
    {
        return $this->similarityScore >= 0.95;
    }

    public function isMediumConfidence(): bool
    {
        return $this->similarityScore >= 0.80 && $this->similarityScore < 0.95;
    }

    public function isLowConfidence(): bool
    {
        return $this->similarityScore >= 0.60 && $this->similarityScore < 0.80;
    }
}