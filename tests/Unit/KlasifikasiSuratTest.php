<?php

namespace Tests\Unit;

use App\Models\KlasifikasiSurat;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlasifikasiSuratTest extends TestCase
{
    use RefreshDatabase;

    public function test_klasifikasi_surat_has_fillable_attributes(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create([
            'kode' => 'KS001',
            'nama' => 'Surat Umum',
            'keterangan' => 'Klasifikasi untuk surat umum',
            'is_active' => true,
        ]);

        $this->assertEquals('KS001', $klasifikasi->kode);
        $this->assertEquals('Surat Umum', $klasifikasi->nama);
        $this->assertEquals('Klasifikasi untuk surat umum', $klasifikasi->keterangan);
        $this->assertTrue($klasifikasi->is_active);
    }

    public function test_klasifikasi_surat_has_many_surat_masuk(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $klasifikasi->suratMasuk());
    }

    public function test_klasifikasi_surat_has_many_surat_keluar(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $klasifikasi->suratKeluar());
    }

    public function test_klasifikasi_surat_casts_is_active_to_boolean(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create(['is_active' => 1]);

        $this->assertIsBool($klasifikasi->is_active);
        $this->assertTrue($klasifikasi->is_active);
    }
}
