<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\KlasifikasiSurat;
use App\Models\LogAktivitas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuratMasukControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected KlasifikasiSurat $klasifikasi;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'operator']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->klasifikasi = KlasifikasiSurat::factory()->create();
    }

    public function test_guest_cannot_access_surat_masuk_index(): void
    {
        $response = $this->get(route('surat-masuk.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_surat_masuk_index(): void
    {
        SuratMasuk::factory()->count(5)->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat-masuk.index'));

        $response->assertStatus(200);
        $response->assertViewIs('surat-masuk.index');
        $response->assertViewHas('suratMasuk');
    }

    public function test_operator_can_view_surat_masuk_index(): void
    {
        $response = $this->actingAs($this->operator)->get(route('surat-masuk.index'));

        $response->assertStatus(200);
    }

    public function test_surat_masuk_index_can_filter_by_nomor_surat(): void
    {
        SuratMasuk::factory()->create([
            'nomor_surat' => '001/SM/2024',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);
        SuratMasuk::factory()->create([
            'nomor_surat' => '002/SM/2024',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat-masuk.index', [
            'nomor_surat' => '001',
        ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_surat_masuk_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('surat-masuk.create'));

        $response->assertStatus(200);
        $response->assertViewIs('surat-masuk.create');
        $response->assertViewHas('klasifikasi');
    }

    public function test_admin_can_store_surat_masuk(): void
    {
        $suratData = [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => '001/SM/TEST/2024',
            'perihal' => 'Test Perihal Surat',
            'dari' => 'PT Test Company',
            'kepada' => 'Kepala Bagian',
            'tanggal_surat_masuk' => '2024-01-16',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'keterangan' => 'Keterangan test',
        ];

        $response = $this->actingAs($this->admin)->post(route('surat-masuk.store'), $suratData);

        $response->assertRedirect(route('surat-masuk.index'));
        $this->assertDatabaseHas('surat_masuk', [
            'nomor_surat' => '001/SM/TEST/2024',
            'perihal' => 'Test Perihal Surat',
        ]);
    }

    public function test_store_surat_masuk_creates_log_aktivitas(): void
    {
        $suratData = [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => '002/SM/TEST/2024',
            'perihal' => 'Test Perihal',
            'dari' => 'PT Test',
            'kepada' => 'Kepala',
            'tanggal_surat_masuk' => '2024-01-16',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ];

        $this->actingAs($this->admin)->post(route('surat-masuk.store'), $suratData);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $this->admin->id,
            'aksi' => 'create',
            'modul' => 'surat_masuk',
        ]);
    }

    public function test_store_surat_masuk_with_file_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $suratData = [
            'tanggal_surat' => '2024-01-15',
            'nomor_surat' => '003/SM/TEST/2024',
            'perihal' => 'Test dengan file',
            'dari' => 'PT Test',
            'kepada' => 'Kepala',
            'tanggal_surat_masuk' => '2024-01-16',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
            'file_path' => $file,
        ];

        $response = $this->actingAs($this->admin)->post(route('surat-masuk.store'), $suratData);

        $response->assertRedirect(route('surat-masuk.index'));
        
        $suratMasuk = SuratMasuk::where('nomor_surat', '003/SM/TEST/2024')->first();
        $this->assertNotNull($suratMasuk->file_path);
    }

    public function test_store_surat_masuk_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('surat-masuk.store'), []);

        $response->assertSessionHasErrors([
            'tanggal_surat',
            'nomor_surat',
            'perihal',
            'dari',
            'kepada',
            'tanggal_surat_masuk',
            'klasifikasi_surat_id',
        ]);
    }

    public function test_admin_can_view_surat_masuk_detail(): void
    {
        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat-masuk.show', $suratMasuk));

        $response->assertStatus(200);
        $response->assertViewIs('surat-masuk.show');
        $response->assertViewHas('suratMasuk');
    }

    public function test_admin_can_view_edit_surat_masuk_form(): void
    {
        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat-masuk.edit', $suratMasuk));

        $response->assertStatus(200);
        $response->assertViewIs('surat-masuk.edit');
    }

    public function test_admin_can_update_surat_masuk(): void
    {
        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $updateData = [
            'tanggal_surat' => '2024-02-01',
            'nomor_surat' => 'UPDATED/SM/2024',
            'perihal' => 'Updated Perihal',
            'dari' => 'Updated Company',
            'kepada' => 'Updated Kepada',
            'tanggal_surat_masuk' => '2024-02-02',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ];

        $response = $this->actingAs($this->admin)->put(route('surat-masuk.update', $suratMasuk), $updateData);

        $response->assertRedirect(route('surat-masuk.index'));
        $this->assertDatabaseHas('surat_masuk', [
            'id' => $suratMasuk->id,
            'nomor_surat' => 'UPDATED/SM/2024',
        ]);
    }

    public function test_admin_can_delete_surat_masuk(): void
    {
        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('surat-masuk.destroy', $suratMasuk));

        $response->assertRedirect(route('surat-masuk.index'));
        $this->assertSoftDeleted($suratMasuk);
    }

    public function test_delete_surat_masuk_creates_log_aktivitas(): void
    {
        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $this->actingAs($this->admin)->delete(route('surat-masuk.destroy', $suratMasuk));

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $this->admin->id,
            'aksi' => 'delete',
            'modul' => 'surat_masuk',
        ]);
    }
}
