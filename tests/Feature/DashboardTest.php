<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);
    $this->actingAs($user);

    $response = $this->get(route('admin.dashboard'));
    $response->assertOk()
        ->assertSeeInOrder([
            'Okupansi',
            'Range filter',
            'Distribusi Status',
        ]);
});
