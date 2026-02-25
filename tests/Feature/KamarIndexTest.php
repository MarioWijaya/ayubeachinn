<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('shows all kamar records on index', function () {
    $user = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);
    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

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
            'tipe_kamar' => 'Deluxe',
            'tarif' => 250000,
            'kapasitas' => 3,
            'status_kamar' => 'terisi',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $kamarPerbaikanId = DB::table('kamar')->where('nomor_kamar', '102')->value('id');
    DB::table('kamar_perbaikan')->insert([
        'kamar_id' => $kamarPerbaikanId,
        'mulai' => now()->toDateString(),
        'selesai' => now()->addDay()->toDateString(),
        'catatan' => 'Perbaikan AC',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $kamarTerisiId = DB::table('kamar')->where('nomor_kamar', '103')->value('id');
    DB::table('booking')->insert([
        'kamar_id' => $kamarTerisiId,
        'pegawai_id' => $pegawai->id,
        'nama_tamu' => 'Tamu Test',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'menunggu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.kamar.index'))
        ->assertOk()
        ->assertSee('Tersedia')
        ->assertSee('Perbaikan')
        ->assertSee('Terisi')
        ->assertSee('103');
});
