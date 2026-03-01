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
        ->assertSessionHas('error', 'Booking tidak dapat diedit pada status ini.');

    $booking->refresh();
    expect($booking->status_booking)->toBe('selesai');
});

it('rejects status batal on update when booking is already check_in', function () {
    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'terisi',
    ]);

    $booking = Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $pegawai->id,
        'status_booking' => 'check_in',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
    ]);

    $payload = [
        'kamar_id' => $kamar->id,
        'nama_tamu' => 'Tamu Check In',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'batal',
        'catatan' => null,
        'source_type' => 'walk_in',
        'source_detail' => null,
    ];

    $this->actingAs($pegawai)
        ->from(route('pegawai.booking.edit', $booking->id))
        ->put(route('pegawai.booking.update', $booking->id), $payload)
        ->assertRedirect(route('pegawai.booking.edit', $booking->id))
        ->assertSessionHasErrors(['status_booking']);

    $booking->refresh();
    expect($booking->status_booking)->toBe('check_in');
});

it('prevents changing kamar when booking is already check_in', function () {
    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    $kamarAwal = Kamar::factory()->create([
        'status_kamar' => 'terisi',
    ]);

    $kamarLain = Kamar::factory()->create([
        'status_kamar' => 'tersedia',
    ]);

    $booking = Booking::factory()->create([
        'kamar_id' => $kamarAwal->id,
        'pegawai_id' => $pegawai->id,
        'status_booking' => 'check_in',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
    ]);

    $payload = [
        'kamar_id' => $kamarLain->id,
        'nama_tamu' => 'Tamu Check In',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'check_in',
        'catatan' => null,
        'source_type' => 'walk_in',
        'source_detail' => null,
    ];

    $this->actingAs($pegawai)
        ->from(route('pegawai.booking.edit', $booking->id))
        ->put(route('pegawai.booking.update', $booking->id), $payload)
        ->assertRedirect(route('pegawai.booking.edit', $booking->id))
        ->assertSessionHasErrors(['kamar_id']);

    $booking->refresh();
    expect($booking->kamar_id)->toBe($kamarAwal->id);
});
