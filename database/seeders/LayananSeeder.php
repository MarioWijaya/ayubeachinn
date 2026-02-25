<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('layanan')->updateOrInsert(
            ['kode' => 'extra_bed_normal'],
            [
                'nama' => 'Extra Bed (Normal)',
                'harga' => 100000,
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('layanan')->updateOrInsert(
            ['kode' => 'extra_bed_high'],
            [
                'nama' => 'Extra Bed (High Season)',
                'harga' => 150000,
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
