<?php

use App\Models\Kamar;
use App\Models\KamarPerbaikan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('releases kamar to tersedia after perbaikan selesai lewat 1 hari', function () {
    Carbon::setTestNow(Carbon::create(2026, 2, 11, 10, 0, 0));

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'perbaikan',
    ]);

    KamarPerbaikan::query()->create([
        'kamar_id' => $kamar->id,
        'mulai' => now()->subDays(5)->toDateString(),
        'selesai' => now()->subDays(2)->toDateString(),
        'catatan' => null,
    ]);

    Artisan::call('app:auto-release-kamar-perbaikan');

    $kamar->refresh();
    expect($kamar->status_kamar)->toBe('tersedia');

    Carbon::setTestNow();
});

it('sets kamar to terisi if active booking exists when releasing perbaikan', function () {
    Carbon::setTestNow(Carbon::create(2026, 2, 11, 10, 0, 0));

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'perbaikan',
    ]);

    KamarPerbaikan::query()->create([
        'kamar_id' => $kamar->id,
        'mulai' => now()->subDays(5)->toDateString(),
        'selesai' => now()->subDays(2)->toDateString(),
        'catatan' => null,
    ]);

    DB::table('booking')->insert([
        'kamar_id' => $kamar->id,
        'pegawai_id' => DB::table('users')->insertGetId([
            'nama' => 'Pegawai Test',
            'username' => 'pegawai_test_' . now()->format('YmdHis'),
            'password' => bcrypt('password'),
            'level' => 'pegawai',
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]),
        'nama_tamu' => 'Tamu Aktif',
        'tanggal_check_in' => now()->subDay()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'check_in',
        'status_updated_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Artisan::call('app:auto-release-kamar-perbaikan');

    $kamar->refresh();
    expect($kamar->status_kamar)->toBe('terisi');

    Carbon::setTestNow();
});
