<?php

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('checks out a booking and stores totals as check_out before final completion', function () {
    $admin = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create([
        'tarif' => 200000,
        'status_kamar' => 'terisi',
    ]);

    $layananId = DB::table('layanan')->insertGetId([
        'nama' => 'Laundry',
        'kode' => 'LYD',
        'aktif' => true,
        'harga' => 50000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $admin->id,
        'nama_tamu' => 'Tamu Test',
        'tanggal_check_in' => '2026-01-01',
        'tanggal_check_out' => '2026-01-04',
        'status_booking' => 'check_in',
        'harga_kamar' => 180000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking_layanan')->insert([
        'booking_id' => $bookingId,
        'layanan_id' => $layananId,
        'qty' => 1,
        'harga_satuan' => 50000,
        'subtotal' => 50000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.booking.checkout', $bookingId), [
            'denda' => 10000,
        ])
        ->assertRedirect(route('admin.booking.index'));

    $booking = DB::table('booking')->where('id', $bookingId)->first();

    $expectedTotalKamar = 3 * 180000;
    $expectedTotalLayanan = 50000;
    $expectedTotalFinal = $expectedTotalKamar + $expectedTotalLayanan + 10000;

    expect($booking->status_booking)->toBe('check_out');
    expect($booking->checkout_at)->not->toBeNull();
    expect((int) $booking->denda)->toBe(10000);
    expect((int) $booking->total_kamar)->toBe($expectedTotalKamar);
    expect((int) $booking->total_layanan)->toBe($expectedTotalLayanan);
    expect((int) $booking->total_final)->toBe($expectedTotalFinal);

    $kamar->refresh();
    expect($kamar->status_kamar)->toBe('terisi');
});

it('checks in a waiting booking from list action', function () {
    $admin = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'tersedia',
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $admin->id,
        'nama_tamu' => 'Tamu Checkin',
        'tanggal_check_in' => '2026-01-01',
        'tanggal_check_out' => '2026-01-04',
        'status_booking' => 'menunggu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.booking.checkin', $bookingId))
        ->assertRedirect(route('admin.booking.index'));

    $booking = DB::table('booking')->where('id', $bookingId)->first();
    expect($booking->status_booking)->toBe('check_in');
    expect($booking->status_updated_at)->not->toBeNull();

    $kamar->refresh();
    expect($kamar->status_kamar)->toBe('terisi');
});

it('marks check_out booking as selesai and releases room', function () {
    $admin = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'terisi',
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $admin->id,
        'nama_tamu' => 'Tamu Selesai',
        'tanggal_check_in' => '2026-01-01',
        'tanggal_check_out' => '2026-01-04',
        'status_booking' => 'check_out',
        'checkout_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.booking.selesai', $bookingId))
        ->assertRedirect(route('admin.booking.index'));

    $booking = DB::table('booking')->where('id', $bookingId)->first();
    expect($booking->status_booking)->toBe('selesai');

    $kamar->refresh();
    expect($kamar->status_kamar)->toBe('tersedia');
});
