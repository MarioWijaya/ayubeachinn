{{-- SIDEBAR + TOPBAR (responsive) --}}
@php
  $tipeListSidebar = $tipeListSidebar ?? collect();
@endphp

<div x-data="{ open: false }" class="min-h-screen bg-slate-50">

  {{-- Topbar (mobile) --}}
  <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur lg:hidden">
    <div class="flex items-center justify-between px-4 py-3 pt-[calc(env(safe-area-inset-top)+0.75rem)]">
      <button
        type="button"
        @click="open = true"
        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 hover:bg-slate-50"
        aria-label="Open sidebar"
      >
        <i data-lucide="menu" class="h-5 w-5"></i>
      </button>

      <div class="font-semibold text-slate-900">Ayu Beach Inn</div>

      <div class="w-9"></div>
    </div>
  </header>

  {{-- Overlay (mobile) --}}
  <div
    x-show="open"
    x-transition.opacity
    class="fixed inset-0 z-40 bg-black/40 lg:hidden"
    @click="open = false"
    aria-hidden="true"
  ></div>

  {{-- Sidebar --}}
  <aside
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 lg:translate-x-0 lg:static lg:block"
  >
    <div class="flex h-full flex-col">
      {{-- Brand --}}
      <div class="px-5 pt-3 pb-2">
        <div class="flex justify-center">
          <img
            src="{{ asset('image/logo-sidebar.png') }}"
            alt="Ayu Beach Inn"
            class="w-full max-w-[200px] object-contain"
          >
        </div>
      </div>
      {{-- User (paling atas) --}}
      <div class="px-5 pt-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center">
              <i data-lucide="user" class="h-5 w-5 text-slate-700"></i>
            </div>

            <div class="min-w-0">
              <div class="truncate font-semibold text-slate-900">
                {{ auth()->user()->username }}
                <span class="text-slate-500 font-medium">({{ ucfirst(auth()->user()->level) }})</span>
              </div>
              <div class="text-xs text-slate-500">Signed in</div>
            </div>

            <button
              type="button"
              class="ml-auto lg:hidden rounded-lg p-2 hover:bg-slate-100"
              @click="open = false"
              aria-label="Close sidebar"
            >
              <i data-lucide="x" class="h-5 w-5 text-slate-700"></i>
            </button>
          </div>
        </div>
      </div>

      {{-- Menu --}}
      <nav class="mt-5 flex-1 px-3">
        <div class="px-3 pb-2 text-[11px] font-semibold tracking-wide text-slate-500">
          MAIN MENU
        </div>

        {{-- MENU ADMIN --}}
        @if(auth()->user()->level === 'admin')
          <a href="{{ route('admin.dashboard') }}"
             class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:text-white hover:bg-[#854836] transition">
            <i data-lucide="layout-dashboard" class="h-5 w-5 text-slate-500 group-hover:text-white"></i>
            <span>Dashboard</span>
          </a>

          <a href="{{ route('admin.pegawai.index') }}"
             class="group mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:text-white hover:bg-[#854836] transition">
            <i data-lucide="users" class="h-5 w-5 text-slate-500 group-hover:text-white"></i>
            <span>Pegawai</span>
          </a>

          <a href="{{ route('admin.kamar.index') }}"
             class="group mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:text-white hover:bg-[#854836] transition">
            <i data-lucide="bed-double" class="h-5 w-5 text-slate-500 group-hover:text-white"></i>
            <span>Kamar</span>
          </a>

          <a href="{{ route('admin.booking.index') }}"
             class="group mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:text-white hover:bg-[#854836] transition">
            <i data-lucide="calendar-check" class="h-5 w-5 text-slate-500 group-hover:text-white"></i>
            <span>Monitoring Booking</span>
          </a>
        @endif

        {{-- MENU PEGAWAI --}}
        @if(auth()->user()->level === 'pegawai')
          <div x-data="{ openSub: {{ request()->routeIs('pegawai.dashboard') ? 'true' : 'false' }} }">
            <button
              type="button"
              class="w-full flex items-center justify-between rounded-xl px-4 py-3 font-semibold
                     {{ request()->routeIs('pegawai.dashboard') ? 'bg-[#854836] text-white' : 'text-slate-700 hover:bg-slate-50' }}"
              @click="openSub = !openSub"
            >
              <span class="inline-flex items-center gap-2">
                <i data-lucide="layout-dashboard" class="h-4 w-4"></i> Dashboard
              </span>
              <i data-lucide="chevron-down" class="h-4 w-4 transition" :class="openSub ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="openSub" x-cloak class="mt-2 space-y-1 pl-3">
              @foreach($tipeListSidebar as $t)
                <a
                  wire:navigate
                  href="{{ route('pegawai.dashboard', ['tipe' => $t->tipe_kamar]) }}"
                  class="block rounded-lg px-3 py-2 text-sm
                    {{ request('tipe') == $t->tipe_kamar
                        ? 'text-[#854836] font-semibold bg-[#854836]/5'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                >
                  {{ $t->tipe_kamar }}
                </a>
              @endforeach
            </div>
          </div>
        @endif

        <div class="my-4 border-t border-slate-200"></div>

        <div class="px-3 pb-2 text-[11px] font-semibold tracking-wide text-slate-500">
          OTHERS
        </div>
      </nav>

      {{-- Logout (paling bawah) --}}
      <div class="border-t border-slate-200 p-4">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button
            type="submit"
            class="group w-full inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 transition"
          >
            <i data-lucide="log-out" class="h-5 w-5 text-slate-500 group-hover:text-rose-600"></i>
            Logout
          </button>
        </form>
      </div>

    </div>
  </aside>

  {{-- Main content wrapper --}}
  <main class="lg:ml-72">
    <div class="p-4 lg:p-8">
      {{ $slot ?? '' }}
      @yield('content')
    </div>
  </main>
</div>

<script>
  function refreshLucide() {
    if (window.lucide) window.lucide.createIcons();
  }
  document.addEventListener('DOMContentLoaded', refreshLucide);
  document.addEventListener('livewire:navigated', refreshLucide);
</script>
