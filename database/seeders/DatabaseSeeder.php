<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\SumberBookingSeeder;
use Database\Seeders\LayananSeeder;
use Database\Seeders\BookingSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SumberBookingSeeder::class,
            KamarSeeder::class,
            LayananSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
