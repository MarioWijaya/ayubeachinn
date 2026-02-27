<?php

use App\Livewire\Backoffice\Reports\Revenue\Index as RevenueIndex;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('prevents to date from being earlier than from date in revenue report filter', function () {
    Carbon::setTestNow(Carbon::create(2026, 2, 27));

    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    Livewire::actingAs($pegawai)
        ->test(RevenueIndex::class)
        ->set('from', '2026-02-15')
        ->set('to', '2026-02-10')
        ->assertSet('to', '2026-02-15')
        ->assertSee('min="2026-02-15"', false);

    Carbon::setTestNow();
});

it('syncs to date when from date moves beyond current to date', function () {
    Carbon::setTestNow(Carbon::create(2026, 2, 27));

    $pegawai = User::factory()->create([
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    Livewire::actingAs($pegawai)
        ->test(RevenueIndex::class)
        ->set('to', '2026-02-12')
        ->set('from', '2026-02-20')
        ->assertSet('to', '2026-02-20');

    Carbon::setTestNow();
});
