<?php

namespace App\Services;

class SimilarityCalculator
{
    /**
     * Calculate comprehensive similarity between two documents
     */
    public function calculateDocumentSimilarity(array $doc1, array $doc2, array $weights = []): float
    {
        $defaultWeights = [
            'nomor_surat' => 0.35,
            'tanggal_surat' => 0.15,
            'perihal' => 0.30,
            'dari' => 0.10,
            'kepada' => 0.05,
            'tujuan' => 0.05,
        ];

        $weights = array_merge($defaultWeights, $weights);
        $totalScore = 0.0;
        $totalWeight = 0.0;

        foreach ($weights as $field => $weight) {
            if (isset($doc1[$field]) && isset($doc2[$field])) {
                $fieldSimilarity = $this->calculateFieldSimilarity($field, $doc1[$field], $doc2[$field]);
                $totalScore += $fieldSimilarity * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0.0;
    }

    /**
     * Calculate similarity for specific field types
     */
    private function calculateFieldSimilarity(string $fieldName, $value1, $value2): float
    {
        return match ($fieldName) {
            'nomor_surat' => $this->calculateNomorSuratSimilarity($value1, $value2),
            'tanggal_surat', 'tanggal_surat_masuk', 'tanggal_keluar' => $this->calculateDateSimilarity($value1, $value2),
            'perihal' => $this->calculateTextSimilarity($value1, $value2, true),
            'dari', 'kepada', 'tujuan' => $this->calculateTextSimilarity($value1, $value2, false),
            default => $this->calculateTextSimilarity($value1, $value2, false)
        };
    }

    /**
     * Calculate similarity for nomor surat (special handling for document numbers)
     */
    private function calculateNomorSuratSimilarity(string $nomor1, string $nomor2): float
    {
        // Exact match gets highest score
        if (strtolower(trim($nomor1)) === strtolower(trim($nomor2))) {
            return 1.0;
        }

        // Extract numbers and patterns from document numbers
        $pattern1 = $this->extractDocumentPattern($nomor1);
        $pattern2 = $this->extractDocumentPattern($nomor2);

        // Compare patterns
        $patternSimilarity = $this->compareDocumentPatterns($pattern1, $pattern2);
        
        // Also do text similarity as fallback
        $textSimilarity = $this->calculateTextSimilarity($nomor1, $nomor2, false);

        // Return the higher of the two scores
        return max($patternSimilarity, $textSimilarity);
    }

    /**
     * Extract pattern from document number (e.g., "123/SE/2024" -> ["123", "SE", "2024"])
     */
    private function extractDocumentPattern(string $nomor): array
    {
        // Common separators in Indonesian document numbers
        $separators = ['/', '-', '.', '_'];
        
        $parts = preg_split('/[\/\-\._]/', $nomor);
        
        return array_map('trim', array_filter($parts));
    }

    /**
     * Compare document number patterns
     */
    private function compareDocumentPatterns(array $pattern1, array $pattern2): float
    {
        if (empty($pattern1) || empty($pattern2)) {
            return 0.0;
        }

        $matches = 0;
        $totalParts = max(count($pattern1), count($pattern2));

        // Compare each part
        for ($i = 0; $i < min(count($pattern1), count($pattern2)); $i++) {
            if (strtolower($pattern1[$i]) === strtolower($pattern2[$i])) {
                $matches++;
            } elseif ($this->isNumericSimilar($pattern1[$i], $pattern2[$i])) {
                $matches += 0.5; // Partial match for similar numbers
            }
        }

        return $matches / $totalParts;
    }

    /**
     * Check if two strings are numerically similar
     */
    private function isNumericSimilar(string $str1, string $str2): bool
    {
        if (!is_numeric($str1) || !is_numeric($str2)) {
            return false;
        }

        $num1 = (int) $str1;
        $num2 = (int) $str2;

        // Consider numbers similar if they're within 10% of each other
        $diff = abs($num1 - $num2);
        $avg = ($num1 + $num2) / 2;

        return $avg > 0 && ($diff / $avg) <= 0.1;
    }

    /**
     * Calculate date similarity
     */
    private function calculateDateSimilarity($date1, $date2): float
    {
        try {
            $d1 = is_string($date1) ? \Carbon\Carbon::parse($date1) : $date1;
            $d2 = is_string($date2) ? \Carbon\Carbon::parse($date2) : $date2;

            if ($d1->isSameDay($d2)) {
                return 1.0;
            }

            // Calculate similarity based on date difference
            $diffInDays = abs($d1->diffInDays($d2));
            
            return match (true) {
                $diffInDays <= 1 => 0.9,
                $diffInDays <= 7 => 0.7,
                $diffInDays <= 30 => 0.5,
                $diffInDays <= 90 => 0.3,
                default => 0.1
            };

        } catch (\Exception $e) {
            // If date parsing fails, fall back to string comparison
            return $this->calculateTextSimilarity((string) $date1, (string) $date2, false);
        }
    }

    /**
     * Calculate text similarity with optional fuzzy matching
     */
    private function calculateTextSimilarity(string $text1, string $text2, bool $useFuzzy = false): float
    {
        if (empty($text1) || empty($text2)) {
            return 0.0;
        }

        // Normalize text
        $text1 = $this->normalizeText($text1);
        $text2 = $this->normalizeText($text2);

        // Exact match
        if ($text1 === $text2) {
            return 1.0;
        }

        // Use different algorithms based on text length and fuzzy flag
        if ($useFuzzy && (strlen($text1) > 10 || strlen($text2) > 10)) {
            return $this->calculateFuzzyTextSimilarity($text1, $text2);
        }

        // Simple similarity for short texts
        $percent = 0;
        similar_text($text1, $text2, $percent);
        
        return $percent / 100.0;
    }

    /**
     * Calculate fuzzy text similarity for longer texts
     */
    private function calculateFuzzyTextSimilarity(string $text1, string $text2): float
    {
        // Tokenize texts into words
        $words1 = $this->tokenizeText($text1);
        $words2 = $this->tokenizeText($text2);

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        // Calculate Jaccard similarity (intersection over union)
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));

        $jaccardSimilarity = $union > 0 ? $intersection / $union : 0.0;

        // Also calculate word order similarity
        $orderSimilarity = $this->calculateWordOrderSimilarity($words1, $words2);

        // Combine both similarities (weighted average)
        return ($jaccardSimilarity * 0.7) + ($orderSimilarity * 0.3);
    }

    /**
     * Calculate word order similarity
     */
    private function calculateWordOrderSimilarity(array $words1, array $words2): float
    {
        $commonWords = array_intersect($words1, $words2);
        
        if (empty($commonWords)) {
            return 0.0;
        }

        $orderMatches = 0;
        $totalCommon = count($commonWords);

        foreach ($commonWords as $word) {
            $pos1 = array_search($word, $words1);
            $pos2 = array_search($word, $words2);
            
            // Normalize positions to 0-1 range (avoid division by zero)
            $normalizedPos1 = count($words1) > 1 ? $pos1 / (count($words1) - 1) : 0;
            $normalizedPos2 = count($words2) > 1 ? $pos2 / (count($words2) - 1) : 0;
            
            // Calculate position similarity (closer positions = higher similarity)
            $positionSimilarity = 1 - abs($normalizedPos1 - $normalizedPos2);
            $orderMatches += $positionSimilarity;
        }

        return $orderMatches / $totalCommon;
    }

    /**
     * Normalize text for comparison
     */
    private function normalizeText(string $text): string
    {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove common punctuation
        $text = preg_replace('/[^\w\s]/', '', $text);
        
        return trim($text);
    }

    /**
     * Tokenize text into words
     */
    private function tokenizeText(string $text): array
    {
        $words = preg_split('/\s+/', $this->normalizeText($text));
        
        // Remove empty strings and very short words
        return array_filter($words, fn($word) => strlen($word) > 2);
    }

    /**
     * Calculate file-based similarity
     */
    public function calculateFileSimilarity(int $size1, int $size2, string $hash1 = '', string $hash2 = ''): float
    {
        // Exact hash match
        if (!empty($hash1) && !empty($hash2) && $hash1 === $hash2) {
            return 1.0;
        }

        // Size similarity
        if ($size1 === $size2) {
            return 0.9; // High similarity for same size
        }

        // Calculate size similarity with tolerance
        $sizeDiff = abs($size1 - $size2);
        $avgSize = ($size1 + $size2) / 2;

        if ($avgSize === 0) {
            return 0.0;
        }

        $sizeRatio = $sizeDiff / $avgSize;

        return match (true) {
            $sizeRatio <= 0.01 => 0.8, // Within 1%
            $sizeRatio <= 0.05 => 0.6, // Within 5%
            $sizeRatio <= 0.10 => 0.4, // Within 10%
            $sizeRatio <= 0.20 => 0.2, // Within 20%
            default => 0.0
        };
    }
}