<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SumberBookingSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama' => 'Walk-in',     'kode' => 'walk_in',     'aktif' => 1],
            ['nama' => 'Agoda',       'kode' => 'agoda',       'aktif' => 1],
            ['nama' => 'Traveloka',   'kode' => 'traveloka',   'aktif' => 1],
            ['nama' => 'OYO',         'kode' => 'oyo',         'aktif' => 1],
            ['nama' => 'Booking.com', 'kode' => 'booking_com', 'aktif' => 1],
            ['nama' => 'Telepon',     'kode' => 'phone',       'aktif' => 1],
        ];

        foreach ($items as $it) {
            DB::table('sumber_booking')->updateOrInsert(
                ['kode' => $it['kode']],
                array_merge($it, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}