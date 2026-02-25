<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('sets status kamar to perbaikan when today is within maintenance range', function () {
    Carbon::setTestNow('2026-02-12 08:00:00');

    $kamarAktif = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '301',
        'tipe_kamar' => 'Standard Fan',
        'tarif' => 150000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $kamarBelumMulai = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '302',
        'tipe_kamar' => 'Superior',
        'tarif' => 200000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('kamar_perbaikan')->insert([
        [
            'kamar_id' => $kamarAktif,
            'mulai' => '2026-02-11',
            'selesai' => '2026-02-15',
            'catatan' => 'Perbaikan AC',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'kamar_id' => $kamarBelumMulai,
            'mulai' => '2026-02-20',
            'selesai' => '2026-02-21',
            'catatan' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    Artisan::call('app:auto-start-kamar-perbaikan');

    expect(DB::table('kamar')->where('id', $kamarAktif)->value('status_kamar'))->toBe('perbaikan');
    expect(DB::table('kamar')->where('id', $kamarBelumMulai)->value('status_kamar'))->toBe('tersedia');
});
