<?php

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes check out status in dashboard filter data', function () {
    $user = User::factory()->create();

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'tersedia',
    ]);

    $start = Carbon::parse('2026-02-01');
    $end = Carbon::parse('2026-02-10');

    Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'tanggal_check_in' => '2026-02-03',
        'tanggal_check_out' => '2026-02-05',
        'status_booking' => 'check_out',
        'status_updated_at' => now(),
    ]);

    Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'tanggal_check_in' => '2026-02-04',
        'tanggal_check_out' => '2026-02-06',
        'status_booking' => 'menunggu',
        'status_updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(route('dashboard.charts', [
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('charts.status_distribution.labels.2', 'check_out')
        ->assertJsonPath('charts.status_distribution.totals.2', 1);
});

it('returns all data range when dashboard chart called without date filters', function () {
    $user = User::factory()->create();

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'tersedia',
    ]);

    Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'tanggal_check_in' => '2026-01-05',
        'tanggal_check_out' => '2026-01-10',
        'status_booking' => 'menunggu',
        'status_updated_at' => now(),
    ]);

    Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'tanggal_check_in' => '2026-03-01',
        'tanggal_check_out' => '2026-03-07',
        'status_booking' => 'selesai',
        'status_updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(route('dashboard.charts'));

    $response->assertSuccessful()
        ->assertJsonPath('range.start_date', '2026-01-05')
        ->assertJsonPath('range.end_date', '2026-03-07');
});

it('returns weekly occupancy labels as date ranges for long periods', function () {
    $user = User::factory()->create();

    $kamar = Kamar::factory()->create([
        'status_kamar' => 'tersedia',
    ]);

    Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'tanggal_check_in' => '2026-01-25',
        'tanggal_check_out' => '2026-02-23',
        'status_booking' => 'menunggu',
        'status_updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(route('dashboard.charts', [
        'start_date' => '2026-01-25',
        'end_date' => '2026-02-23',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('charts.occupancy.granularity', 'week');

    $firstLabel = data_get($response->json(), 'charts.occupancy.labels.0');

    expect($firstLabel)->toBeString();
    expect($firstLabel)->toContain(' - ');
});
