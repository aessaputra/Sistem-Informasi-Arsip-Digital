<?php

namespace Tests\Unit;

use App\Models\SuratMasuk;
use App\Models\User;
use App\Models\KlasifikasiSurat;
use App\Models\LampiranSurat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratMasukTest extends TestCase
{
    use RefreshDatabase;

    public function test_surat_masuk_has_fillable_attributes(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create();
        $user = User::factory()->create();

        $suratMasuk = SuratMasuk::factory()->create([
            'nomor_surat' => '001/SM/2024',
            'perihal' => 'Test Perihal',
            'dari' => 'PT Test',
            'kepada' => 'Kepala Bagian',
            'petugas_input_id' => $user->id,
            'klasifikasi_surat_id' => $klasifikasi->id,
        ]);

        $this->assertEquals('001/SM/2024', $suratMasuk->nomor_surat);
        $this->assertEquals('Test Perihal', $suratMasuk->perihal);
        $this->assertEquals('PT Test', $suratMasuk->dari);
        $this->assertEquals('Kepala Bagian', $suratMasuk->kepada);
    }

    public function test_surat_masuk_belongs_to_petugas(): void
    {
        $suratMasuk = SuratMasuk::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $suratMasuk->petugas());
        $this->assertInstanceOf(User::class, $suratMasuk->petugas);
    }

    public function test_surat_masuk_belongs_to_klasifikasi(): void
    {
        $suratMasuk = SuratMasuk::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $suratMasuk->klasifikasi());
        $this->assertInstanceOf(KlasifikasiSurat::class, $suratMasuk->klasifikasi);
    }

    public function test_surat_masuk_has_many_lampiran(): void
    {
        $suratMasuk = SuratMasuk::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $suratMasuk->lampiran());
    }

    public function test_surat_masuk_uses_soft_deletes(): void
    {
        $suratMasuk = SuratMasuk::factory()->create();
        $suratMasuk->delete();

        $this->assertSoftDeleted($suratMasuk);
        $this->assertNotNull($suratMasuk->deleted_at);
    }

    public function test_surat_masuk_casts_dates_correctly(): void
    {
        $suratMasuk = SuratMasuk::factory()->create([
            'tanggal_surat' => '2024-01-15',
            'tanggal_surat_masuk' => '2024-01-16',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $suratMasuk->tanggal_surat);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $suratMasuk->tanggal_surat_masuk);
    }
}
