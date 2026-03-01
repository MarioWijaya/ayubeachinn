<?php

use App\Livewire\Backoffice\Kamar\Index as KamarIndex;
use App\Models\Kamar;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('paginates kamar at 10 per page', function () {
    Kamar::factory()
        ->count(15)
        ->sequence(fn ($sequence) => ['nomor_kamar' => $sequence->index + 1])
        ->create();

    Livewire::test(KamarIndex::class)
        ->assertViewHas('kamar', function ($kamar) {
            return $kamar->perPage() === 10
                && $kamar->total() === 15
                && $kamar->count() === 10;
        });
});

it('filters kamar by search and status', function () {
    Kamar::factory()->create([
        'nomor_kamar' => 101,
        'tipe_kamar' => 'Deluxe',
        'status_kamar' => 'tersedia',
    ]);

    $kamarTerisi = Kamar::factory()->create([
        'nomor_kamar' => 202,
        'tipe_kamar' => 'Superior',
        'status_kamar' => 'terisi',
    ]);

    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    DB::table('booking')->insert([
        'kamar_id' => $kamarTerisi->id,
        'pegawai_id' => $pegawai->id,
        'nama_tamu' => 'Tamu Test',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'menunggu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(KamarIndex::class)
        ->set('q', 'Deluxe')
        ->set('status', 'tersedia')
        ->assertSee('Deluxe')
        ->assertDontSee('Superior');
});

it('prevents check to date from being earlier than check from date in kamar filter', function () {
    Livewire::test(KamarIndex::class)
        ->set('checkTo', '2026-03-01')
        ->set('checkFrom', '2026-03-05')
        ->assertSet('checkTo', '2026-03-05')
        ->assertSee('min="2026-03-05"', false);
});
