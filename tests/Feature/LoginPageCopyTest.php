<?php

use function Pest\Laravel\get;

test('login page shows updated helper copy', function () {
    $response = get(route('login'));

    $response
        ->assertOk()
        ->assertSeeText('Selamat Datang Kembali')
        ->assertSeeText('Masuk untuk mengakses sistem manajemen booking hotel')
        ->assertDontSeeText('Akses Sistem Manajemen Booking Hotel');
});
