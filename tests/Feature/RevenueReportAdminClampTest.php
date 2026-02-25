<?php

use App\Livewire\Backoffice\Reports\Revenue\Index as RevenueIndex;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('clamps admin revenue report range to current month', function () {
    Carbon::setTestNow(Carbon::create(2026, 2, 9));

    $admin = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(RevenueIndex::class)
        ->set('from', '2025-01-01')
        ->set('to', '2025-01-31')
        ->assertSet('from', Carbon::now()->startOfMonth()->toDateString())
        ->assertSet('to', Carbon::now()->endOfMonth()->toDateString());

    Carbon::setTestNow();
});
