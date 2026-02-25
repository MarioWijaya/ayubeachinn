<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('shows perpanjangan confirmation modal with computed totals', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 201,
        'tipe_kamar' => 'Standard',
        'tarif' => 150000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamarId,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Modal Perpanjang',
        'tanggal_check_in' => '2026-01-10',
        'tanggal_check_out' => '2026-01-12',
        'status_booking' => 'menunggu',
        'harga_kamar' => 120000,
        'catatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $layananId = DB::table('layanan')->insertGetId([
        'nama' => 'Extra Bed',
        'aktif' => 1,
        'harga' => 20000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking_layanan')->insert([
        'booking_id' => $bookingId,
        'layanan_id' => $layananId,
        'qty' => 1,
        'harga_satuan' => 20000,
        'subtotal' => 20000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.edit', $bookingId))
        ->assertOk()
        ->assertSee('Konfirmasi Perpanjangan')
        ->assertSee('Biaya Tambahan')
        ->assertSee('id="extendModal"', false)
        ->assertSee('data-current-total="260000"', false);
});
