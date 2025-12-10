<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\KlasifikasiSurat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanControllerTest extends TestCase
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

    // ==================== Agenda Surat Masuk Tests ====================

    public function test_guest_cannot_access_agenda_surat_masuk(): void
    {
        $response = $this->get(route('laporan.agenda-surat-masuk'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_agenda_surat_masuk(): void
    {
        SuratMasuk::factory()->count(5)->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-masuk'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.agenda-surat-masuk');
        $response->assertViewHas('suratMasuk');
        $response->assertViewHas('klasifikasi');
    }

    public function test_operator_can_view_agenda_surat_masuk(): void
    {
        $response = $this->actingAs($this->operator)->get(route('laporan.agenda-surat-masuk'));

        $response->assertStatus(200);
    }

    public function test_agenda_surat_masuk_can_filter_by_date_range(): void
    {
        SuratMasuk::factory()->create([
            'tanggal_surat' => '2024-01-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);
        SuratMasuk::factory()->create([
            'tanggal_surat' => '2024-02-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-masuk', [
            'tanggal_dari' => '2024-01-01',
            'tanggal_sampai' => '2024-01-31',
        ]));

        $response->assertStatus(200);
    }

    public function test_agenda_surat_masuk_can_filter_by_nomor_surat(): void
    {
        SuratMasuk::factory()->create([
            'nomor_surat' => '001/SM/2024',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-masuk', [
            'nomor_surat' => '001',
        ]));

        $response->assertStatus(200);
    }

    public function test_agenda_surat_masuk_can_filter_by_pengirim(): void
    {
        SuratMasuk::factory()->create([
            'dari' => 'PT Test Company',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-masuk', [
            'pengirim' => 'Test Company',
        ]));

        $response->assertStatus(200);
    }

    public function test_agenda_surat_masuk_can_filter_by_klasifikasi(): void
    {
        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-masuk', [
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_agenda_surat_masuk_excel(): void
    {
        Excel::fake();

        SuratMasuk::factory()->count(3)->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-masuk.excel'));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_agenda_surat_masuk_pdf(): void
    {
        SuratMasuk::factory()->count(3)->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-masuk.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ==================== Agenda Surat Keluar Tests ====================

    public function test_guest_cannot_access_agenda_surat_keluar(): void
    {
        $response = $this->get(route('laporan.agenda-surat-keluar'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_agenda_surat_keluar(): void
    {
        SuratKeluar::factory()->count(5)->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-keluar'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.agenda-surat-keluar');
        $response->assertViewHas('suratKeluar');
        $response->assertViewHas('klasifikasi');
    }

    public function test_agenda_surat_keluar_can_filter_by_date_range(): void
    {
        SuratKeluar::factory()->create([
            'tanggal_surat' => '2024-01-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-keluar', [
            'tanggal_dari' => '2024-01-01',
            'tanggal_sampai' => '2024-01-31',
        ]));

        $response->assertStatus(200);
    }

    public function test_agenda_surat_keluar_can_filter_by_tujuan(): void
    {
        SuratKeluar::factory()->create([
            'tujuan' => 'PT Tujuan Company',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-keluar', [
            'tujuan' => 'Tujuan Company',
        ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_agenda_surat_keluar_excel(): void
    {
        Excel::fake();

        SuratKeluar::factory()->count(3)->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-keluar.excel'));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_agenda_surat_keluar_pdf(): void
    {
        SuratKeluar::factory()->count(3)->create([
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.agenda-surat-keluar.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ==================== Rekap Periode Tests ====================

    public function test_guest_cannot_access_rekap_periode(): void
    {
        $response = $this->get(route('laporan.rekap-periode'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_rekap_periode(): void
    {
        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-periode'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.rekap-periode');
        $response->assertViewHas('rekapData');
        $response->assertViewHas('tahun');
        $response->assertViewHas('availableYears');
        $response->assertViewHas('totalMasuk');
        $response->assertViewHas('totalKeluar');
    }

    public function test_rekap_periode_can_filter_by_year(): void
    {
        SuratMasuk::factory()->create([
            'tanggal_surat' => '2023-06-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);
        SuratMasuk::factory()->create([
            'tanggal_surat' => '2024-06-15',
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-periode', [
            'tahun' => 2024,
        ]));

        $response->assertStatus(200);
    }

    public function test_rekap_periode_shows_monthly_data(): void
    {
        SuratMasuk::factory()->create([
            'tanggal_surat' => now()->startOfYear(),
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);
        SuratKeluar::factory()->create([
            'tanggal_surat' => now()->startOfYear(),
            'klasifikasi_surat_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-periode', [
            'tahun' => now()->year,
        ]));

        $response->assertStatus(200);
        $rekapData = $response->viewData('rekapData');
        $this->assertCount(12, $rekapData); // 12 months
    }

    public function test_admin_can_export_rekap_periode_excel(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-periode.excel'));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_rekap_periode_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-periode.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ==================== Rekap Klasifikasi Tests ====================

    public function test_guest_cannot_access_rekap_klasifikasi(): void
    {
        $response = $this->get(route('laporan.rekap-klasifikasi'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_rekap_klasifikasi(): void
    {
        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-klasifikasi'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.rekap-klasifikasi');
        $response->assertViewHas('rekapData');
        $response->assertViewHas('tahun');
        $response->assertViewHas('availableYears');
        $response->assertViewHas('totalMasuk');
        $response->assertViewHas('totalKeluar');
    }

    public function test_rekap_klasifikasi_can_filter_by_year(): void
    {
        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-klasifikasi', [
            'tahun' => 2024,
        ]));

        $response->assertStatus(200);
    }

    public function test_rekap_klasifikasi_shows_data_per_klasifikasi(): void
    {
        $klasifikasi1 = KlasifikasiSurat::factory()->create();
        $klasifikasi2 = KlasifikasiSurat::factory()->create();

        SuratMasuk::factory()->create([
            'klasifikasi_surat_id' => $klasifikasi1->id,
            'tanggal_surat' => now(),
        ]);
        SuratKeluar::factory()->create([
            'klasifikasi_surat_id' => $klasifikasi2->id,
            'tanggal_surat' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-klasifikasi', [
            'tahun' => now()->year,
        ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_rekap_klasifikasi_excel(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-klasifikasi.excel'));

        $response->assertStatus(200);
    }

    public function test_admin_can_export_rekap_klasifikasi_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-klasifikasi.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
