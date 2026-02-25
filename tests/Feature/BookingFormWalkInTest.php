<?php

use App\Livewire\Pegawai\Booking\Form as PegawaiBookingForm;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('auto-fills harga kamar when sumber booking is walk-in', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $walkInId = DB::table('sumber_booking')->insertGetId([
        'nama' => 'Walk-in',
        'kode' => 'walk_in',
        'aktif' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 101,
        'tipe_kamar' => 'Standard',
        'tarif' => 350000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(PegawaiBookingForm::class)
        ->set('kamar_id', (string) $kamarId)
        ->set('sumber_booking_id', (string) $walkInId)
        ->assertSet('harga_kamar', 350000)
        ->assertSee('Konfirmasi Booking');
});

it('keeps manual harga kamar for non walk-in sumber booking', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $agodaId = DB::table('sumber_booking')->insertGetId([
        'nama' => 'Agoda',
        'kode' => 'agoda',
        'aktif' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 102,
        'tipe_kamar' => 'Deluxe',
        'tarif' => 400000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(PegawaiBookingForm::class)
        ->set('kamar_id', (string) $kamarId)
        ->set('harga_kamar', 250000)
        ->set('sumber_booking_id', (string) $agodaId)
        ->assertSet('harga_kamar', 250000);
});
