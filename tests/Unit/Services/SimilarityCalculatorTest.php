<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\SimilarityCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class SimilarityCalculatorTest extends TestCase
{
    private SimilarityCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new SimilarityCalculator();
    }

    #[Test]
    public function it_calculates_perfect_document_similarity()
    {
        $doc1 = [
            'nomor_surat' => 'TEST/001/2024',
            'tanggal_surat' => '2024-01-15',
            'perihal' => 'Surat Penting',
            'dari' => 'Kepala Dinas',
            'kepada' => 'Direktur',
        ];

        $doc2 = [
            'nomor_surat' => 'TEST/001/2024',
            'tanggal_surat' => '2024-01-15',
            'perihal' => 'Surat Penting',
            'dari' => 'Kepala Dinas',
            'kepada' => 'Direktur',
        ];

        $similarity = $this->calculator->calculateDocumentSimilarity($doc1, $doc2);

        $this->assertEquals(1.0, $similarity);
    }

    #[Test]
    public function it_calculates_partial_document_similarity()
    {
        $doc1 = [
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Surat Penting',
            'dari' => 'Kepala Dinas',
        ];

        $doc2 = [
            'nomor_surat' => 'TEST/002/2024', // Different
            'perihal' => 'Surat Penting',     // Same
            'dari' => 'Kepala Dinas',        // Same
        ];

        $similarity = $this->calculator->calculateDocumentSimilarity($doc1, $doc2);

        $this->assertGreaterThan(0.5, $similarity);
        $this->assertLessThan(1.0, $similarity);
    }

    #[Test]
    public function it_calculates_nomor_surat_similarity_with_exact_match()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateNomorSuratSimilarity');
        $method->setAccessible(true);

        $similarity = $method->invoke($this->calculator, 'TEST/001/2024', 'TEST/001/2024');

        $this->assertEquals(1.0, $similarity);
    }

    #[Test]
    public function it_calculates_nomor_surat_similarity_with_pattern_match()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateNomorSuratSimilarity');
        $method->setAccessible(true);

        $similarity = $method->invoke($this->calculator, 'TEST/001/2024', 'TEST/002/2024');

        $this->assertGreaterThan(0.5, $similarity);
        $this->assertLessThan(1.0, $similarity);
    }

    #[Test]
    public function it_extracts_document_pattern_correctly()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('extractDocumentPattern');
        $method->setAccessible(true);

        $pattern = $method->invoke($this->calculator, 'TEST/001/2024');

        $this->assertEquals(['TEST', '001', '2024'], $pattern);
    }

    #[Test]
    public function it_compares_document_patterns()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('compareDocumentPatterns');
        $method->setAccessible(true);

        $pattern1 = ['TEST', '001', '2024'];
        $pattern2 = ['TEST', '001', '2024'];

        $similarity = $method->invoke($this->calculator, $pattern1, $pattern2);

        $this->assertEquals(1.0, $similarity);
    }

    #[Test]
    public function it_detects_numeric_similarity()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('isNumericSimilar');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->calculator, '100', '105')); // Within 10%
        $this->assertFalse($method->invoke($this->calculator, '100', '150')); // Outside 10%
        $this->assertFalse($method->invoke($this->calculator, 'abc', '123')); // Non-numeric
    }

    #[Test]
    public function it_calculates_date_similarity_for_same_day()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateDateSimilarity');
        $method->setAccessible(true);

        $date1 = Carbon::parse('2024-01-15');
        $date2 = Carbon::parse('2024-01-15');

        $similarity = $method->invoke($this->calculator, $date1, $date2);

        $this->assertEquals(1.0, $similarity);
    }

    #[Test]
    public function it_calculates_date_similarity_for_different_days()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateDateSimilarity');
        $method->setAccessible(true);

        $date1 = Carbon::parse('2024-01-15');
        $date2 = Carbon::parse('2024-01-16'); // 1 day difference

        $similarity = $method->invoke($this->calculator, $date1, $date2);

        $this->assertEquals(0.9, $similarity);
    }

    #[Test]
    public function it_calculates_text_similarity()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateTextSimilarity');
        $method->setAccessible(true);

        $similarity = $method->invoke($this->calculator, 'Surat Penting', 'Surat Penting');

        $this->assertEquals(1.0, $similarity);
    }

    #[Test]
    public function it_calculates_fuzzy_text_similarity()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateFuzzyTextSimilarity');
        $method->setAccessible(true);

        $text1 = 'Surat pemberitahuan tentang kegiatan';
        $text2 = 'Surat pemberitahuan mengenai kegiatan';

        $similarity = $method->invoke($this->calculator, $text1, $text2);

        $this->assertGreaterThan(0.7, $similarity);
        $this->assertLessThan(1.0, $similarity);
    }

    #[Test]
    public function it_tokenizes_text_correctly()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('tokenizeText');
        $method->setAccessible(true);

        $tokens = $method->invoke($this->calculator, 'Surat penting untuk direktur');

        $this->assertContains('surat', $tokens);
        $this->assertContains('penting', $tokens);
        $this->assertContains('untuk', $tokens);
        $this->assertContains('direktur', $tokens);
    }

    #[Test]
    public function it_normalizes_text_correctly()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('normalizeText');
        $method->setAccessible(true);

        $normalized = $method->invoke($this->calculator, 'SURAT  Penting!!! @#$');

        $this->assertEquals('surat penting', $normalized);
    }

    #[Test]
    public function it_calculates_file_similarity_with_exact_hash()
    {
        $similarity = $this->calculator->calculateFileSimilarity(
            1000, 1000, 'hash123', 'hash123'
        );

        $this->assertEquals(1.0, $similarity);
    }

    #[Test]
    public function it_calculates_file_similarity_with_same_size()
    {
        $similarity = $this->calculator->calculateFileSimilarity(
            1000, 1000, 'hash1', 'hash2'
        );

        $this->assertEquals(0.9, $similarity);
    }

    #[Test]
    public function it_calculates_file_similarity_with_size_tolerance()
    {
        $similarity = $this->calculator->calculateFileSimilarity(
            1000, 1005 // Within 1%
        );

        $this->assertEquals(0.8, $similarity);
    }

    #[Test]
    public function it_returns_zero_similarity_for_very_different_sizes()
    {
        $similarity = $this->calculator->calculateFileSimilarity(
            1000, 5000 // Very different
        );

        $this->assertEquals(0.0, $similarity);
    }

    #[Test]
    public function it_handles_empty_documents()
    {
        $similarity = $this->calculator->calculateDocumentSimilarity([], []);

        $this->assertEquals(0.0, $similarity);
    }

    #[Test]
    public function it_uses_custom_weights()
    {
        $doc1 = [
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Different',
        ];

        $doc2 = [
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Very Different',
        ];

        $customWeights = [
            'nomor_surat' => 0.9, // High weight on nomor_surat
            'perihal' => 0.1,    // Low weight on perihal
        ];

        $similarity = $this->calculator->calculateDocumentSimilarity($doc1, $doc2, $customWeights);

        $this->assertGreaterThan(0.8, $similarity); // Should be high due to nomor_surat match
    }
}