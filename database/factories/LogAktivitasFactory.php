<?php

namespace Database\Factories;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LogAktivitas>
 */
class LogAktivitasFactory extends Factory
{
    protected $model = LogAktivitas::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'aksi' => fake()->randomElement(['create', 'update', 'delete']),
            'modul' => fake()->randomElement(['surat_masuk', 'surat_keluar', 'user', 'klasifikasi']),
            'reference_table' => fake()->randomElement(['surat_masuk', 'surat_keluar', 'users', 'klasifikasi_surat']),
            'reference_id' => fake()->numberBetween(1, 100),
            'keterangan' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
