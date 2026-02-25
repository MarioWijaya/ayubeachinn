<?php

use App\Livewire\Backoffice\Reports\Revenue\Index as RevenueIndex;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('shows correct preview transaction count', function () {
    Carbon::setTestNow(Carbon::create(2026, 2, 9, 10, 0, 0));

    $admin = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    Booking::factory()->create([
        'status_booking' => 'selesai',
        'checkout_at' => now(),
        'total_final' => 150000,
    ]);

    Booking::factory()->create([
        'status_booking' => 'selesai',
        'checkout_at' => now(),
        'total_final' => 250000,
    ]);

    Livewire::actingAs($admin)
        ->test(RevenueIndex::class)
        ->set('from', now()->startOfMonth()->toDateString())
        ->set('to', now()->endOfMonth()->toDateString())
        ->call('requestDownload', 'pdf')
        ->assertSet('previewReady', true)
        ->assertSet('previewCount', 2)
        ->assertDontSee('Baris Preview');

    Carbon::setTestNow();
});
