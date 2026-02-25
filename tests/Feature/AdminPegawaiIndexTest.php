<?php

use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Admin\Pegawai\Index as AdminPegawaiIndex;

it('filters admin pegawai list by search and status', function () {
    User::factory()->create([
        'nama' => 'Pegawai Aktif',
        'username' => 'pegawai_aktif',
        'level' => 'pegawai',
        'status_aktif' => true,
    ]);

    User::factory()->create([
        'nama' => 'Pegawai Nonaktif',
        'username' => 'pegawai_nonaktif',
        'level' => 'pegawai',
        'status_aktif' => false,
    ]);

    User::factory()->create([
        'nama' => 'Admin Satu',
        'username' => 'admin_satu',
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    Livewire::test(AdminPegawaiIndex::class)
        ->set('q', 'Pegawai')
        ->set('status', '1')
        ->assertSee('Pegawai Aktif')
        ->assertDontSee('Pegawai Nonaktif')
        ->assertDontSee('Admin Satu');
});

it('paginates pegawai list by 10 per page', function () {
    User::factory()
        ->count(11)
        ->sequence(fn ($sequence) => [
            'nama' => sprintf('Pegawai %02d', $sequence->index + 1),
            'username' => sprintf('pegawai_%02d', $sequence->index + 1),
            'level' => 'pegawai',
            'status_aktif' => true,
        ])
        ->create();

    Livewire::test(AdminPegawaiIndex::class)
        ->assertSee('Pegawai 01')
        ->assertSee('Pegawai 10')
        ->assertDontSee('Pegawai 11');
});
