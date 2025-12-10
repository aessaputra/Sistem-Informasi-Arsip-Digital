<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SuratKeluar;
use App\Models\KlasifikasiSurat;
use App\Models\LogAktivitas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuratKeluarControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected KlasifikasiSurat $klasifikasi;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles are already created in TestCase::ensureRolesExist()

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->klasifikasi = KlasifikasiSurat::factory()->create();
    }

    public function test_guest_cannot_access_surat_keluar_index(): void
    {
        $response = $this->get(route('surat-keluar.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_surat_keluar_index(): void
    {
        SuratKeluar::factory()->count(5)->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat-keluar.index'));

        $response->assertStatus(200);
        $response->assertViewIs('surat-keluar.index');
        $response->assertViewHas('suratKeluar');
    }

    public function test_operator_can_view_surat_keluar_index(): void
    {
        $response = $this->actingAs($this->operator)->get(route('surat-keluar.index'));

        $response->assertStatus(200);
    }

    public function test_surat_keluar_index_can_filter_by_nomor_surat(): void
    {
        SuratKeluar::factory()->create([
            'nomor_surat' => '001/SK/2024',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);
        SuratKeluar::factory()->create([
            'nomor_surat' => '002/SK/2024',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat-keluar.index', [
            'nomor_surat' => '001',
        ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_surat_keluar_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('surat-keluar.create'));

        $response->assertStatus(200);
        $response->assertViewIs('surat-keluar.create');
        $response->assertViewHas('klasifikasi');
    }

    public function test_admin_can_store_surat_keluar(): void
    {
        $suratData = [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => '001/SK/TEST/2024',
            'perihal' => 'Test Perihal Surat Keluar',
            'tujuan' => 'PT Tujuan Company',
            'dari' => 'Kepala Bagian',
            'tanggal_keluar' => '2024-01-16',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'keterangan' => 'Keterangan test',
        ];

        $response = $this->actingAs($this->admin)->post(route('surat-keluar.store'), $suratData);

        $response->assertRedirect(route('surat-keluar.index'));
        $this->assertDatabaseHas('surat_keluar', [
            'nomor_surat' => '001/SK/TEST/2024',
            'perihal' => 'Test Perihal Surat Keluar',
        ]);
    }

    public function test_store_surat_keluar_creates_log_aktivitas(): void
    {
        $suratData = [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => '002/SK/TEST/2024',
            'perihal' => 'Test Perihal',
            'tujuan' => 'PT Tujuan',
            'dari' => 'Kepala',
            'tanggal_keluar' => '2024-01-16',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ];

        $this->actingAs($this->admin)->post(route('surat-keluar.store'), $suratData);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $this->admin->id,
            'aksi' => 'create',
            'modul' => 'surat_keluar',
        ]);
    }

    public function test_store_surat_keluar_with_file_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $suratData = [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => '003/SK/TEST/2024',
            'perihal' => 'Test dengan file',
            'tujuan' => 'PT Tujuan',
            'dari' => 'Kepala',
            'tanggal_keluar' => '2024-01-16',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'file_path' => $file,
        ];

        $response = $this->actingAs($this->admin)->post(route('surat-keluar.store'), $suratData);

        $response->assertRedirect(route('surat-keluar.index'));
        
        $suratKeluar = SuratKeluar::where('nomor_surat', '003/SK/TEST/2024')->first();
        $this->assertNotNull($suratKeluar->file_path);
    }

    public function test_store_surat_keluar_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('surat-keluar.store'), []);

        $response->assertSessionHasErrors([
            'tanggal_surat',
            'nomor_surat',
            'perihal',
            'tujuan',
            'dari',
            'tanggal_keluar',
            'klasifikasi_surat_id',
        ]);
    }

    public function test_admin_can_view_surat_keluar_detail(): void
    {
        $suratKeluar = SuratKeluar::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat-keluar.show', $suratKeluar));

        $response->assertStatus(200);
        $response->assertViewIs('surat-keluar.show');
        $response->assertViewHas('suratKeluar');
    }

    public function test_admin_can_view_edit_surat_keluar_form(): void
    {
        $suratKeluar = SuratKeluar::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat-keluar.edit', $suratKeluar));

        $response->assertStatus(200);
        $response->assertViewIs('surat-keluar.edit');
    }

    public function test_admin_can_update_surat_keluar(): void
    {
        $suratKeluar = SuratKeluar::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $updateData = [
            'tanggal_surat' => '2024-02-01',
            'nomor_surat' => 'UPDATED/SK/2024',
            'perihal' => 'Updated Perihal',
            'tujuan' => 'Updated Tujuan',
            'dari' => 'Updated Dari',
            'tanggal_keluar' => '2024-02-02',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ];

        $response = $this->actingAs($this->admin)->put(route('surat-keluar.update', $suratKeluar), $updateData);

        $response->assertRedirect(route('surat-keluar.index'));
        $this->assertDatabaseHas('surat_keluar', [
            'id' => $suratKeluar->id,
            'nomor_surat' => 'UPDATED/SK/2024',
        ]);
    }

    public function test_admin_can_delete_surat_keluar(): void
    {
        $suratKeluar = SuratKeluar::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('surat-keluar.destroy', $suratKeluar));

        $response->assertRedirect(route('surat-keluar.index'));
        $this->assertSoftDeleted($suratKeluar);
    }

    public function test_delete_surat_keluar_creates_log_aktivitas(): void
    {
        $suratKeluar = SuratKeluar::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $this->actingAs($this->admin)->delete(route('surat-keluar.destroy', $suratKeluar));

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $this->admin->id,
            'aksi' => 'delete',
            'modul' => 'surat_keluar',
        ]);
    }
}
