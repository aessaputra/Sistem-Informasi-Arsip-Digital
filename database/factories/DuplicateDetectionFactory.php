<?php

namespace Database\Factories;

use App\Models\DuplicateDetection;
use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use Illuminate\Database\Eloquent\Factories\Factory;

class DuplicateDetectionFactory extends Factory
{
    protected $model = DuplicateDetection::class;

    public function definition(): array
    {
        $documentTypes = ['surat_masuk', 'surat_keluar'];
        $documentType = $this->faker->randomElement($documentTypes);
        $originalDocumentType = $this->faker->randomElement($documentTypes);
        
        // Create documents if they don't exist
        $document = $documentType === 'surat_masuk' 
            ? SuratMasuk::factory()->create()
            : SuratKeluar::factory()->create();
            
        $originalDocument = $originalDocumentType === 'surat_masuk'
            ? SuratMasuk::factory()->create()
            : SuratKeluar::factory()->create();

        return [
            'document_type' => $documentType,
            'document_id' => $document->id,
            'original_document_type' => $originalDocumentType,
            'original_document_id' => $originalDocument->id,
            'detection_method' => $this->faker->randomElement([
                'file_hash',
                'file_size_metadata',
                'content_similarity',
                'metadata'
            ]),
            'similarity_score' => $this->faker->randomFloat(4, 0.6, 1.0),
            'status' => $this->faker->randomElement(['detected', 'resolved', 'ignored']),
            'resolution_action' => $this->faker->optional()->randomElement([
                'replace',
                'skip',
                'force_save',
                'ignore'
            ]),
            'detection_metadata' => [
                'match_type' => $this->faker->randomElement(['exact_file_hash', 'size_metadata', 'content_similar']),
                'confidence' => $this->faker->randomFloat(2, 0.6, 1.0),
                'additional_info' => [
                    'file_size_diff' => $this->faker->numberBetween(0, 1000),
                    'metadata_matches' => $this->faker->numberBetween(1, 5),
                ]
            ],
            'detected_by' => User::factory(),
            'resolved_by' => $this->faker->optional(0.3)->passthrough(User::factory()),
            'resolved_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'detected',
            'resolved_by' => null,
            'resolved_at' => null,
            'resolution_action' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_by' => User::factory(),
            'resolved_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'resolution_action' => $this->faker->randomElement(['replace', 'skip', 'force_save']),
        ]);
    }

    public function ignored(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ignored',
            'resolved_by' => User::factory(),
            'resolved_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'resolution_action' => 'ignore',
        ]);
    }

    public function highSimilarity(): static
    {
        return $this->state(fn (array $attributes) => [
            'similarity_score' => $this->faker->randomFloat(4, 0.95, 1.0),
            'detection_method' => 'file_hash',
        ]);
    }

    public function mediumSimilarity(): static
    {
        return $this->state(fn (array $attributes) => [
            'similarity_score' => $this->faker->randomFloat(4, 0.80, 0.94),
            'detection_method' => 'file_size_metadata',
        ]);
    }

    public function lowSimilarity(): static
    {
        return $this->state(fn (array $attributes) => [
            'similarity_score' => $this->faker->randomFloat(4, 0.60, 0.79),
            'detection_method' => 'content_similarity',
        ]);
    }

    public function fileHashMethod(): static
    {
        return $this->state(fn (array $attributes) => [
            'detection_method' => 'file_hash',
            'similarity_score' => 1.0,
            'detection_metadata' => [
                'match_type' => 'exact_file_hash',
                'confidence' => 1.0,
                'file_hash' => $this->faker->sha256,
            ],
        ]);
    }

    public function sizeMetadataMethod(): static
    {
        return $this->state(fn (array $attributes) => [
            'detection_method' => 'file_size_metadata',
            'similarity_score' => $this->faker->randomFloat(4, 0.80, 0.95),
            'detection_metadata' => [
                'match_type' => 'size_metadata',
                'confidence' => $this->faker->randomFloat(2, 0.80, 0.95),
                'file_size_similarity' => $this->faker->randomFloat(2, 0.85, 1.0),
                'metadata_similarity' => $this->faker->randomFloat(2, 0.75, 0.95),
            ],
        ]);
    }

    public function contentSimilarityMethod(): static
    {
        return $this->state(fn (array $attributes) => [
            'detection_method' => 'content_similarity',
            'similarity_score' => $this->faker->randomFloat(4, 0.70, 0.90),
            'detection_metadata' => [
                'match_type' => 'content_similar',
                'confidence' => $this->faker->randomFloat(2, 0.70, 0.90),
                'text_similarity' => $this->faker->randomFloat(2, 0.65, 0.85),
                'structure_similarity' => $this->faker->randomFloat(2, 0.70, 0.95),
            ],
        ]);
    }
}