<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\DocumentFingerprintService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class DocumentFingerprintServiceTest extends TestCase
{
    private DocumentFingerprintService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocumentFingerprintService();
        Storage::fake('public');
    }

    #[Test]
    public function it_generates_basic_fingerprint()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        
        $fingerprint = $this->service->generateFingerprint($file);

        $this->assertArrayHasKey('file_hash', $fingerprint);
        $this->assertArrayHasKey('file_size', $fingerprint);
        $this->assertArrayHasKey('mime_type', $fingerprint);
        $this->assertArrayHasKey('original_name', $fingerprint);
        $this->assertArrayHasKey('extension', $fingerprint);
        $this->assertArrayHasKey('created_at', $fingerprint);

        $this->assertEquals(100 * 1024, $fingerprint['file_size']); // 100KB in bytes
        $this->assertEquals('application/pdf', $fingerprint['mime_type']);
        $this->assertEquals('document.pdf', $fingerprint['original_name']);
        $this->assertEquals('pdf', $fingerprint['extension']);
    }

    #[Test]
    public function it_generates_file_hash()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $hash = $this->service->generateFileHash($file);

        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash)); // SHA-256 produces 64 character hex string
    }

    #[Test]
    public function it_generates_fingerprint_with_metadata()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $metadata = [
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Test Document',
            'dari' => 'Test Sender',
        ];

        $fingerprint = $this->service->generateFingerprint($file, $metadata);

        $this->assertArrayHasKey('metadata_hash', $fingerprint);
        $this->assertArrayHasKey('metadata_signature', $fingerprint);
        $this->assertIsString($fingerprint['metadata_hash']);
        $this->assertIsArray($fingerprint['metadata_signature']);
    }

    #[Test]
    public function it_identifies_pdf_files()
    {
        $pdfFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        
        $fingerprint = $this->service->generateFingerprint($pdfFile);

        $this->assertArrayHasKey('is_pdf', $fingerprint);
        $this->assertTrue($fingerprint['is_pdf']);
        $this->assertArrayHasKey('pdf_size_category', $fingerprint);
        $this->assertArrayHasKey('estimated_pages', $fingerprint);
    }

    #[Test]
    public function it_categorizes_pdf_sizes_correctly()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('categorizePDFSize');
        $method->setAccessible(true);

        $this->assertEquals('small', $method->invoke($this->service, 50000));      // 50KB
        $this->assertEquals('medium', $method->invoke($this->service, 500000));    // 500KB
        $this->assertEquals('large', $method->invoke($this->service, 2000000));    // 2MB
        $this->assertEquals('very_large', $method->invoke($this->service, 10000000)); // 10MB
    }

    #[Test]
    public function it_estimates_pdf_pages()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('estimatePDFPages');
        $method->setAccessible(true);

        $this->assertEquals(1, $method->invoke($this->service, 25000));  // 25KB -> 1 page
        $this->assertEquals(2, $method->invoke($this->service, 100000)); // 100KB -> 2 pages
        $this->assertEquals(10, $method->invoke($this->service, 500000)); // 500KB -> 10 pages
    }

    #[Test]
    public function it_identifies_text_extractable_files()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('isTextExtractable');
        $method->setAccessible(true);

        $pdfFile = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $docFile = UploadedFile::fake()->create('doc.doc', 100, 'application/msword');
        $docxFile = UploadedFile::fake()->create('doc.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $imageFile = UploadedFile::fake()->image('image.jpg');

        $this->assertTrue($method->invoke($this->service, $pdfFile));
        $this->assertTrue($method->invoke($this->service, $docFile));
        $this->assertTrue($method->invoke($this->service, $docxFile));
        $this->assertFalse($method->invoke($this->service, $imageFile));
    }

    #[Test]
    public function it_estimates_text_length_by_file_type()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('estimateTextLength');
        $method->setAccessible(true);

        $pdfFile = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $docFile = UploadedFile::fake()->create('doc.doc', 100, 'application/msword');
        $docxFile = UploadedFile::fake()->create('doc.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $pdfLength = $method->invoke($this->service, $pdfFile);
        $docLength = $method->invoke($this->service, $docFile);
        $docxLength = $method->invoke($this->service, $docxFile);

        $this->assertIsInt($pdfLength);
        $this->assertIsInt($docLength);
        $this->assertIsInt($docxLength);
        $this->assertGreaterThan(0, $pdfLength);
        $this->assertGreaterThan(0, $docLength);
        $this->assertGreaterThan(0, $docxLength);
    }

    #[Test]
    public function it_normalizes_text_for_signature()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('normalizeForSignature');
        $method->setAccessible(true);

        $normalized = $method->invoke($this->service, 'SURAT  Penting!!! @#$');

        $this->assertEquals('surat penting', $normalized);
    }

    #[Test]
    public function it_compares_fingerprints_with_exact_match()
    {
        $fingerprint1 = [
            'file_hash' => 'same_hash',
            'file_size' => 1000,
        ];

        $fingerprint2 = [
            'file_hash' => 'same_hash',
            'file_size' => 1000,
        ];

        $comparison = $this->service->compareFingerprints($fingerprint1, $fingerprint2);

        $this->assertTrue($comparison['file_match']);
        $this->assertEquals(1.0, $comparison['overall_similarity']);
    }

    #[Test]
    public function it_compares_fingerprints_with_size_similarity()
    {
        $fingerprint1 = [
            'file_hash' => 'hash1',
            'file_size' => 1000,
        ];

        $fingerprint2 = [
            'file_hash' => 'hash2',
            'file_size' => 1005, // Very close size
        ];

        $comparison = $this->service->compareFingerprints($fingerprint1, $fingerprint2);

        $this->assertFalse($comparison['file_match']);
        $this->assertGreaterThan(0.0, $comparison['overall_similarity']);
        $this->assertArrayHasKey('size_similarity', $comparison['details']);
    }

    #[Test]
    public function it_compares_fingerprints_with_metadata_similarity()
    {
        $fingerprint1 = [
            'file_hash' => 'hash1',
            'metadata_signature' => [
                'nomor_surat' => 'test001',
                'perihal' => 'surat penting',
            ],
        ];

        $fingerprint2 = [
            'file_hash' => 'hash2',
            'metadata_signature' => [
                'nomor_surat' => 'test001',
                'perihal' => 'surat penting',
            ],
        ];

        $comparison = $this->service->compareFingerprints($fingerprint1, $fingerprint2);

        $this->assertEquals(1.0, $comparison['metadata_similarity']);
    }

    #[Test]
    public function it_calculates_size_similarity()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateSizeSimilarity');
        $method->setAccessible(true);

        $this->assertEquals(1.0, $method->invoke($this->service, 1000, 1000)); // Exact match
        $this->assertEquals(0.95, $method->invoke($this->service, 1000, 1005)); // Within 1%
        $this->assertEquals(0.0, $method->invoke($this->service, 1000, 2000)); // Very different
    }

    #[Test]
    public function it_calculates_metadata_similarity()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateMetadataSimilarity');
        $method->setAccessible(true);

        $signature1 = [
            'nomor_surat' => 'test001',
            'perihal' => 'surat penting',
        ];

        $signature2 = [
            'nomor_surat' => 'test001',
            'perihal' => 'surat penting',
        ];

        $similarity = $method->invoke($this->service, $signature1, $signature2);

        $this->assertEquals(1.0, $similarity);
    }

    #[Test]
    public function it_handles_errors_gracefully()
    {
        // Create a real temporary file for the mock to use
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'test content');
        
        // Create a mock file that will cause an error in getMimeType (after hash is generated)
        $file = \Mockery::mock(UploadedFile::class);
        $file->shouldReceive('path')->andReturn($tempFile);
        $file->shouldReceive('getSize')->andReturn(12);
        $file->shouldReceive('getMimeType')->andThrow(new \Exception('MIME type error'));
        $file->shouldReceive('getClientOriginalName')->andReturn('test.pdf');
        $file->shouldReceive('getClientOriginalExtension')->andReturn('pdf');

        $fingerprint = $this->service->generateFingerprint($file);

        // Clean up temp file
        @unlink($tempFile);

        // The error handling should return a partial fingerprint
        $this->assertArrayHasKey('error', $fingerprint);
        $this->assertStringContainsString('Partial fingerprint', $fingerprint['error']);
    }

    #[Test]
    public function it_logs_cleanup_requests()
    {
        // This test verifies that cleanup method doesn't throw errors
        $this->service->cleanupFingerprints('surat_masuk', 123);
        
        // If we reach here without exception, the test passes
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}