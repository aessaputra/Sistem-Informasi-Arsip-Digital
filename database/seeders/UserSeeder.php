<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $operator = User::factory()->create([
            'name' => 'Operator',
            'email' => 'operator@operator.com',
            'username' => 'operator',
            'password' => bcrypt('password'),
        ]);
        $operator->assignRole('operator');
    }
}
