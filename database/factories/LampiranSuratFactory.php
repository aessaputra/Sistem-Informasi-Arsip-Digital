<?php

namespace Database\Factories;

use App\Models\LampiranSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LampiranSurat>
 */
class LampiranSuratFactory extends Factory
{
    protected $model = LampiranSurat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'surat_type' => fake()->randomElement(['masuk', 'keluar']),
            'surat_id' => fake()->numberBetween(1, 100),
            'nama_file_asli' => fake()->word() . '.pdf',
            'file_path' => 'lampiran/' . fake()->uuid() . '.pdf',
            'keterangan' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the lampiran is for surat masuk.
     */
    public function forSuratMasuk(int $suratId): static
    {
        return $this->state(fn (array $attributes) => [
            'surat_type' => 'masuk',
            'surat_id' => $suratId,
        ]);
    }

    /**
     * Indicate that the lampiran is for surat keluar.
     */
    public function forSuratKeluar(int $suratId): static
    {
        return $this->state(fn (array $attributes) => [
            'surat_type' => 'keluar',
            'surat_id' => $suratId,
        ]);
    }
}
