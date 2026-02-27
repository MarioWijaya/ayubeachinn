<!doctype html>
<html lang="id">
<head>
  @php
    $faviconPath = public_path('image/favicon.ico');
    $faviconVersion = file_exists($faviconPath) ? filemtime($faviconPath) : time();
  @endphp
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Ayu Beach Inn')</title>
  <link rel="icon" href="{{ asset('image/favicon.ico') }}?v={{ $faviconVersion }}" sizes="any" type="image/x-icon">
  <link rel="shortcut icon" href="{{ asset('image/favicon.ico') }}?v={{ $faviconVersion }}" type="image/x-icon">
  <link rel="apple-touch-icon" href="{{ asset('image/favicon.ico') }}?v={{ $faviconVersion }}">

  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')
  @livewireStyles

  <style>
    a[data-current="true"] { color: #854836 !important; }
    a[data-current="true"] svg { color: #854836 !important; }
    :root { --brand: 133 72 54; }
  </style>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://unpkg.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@0.563.0/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body class="bg-slate-50 font-[Poppins]">
  <div x-data="{ open:false }" class="min-h-screen">

    {{-- TOPBAR MOBILE --}}
    <div class="lg:hidden sticky top-0 z-20 bg-white border-b border-slate-200">
      <div class="flex items-center justify-between px-4 py-3">
        <button @click="open=true" class="p-2 rounded-xl border border-slate-200 bg-white active:scale-[0.98]">
          <i data-lucide="menu" class="h-5 w-5"></i>
        </button>
        <div class="font-semibold text-slate-900">Ayu Beach Inn</div>
        <div class="w-10"></div>
      </div>
    </div>

    {{-- OVERLAY MOBILE --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-black/70 backdrop-blur-[1px] lg:hidden" @click="open=false"></div>

    <div class="flex min-h-screen">
      {{-- SIDEBAR DESKTOP --}}
      <aside class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0 bg-white border-r border-slate-200">
        @include('partials.sidebar')
      </aside>

      {{-- SIDEBAR MOBILE DRAWER --}}
      <aside x-show="open" x-transition class="fixed inset-y-0 left-0 z-60 w-64 bg-white border-r border-slate-200 lg:hidden">
        <div class="flex flex-col h-full">
          <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <div class="font-semibold text-slate-900">Ayu Beach Inn</div>
            <button @click="open=false" class="p-2 rounded-lg hover:bg-slate-100">
              <i data-lucide="x" class="h-5 w-5"></i>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto" wire:navigate:scroll>
            @include('partials.sidebar')
          </div>
        </div>
      </aside>

      {{-- CONTENT (✅ hanya slot, jangan yield) --}}
      <main class="flex-1 lg:ml-64">
        <div class="min-h-screen w-full p-4 pt-6 lg:px-8 lg:pb-8 lg:pt-8">
          {{ $slot }}
        </div>
      </main>

    </div>
  </div>

  @livewireScripts
  <script>
    (() => {
      const refreshLucideIcons = () => {
        if (window.lucide?.createIcons) {
          window.lucide.createIcons();
        }
      };

      if (window.__lucideAutoRefreshBound) {
        refreshLucideIcons();
        return;
      }

      window.__lucideAutoRefreshBound = true;

      const bindProcessedHook = () => {
        if (!window.Livewire || window.__lucideProcessedHookBound) {
          return;
        }

        window.__lucideProcessedHookBound = true;
        Livewire.hook('message.processed', refreshLucideIcons);
      };

      document.addEventListener('DOMContentLoaded', refreshLucideIcons);
      document.addEventListener('livewire:navigated', refreshLucideIcons);
      document.addEventListener('livewire:initialized', bindProcessedHook, { once: true });

      bindProcessedHook();
      requestAnimationFrame(refreshLucideIcons);
    })();
  </script>
  @stack('scripts')
</body>
</html>
