@php
    $logoFile = 'image/logo ayu beach inn.png';
    $faviconFile = 'image/favicon.ico';

    if (file_exists(public_path($logoFile))) {
        $logoSrc = asset(str_replace(' ', '%20', $logoFile));
    } elseif (file_exists(public_path($faviconFile))) {
        $logoSrc = asset($faviconFile);
    } else {
        $logoSrc = asset('favicon.ico');
    }
@endphp

<img
    src="{{ $logoSrc }}"
    alt="Ayu Beach Inn"
    {{ $attributes->class('object-contain') }}
>
