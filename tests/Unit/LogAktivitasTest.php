<?php

namespace Tests\Unit;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogAktivitasTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_aktivitas_has_fillable_attributes(): void
    {
        $user = User::factory()->create();

        $log = LogAktivitas::factory()->create([
            'user_id' => $user->id,
            'aksi' => 'create',
            'modul' => 'surat_masuk',
            'reference_table' => 'surat_masuk',
            'reference_id' => 1,
            'keterangan' => 'Menambahkan surat masuk',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $this->assertEquals('create', $log->aksi);
        $this->assertEquals('surat_masuk', $log->modul);
        $this->assertEquals('Menambahkan surat masuk', $log->keterangan);
        $this->assertEquals('127.0.0.1', $log->ip_address);
    }

    public function test_log_aktivitas_belongs_to_user(): void
    {
        $log = LogAktivitas::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $log->user());
        $this->assertInstanceOf(User::class, $log->user);
    }

    public function test_log_aktivitas_can_be_created_with_minimal_fields(): void
    {
        $user = User::factory()->create();

        $log = LogAktivitas::create([
            'user_id' => $user->id,
            'aksi' => 'delete',
            'modul' => 'surat_keluar',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertNotNull($log->id);
        $this->assertEquals('delete', $log->aksi);
    }
}
