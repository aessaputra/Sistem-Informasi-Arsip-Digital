<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LampiranSurat extends Model
{
    use HasFactory;
    protected $table = 'lampiran_surat';

    protected $fillable = [
        'surat_type',
        'surat_id',
        'nama_file_asli',
        'file_path',
        'keterangan',
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_id')->where('surat_type', 'masuk');
    }

    public function suratKeluar()
    {
        return $this->belongsTo(SuratKeluar::class, 'surat_id')->where('surat_type', 'keluar');
    }
}
