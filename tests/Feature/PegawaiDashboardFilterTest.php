<?php

use App\Livewire\App\Dashboard as AppDashboard;
use App\Livewire\Pegawai\Dashboard as PegawaiDashboard;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

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

it('prevents check to date from being earlier than check from date on pegawai dashboard filter', function () {
    Livewire::test(PegawaiDashboard::class)
        ->set('checkTo', '2026-03-02')
        ->set('checkFrom', '2026-03-10')
        ->assertSet('checkTo', '2026-03-10')
        ->assertSee('min="2026-03-10"', false);
});

it('prevents check to date from being earlier than check from date on app dashboard filter', function () {
    Livewire::test(AppDashboard::class)
        ->set('checkTo', '2026-03-02')
        ->set('checkFrom', '2026-03-10')
        ->assertSet('checkTo', '2026-03-10')
        ->assertSee('min="2026-03-10"', false);
});
