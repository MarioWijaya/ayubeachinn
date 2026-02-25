<?php

it('uses size-9 for the auth split logo', function () {
    $path = __DIR__ . '/../../resources/views/layouts/auth/split.blade.php';
    $contents = file_get_contents($path);

    expect(str_contains($contents, 'me-2 size-9 fill-current text-white'))->toBeTrue();
});
