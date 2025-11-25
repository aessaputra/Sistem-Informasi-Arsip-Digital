<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratKeluar extends Model
{
    use SoftDeletes;

    protected $table = 'surat_keluar';

    protected $fillable = [
        'tanggal_surat',
        'nomor_surat',
        'perihal',
        'tujuan',
        'dari',
        'tanggal_keluar',
        'jam_input',
        'petugas_input_id',
        'klasifikasi_surat_id',
        'keterangan',
        'file_path',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_keluar' => 'date',
        'jam_input' => 'datetime',
    ];

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_input_id');
    }

    public function klasifikasi()
    {
        return $this->belongsTo(KlasifikasiSurat::class, 'klasifikasi_surat_id');
    }

    public function lampiran()
    {
        return $this->hasMany(LampiranSurat::class, 'surat_id')->where('surat_type', 'keluar');
    }
}
