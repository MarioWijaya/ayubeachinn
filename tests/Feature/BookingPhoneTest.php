<?php

use App\Livewire\App\Booking\Create as BookingCreate;
use App\Models\Kamar;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('stores guest phone number on booking creation', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'tersedia',
        'tarif' => 300000,
    ]);

    Livewire::actingAs($user)
        ->test(BookingCreate::class)
        ->set('kamar_id', (string) $kamar->id)
        ->set('nama_tamu', 'Tamu Telepon')
        ->set('no_telp_tamu', '081234567890')
        ->set('source_type', 'walk_in')
        ->set('hargaKamar', 300000)
        ->set('tanggal_range', now()->toDateString().' to '.now()->addDay()->toDateString())
        ->call('save');

    $booking = DB::table('booking')->where('nama_tamu', 'Tamu Telepon')->first();

    expect($booking)->not->toBeNull();
    expect($booking->no_telp_tamu)->toBe('081234567890');
});
