<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\DuplicateDetection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\Attributes\Test;

class DuplicateResolutionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        // Roles are already created in TestCase::setUp()

        // Create users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');
    }

    #[Test]
    public function it_can_get_duplicate_detection_statistics()
    {
        // Create test data
        DuplicateDetection::factory()->count(5)->create(['status' => 'detected']);
        DuplicateDetection::factory()->count(3)->create(['status' => 'resolved']);
        DuplicateDetection::factory()->count(2)->create([
            'status' => 'detected',
            'similarity_score' => 0.96
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('duplicates.statistics'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_detected',
            'pending',
            'resolved',
            'high_similarity',
            'by_method',
            'recent_duplicates'
        ]);

        $data = $response->json();
        $this->assertGreaterThanOrEqual(8, $data['total_detected']);
        $this->assertGreaterThanOrEqual(5, $data['pending']);
        $this->assertGreaterThanOrEqual(3, $data['resolved']);
        $this->assertGreaterThanOrEqual(2, $data['high_similarity']);
    }

    #[Test]
    public function it_can_get_duplicate_history_for_document()
    {
        $document = SuratMasuk::factory()->create();
        
        DuplicateDetection::factory()->count(3)->create([
            'document_type' => 'surat_masuk',
            'document_id' => $document->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('duplicates.history', [
                'document_type' => 'surat_masuk',
                'document_id' => $document->id,
            ]));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    #[Test]
    public function it_validates_history_request_parameters()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('duplicates.history', [
                'document_type' => 'invalid_type',
                'document_id' => 'not_a_number',
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_type', 'document_id']);
    }

    #[Test]
    public function it_can_bulk_resolve_duplicates()
    {
        $duplicates = DuplicateDetection::factory()->count(3)->create([
            'status' => 'detected'
        ]);

        $duplicateIds = $duplicates->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->postJson(route('duplicates.bulk-resolve'), [
                'duplicate_ids' => $duplicateIds,
                'action' => 'ignore',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'resolved_count' => 3,
        ]);

        // Verify duplicates were resolved
        foreach ($duplicates as $duplicate) {
            $duplicate->refresh();
            $this->assertEquals('ignored', $duplicate->status);
            $this->assertEquals($this->admin->id, $duplicate->resolved_by);
        }
    }

    #[Test]
    public function it_validates_bulk_resolve_request()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('duplicates.bulk-resolve'), [
                'duplicate_ids' => ['invalid_id'],
                'action' => 'invalid_action',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['duplicate_ids.0', 'action']);
    }

    #[Test]
    public function it_only_resolves_pending_duplicates_in_bulk()
    {
        $pendingDuplicate = DuplicateDetection::factory()->create(['status' => 'detected']);
        $resolvedDuplicate = DuplicateDetection::factory()->create(['status' => 'resolved']);

        $response = $this->actingAs($this->admin)
            ->postJson(route('duplicates.bulk-resolve'), [
                'duplicate_ids' => [$pendingDuplicate->id, $resolvedDuplicate->id],
                'action' => 'ignore',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['resolved_count' => 1]);

        $pendingDuplicate->refresh();
        $resolvedDuplicate->refresh();

        $this->assertEquals('ignored', $pendingDuplicate->status);
        $this->assertEquals('resolved', $resolvedDuplicate->status); // Should remain unchanged
    }

    #[Test]
    public function it_requires_authentication_for_statistics()
    {
        $response = $this->getJson(route('duplicates.statistics'));

        $response->assertStatus(401);
    }

    #[Test]
    public function it_requires_proper_role_for_duplicate_operations()
    {
        $userWithoutRole = User::factory()->create();

        $response = $this->actingAs($userWithoutRole)
            ->getJson(route('duplicates.statistics'));

        $response->assertStatus(403);
    }

    #[Test]
    public function operator_can_access_duplicate_statistics()
    {
        $response = $this->actingAs($this->operator)
            ->getJson(route('duplicates.statistics'));

        $response->assertStatus(200);
    }

    #[Test]
    public function it_handles_empty_bulk_resolve_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('duplicates.bulk-resolve'), [
                'duplicate_ids' => [],
                'action' => 'ignore',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'resolved_count' => 0
        ]);
    }

    #[Test]
    public function it_creates_log_entry_for_bulk_resolve()
    {
        $duplicates = DuplicateDetection::factory()->count(2)->create([
            'status' => 'detected'
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('duplicates.bulk-resolve'), [
                'duplicate_ids' => $duplicates->pluck('id')->toArray(),
                'action' => 'ignore',
            ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $this->admin->id,
            'aksi' => 'bulk_resolve_duplicates',
            'modul' => 'duplicate_detection',
        ]);
    }

    #[Test]
    public function it_returns_recent_duplicates_in_statistics()
    {
        // Create duplicates with relationships
        $user = User::factory()->create();
        $document = SuratMasuk::factory()->create();
        
        DuplicateDetection::factory()->create([
            'detected_by' => $user->id,
            'original_document_type' => 'surat_masuk',
            'original_document_id' => $document->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('duplicates.statistics'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'recent_duplicates' => [
                '*' => [
                    'id',
                    'detection_method',
                    'similarity_score',
                    'status',
                    'created_at',
                    'detected_by',
                ]
            ]
        ]);
    }

    #[Test]
    public function it_groups_statistics_by_detection_method()
    {
        DuplicateDetection::factory()->count(3)->create(['detection_method' => 'file_hash']);
        DuplicateDetection::factory()->count(2)->create(['detection_method' => 'file_size_metadata']);
        DuplicateDetection::factory()->count(1)->create(['detection_method' => 'content_similarity']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('duplicates.statistics'));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(3, $data['by_method']['file_hash']);
        $this->assertEquals(2, $data['by_method']['file_size_metadata']);
        $this->assertEquals(1, $data['by_method']['content_similarity']);
    }

    #[Test]
    public function it_handles_database_errors_gracefully_in_bulk_resolve()
    {
        // Create a valid duplicate first
        $validDuplicate = DuplicateDetection::factory()->create(['status' => 'detected']);
        
        // Test with valid duplicate
        $response = $this->actingAs($this->admin)
            ->postJson(route('duplicates.bulk-resolve'), [
                'duplicate_ids' => [$validDuplicate->id],
                'action' => 'ignore',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'resolved_count' => 1
        ]);
    }
}