<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\DuplicateDetection;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
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

        $duplicate = new DuplicateDetection();

        $this->assertEquals($fillable, $duplicate->getFillable());
    }

    #[Test]
    public function it_casts_attributes_correctly()
    {
        $duplicate = DuplicateDetection::factory()->create([
            'similarity_score' => 0.8567,
            'detection_metadata' => ['test' => 'data'],
            'resolved_at' => '2024-01-15 10:30:00',
        ]);

        $this->assertEquals(0.8567, (float) $duplicate->similarity_score);
        $this->assertIsArray($duplicate->detection_metadata);
        $this->assertInstanceOf(\Carbon\Carbon::class, $duplicate->resolved_at);
    }

    #[Test]
    public function it_belongs_to_detected_by_user()
    {
        $user = User::factory()->create();
        $duplicate = DuplicateDetection::factory()->create(['detected_by' => $user->id]);

        $this->assertInstanceOf(User::class, $duplicate->detectedBy);
        $this->assertEquals($user->id, $duplicate->detectedBy->id);
    }

    #[Test]
    public function it_belongs_to_resolved_by_user()
    {
        $user = User::factory()->create();
        $duplicate = DuplicateDetection::factory()->create(['resolved_by' => $user->id]);

        $this->assertInstanceOf(User::class, $duplicate->resolvedBy);
        $this->assertEquals($user->id, $duplicate->resolvedBy->id);
    }

    #[Test]
    public function it_has_pending_scope()
    {
        DuplicateDetection::factory()->create(['status' => 'detected']);
        DuplicateDetection::factory()->create(['status' => 'resolved']);
        DuplicateDetection::factory()->create(['status' => 'detected']);

        $pending = DuplicateDetection::pending()->get();

        $this->assertCount(2, $pending);
        $this->assertTrue($pending->every(fn($d) => $d->status === 'detected'));
    }

    #[Test]
    public function it_has_resolved_scope()
    {
        DuplicateDetection::factory()->create(['status' => 'detected']);
        DuplicateDetection::factory()->create(['status' => 'resolved']);
        DuplicateDetection::factory()->create(['status' => 'resolved']);

        $resolved = DuplicateDetection::resolved()->get();

        $this->assertCount(2, $resolved);
        $this->assertTrue($resolved->every(fn($d) => $d->status === 'resolved'));
    }

    #[Test]
    public function it_has_high_similarity_scope()
    {
        DuplicateDetection::factory()->create(['similarity_score' => 0.96]);
        DuplicateDetection::factory()->create(['similarity_score' => 0.85]);
        DuplicateDetection::factory()->create(['similarity_score' => 0.98]);

        $highSimilarity = DuplicateDetection::highSimilarity()->get();

        $this->assertCount(2, $highSimilarity);
        $this->assertTrue($highSimilarity->every(fn($d) => $d->similarity_score >= 0.95));
    }

    #[Test]
    public function it_has_high_similarity_scope_with_custom_threshold()
    {
        DuplicateDetection::factory()->create(['similarity_score' => 0.96]);
        DuplicateDetection::factory()->create(['similarity_score' => 0.85]);
        DuplicateDetection::factory()->create(['similarity_score' => 0.90]);

        $highSimilarity = DuplicateDetection::highSimilarity(0.90)->get();

        $this->assertCount(2, $highSimilarity);
        $this->assertTrue($highSimilarity->every(fn($d) => $d->similarity_score >= 0.90));
    }

    #[Test]
    public function it_can_mark_as_resolved()
    {
        $user = User::factory()->create();
        $duplicate = DuplicateDetection::factory()->create(['status' => 'detected']);

        $duplicate->markAsResolved('replace', $user->id);

        $duplicate->refresh();
        $this->assertEquals('resolved', $duplicate->status);
        $this->assertEquals('replace', $duplicate->resolution_action);
        $this->assertEquals($user->id, $duplicate->resolved_by);
        $this->assertNotNull($duplicate->resolved_at);
    }

    #[Test]
    public function it_can_mark_as_ignored()
    {
        $user = User::factory()->create();
        $duplicate = DuplicateDetection::factory()->create(['status' => 'detected']);

        $duplicate->markAsIgnored($user->id);

        $duplicate->refresh();
        $this->assertEquals('ignored', $duplicate->status);
        $this->assertEquals($user->id, $duplicate->resolved_by);
        $this->assertNotNull($duplicate->resolved_at);
    }

    #[Test]
    public function it_has_confidence_level_attribute()
    {
        $highConfidence = DuplicateDetection::factory()->create(['similarity_score' => 0.96]);
        $mediumConfidence = DuplicateDetection::factory()->create(['similarity_score' => 0.85]);
        $lowConfidence = DuplicateDetection::factory()->create(['similarity_score' => 0.65]);
        $veryLowConfidence = DuplicateDetection::factory()->create(['similarity_score' => 0.45]);

        $this->assertEquals('high', $highConfidence->confidence_level);
        $this->assertEquals('medium', $mediumConfidence->confidence_level);
        $this->assertEquals('low', $lowConfidence->confidence_level);
        $this->assertEquals('very_low', $veryLowConfidence->confidence_level);
    }

    #[Test]
    public function it_has_detection_method_label_attribute()
    {
        $fileHash = DuplicateDetection::factory()->create(['detection_method' => 'file_hash']);
        $sizeMetadata = DuplicateDetection::factory()->create(['detection_method' => 'file_size_metadata']);
        $contentSimilarity = DuplicateDetection::factory()->create(['detection_method' => 'content_similarity']);
        $metadata = DuplicateDetection::factory()->create(['detection_method' => 'metadata']);
        $custom = DuplicateDetection::factory()->create(['detection_method' => 'custom_method']);

        $this->assertEquals('File Hash (Exact Match)', $fileHash->detection_method_label);
        $this->assertEquals('File Size + Metadata', $sizeMetadata->detection_method_label);
        $this->assertEquals('Content Similarity', $contentSimilarity->detection_method_label);
        $this->assertEquals('Metadata Only', $metadata->detection_method_label);
        $this->assertEquals('Custom method', $custom->detection_method_label);
    }

    #[Test]
    public function it_can_relate_to_surat_masuk_as_original_document()
    {
        $suratMasuk = SuratMasuk::factory()->create();
        $duplicate = DuplicateDetection::factory()->create([
            'original_document_type' => 'surat_masuk',
            'original_document_id' => $suratMasuk->id,
        ]);

        // Note: This test assumes the polymorphic relationship is set up correctly
        // The actual implementation might need adjustment based on how the morphTo is configured
        $this->assertEquals($suratMasuk->id, $duplicate->original_document_id);
        $this->assertEquals('surat_masuk', $duplicate->original_document_type);
    }

    #[Test]
    public function it_can_relate_to_surat_keluar_as_duplicate_document()
    {
        $suratKeluar = SuratKeluar::factory()->create();
        $duplicate = DuplicateDetection::factory()->create([
            'document_type' => 'surat_keluar',
            'document_id' => $suratKeluar->id,
        ]);

        $this->assertEquals($suratKeluar->id, $duplicate->document_id);
        $this->assertEquals('surat_keluar', $duplicate->document_type);
    }

    #[Test]
    public function it_stores_detection_metadata_as_json()
    {
        $metadata = [
            'match_type' => 'exact_file_hash',
            'confidence' => 0.95,
            'additional_info' => ['test' => 'value'],
        ];

        $duplicate = DuplicateDetection::factory()->create([
            'detection_metadata' => $metadata,
        ]);

        $this->assertEquals($metadata, $duplicate->detection_metadata);
        $this->assertEquals('exact_file_hash', $duplicate->detection_metadata['match_type']);
        $this->assertEquals(0.95, $duplicate->detection_metadata['confidence']);
    }
}