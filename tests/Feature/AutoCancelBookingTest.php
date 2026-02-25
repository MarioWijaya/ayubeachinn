<?php

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

it('auto-cancels menunggu booking older than 2 days', function () {
    Carbon::setTestNow(Carbon::create(2026, 2, 9, 10, 0, 0));

    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    $kamar = Kamar::factory()->create();

    $stale = Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $pegawai->id,
        'status_booking' => 'menunggu',
        'status_updated_at' => now()->subDays(3),
    ]);

    $fresh = Booking::factory()->create([
        'kamar_id' => $kamar->id,
        'pegawai_id' => $pegawai->id,
        'status_booking' => 'menunggu',
        'status_updated_at' => now()->subDay(),
    ]);

    Artisan::call('app:auto-cancel-stale-bookings');

    $stale->refresh();
    $fresh->refresh();

    expect($stale->status_booking)->toBe('batal');
    expect($stale->canceled_reason)->toBe('auto_cancel_timeout');
    expect($stale->status_updated_at)->toBeInstanceOf(CarbonInterface::class);
    expect($fresh->status_booking)->toBe('menunggu');

    Carbon::setTestNow();
});
