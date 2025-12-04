<?php

namespace Database\Factories;

use App\Models\SuratMasuk;
use App\Models\User;
use App\Models\KlasifikasiSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuratMasuk>
 */
class SuratMasukFactory extends Factory
{
    protected $model = SuratMasuk::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal_surat' => fake()->dateTimeBetween('-1 year', 'now'),
            'nomor_surat' => fake()->unique()->regexify('[0-9]{3}/SM/[A-Z]{2}/[0-9]{4}'),
            'perihal' => fake()->sentence(),
            'dari' => fake()->company(),
            'kepada' => fake()->name(),
            'tanggal_surat_masuk' => fake()->dateTimeBetween('-1 year', 'now'),
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
            'file_path' => 'surat-masuk/' . now()->format('Y/m') . '/' . fake()->uuid() . '.pdf',
        ]);
    }
}
