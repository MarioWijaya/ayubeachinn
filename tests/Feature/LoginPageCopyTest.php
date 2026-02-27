<?php

test('login page shows updated helper copy', function () {
    $response = $this->get(route('login'));

    $response
        ->assertOk()
        ->assertSeeText('Selamat Datang Kembali')
        ->assertSeeText('masuk untuk mengakses sistem manejemn booking hotel')
        ->assertDontSeeText('Akses Sistem Manajemen Booking Hotel');
});
