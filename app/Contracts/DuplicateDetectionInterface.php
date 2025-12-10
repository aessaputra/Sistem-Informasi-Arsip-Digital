<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;
use App\DTOs\DuplicateDetectionResult;

interface DuplicateDetectionInterface
{
    /**
     * Check if uploaded file is a duplicate
     */
    public function checkDuplicate(UploadedFile $file, string $documentType, array $metadata = []): DuplicateDetectionResult;

    /**
     * Generate file hash for duplicate detection
     */
    public function generateFileHash(UploadedFile $file): string;

    /**
     * Calculate similarity score between two documents
     */
    public function calculateSimilarity(array $document1, array $document2): float;

    /**
     * Get supported detection methods
     */
    public function getSupportedMethods(): array;

    /**
     * Set similarity threshold for duplicate detection
     */
    public function setSimilarityThreshold(float $threshold): void;
}