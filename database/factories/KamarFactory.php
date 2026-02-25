<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kamar>
 */
class KamarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_kamar' => fake()->unique()->numberBetween(1, 999),
            'tipe_kamar' => fake()->randomElement(['Standard Fan', 'Superior', 'Deluxe', 'FamilyRoom']),
            'tarif' => fake()->numberBetween(150000, 500000),
            'kapasitas' => fake()->numberBetween(1, 6),
            'status_kamar' => fake()->randomElement(['tersedia', 'terisi', 'perbaikan']),
        ];
    }
}
