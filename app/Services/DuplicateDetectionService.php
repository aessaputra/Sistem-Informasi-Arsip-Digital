<?php

namespace App\Services;

use App\Contracts\DuplicateDetectionInterface;
use App\DTOs\DuplicateDetectionResult;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\DuplicateDetection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class DuplicateDetectionService implements DuplicateDetectionInterface
{
    private float $similarityThreshold = 0.80;
    private array $supportedMethods = ['file_hash', 'file_size', 'content_similarity', 'metadata'];

    public function checkDuplicate(UploadedFile $file, string $documentType, array $metadata = []): DuplicateDetectionResult
    {
        try {
            // Generate file fingerprints
            $fileHash = $this->generateFileHash($file);
            $fileSize = $file->getSize();

            // Check for exact file hash match (highest priority)
            $exactMatch = $this->checkExactFileMatch($fileHash, $documentType);
            if ($exactMatch) {
                return new DuplicateDetectionResult(
                    isDuplicate: true,
                    existingDocument: $exactMatch,
                    similarityScore: 1.0,
                    detectionMethod: 'file_hash',
                    fileHash: $fileHash,
                    fileSize: $fileSize,
                    metadata: ['match_type' => 'exact_file_hash']
                );
            }

            // Check for file size + metadata similarity (medium priority)
            $sizeMatch = $this->checkFileSizeMatch($fileSize, $documentType, $metadata);
            if ($sizeMatch) {
                $similarity = $this->calculateMetadataSimilarity($metadata, $sizeMatch);
                if ($similarity >= $this->similarityThreshold) {
                    return new DuplicateDetectionResult(
                        isDuplicate: true,
                        existingDocument: $sizeMatch,
                        similarityScore: $similarity,
                        detectionMethod: 'file_size_metadata',
                        fileHash: $fileHash,
                        fileSize: $fileSize,
                        metadata: ['match_type' => 'size_metadata', 'metadata_similarity' => $similarity]
                    );
                }
            }

            // No duplicates found
            return new DuplicateDetectionResult(
                isDuplicate: false,
                fileHash: $fileHash,
                fileSize: $fileSize,
                metadata: ['checked_methods' => $this->supportedMethods]
            );

        } catch (Exception $e) {
            Log::error('Duplicate detection failed', [
                'file_name' => $file->getClientOriginalName(),
                'document_type' => $documentType,
                'error' => $e->getMessage()
            ]);

            return new DuplicateDetectionResult(
                isDuplicate: false,
                errorMessage: 'Duplicate detection failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Check for duplicate based on metadata only (without file)
     * 
     * IMPORTANT: Only triggers duplicate warning for EXACT nomor_surat match.
     * Metadata similarity (perihal, dari, tanggal) alone is NOT enough to trigger warning
     * because it causes too many false positives in real-world usage.
     */
    public function checkDuplicateByMetadata(string $documentType, array $metadata, ?int $excludeId = null): DuplicateDetectionResult
    {
        try {
            $model = $this->getModelClass($documentType);
            
            // ONLY check for exact nomor_surat match
            // This is the only reliable indicator of duplicate without file comparison
            if (!empty($metadata['nomor_surat'])) {
                $query = $model::where('nomor_surat', $metadata['nomor_surat']);
                
                if ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                }
                
                $exactMatch = $query->first();
                
                if ($exactMatch) {
                    return new DuplicateDetectionResult(
                        isDuplicate: true,
                        existingDocument: $exactMatch,
                        similarityScore: 1.0,
                        detectionMethod: 'nomor_surat',
                        metadata: ['match_type' => 'exact_nomor_surat']
                    );
                }
            }

            // NOTE: We intentionally DO NOT check metadata similarity here
            // because similar perihal/dari/tanggal is common and causes false positives.
            // Metadata similarity is only used as additional info when FILE is duplicate.

            // No duplicates found
            return new DuplicateDetectionResult(
                isDuplicate: false,
                metadata: ['checked_methods' => ['nomor_surat']]
            );

        } catch (Exception $e) {
            Log::error('Metadata duplicate detection failed', [
                'document_type' => $documentType,
                'metadata' => $metadata,
                'error' => $e->getMessage()
            ]);

            return new DuplicateDetectionResult(
                isDuplicate: false,
                errorMessage: 'Metadata duplicate detection failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Check for metadata match (similar documents based on key fields)
     */
    private function checkMetadataMatch(string $documentType, array $metadata, ?int $excludeId = null): ?object
    {
        $model = $this->getModelClass($documentType);
        
        $query = $model::query();
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Look for documents with similar perihal and dari on the same date
        if (!empty($metadata['perihal']) && !empty($metadata['dari']) && !empty($metadata['tanggal_surat'])) {
            $query->where('perihal', 'like', '%' . $metadata['perihal'] . '%')
                  ->where('dari', 'like', '%' . $metadata['dari'] . '%')
                  ->whereDate('tanggal_surat', $metadata['tanggal_surat']);
            
            return $query->first();
        }

        return null;
    }

    public function generateFileHash(UploadedFile $file): string
    {
        return hash_file('sha256', $file->path());
    }

    public function calculateSimilarity(array $document1, array $document2): float
    {
        $score = 0.0;
        $totalFields = 0;

        // Compare key fields with weights
        $fieldWeights = [
            'nomor_surat' => 0.4,
            'tanggal_surat' => 0.2,
            'perihal' => 0.3,
            'dari' => 0.1,
        ];

        foreach ($fieldWeights as $field => $weight) {
            if (isset($document1[$field]) && isset($document2[$field])) {
                $similarity = $this->calculateStringSimilarity(
                    (string) $document1[$field],
                    (string) $document2[$field]
                );
                $score += $similarity * $weight;
                $totalFields += $weight;
            }
        }

        return $totalFields > 0 ? $score / $totalFields : 0.0;
    }

    public function getSupportedMethods(): array
    {
        return $this->supportedMethods;
    }

    public function setSimilarityThreshold(float $threshold): void
    {
        $this->similarityThreshold = max(0.0, min(1.0, $threshold));
    }

    /**
     * Check for exact file hash match
     */
    private function checkExactFileMatch(string $fileHash, string $documentType): ?object
    {
        $model = $this->getModelClass($documentType);
        
        return $model::where('file_hash', $fileHash)
                    ->whereNotNull('file_hash')
                    ->first();
    }

    /**
     * Check for file size match with similar metadata
     */
    private function checkFileSizeMatch(int $fileSize, string $documentType, array $metadata): ?object
    {
        $model = $this->getModelClass($documentType);
        
        // Look for files with same size (within 5% tolerance)
        $tolerance = $fileSize * 0.05;
        
        $query = $model::whereBetween('file_size', [$fileSize - $tolerance, $fileSize + $tolerance])
                      ->whereNotNull('file_size');

        // Add metadata filters if available
        if (isset($metadata['nomor_surat'])) {
            $query->where('nomor_surat', $metadata['nomor_surat']);
        }

        return $query->first();
    }

    /**
     * Calculate metadata similarity between documents
     */
    private function calculateMetadataSimilarity(array $newMetadata, object $existingDocument): float
    {
        $existingMetadata = $existingDocument->toArray();
        return $this->calculateSimilarity($newMetadata, $existingMetadata);
    }

    /**
     * Calculate string similarity using multiple algorithms
     */
    private function calculateStringSimilarity(string $str1, string $str2): float
    {
        if (empty($str1) || empty($str2)) {
            return 0.0;
        }

        // Normalize strings
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        // Exact match
        if ($str1 === $str2) {
            return 1.0;
        }

        // Use similar_text for similarity percentage
        $percent = 0;
        similar_text($str1, $str2, $percent);
        
        return $percent / 100.0;
    }

    /**
     * Get model class based on document type
     */
    private function getModelClass(string $documentType): string
    {
        return match ($documentType) {
            'surat_masuk' => SuratMasuk::class,
            'surat_keluar' => SuratKeluar::class,
            default => throw new Exception("Unsupported document type: {$documentType}")
        };
    }

    /**
     * Log duplicate detection for audit trail
     */
    public function logDuplicateDetection(DuplicateDetectionResult $result, string $documentType, int $userId): void
    {
        if ($result->isDuplicate && $result->existingDocument) {
            DuplicateDetection::create([
                'document_type' => $documentType,
                'document_id' => 0, // Will be updated after document creation
                'original_document_type' => $documentType,
                'original_document_id' => $result->existingDocument->id,
                'detection_method' => $result->detectionMethod,
                'similarity_score' => $result->similarityScore,
                'status' => 'detected',
                'detection_metadata' => $result->metadata,
                'detected_by' => $userId,
            ]);
        }
    }

    /**
     * Update document with duplicate detection results
     */
    public function updateDocumentWithDuplicateInfo(object $document, DuplicateDetectionResult $result): void
    {
        $document->update([
            'file_hash' => $result->fileHash,
            'file_size' => $result->fileSize,
            'is_duplicate' => $result->isDuplicate,
            'duplicate_metadata' => $result->isDuplicate ? $result->metadata : null,
        ]);
    }
}