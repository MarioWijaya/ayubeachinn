<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KamarSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('booking_layanan')->delete();
        DB::table('perpanjangan_booking')->delete();
        DB::table('booking')->delete();
        DB::table('kamar')->delete();

        $tipeList = [
            [
                'tipe_kamar' => 'Standard Fan',
                'tarif' => 175000,
                'kapasitas' => 2,
            ],
            [
                'tipe_kamar' => 'Superior',
                'tarif' => 200000,
                'kapasitas' => 2,
            ],
            [
                'tipe_kamar' => 'Deluxe',
                'tarif' => 300000,
                'kapasitas' => 4,
            ],
            [
                'tipe_kamar' => 'FamilyRoom',
                'tarif' => 400000,
                'kapasitas' => 5,
            ],
        ];

        $data = [];
        for ($i = 1; $i <= 40; $i++) {
            $tipe = $tipeList[($i - 1) % count($tipeList)];
            $data[] = [
                'nomor_kamar' => (string) $i,
                'tipe_kamar' => $tipe['tipe_kamar'],
                'tarif' => $tipe['tarif'],
                'kapasitas' => $tipe['kapasitas'],
                'status_kamar' => 'tersedia',
            ];
        }

        foreach ($data as $kamar) {
            DB::table('kamar')->insert(array_merge($kamar, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
