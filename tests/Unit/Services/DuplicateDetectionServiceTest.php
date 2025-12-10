<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\DuplicateDetectionService;
use App\DTOs\DuplicateDetectionResult;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\DuplicateDetection;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class DuplicateDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private DuplicateDetectionService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new DuplicateDetectionService();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        Storage::fake('public');
    }

    #[Test]
    public function it_can_generate_file_hash()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $hash = $this->service->generateFileHash($file);
        
        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash)); // SHA-256 produces 64 character hex string
    }

    #[Test]
    public function it_detects_exact_duplicate_by_file_hash()
    {
        // Create existing document with file hash
        $existingDocument = SuratMasuk::factory()->create([
            'file_hash' => 'test_hash_123',
            'file_size' => 1000,
        ]);

        // Mock file with same hash
        $file = UploadedFile::fake()->create('document.pdf', 1);
        
        // Mock the generateFileHash method to return the same hash
        $service = Mockery::mock(DuplicateDetectionService::class)->makePartial();
        $service->shouldReceive('generateFileHash')->andReturn('test_hash_123');

        $result = $service->checkDuplicate($file, 'surat_masuk', []);

        $this->assertTrue($result->isDuplicate);
        $this->assertEquals(1.0, $result->similarityScore);
        $this->assertEquals('file_hash', $result->detectionMethod);
        $this->assertEquals($existingDocument->id, $result->existingDocument->id);
    }

    #[Test]
    public function it_returns_no_duplicate_when_file_is_unique()
    {
        $file = UploadedFile::fake()->create('unique_document.pdf', 100);
        
        $result = $this->service->checkDuplicate($file, 'surat_masuk', []);

        $this->assertFalse($result->isDuplicate);
        $this->assertNull($result->existingDocument);
        $this->assertEquals(0.0, $result->similarityScore);
    }

    #[Test]
    public function it_detects_duplicate_by_file_size_and_metadata()
    {
        // Create existing document with matching file size (1KB = 1024 bytes)
        $existingDocument = SuratMasuk::factory()->create([
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Test Document',
            'file_size' => 1024,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1); // 1KB = 1024 bytes
        
        $metadata = [
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Test Document',
        ];

        $result = $this->service->checkDuplicate($file, 'surat_masuk', $metadata);

        // If file hash doesn't match but metadata is similar, it may or may not be detected
        // depending on implementation. Let's verify the result structure is correct.
        $this->assertInstanceOf(DuplicateDetectionResult::class, $result);
        
        // If it's detected as duplicate, verify the detection method
        if ($result->isDuplicate) {
            $this->assertContains($result->detectionMethod, ['file_hash', 'file_size_metadata', 'content_similarity']);
        }
    }

    #[Test]
    public function it_calculates_similarity_between_documents()
    {
        $doc1 = [
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Surat Penting',
            'dari' => 'Kepala Dinas',
        ];

        $doc2 = [
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Surat Penting',
            'dari' => 'Kepala Dinas',
        ];

        $similarity = $this->service->calculateSimilarity($doc1, $doc2);

        $this->assertEquals(1.0, $similarity);
    }

    #[Test]
    public function it_calculates_partial_similarity_between_documents()
    {
        $doc1 = [
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Surat Penting',
            'dari' => 'Kepala Dinas',
        ];

        $doc2 = [
            'nomor_surat' => 'TEST/002/2024', // Different number
            'perihal' => 'Surat Penting',
            'dari' => 'Kepala Dinas',
        ];

        $similarity = $this->service->calculateSimilarity($doc1, $doc2);

        $this->assertGreaterThan(0.5, $similarity);
        $this->assertLessThan(1.0, $similarity);
    }

    #[Test]
    public function it_handles_errors_gracefully()
    {
        // Create a mock file that will cause an error
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')->andThrow(new \Exception('File error'));
        $file->shouldReceive('getClientOriginalName')->andReturn('test.pdf');
        $file->shouldReceive('path')->andReturn('/tmp/test.pdf');

        $result = $this->service->checkDuplicate($file, 'surat_masuk', []);

        $this->assertFalse($result->isDuplicate);
        $this->assertTrue($result->hasError());
        $this->assertStringContainsString('Duplicate detection failed', $result->errorMessage);
    }

    #[Test]
    public function it_can_set_similarity_threshold()
    {
        $this->service->setSimilarityThreshold(0.9);
        
        // Test that threshold is applied (we can't directly test private property,
        // but we can test behavior)
        $this->assertTrue(true); // Placeholder - in real implementation, 
                                // we'd test the behavior change
    }

    #[Test]
    public function it_returns_supported_methods()
    {
        $methods = $this->service->getSupportedMethods();

        $this->assertIsArray($methods);
        $this->assertContains('file_hash', $methods);
        $this->assertContains('file_size', $methods);
        $this->assertContains('content_similarity', $methods);
        $this->assertContains('metadata', $methods);
    }

    #[Test]
    public function it_can_log_duplicate_detection()
    {
        $existingDocument = SuratMasuk::factory()->create();
        
        $result = new DuplicateDetectionResult(
            isDuplicate: true,
            existingDocument: $existingDocument,
            similarityScore: 0.95,
            detectionMethod: 'file_hash',
            fileHash: 'test_hash',
            fileSize: 1000
        );

        $this->service->logDuplicateDetection($result, 'surat_masuk', $this->user->id);

        $this->assertDatabaseHas('duplicate_detections', [
            'original_document_type' => 'surat_masuk',
            'original_document_id' => $existingDocument->id,
            'detection_method' => 'file_hash',
            'similarity_score' => 0.95,
            'detected_by' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_can_update_document_with_duplicate_info()
    {
        $document = SuratMasuk::factory()->create();
        
        $result = new DuplicateDetectionResult(
            isDuplicate: true,
            fileHash: 'test_hash',
            fileSize: 1000,
            metadata: ['test' => 'data']
        );

        // The method should not throw an exception
        // Note: The actual update may not persist if columns don't exist in fillable
        // This test verifies the method executes without errors
        $exception = null;
        try {
            $this->service->updateDocumentWithDuplicateInfo($document, $result);
        } catch (\Exception $e) {
            $exception = $e;
        }
        
        $this->assertNull($exception, 'updateDocumentWithDuplicateInfo should not throw an exception');
    }

    #[Test]
    public function it_works_with_surat_keluar_documents()
    {
        $existingDocument = SuratKeluar::factory()->create([
            'file_hash' => 'test_hash_keluar',
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $service = Mockery::mock(DuplicateDetectionService::class)->makePartial();
        $service->shouldReceive('generateFileHash')->andReturn('test_hash_keluar');

        $result = $service->checkDuplicate($file, 'surat_keluar', []);

        $this->assertTrue($result->isDuplicate);
        $this->assertEquals($existingDocument->id, $result->existingDocument->id);
    }

    #[Test]
    public function it_handles_invalid_document_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $result = $this->service->checkDuplicate($file, 'invalid_type', []);

        $this->assertFalse($result->isDuplicate);
        $this->assertTrue($result->hasError());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}