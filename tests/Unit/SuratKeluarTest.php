<?php

namespace Tests\Unit;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Models\KlasifikasiSurat;
use App\Models\LampiranSurat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratKeluarTest extends TestCase
{
    use RefreshDatabase;

    public function test_surat_keluar_has_fillable_attributes(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create();
        $user = User::factory()->create();

        $suratKeluar = SuratKeluar::factory()->create([
            'nomor_surat' => '001/SK/2024',
            'perihal' => 'Test Perihal',
            'tujuan' => 'PT Tujuan',
            'dari' => 'Kepala Bagian',
            'petugas_input_id' => $user->id,
            'klasifikasi_surat_id' => $klasifikasi->id,
        ]);

        $this->assertEquals('001/SK/2024', $suratKeluar->nomor_surat);
        $this->assertEquals('Test Perihal', $suratKeluar->perihal);
        $this->assertEquals('PT Tujuan', $suratKeluar->tujuan);
        $this->assertEquals('Kepala Bagian', $suratKeluar->dari);
    }

    public function test_surat_keluar_belongs_to_petugas(): void
    {
        $suratKeluar = SuratKeluar::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $suratKeluar->petugas());
        $this->assertInstanceOf(User::class, $suratKeluar->petugas);
    }

    public function test_surat_keluar_belongs_to_klasifikasi(): void
    {
        $suratKeluar = SuratKeluar::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $suratKeluar->klasifikasi());
        $this->assertInstanceOf(KlasifikasiSurat::class, $suratKeluar->klasifikasi);
    }

    public function test_surat_keluar_has_many_lampiran(): void
    {
        $suratKeluar = SuratKeluar::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $suratKeluar->lampiran());
    }

    public function test_surat_keluar_uses_soft_deletes(): void
    {
        $suratKeluar = SuratKeluar::factory()->create();
        $suratKeluar->delete();

        $this->assertSoftDeleted($suratKeluar);
        $this->assertNotNull($suratKeluar->deleted_at);
    }

    public function test_surat_keluar_casts_dates_correctly(): void
    {
        $suratKeluar = SuratKeluar::factory()->create([
            'tanggal_surat' => '2024-01-15',
            'tanggal_keluar' => '2024-01-16',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $suratKeluar->tanggal_surat);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $suratKeluar->tanggal_keluar);
    }
}
