<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\KlasifikasiSurat;
use App\Models\DuplicateDetection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\Attributes\Test;

class DuplicateDetectionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private KlasifikasiSurat $klasifikasi;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles are already created in TestCase::setUp()

        // Create users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        // Create classification
        $this->klasifikasi = KlasifikasiSurat::factory()->create();

        Storage::fake('public');
    }

    #[Test]
    public function it_detects_duplicate_when_uploading_same_file_to_surat_masuk()
    {
        // Create existing document with file
        $existingFile = UploadedFile::fake()->create('document.pdf', 100);
        $existingFileHash = hash_file('sha256', $existingFile->path());
        
        $existingDocument = SuratMasuk::factory()->create([
            'file_hash' => $existingFileHash,
            'file_size' => $existingFile->getSize(),
            'file_path' => 'surat-masuk/existing.pdf',
        ]);

        // Try to upload the same file
        $duplicateFile = UploadedFile::fake()->create('duplicate.pdf', 100);
        // Mock the same hash for duplicate file
        file_put_contents($duplicateFile->path(), file_get_contents($existingFile->path()));

        $response = $this->actingAs($this->operator)
            ->post(route('surat-masuk.store'), [
                'tanggal_surat' => '2024-01-15',
                'nomor_surat' => 'TEST/001/2024',
                'perihal' => 'Test Document',
                'dari' => 'Test Sender',
                'kepada' => 'Test Receiver',
                'tanggal_surat_masuk' => '2024-01-15',
                'klasifikasi_surat_id' => $this->klasifikasi->id,
                'file_path' => $duplicateFile,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('duplicate_detected', true);
        $response->assertSessionHas('existing_document');
        $response->assertSessionHas('similarity_score');
    }

    #[Test]
    public function it_allows_force_save_of_duplicate_document()
    {
        $this->actingAs($this->operator);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('surat-masuk.store'), [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Test Document',
            'dari' => 'Test Sender',
            'kepada' => 'Test Receiver',
            'tanggal_surat_masuk' => '2024-01-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'file_path' => $file,
            'force_save' => true,
            'is_duplicate_override' => true,
        ]);

        $response->assertRedirect(route('surat-masuk.index'));
        
        // Document should be saved (force_save flag was set)
        $this->assertDatabaseHas('surat_masuk', [
            'nomor_surat' => 'TEST/001/2024',
        ]);
    }

    #[Test]
    public function it_saves_document_without_duplicate_warning_when_unique()
    {
        $this->actingAs($this->operator);

        $file = UploadedFile::fake()->create('unique_document.pdf', 100);

        $response = $this->post(route('surat-masuk.store'), [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Test Document',
            'dari' => 'Test Sender',
            'kepada' => 'Test Receiver',
            'tanggal_surat_masuk' => '2024-01-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'file_path' => $file,
        ]);

        $response->assertRedirect(route('surat-masuk.index'));
        $response->assertSessionMissing('duplicate_detected');
        
        $this->assertDatabaseHas('surat_masuk', [
            'nomor_surat' => 'TEST/001/2024',
            'is_duplicate' => false,
        ]);
    }

    #[Test]
    public function it_detects_duplicate_in_surat_keluar()
    {
        // Create existing document
        $existingFile = UploadedFile::fake()->create('document.pdf', 100);
        $existingFileHash = hash_file('sha256', $existingFile->path());
        
        $existingDocument = SuratKeluar::factory()->create([
            'file_hash' => $existingFileHash,
            'file_size' => $existingFile->getSize(),
        ]);

        // Try to upload duplicate
        $duplicateFile = UploadedFile::fake()->create('duplicate.pdf', 100);
        file_put_contents($duplicateFile->path(), file_get_contents($existingFile->path()));

        $response = $this->actingAs($this->operator)
            ->post(route('surat-keluar.store'), [
                'tanggal_surat' => '2024-01-15',
                'nomor_surat' => 'TEST/OUT/001/2024',
                'perihal' => 'Test Document',
                'tujuan' => 'Test Destination',
                'dari' => 'Test Sender',
                'tanggal_keluar' => '2024-01-15',
                'klasifikasi_surat_id' => $this->klasifikasi->id,
                'file_path' => $duplicateFile,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('duplicate_detected', true);
    }

    #[Test]
    public function it_excludes_current_document_from_duplicate_check_on_update()
    {
        $this->actingAs($this->operator);

        // Create document
        $originalFile = UploadedFile::fake()->create('original.pdf', 100);
        $document = SuratMasuk::factory()->create([
            'file_hash' => hash_file('sha256', $originalFile->path()),
            'file_size' => $originalFile->getSize(),
        ]);

        // Update with same file (should not trigger duplicate warning)
        $sameFile = UploadedFile::fake()->create('same.pdf', 100);
        file_put_contents($sameFile->path(), file_get_contents($originalFile->path()));

        $response = $this->put(route('surat-masuk.update', $document), [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => $document->nomor_surat,
            'perihal' => 'Updated Document',
            'dari' => $document->dari,
            'kepada' => $document->kepada,
            'tanggal_surat_masuk' => '2024-01-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'file_path' => $sameFile,
        ]);

        $response->assertRedirect(route('surat-masuk.index'));
        $response->assertSessionMissing('duplicate_detected');
    }

    #[Test]
    public function it_creates_duplicate_detection_log_entry()
    {
        // Create existing document
        $existingFile = UploadedFile::fake()->create('existing.pdf', 100);
        $existingDocument = SuratMasuk::factory()->create([
            'file_hash' => hash_file('sha256', $existingFile->path()),
            'file_size' => $existingFile->getSize(),
        ]);

        // Upload duplicate and force save
        $duplicateFile = UploadedFile::fake()->create('duplicate.pdf', 100);
        file_put_contents($duplicateFile->path(), file_get_contents($existingFile->path()));

        $this->actingAs($this->operator)
            ->post(route('surat-masuk.store'), [
                'tanggal_surat' => '2024-01-15',
                'nomor_surat' => 'TEST/002/2024',
                'perihal' => 'Duplicate Document',
                'dari' => 'Test Sender',
                'kepada' => 'Test Receiver',
                'tanggal_surat_masuk' => '2024-01-15',
                'klasifikasi_surat_id' => $this->klasifikasi->id,
                'file_path' => $duplicateFile,
                'force_save' => true,
                'is_duplicate_override' => true,
            ]);

        // Check that duplicate detection was logged
        $this->assertDatabaseHas('duplicate_detections', [
            'original_document_id' => $existingDocument->id,
            'detection_method' => 'file_hash',
            'detected_by' => $this->operator->id,
        ]);
    }

    #[Test]
    public function it_displays_duplicate_warning_component_on_create_page()
    {
        $this->actingAs($this->operator);

        $response = $this->get(route('surat-masuk.create'));

        $response->assertStatus(200);
        // Component is included in the view
        $response->assertViewIs('surat-masuk.create');
    }

    #[Test]
    public function it_displays_duplicate_warning_component_on_edit_page()
    {
        $this->actingAs($this->operator);
        
        $document = SuratMasuk::factory()->create();

        $response = $this->get(route('surat-masuk.edit', $document));

        $response->assertStatus(200);
        // Component is included in the view
        $response->assertViewIs('surat-masuk.edit');
    }

    #[Test]
    public function it_requires_authentication_for_duplicate_detection()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('surat-masuk.store'), [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Test Document',
            'dari' => 'Test Sender',
            'kepada' => 'Test Receiver',
            'tanggal_surat_masuk' => '2024-01-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'file_path' => $file,
        ]);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function it_requires_proper_role_for_document_creation()
    {
        $userWithoutRole = User::factory()->create();
        
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($userWithoutRole)
            ->post(route('surat-masuk.store'), [
                'tanggal_surat' => '2024-01-15',
                'nomor_surat' => 'TEST/001/2024',
                'perihal' => 'Test Document',
                'dari' => 'Test Sender',
                'kepada' => 'Test Receiver',
                'tanggal_surat_masuk' => '2024-01-15',
                'klasifikasi_surat_id' => $this->klasifikasi->id,
                'file_path' => $file,
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function it_handles_file_upload_errors_gracefully()
    {
        $this->actingAs($this->operator);

        // Create an invalid file
        $invalidFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->post(route('surat-masuk.store'), [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Test Document',
            'dari' => 'Test Sender',
            'kepada' => 'Test Receiver',
            'tanggal_surat_masuk' => '2024-01-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'file_path' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('file_path');
    }

    #[Test]
    public function it_works_with_documents_without_files()
    {
        $this->actingAs($this->operator);

        $response = $this->post(route('surat-masuk.store'), [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => 'TEST/001/2024',
            'perihal' => 'Test Document',
            'dari' => 'Test Sender',
            'kepada' => 'Test Receiver',
            'tanggal_surat_masuk' => '2024-01-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            // No file_path
        ]);

        $response->assertRedirect(route('surat-masuk.index'));
        
        $this->assertDatabaseHas('surat_masuk', [
            'nomor_surat' => 'TEST/001/2024',
            'file_path' => null,
            'file_hash' => null,
        ]);
    }
}