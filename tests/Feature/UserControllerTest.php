<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
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

    public function test_guest_cannot_access_users_index(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_cannot_access_users_index(): void
    {
        $response = $this->actingAs($this->operator)->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('users.index');
        $response->assertViewHas('users');
    }

    public function test_operator_cannot_access_create_user_form(): void
    {
        $response = $this->actingAs($this->operator)->get(route('users.create'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_create_user_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('users.create');
        $response->assertViewHas('roles');
    }

    public function test_admin_can_store_new_user(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'username' => 'newuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'role' => 'operator',
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'username' => 'newuser',
        ]);
    }

    public function test_store_user_assigns_role(): void
    {
        $userData = [
            'name' => 'New Operator',
            'email' => 'newoperator@example.com',
            'username' => 'newoperator',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'role' => 'operator',
        ];

        $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $newUser = User::where('email', 'newoperator@example.com')->first();
        $this->assertTrue($newUser->hasRole('operator'));
    }

    public function test_store_user_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), []);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'username',
            'password',
            'role',
        ]);
    }

    public function test_store_user_email_must_be_unique(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'username' => 'newuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'operator',
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_can_view_edit_user_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('users.edit');
        $response->assertViewHas('user');
        $response->assertViewHas('roles');
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operator');

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'username' => 'updateduser',
            'is_active' => true,
            'role' => 'admin',
        ];

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_update_user_can_change_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operator');

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'role' => 'admin',
        ];

        $this->actingAs($this->admin)->put(route('users.update', $user), $updateData);

        $user->refresh();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('operator'));
    }

    public function test_update_user_password_is_optional(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operator');
        $originalPassword = $user->password;

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'username' => $user->username,
            'role' => 'operator',
        ];

        $this->actingAs($this->admin)->put(route('users.update', $user), $updateData);

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
