<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('returns only available rooms for selected date range on booking edit', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $roomA = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '101',
        'tipe_kamar' => 'Standard',
        'tarif' => 250000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $roomB = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '102',
        'tipe_kamar' => 'Deluxe',
        'tarif' => 350000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $roomC = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '103',
        'tipe_kamar' => 'FamilyRoom',
        'tarif' => 450000,
        'kapasitas' => 3,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $editedBookingId = DB::table('booking')->insertGetId([
        'kamar_id' => $roomA,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Edit',
        'tanggal_check_in' => '2026-03-16',
        'tanggal_check_out' => '2026-03-20',
        'status_booking' => 'menunggu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        'kamar_id' => $roomB,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Bentrok',
        'tanggal_check_in' => '2026-03-18',
        'tanggal_check_out' => '2026-03-22',
        'status_booking' => 'check_in',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        'kamar_id' => $roomC,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Selesai',
        'tanggal_check_in' => '2026-03-10',
        'tanggal_check_out' => '2026-03-12',
        'status_booking' => 'selesai',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(route('pegawai.booking.kamar_tersedia', [
        'tanggal_check_in' => '2026-03-16',
        'tanggal_check_out' => '2026-03-20',
        'exclude_booking_id' => $editedBookingId,
    ]));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'rooms' => [
            ['id', 'nomor_kamar', 'tipe_kamar', 'tarif'],
        ],
    ]);
    $rooms = collect($response->json('rooms'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($rooms)->toContain($roomA, $roomC)->not->toContain($roomB);
});

it('validates date range when requesting available rooms', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $this->actingAs($user)
        ->getJson(route('pegawai.booking.kamar_tersedia', [
            'tanggal_check_in' => '2026-03-20',
            'tanggal_check_out' => '2026-03-20',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['tanggal_check_out']);
});

it('returns only available rooms by selected room type', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $availableDeluxe = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '201',
        'tipe_kamar' => 'Deluxe',
        'tarif' => 350000,
        'kapasitas' => 2,
        'status_kamar' => 'terisi',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $occupiedDeluxe = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '202',
        'tipe_kamar' => 'Deluxe',
        'tarif' => 350000,
        'kapasitas' => 2,
        'status_kamar' => 'terisi',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('kamar')->insert([
        'nomor_kamar' => '203',
        'tipe_kamar' => 'Standard Fan',
        'tarif' => 250000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        'kamar_id' => $occupiedDeluxe,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Aktif Deluxe',
        'tanggal_check_in' => now()->subDay()->toDateString(),
        'tanggal_check_out' => now()->addDay()->toDateString(),
        'status_booking' => 'check_in',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(route('pegawai.booking.kamar_tersedia_tipe', [
        'tipe_kamar' => 'Deluxe',
    ]));

    $response->assertSuccessful();
    $rooms = collect($response->json('rooms'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($rooms)
        ->toContain($availableDeluxe)
        ->not->toContain($occupiedDeluxe);
});

it('returns available room types and rooms by date range', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $deluxeAvailable = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '301',
        'tipe_kamar' => 'Deluxe',
        'tarif' => 350000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $standardBooked = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '302',
        'tipe_kamar' => 'Standard Fan',
        'tarif' => 250000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        'kamar_id' => $standardBooked,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Tamu Bentrok',
        'tanggal_check_in' => '2026-04-10',
        'tanggal_check_out' => '2026-04-12',
        'status_booking' => 'check_in',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(route('pegawai.booking.kamar_availability', [
        'start_date' => '2026-04-10',
        'end_date' => '2026-04-12',
    ]));

    $response->assertSuccessful();

    $types = collect($response->json('types'))->values()->all();
    expect($types)->toContain('Deluxe')->not->toContain('Standard Fan');

    $roomsByType = $this->actingAs($user)->getJson(route('pegawai.booking.kamar_availability', [
        'start_date' => '2026-04-10',
        'end_date' => '2026-04-12',
        'tipe_kamar' => 'Deluxe',
    ]));

    $roomsByType->assertSuccessful();
    $roomIds = collect($roomsByType->json('rooms'))->pluck('id')->map(fn ($id) => (int) $id)->all();
    expect($roomIds)->toContain($deluxeAvailable)->not->toContain($standardBooked);
});

it('validates date range for availability endpoint', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $this->actingAs($user)
        ->getJson(route('pegawai.booking.kamar_availability', [
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-10',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['end_date']);
});
