<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('applies tipe filter on pegawai dashboard', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    DB::table('kamar')->insert([
        [
            'nomor_kamar' => 101,
            'tipe_kamar' => 'Standard',
            'tarif' => 200000,
            'kapasitas' => 2,
            'status_kamar' => 'tersedia',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nomor_kamar' => 202,
            'tipe_kamar' => 'Deluxe',
            'tarif' => 300000,
            'kapasitas' => 2,
            'status_kamar' => 'tersedia',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($user);

    $this->actingAs($user)
        ->get(route('pegawai.dashboard', [
            'tipe' => 'Standard',
        ]))
        ->assertOk()
        ->assertSee('Kamar 101')
        ->assertDontSee('Kamar 202');
});
