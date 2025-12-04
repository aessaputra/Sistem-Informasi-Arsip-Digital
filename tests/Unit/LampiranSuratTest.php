<?php

namespace Tests\Unit;

use App\Models\LampiranSurat;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LampiranSuratTest extends TestCase
{
    use RefreshDatabase;

    public function test_lampiran_surat_has_fillable_attributes(): void
    {
        $lampiran = LampiranSurat::factory()->create([
            'surat_type' => 'masuk',
            'surat_id' => 1,
            'nama_file_asli' => 'dokumen.pdf',
            'file_path' => 'lampiran/2024/01/dokumen.pdf',
            'keterangan' => 'Lampiran dokumen penting',
        ]);

        $this->assertEquals('masuk', $lampiran->surat_type);
        $this->assertEquals(1, $lampiran->surat_id);
        $this->assertEquals('dokumen.pdf', $lampiran->nama_file_asli);
        $this->assertEquals('lampiran/2024/01/dokumen.pdf', $lampiran->file_path);
    }

    public function test_lampiran_surat_belongs_to_surat_masuk(): void
    {
        $suratMasuk = SuratMasuk::factory()->create();
        
        $lampiran = LampiranSurat::factory()->forSuratMasuk($suratMasuk->id)->create();
        
        $this->assertEquals('masuk', $lampiran->surat_type);
        $this->assertEquals($suratMasuk->id, $lampiran->surat_id);
    }

    public function test_lampiran_surat_belongs_to_surat_keluar(): void
    {
        $suratKeluar = SuratKeluar::factory()->create();
        
        $lampiran = LampiranSurat::factory()->forSuratKeluar($suratKeluar->id)->create();
        
        $this->assertEquals('keluar', $lampiran->surat_type);
        $this->assertEquals($suratKeluar->id, $lampiran->surat_id);
    }
}
