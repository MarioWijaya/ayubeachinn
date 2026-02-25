<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('returns dashboard chart payload', function () {
    $user = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $today = now()->toDateString();
    $tomorrow = now()->addDay()->toDateString();

    DB::table('kamar')->insert([
        [
            'nomor_kamar' => '101',
            'tipe_kamar' => 'Standard Fan',
            'tarif' => 150000,
            'kapasitas' => 2,
            'status_kamar' => 'tersedia',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nomor_kamar' => '102',
            'tipe_kamar' => 'Standard Fan',
            'tarif' => 150000,
            'kapasitas' => 2,
            'status_kamar' => 'perbaikan',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('booking')->insert([
        [
            'kamar_id' => 1,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Budi',
            'tanggal_check_in' => $today,
            'tanggal_check_out' => $tomorrow,
            'checkout_at' => null,
            'status_booking' => 'check_in',
            'catatan' => null,
            'total_final' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'kamar_id' => 1,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Sari',
            'tanggal_check_in' => $today,
            'tanggal_check_out' => $tomorrow,
            'checkout_at' => now(),
            'status_booking' => 'selesai',
            'catatan' => null,
            'total_final' => 500000,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'kamar_id' => 1,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Adi',
            'tanggal_check_in' => $today,
            'tanggal_check_out' => $tomorrow,
            'checkout_at' => null,
            'status_booking' => 'batal',
            'catatan' => null,
            'total_final' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.charts', [
        'start_date' => $today,
        'end_date' => $tomorrow,
    ]));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'range' => ['start_date', 'end_date'],
            'stats' => ['total_rooms', 'occupied_today', 'occupancy_rate', 'bookings_count', 'revenue_total'],
            'charts' => [
                'occupancy' => ['labels', 'occupied', 'empty', 'total_rooms'],
                'revenue_daily' => ['labels', 'totals'],
                'status_distribution' => ['labels', 'totals'],
                'top_rooms' => ['labels', 'totals'],
            ],
            'bookings',
        ]);

    $firstBooking = collect($response->json('bookings'))->first();
    expect($firstBooking['check_in'])->toMatch('/^\d{2}-\d{2}-\d{4}$/');
    expect($firstBooking['check_out'])->toMatch('/^\d{2}-\d{2}-\d{4}$/');
});

it('defaults dashboard range to booking bounds when no dates are provided', function () {
    $user = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    DB::table('kamar')->insert([
        'id' => 1,
        'nomor_kamar' => '201',
        'tipe_kamar' => 'Standard Fan',
        'tarif' => 150000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        [
            'kamar_id' => 1,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Range Start',
            'tanggal_check_in' => '2025-01-10',
            'tanggal_check_out' => '2025-01-12',
            'status_booking' => 'check_in',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'kamar_id' => 1,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Range End',
            'tanggal_check_in' => '2026-02-01',
            'tanggal_check_out' => '2026-02-05',
            'status_booking' => 'menunggu',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.charts'));

    $response->assertSuccessful()
        ->assertJsonPath('range.start_date', '2025-01-10')
        ->assertJsonPath('range.end_date', '2026-02-05');
});

it('counts active rooms based on maintenance schedule for today', function () {
    Carbon::setTestNow(Carbon::create(2026, 2, 17, 10, 0, 0));

    $user = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $stalePerbaikanRoomId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '301',
        'tipe_kamar' => 'Standard Fan',
        'tarif' => 150000,
        'kapasitas' => 2,
        'status_kamar' => 'perbaikan',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $activePerbaikanRoomId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => '302',
        'tipe_kamar' => 'Standard Fan',
        'tarif' => 150000,
        'kapasitas' => 2,
        'status_kamar' => 'perbaikan',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('kamar_perbaikan')->insert([
        [
            'kamar_id' => $stalePerbaikanRoomId,
            'mulai' => '2026-02-10',
            'selesai' => '2026-02-12',
            'catatan' => 'Selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'kamar_id' => $activePerbaikanRoomId,
            'mulai' => '2026-02-16',
            'selesai' => '2026-02-18',
            'catatan' => 'Sedang perbaikan',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.charts'));

    $response->assertSuccessful()
        ->assertJsonPath('stats.total_rooms', 1);

    Carbon::setTestNow();
});
