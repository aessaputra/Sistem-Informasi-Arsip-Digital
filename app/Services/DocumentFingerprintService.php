<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentFingerprintService
{
    /**
     * Generate comprehensive fingerprint for a document
     */
    public function generateFingerprint(UploadedFile $file, array $metadata = []): array
    {
        try {
            $fingerprint = [
                'file_hash' => $this->generateFileHash($file),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'created_at' => now()->toISOString(),
            ];

            // Add content-based fingerprints if possible
            if ($this->isTextExtractable($file)) {
                $textFingerprint = $this->generateTextFingerprint($file);
                $fingerprint = array_merge($fingerprint, $textFingerprint);
            }

            // Add metadata fingerprints
            if (!empty($metadata)) {
                $fingerprint['metadata_hash'] = $this->generateMetadataHash($metadata);
                $fingerprint['metadata_signature'] = $this->generateMetadataSignature($metadata);
            }

            // Add structural fingerprints for PDFs
            if ($this->isPDF($file)) {
                $pdfFingerprint = $this->generatePDFFingerprint($file);
                $fingerprint = array_merge($fingerprint, $pdfFingerprint);
            }

            return $fingerprint;

        } catch (\Exception $e) {
            Log::error('Failed to generate document fingerprint', [
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);

            // Return basic fingerprint on error
            return [
                'file_hash' => $this->generateFileHash($file),
                'file_size' => $file->getSize(),
                'error' => 'Partial fingerprint due to processing error',
                'created_at' => now()->toISOString(),
            ];
        }
    }

    /**
     * Generate SHA-256 hash of file content
     */
    public function generateFileHash(UploadedFile $file): string
    {
        return hash_file('sha256', $file->path());
    }

    /**
     * Generate text-based fingerprint for documents
     */
    private function generateTextFingerprint(UploadedFile $file): array
    {
        $fingerprint = [];

        try {
            // For now, we'll create placeholder for text extraction
            // This would integrate with OCR service in the future
            $fingerprint['text_extractable'] = true;
            $fingerprint['estimated_text_length'] = $this->estimateTextLength($file);
            
            // Generate content hash based on file structure
            $fingerprint['content_structure_hash'] = $this->generateContentStructureHash($file);

        } catch (\Exception $e) {
            $fingerprint['text_extractable'] = false;
            $fingerprint['text_extraction_error'] = $e->getMessage();
        }

        return $fingerprint;
    }

    /**
     * Generate metadata-based hash
     */
    private function generateMetadataHash(array $metadata): string
    {
        // Sort metadata to ensure consistent hashing
        ksort($metadata);
        
        // Create a normalized string from metadata
        $metadataString = '';
        foreach ($metadata as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $metadataString .= $key . ':' . strtolower(trim((string) $value)) . '|';
        }

        return hash('sha256', $metadataString);
    }

    /**
     * Generate metadata signature for similarity comparison
     */
    private function generateMetadataSignature(array $metadata): array
    {
        $signature = [];

        // Extract key fields for signature
        $keyFields = ['nomor_surat', 'tanggal_surat', 'perihal', 'dari', 'kepada', 'tujuan'];
        
        foreach ($keyFields as $field) {
            if (isset($metadata[$field])) {
                $signature[$field] = $this->normalizeForSignature((string) $metadata[$field]);
            }
        }

        return $signature;
    }

    /**
     * Generate PDF-specific fingerprint
     */
    private function generatePDFFingerprint(UploadedFile $file): array
    {
        $fingerprint = [];

        try {
            // Basic PDF analysis
            $fingerprint['is_pdf'] = true;
            $fingerprint['pdf_size_category'] = $this->categorizePDFSize($file->getSize());
            
            // Estimate page count based on file size (rough approximation)
            $fingerprint['estimated_pages'] = $this->estimatePDFPages($file->getSize());
            
            // Generate structural hash based on file header/footer
            $fingerprint['pdf_structure_hash'] = $this->generatePDFStructureHash($file);

        } catch (\Exception $e) {
            $fingerprint['pdf_analysis_error'] = $e->getMessage();
        }

        return $fingerprint;
    }

    /**
     * Check if file is text extractable
     */
    private function isTextExtractable(UploadedFile $file): bool
    {
        $extractableTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        return in_array($file->getMimeType(), $extractableTypes);
    }

    /**
     * Check if file is PDF
     */
    private function isPDF(UploadedFile $file): bool
    {
        return $file->getMimeType() === 'application/pdf';
    }

    /**
     * Estimate text length based on file size and type
     */
    private function estimateTextLength(UploadedFile $file): int
    {
        $size = $file->getSize();
        $mimeType = $file->getMimeType();

        // Rough estimates based on file type
        return match ($mimeType) {
            'application/pdf' => intval($size / 50), // ~50 bytes per character in PDF
            'application/msword' => intval($size / 20), // ~20 bytes per character in DOC
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => intval($size / 30), // ~30 bytes per character in DOCX
            default => intval($size / 10)
        };
    }

    /**
     * Generate content structure hash
     */
    private function generateContentStructureHash(UploadedFile $file): string
    {
        // Read first and last few bytes to create structure signature
        $handle = fopen($file->path(), 'rb');
        $header = fread($handle, 1024); // First 1KB
        fseek($handle, -1024, SEEK_END);
        $footer = fread($handle, 1024); // Last 1KB
        fclose($handle);

        return hash('sha256', $header . $footer);
    }

    /**
     * Categorize PDF size
     */
    private function categorizePDFSize(int $size): string
    {
        return match (true) {
            $size < 100000 => 'small',      // < 100KB
            $size < 1000000 => 'medium',    // < 1MB
            $size < 5000000 => 'large',     // < 5MB
            default => 'very_large'         // >= 5MB
        };
    }

    /**
     * Estimate PDF page count
     */
    private function estimatePDFPages(int $size): int
    {
        // Very rough estimate: ~50KB per page for typical office documents
        return max(1, intval($size / 50000));
    }

    /**
     * Generate PDF structure hash
     */
    private function generatePDFStructureHash(UploadedFile $file): string
    {
        // Read PDF header and trailer for structural analysis
        $handle = fopen($file->path(), 'rb');
        
        // PDF header (first 100 bytes)
        $header = fread($handle, 100);
        
        // PDF trailer (last 200 bytes)
        fseek($handle, -200, SEEK_END);
        $trailer = fread($handle, 200);
        
        fclose($handle);

        return hash('sha256', $header . $trailer);
    }

    /**
     * Normalize string for signature comparison
     */
    private function normalizeForSignature(string $text): string
    {
        // Remove extra whitespace and convert to lowercase
        $text = strtolower(trim(preg_replace('/\s+/', ' ', $text)));
        
        // Remove common punctuation
        $text = preg_replace('/[^\w\s]/', '', $text);
        
        // Final trim to remove any trailing spaces
        return trim($text);
    }

    /**
     * Compare two fingerprints for similarity
     */
    public function compareFingerprints(array $fingerprint1, array $fingerprint2): array
    {
        $comparison = [
            'overall_similarity' => 0.0,
            'file_match' => false,
            'content_similarity' => 0.0,
            'metadata_similarity' => 0.0,
            'structure_similarity' => 0.0,
            'details' => []
        ];

        // Exact file match
        if (isset($fingerprint1['file_hash']) && isset($fingerprint2['file_hash'])) {
            $comparison['file_match'] = $fingerprint1['file_hash'] === $fingerprint2['file_hash'];
            if ($comparison['file_match']) {
                $comparison['overall_similarity'] = 1.0;
                return $comparison;
            }
        }

        // Size similarity
        if (isset($fingerprint1['file_size']) && isset($fingerprint2['file_size'])) {
            $sizeSimilarity = $this->calculateSizeSimilarity($fingerprint1['file_size'], $fingerprint2['file_size']);
            $comparison['details']['size_similarity'] = $sizeSimilarity;
        }

        // Content structure similarity
        if (isset($fingerprint1['content_structure_hash']) && isset($fingerprint2['content_structure_hash'])) {
            $comparison['structure_similarity'] = $fingerprint1['content_structure_hash'] === $fingerprint2['content_structure_hash'] ? 1.0 : 0.0;
        }

        // Metadata similarity
        if (isset($fingerprint1['metadata_signature']) && isset($fingerprint2['metadata_signature'])) {
            $comparison['metadata_similarity'] = $this->calculateMetadataSimilarity(
                $fingerprint1['metadata_signature'],
                $fingerprint2['metadata_signature']
            );
        }

        // Calculate overall similarity
        $weights = [
            'size' => 0.2,
            'structure' => 0.3,
            'metadata' => 0.5,
        ];

        $weightedSum = 0;
        $totalWeight = 0;

        if (isset($comparison['details']['size_similarity'])) {
            $weightedSum += $comparison['details']['size_similarity'] * $weights['size'];
            $totalWeight += $weights['size'];
        }

        if ($comparison['structure_similarity'] > 0) {
            $weightedSum += $comparison['structure_similarity'] * $weights['structure'];
            $totalWeight += $weights['structure'];
        }

        if ($comparison['metadata_similarity'] > 0) {
            $weightedSum += $comparison['metadata_similarity'] * $weights['metadata'];
            $totalWeight += $weights['metadata'];
        }

        $comparison['overall_similarity'] = $totalWeight > 0 ? $weightedSum / $totalWeight : 0.0;

        return $comparison;
    }

    /**
     * Calculate size similarity
     */
    private function calculateSizeSimilarity(int $size1, int $size2): float
    {
        if ($size1 === $size2) {
            return 1.0;
        }

        $diff = abs($size1 - $size2);
        $avg = ($size1 + $size2) / 2;

        if ($avg === 0) {
            return 0.0;
        }

        $ratio = $diff / $avg;

        return match (true) {
            $ratio <= 0.01 => 0.95,
            $ratio <= 0.05 => 0.80,
            $ratio <= 0.10 => 0.60,
            $ratio <= 0.20 => 0.40,
            $ratio <= 0.50 => 0.20,
            default => 0.0
        };
    }

    /**
     * Calculate metadata similarity
     */
    private function calculateMetadataSimilarity(array $signature1, array $signature2): float
    {
        $commonFields = array_intersect_key($signature1, $signature2);
        
        if (empty($commonFields)) {
            return 0.0;
        }

        $matches = 0;
        $total = count($commonFields);

        foreach ($commonFields as $field => $value1) {
            $value2 = $signature2[$field];
            
            if ($value1 === $value2) {
                $matches++;
            } else {
                // Partial match for similar strings
                $similarity = $this->calculateStringSimilarity($value1, $value2);
                $matches += $similarity;
            }
        }

        return $matches / $total;
    }

    /**
     * Calculate string similarity
     */
    private function calculateStringSimilarity(string $str1, string $str2): float
    {
        if (empty($str1) || empty($str2)) {
            return 0.0;
        }

        $percent = 0;
        similar_text($str1, $str2, $percent);
        
        return $percent / 100.0;
    }

    /**
     * Clean up fingerprints for deleted documents
     */
    public function cleanupFingerprints(string $documentType, int $documentId): void
    {
        // This would be called when documents are deleted
        // For now, we'll just log the cleanup
        Log::info('Fingerprint cleanup requested', [
            'document_type' => $documentType,
            'document_id' => $documentId
        ]);
    }
}