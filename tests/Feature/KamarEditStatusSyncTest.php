<?php

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

it('syncs stale perbaikan status to tersedia when opening edit kamar page', function () {
    Carbon::setTestNow(Carbon::create(2026, 3, 5, 9, 0, 0));

    $admin = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '501',
        'tipe_kamar' => 'Standard Fan',
        'tarif' => 175000,
        'kapasitas' => 2,
        'status_kamar' => 'perbaikan',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('kamar_perbaikan')->insert([
        'kamar_id' => $kamarId,
        'mulai' => now()->subDays(3)->toDateString(),
        'selesai' => now()->subDay()->toDateString(),
        'catatan' => 'Perbaikan AC',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.kamar.edit', $kamarId))
        ->assertOk();

    expect(DB::table('kamar')->where('id', $kamarId)->value('status_kamar'))
        ->toBe('tersedia');

    Carbon::setTestNow();
});

it('syncs stale perbaikan status to terisi if active booking exists when opening edit kamar page', function () {
    Carbon::setTestNow(Carbon::create(2026, 3, 5, 9, 0, 0));

    $admin = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '502',
        'tipe_kamar' => 'Superior',
        'tarif' => 200000,
        'kapasitas' => 2,
        'status_kamar' => 'perbaikan',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('kamar_perbaikan')->insert([
        'kamar_id' => $kamarId,
        'mulai' => now()->subDays(3)->toDateString(),
        'selesai' => now()->subDay()->toDateString(),
        'catatan' => 'Perbaikan shower',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        'kamar_id' => $kamarId,
        'pegawai_id' => $pegawai->id,
        'nama_tamu' => 'Tamu Aktif',
        'tanggal_check_in' => now()->subDay()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'check_in',
        'status_updated_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.kamar.edit', $kamarId))
        ->assertOk();

    expect(DB::table('kamar')->where('id', $kamarId)->value('status_kamar'))
        ->toBe('terisi');

    Carbon::setTestNow();
});
