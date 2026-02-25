@php
    $faviconPath = public_path('image/favicon.ico');
    $faviconVersion = file_exists($faviconPath) ? filemtime($faviconPath) : time();
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{ asset('image/favicon.ico') }}?v={{ $faviconVersion }}" sizes="any" type="image/x-icon">
<link rel="shortcut icon" href="{{ asset('image/favicon.ico') }}?v={{ $faviconVersion }}" type="image/x-icon">
<link rel="apple-touch-icon" href="{{ asset('image/favicon.ico') }}?v={{ $faviconVersion }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
