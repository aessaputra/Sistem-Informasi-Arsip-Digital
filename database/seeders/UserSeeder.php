<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

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
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $operator = User::factory()->create([
            'name' => 'Operator',
            'email' => 'operator@operator.com',
            'password' => bcrypt('password'),
        ]);
        $operator->assignRole('operator');
    }
}
