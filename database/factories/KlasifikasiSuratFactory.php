<?php

namespace Database\Factories;

use App\Models\KlasifikasiSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KlasifikasiSurat>
 */
class KlasifikasiSuratFactory extends Factory
{
    protected $model = KlasifikasiSurat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'nama' => fake()->words(3, true),
            'keterangan' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the klasifikasi is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
