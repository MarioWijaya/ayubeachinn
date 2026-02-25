<?php

use App\Livewire\App\Calendar\Index as AppCalendarIndex;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('builds calendar days with occupied counts', function () {
    Carbon::setTestNow(Carbon::parse('2026-01-25'));

    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 10,
        'tipe_kamar' => 'Superior',
        'tarif' => 300000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamarId,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu 8',
        'tanggal_check_in' => '2026-01-25',
        'tanggal_check_out' => '2026-01-27',
        'status_booking' => 'menunggu',
        'catatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(AppCalendarIndex::class)
        ->assertSet("days.2026-01-25.occupied_count", 1)
        ->assertSet("days.2026-01-26.occupied_count", 1)
        ->assertSet("days.2026-01-27.occupied_count", 0);

    Carbon::setTestNow();
});
