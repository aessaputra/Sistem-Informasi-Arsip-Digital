<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;
    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'aksi',
        'modul',
        'reference_table',
        'reference_id',
        'keterangan',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
