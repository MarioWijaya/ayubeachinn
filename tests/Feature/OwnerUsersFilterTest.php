<?php

use App\Models\User;
use Livewire\Livewire;

it('filters owner users list by search, level, and status', function () {
    User::factory()->create([
        'nama' => 'Admin Satu',
        'username' => 'admin_satu',
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    User::factory()->create([
        'nama' => 'Admin Dua',
        'username' => 'admin_dua',
        'level' => 'admin',
        'status_aktif' => false,
    ]);

    User::factory()->create([
        'nama' => 'Pegawai Satu',
        'username' => 'pegawai_satu',
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    Livewire::test(\App\Livewire\Owner\Users\Index::class)
        ->set('q', 'Admin')
        ->set('level', 'admin')
        ->set('status', '1')
        ->assertSee('Admin Satu')
        ->assertDontSee('Admin Dua')
        ->assertDontSee('Pegawai Satu');
});
