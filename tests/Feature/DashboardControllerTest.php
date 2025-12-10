<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\KlasifikasiSurat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles are already created in TestCase::ensureRolesExist()

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_operator_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->operator)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_dashboard_displays_statistics(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create();
        
        SuratMasuk::factory()->count(5)->create([
            'klasifikasi_surat_id' => $klasifikasi->id,
        ]);
        SuratKeluar::factory()->count(3)->create([
            'klasifikasi_surat_id' => $klasifikasi->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('suratMasukCount');
        $response->assertViewHas('suratKeluarCount');
    }
}
