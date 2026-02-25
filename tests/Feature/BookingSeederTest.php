<?php

use Database\Seeders\BookingSeeder;
use Database\Seeders\KamarSeeder;
use Database\Seeders\SumberBookingSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

it('seeds one booking per room for the current date', function () {
    $this->seed([
        SumberBookingSeeder::class,
        KamarSeeder::class,
        BookingSeeder::class,
    ]);

    $today = now()->toDateString();

    $totalRooms = DB::table('kamar')->count();
    $totalBookings = DB::table('booking')
        ->whereDate('tanggal_check_in', '<=', $today)
        ->whereDate('tanggal_check_out', '>', $today)
        ->count();

    expect($totalRooms)->toBe(40);
    expect($totalBookings)->toBe($totalRooms);

    $duplicateRooms = DB::table('booking')
        ->select('kamar_id', DB::raw('COUNT(*) as total'))
        ->whereDate('tanggal_check_in', '<=', $today)
        ->whereDate('tanggal_check_out', '>', $today)
        ->groupBy('kamar_id')
        ->having('total', '>', 1)
        ->count();

    expect($duplicateRooms)->toBe(0);
});

it('seeds bookings for every day in the configured seed range', function () {
    $this->seed([
        SumberBookingSeeder::class,
        KamarSeeder::class,
        BookingSeeder::class,
    ]);

    $startDate = Carbon::create(2024, 1, 1)->toDateString();
    $endDate = Carbon::create(2026, 12, 31)->toDateString();
    $expectedDays = (int) Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

    $seededDays = DB::table('booking')
        ->whereBetween('tanggal_check_in', [$startDate, $endDate])
        ->distinct('tanggal_check_in')
        ->count('tanggal_check_in');

    expect($seededDays)->toBe($expectedDays);
});
