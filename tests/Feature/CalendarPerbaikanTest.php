<?php

use App\Livewire\App\Calendar\Index as CalendarIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('excludes rooms in perbaikan during active maintenance range', function () {
    $today = now()->toDateString();
    $tomorrow = now()->addDay()->toDateString();

    DB::table('kamar')->insert([
        [
            'nomor_kamar' => '101',
            'tipe_kamar' => 'Standard Fan',
            'tarif' => 150000,
            'kapasitas' => 2,
            'status_kamar' => 'tersedia',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nomor_kamar' => '102',
            'tipe_kamar' => 'Standard Fan',
            'tarif' => 150000,
            'kapasitas' => 2,
            'status_kamar' => 'perbaikan',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nomor_kamar' => '103',
            'tipe_kamar' => 'Standard Fan',
            'tarif' => 150000,
            'kapasitas' => 2,
            'status_kamar' => 'perbaikan',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('kamar_perbaikan')->insert([
        [
            'kamar_id' => 2,
            'mulai' => $today,
            'selesai' => null,
            'catatan' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'kamar_id' => 3,
            'mulai' => $tomorrow,
            'selesai' => null,
            'catatan' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $days = Livewire::test(CalendarIndex::class)->get('days');

    expect($days[$today]['total_rooms'])->toBe(2)
        ->and($days[$tomorrow]['total_rooms'])->toBe(1)
        ->and($days[$today]['occupied_count'])->toBe(0)
        ->and($days[$tomorrow]['occupied_count'])->toBe(0);
});
