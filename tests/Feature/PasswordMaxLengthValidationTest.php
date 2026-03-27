<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects owner user creation when password exceeds 25 characters', function () {
    $owner = User::factory()->create([
        'level' => 'owner',
        'status_aktif' => true,
    ]);

    $tooLongPassword = str_repeat('a', 26);

    $response = $this->actingAs($owner)
        ->from(route('owner.users.create'))
        ->post(route('owner.users.store'), [
            'nama' => 'Owner Test User',
            'username' => 'owner_test_user',
            'password' => $tooLongPassword,
            'level' => 'pegawai',
            'status_aktif' => '1',
        ]);

    $response
        ->assertRedirect(route('owner.users.create'))
        ->assertSessionHasErrors(['password']);
});

it('rejects admin pegawai creation when password exceeds 25 characters', function () {
    $admin = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $tooLongPassword = str_repeat('b', 26);

    $response = $this->actingAs($admin)
        ->from(route('admin.pegawai.create'))
        ->post(route('admin.pegawai.store'), [
            'nama' => 'Pegawai Test',
            'username' => 'pegawai_test_user',
            'password' => $tooLongPassword,
        ]);

    $response
        ->assertRedirect(route('admin.pegawai.create'))
        ->assertSessionHasErrors(['password']);
});

it('rejects login when password input exceeds 25 characters', function () {
    $tooLongPassword = str_repeat('c', 26);

    $response = $this->from(route('login'))
        ->post(route('login.proses'), [
            'username' => 'random_user',
            'password' => $tooLongPassword,
        ]);

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['password']);
});
