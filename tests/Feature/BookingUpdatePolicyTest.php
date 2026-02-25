<?php

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

it('denies update when booking selesai', function () {
    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create();

    $booking = Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $pegawai->id,
        'status_booking' => 'selesai',
        'checkout_at' => now(),
        'total_final' => 350000,
    ]);

    expect(fn () => Gate::forUser($pegawai)->authorize('update', $booking))
        ->toThrow(AuthorizationException::class);
});

it('denies update when booking batal', function () {
    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create();

    $booking = Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $pegawai->id,
        'status_booking' => 'batal',
        'status_updated_at' => now(),
    ]);

    expect(fn () => Gate::forUser($pegawai)->authorize('update', $booking))
        ->toThrow(AuthorizationException::class);
});

it('blocks update request for selesai booking', function () {
    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'tersedia',
    ]);

    $booking = Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $pegawai->id,
        'status_booking' => 'selesai',
        'checkout_at' => now(),
        'total_final' => 450000,
    ]);
    $booking->forceFill(['status_booking' => 'selesai'])->save();
    $booking->refresh();
    expect($booking->status_booking)->toBe('selesai');

    $payload = [
        'kamar_id' => $kamar->id,
        'nama_tamu' => 'Tamu Update',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'menunggu',
        'catatan' => 'Update coba',
        'source_type' => 'walk_in',
        'source_detail' => null,
    ];

    $this->actingAs($pegawai)
        ->put(route('pegawai.booking.update', $booking->id), $payload)
        ->assertRedirect(route('pegawai.booking.index'))
        ->assertSessionHas('error', 'Booking sudah selesai dan tidak dapat diedit.');

    $booking->refresh();
    expect($booking->status_booking)->toBe('selesai');
});
