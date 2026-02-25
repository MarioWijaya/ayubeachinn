<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-7 days', '+7 days');
        $durasi = fake()->numberBetween(1, 4);
        $checkOut = (clone $checkIn)->modify("+{$durasi} days");

        return [
            'kamar_id' => Kamar::factory(),
            'pegawai_id' => User::factory()->state([
                'level' => 'pegawai',
                'status_aktif' => true,
            ]),
            'nama_tamu' => fake()->name(),
            'no_telp_tamu' => fake()->numerify('08##########'),
            'tanggal_check_in' => $checkIn->format('Y-m-d'),
            'tanggal_check_out' => $checkOut->format('Y-m-d'),
            'status_booking' => 'menunggu',
            'status_updated_at' => now(),
            'catatan' => null,
            'harga_kamar' => fake()->numberBetween(150000, 500000),
            'source_type' => 'walk_in',
            'source_detail' => null,
        ];
    }

    public function selesai(): static
    {
        return $this->state(function () {
            return [
                'status_booking' => 'selesai',
                'status_updated_at' => now(),
                'checkout_at' => now(),
                'total_final' => fake()->numberBetween(200000, 1000000),
            ];
        });
    }
}
