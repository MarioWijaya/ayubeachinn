<?php

it('renders app-logo-icon in sidebar partial', function () {
    $path = __DIR__ . '/../../resources/views/partials/sidebar.blade.php';
    $contents = file_get_contents($path);
    expect(str_contains($contents, "x-app-logo-icon"))->toBeTrue();
    expect(str_contains($contents, 'w-full max-w-[240px] h-auto'))->toBeTrue();

    // Ensure we did not add separate text next to the logo — the logo image contains brand text
    expect(str_contains($contents, 'Manajemen Booking'))->toBeFalse();
});
