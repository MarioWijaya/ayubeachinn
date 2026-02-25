<?php

use App\Livewire\App\Booking\Create as BookingCreate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('rejects selected room when room type does not match', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $roomId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '301',
        'tipe_kamar' => 'Standard Fan',
        'tarif' => 200000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(BookingCreate::class)
        ->set('nama_tamu', 'Tamu Uji')
        ->set('no_telp_tamu', '08123456789')
        ->set('source_type', 'walk_in')
        ->set('hargaKamar', 200000)
        ->set('tanggal_range', '2026-03-01 to 2026-03-02')
        ->set('tipe_kamar', 'Deluxe')
        ->set('kamar_id', (string) $roomId)
        ->call('openConfirm')
        ->assertHasErrors(['kamar_id']);
});
