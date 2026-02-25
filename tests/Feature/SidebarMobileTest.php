<?php

use App\Models\User;

it('loads alpine for the mobile sidebar on pegawai pages', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.calendar.index'))
        ->assertSuccessful()
        ->assertSee('x-data="{ open:false }"', false);
});
