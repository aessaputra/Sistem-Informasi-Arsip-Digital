<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\KlasifikasiSurat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KlasifikasiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'operator']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');
    }

    public function test_guest_cannot_access_klasifikasi_index(): void
    {
        $response = $this->get(route('klasifikasi.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_access_klasifikasi_index(): void
    {
        $response = $this->actingAs($this->operator)->get(route('klasifikasi.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_klasifikasi_index(): void
    {
        KlasifikasiSurat::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get(route('klasifikasi.index'));

        $response->assertStatus(200);
        $response->assertViewIs('klasifikasi.index');
        $response->assertViewHas('klasifikasi');
    }

    public function test_admin_can_view_create_klasifikasi_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('klasifikasi.create'));

        $response->assertStatus(200);
        $response->assertViewIs('klasifikasi.create');
    }

    public function test_admin_can_store_klasifikasi(): void
    {
        $klasifikasiData = [
            'kode' => 'KS001',
            'nama' => 'Surat Umum',
            'keterangan' => 'Klasifikasi untuk surat umum',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('klasifikasi.store'), $klasifikasiData);

        $response->assertRedirect(route('klasifikasi.index'));
        $this->assertDatabaseHas('klasifikasi_surat', [
            'kode' => 'KS001',
            'nama' => 'Surat Umum',
        ]);
    }

    public function test_store_klasifikasi_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('klasifikasi.store'), []);

        $response->assertSessionHasErrors(['kode', 'nama']);
    }

    public function test_store_klasifikasi_kode_must_be_unique(): void
    {
        KlasifikasiSurat::factory()->create(['kode' => 'EXISTING']);

        $klasifikasiData = [
            'kode' => 'EXISTING',
            'nama' => 'New Klasifikasi',
        ];

        $response = $this->actingAs($this->admin)->post(route('klasifikasi.store'), $klasifikasiData);

        $response->assertSessionHasErrors(['kode']);
    }

    public function test_admin_can_view_edit_klasifikasi_form(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('klasifikasi.edit', $klasifikasi));

        $response->assertStatus(200);
        $response->assertViewIs('klasifikasi.edit');
        $response->assertViewHas('klasifikasi');
    }

    public function test_admin_can_update_klasifikasi(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create();

        $updateData = [
            'kode' => 'UPDATED',
            'nama' => 'Updated Klasifikasi',
            'keterangan' => 'Updated keterangan',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->admin)->put(route('klasifikasi.update', $klasifikasi), $updateData);

        $response->assertRedirect(route('klasifikasi.index'));
        $this->assertDatabaseHas('klasifikasi_surat', [
            'id' => $klasifikasi->id,
            'kode' => 'UPDATED',
            'nama' => 'Updated Klasifikasi',
        ]);
    }

    public function test_update_klasifikasi_kode_unique_ignores_self(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create(['kode' => 'MYCODE']);

        $updateData = [
            'kode' => 'MYCODE',
            'nama' => 'Updated Name',
        ];

        $response = $this->actingAs($this->admin)->put(route('klasifikasi.update', $klasifikasi), $updateData);

        $response->assertRedirect(route('klasifikasi.index'));
    }

    public function test_admin_can_delete_klasifikasi(): void
    {
        $klasifikasi = KlasifikasiSurat::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('klasifikasi.destroy', $klasifikasi));

        $response->assertRedirect(route('klasifikasi.index'));
        $this->assertDatabaseMissing('klasifikasi_surat', ['id' => $klasifikasi->id]);
    }
}
