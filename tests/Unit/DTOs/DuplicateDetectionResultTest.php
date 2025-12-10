<?php

namespace Tests\Unit\DTOs;

use Tests\TestCase;
use App\DTOs\DuplicateDetectionResult;
use App\Models\SuratMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DuplicateDetectionResultTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_be_instantiated_with_all_parameters()
    {
        $document = SuratMasuk::factory()->create();
        
        $result = new DuplicateDetectionResult(
            isDuplicate: true,
            existingDocument: $document,
            similarityScore: 0.95,
            detectionMethod: 'file_hash',
            fileHash: 'test_hash',
            fileSize: 1000,
            metadata: ['test' => 'data'],
            errorMessage: null
        );

        $this->assertTrue($result->isDuplicate);
        $this->assertEquals($document, $result->existingDocument);
        $this->assertEquals(0.95, $result->similarityScore);
        $this->assertEquals('file_hash', $result->detectionMethod);
        $this->assertEquals('test_hash', $result->fileHash);
        $this->assertEquals(1000, $result->fileSize);
        $this->assertEquals(['test' => 'data'], $result->metadata);
        $this->assertNull($result->errorMessage);
    }

    #[Test]
    public function it_can_be_instantiated_with_minimal_parameters()
    {
        $result = new DuplicateDetectionResult(
            isDuplicate: false
        );

        $this->assertFalse($result->isDuplicate);
        $this->assertNull($result->existingDocument);
        $this->assertEquals(0.0, $result->similarityScore);
        $this->assertEquals('', $result->detectionMethod);
        $this->assertEquals('', $result->fileHash);
        $this->assertEquals(0, $result->fileSize);
        $this->assertEquals([], $result->metadata);
        $this->assertNull($result->errorMessage);
    }

    #[Test]
    public function it_converts_to_array_correctly()
    {
        $document = SuratMasuk::factory()->create();
        
        $result = new DuplicateDetectionResult(
            isDuplicate: true,
            existingDocument: $document,
            similarityScore: 0.85,
            detectionMethod: 'file_size_metadata',
            fileHash: 'hash123',
            fileSize: 2000,
            metadata: ['method' => 'test'],
            errorMessage: null
        );

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertTrue($array['is_duplicate']);
        $this->assertEquals($document->toArray(), $array['existing_document']);
        $this->assertEquals(0.85, $array['similarity_score']);
        $this->assertEquals('file_size_metadata', $array['detection_method']);
        $this->assertEquals('hash123', $array['file_hash']);
        $this->assertEquals(2000, $array['file_size']);
        $this->assertEquals(['method' => 'test'], $array['metadata']);
        $this->assertNull($array['error_message']);
    }

    #[Test]
    public function it_converts_to_array_with_null_document()
    {
        $result = new DuplicateDetectionResult(
            isDuplicate: false,
            fileHash: 'hash456',
            fileSize: 1500
        );

        $array = $result->toArray();

        $this->assertFalse($array['is_duplicate']);
        $this->assertNull($array['existing_document']);
        $this->assertEquals('hash456', $array['file_hash']);
        $this->assertEquals(1500, $array['file_size']);
    }

    #[Test]
    public function it_detects_errors_correctly()
    {
        $resultWithError = new DuplicateDetectionResult(
            isDuplicate: false,
            errorMessage: 'Something went wrong'
        );

        $resultWithoutError = new DuplicateDetectionResult(
            isDuplicate: true
        );

        $this->assertTrue($resultWithError->hasError());
        $this->assertFalse($resultWithoutError->hasError());
    }

    #[Test]
    public function it_identifies_high_confidence_correctly()
    {
        $highConfidence = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.96
        );

        $mediumConfidence = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.85
        );

        $this->assertTrue($highConfidence->isHighConfidence());
        $this->assertFalse($mediumConfidence->isHighConfidence());
    }

    #[Test]
    public function it_identifies_medium_confidence_correctly()
    {
        $highConfidence = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.96
        );

        $mediumConfidence = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.85
        );

        $lowConfidence = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.65
        );

        $this->assertFalse($highConfidence->isMediumConfidence());
        $this->assertTrue($mediumConfidence->isMediumConfidence());
        $this->assertFalse($lowConfidence->isMediumConfidence());
    }

    #[Test]
    public function it_identifies_low_confidence_correctly()
    {
        $mediumConfidence = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.85
        );

        $lowConfidence = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.65
        );

        $veryLowConfidence = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.45
        );

        $this->assertFalse($mediumConfidence->isLowConfidence());
        $this->assertTrue($lowConfidence->isLowConfidence());
        $this->assertFalse($veryLowConfidence->isLowConfidence());
    }

    #[Test]
    public function it_handles_boundary_confidence_values()
    {
        $exactHigh = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.95
        );

        $exactMedium = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.80
        );

        $exactLow = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.60
        );

        // Test boundary conditions
        $this->assertTrue($exactHigh->isHighConfidence());
        $this->assertFalse($exactHigh->isMediumConfidence());

        $this->assertTrue($exactMedium->isMediumConfidence());
        $this->assertFalse($exactMedium->isLowConfidence());

        $this->assertTrue($exactLow->isLowConfidence());
        $this->assertFalse($exactLow->isMediumConfidence());
    }

    #[Test]
    public function it_handles_zero_similarity_score()
    {
        $result = new DuplicateDetectionResult(
            isDuplicate: false,
            similarityScore: 0.0
        );

        $this->assertFalse($result->isHighConfidence());
        $this->assertFalse($result->isMediumConfidence());
        $this->assertFalse($result->isLowConfidence());
    }

    #[Test]
    public function it_handles_perfect_similarity_score()
    {
        $result = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 1.0
        );

        $this->assertTrue($result->isHighConfidence());
        $this->assertFalse($result->isMediumConfidence());
        $this->assertFalse($result->isLowConfidence());
    }

    #[Test]
    public function it_is_readonly()
    {
        $result = new DuplicateDetectionResult(
            isDuplicate: true,
            similarityScore: 0.85
        );

        // This should work - reading properties
        $this->assertTrue($result->isDuplicate);
        $this->assertEquals(0.85, $result->similarityScore);

        // Properties are readonly, so we can't test assignment directly
        // but we can verify the readonly nature by checking the class definition
        $reflection = new \ReflectionClass(DuplicateDetectionResult::class);
        $properties = $reflection->getProperties();
        
        foreach ($properties as $property) {
            $this->assertTrue($property->isReadOnly(), "Property {$property->getName()} should be readonly");
        }
    }
}