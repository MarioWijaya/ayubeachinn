<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('renders booking form for pegawai', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.create'))
        ->assertSuccessful()
        ->assertSee('Tambah Booking')
        ->assertSee('id="tanggalRange"', false);
});

it('renders booking edit page for pegawai', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 'A-101',
        'tipe_kamar' => 'Standard',
        'tarif' => 250000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamarId,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Test',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'menunggu',
        'catatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.edit', $bookingId))
        ->assertSuccessful()
        ->assertSee('Ubah Booking')
        ->assertSee('Tipe Kamar')
        ->assertSee('Nomor Kamar')
        ->assertSee('id="tipeKamarSelect"', false)
        ->assertSee('id="kamarSelect"', false)
        ->assertSee('id="checkInDate"', false)
        ->assertSee('id="checkOutDate"', false)
        ->assertSee(
            'data-url-template="'.route('pegawai.booking.tanggal_terpakai', [
                'kamarId' => '__KAMAR__',
                'exclude_booking_id' => $bookingId,
            ]).'"',
            false
        )
        ->assertSee(
            'data-rooms-url="'.route('pegawai.booking.kamar_tersedia').'"',
            false
        );
});

it('renders booking detail page for pegawai', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 'B-201',
        'tipe_kamar' => 'Deluxe',
        'tarif' => 300000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamarId,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Detail',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'check_in',
        'catatan' => 'Catatan detail',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.show', $bookingId))
        ->assertSuccessful()
        ->assertSee('Detail Booking')
        ->assertSee('Tamu Detail');
});

it('hides batal option on booking edit when status is check_in', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 'C-301',
        'tipe_kamar' => 'Deluxe',
        'tarif' => 350000,
        'kapasitas' => 2,
        'status_kamar' => 'terisi',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamarId,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Checkin',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'check_in',
        'catatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.edit', $bookingId))
        ->assertSuccessful()
        ->assertSee('value="check_in"', false)
        ->assertDontSee('value="batal"', false)
        ->assertSee('Kamar tidak dapat diubah ketika status booking sudah check-in.');
});

it('renders responsive check-in and check-out inputs on booking edit', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 'D-401',
        'tipe_kamar' => 'Standard',
        'tarif' => 220000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $bookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $kamarId,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Responsive',
        'tanggal_check_in' => now()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'menunggu',
        'catatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.edit', $bookingId))
        ->assertSuccessful()
        ->assertSee('id="checkInDate"', false)
        ->assertSee('id="checkOutDate"', false)
        ->assertSee('min-w-0 max-w-full w-full rounded-xl border border-slate-300 bg-white', false)
        ->assertSee('disableMobile: true', false);
});
