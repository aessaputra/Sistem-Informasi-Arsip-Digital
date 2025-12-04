<?php

namespace Database\Factories;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Models\KlasifikasiSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuratKeluar>
 */
class SuratKeluarFactory extends Factory
{
    protected $model = SuratKeluar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal_surat' => fake()->dateTimeBetween('-1 year', 'now'),
            'nomor_surat' => fake()->unique()->regexify('[0-9]{3}/SK/[A-Z]{2}/[0-9]{4}'),
            'perihal' => fake()->sentence(),
            'tujuan' => fake()->company(),
            'dari' => fake()->name(),
            'tanggal_keluar' => fake()->dateTimeBetween('-1 year', 'now'),
            'jam_input' => now(),
            'petugas_input_id' => User::factory(),
            'klasifikasi_surat_id' => KlasifikasiSurat::factory(),
            'keterangan' => fake()->optional()->paragraph(),
            'file_path' => null,
        ];
    }

    /**
     * Indicate that the surat has a file attachment.
     */
    public function withFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path' => 'surat-keluar/' . now()->format('Y/m') . '/' . fake()->uuid() . '.pdf',
        ]);
    }
}
