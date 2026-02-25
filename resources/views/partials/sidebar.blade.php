@php
  $user = auth()->user();
  $role = $user->level ?? 'pegawai';
  $isOwner = $role === 'owner';
  $isAdmin = $role === 'admin';
  $isPegawai = $role === 'pegawai';

  $linkBase = 'group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition';
  $linkActive = 'data-[current=true]:bg-[#FFB22C]/20 data-[current=true]:text-slate-900';
  $linkInactive = 'text-slate-600 hover:bg-[#FFB22C]/20 hover:text-slate-900';
  $iconBase = 'h-5 w-5 text-slate-500 group-hover:text-slate-900 group-data-[current=true]:text-slate-900';
@endphp

<div class="h-full flex flex-col bg-white">
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

  <div class="px-6">
    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
      <div class="text-sm font-semibold text-slate-900">
        {{ $user->nama ?? $user->username ?? 'User' }}
      </div>
      <div class="text-xs text-slate-600">
        ({{ ucfirst($role) }})
      </div>
    </div>
  </div>

  <nav class="mt-6 px-3 space-y-1">
    @if($isOwner)
      <a wire:navigate href="{{ route('owner.dashboard') }}" @click="open = false"
         data-current="{{ request()->routeIs('owner.dashboard') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="layout-dashboard" class="{{ $iconBase }}"></i>
        <span>Dashboard</span>
      </a>

      <a wire:navigate href="{{ route('owner.users.index') }}" @click="open = false"
         data-current="{{ request()->routeIs('owner.users.*') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="users" class="{{ $iconBase }}"></i>
        <span>User</span>
      </a>

      <a wire:navigate href="{{ route('owner.kamar.index') }}" @click="open = false"
         data-current="{{ request()->routeIs(['owner.kamar.*','admin.kamar.*']) ? 'true' : 'false' }}"
         data-active-prefixes="/owner/kamar|/admin/kamar"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="bed-double" class="{{ $iconBase }}"></i>
        <span>Kamar</span>
      </a>

      <a wire:navigate href="{{ route('owner.booking.index') }}" @click="open = false"
         data-current="{{ request()->routeIs(['owner.booking.*','admin.booking.*']) ? 'true' : 'false' }}"
         data-active-prefixes="/owner/booking|/admin/booking"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="calendar-check" class="{{ $iconBase }}"></i>
        <span>Booking</span>
      </a>

      <a wire:navigate href="{{ route('owner.calendar.index') }}" @click="open = false"
         data-current="{{ request()->routeIs(['owner.calendar.*','admin.calendar.*']) ? 'true' : 'false' }}"
         data-active-prefixes="/owner/calendar|/admin/calendar"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="calendar" class="{{ $iconBase }}"></i>
        <span>Kalender</span>
      </a>

      <a wire:navigate href="{{ route('owner.reports.revenue.index') }}" @click="open = false"
         data-current="{{ request()->routeIs(['owner.reports.revenue.*','admin.reports.revenue.*']) ? 'true' : 'false' }}"
         data-active-prefixes="/owner/reports/revenue|/admin/reports/revenue"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="wallet" class="{{ $iconBase }}"></i>
        <span>Laporan Pendapatan</span>
      </a>
    @endif

    @if($isAdmin)
      <a wire:navigate href="{{ route('admin.dashboard') }}" @click="open = false"
         data-current="{{ request()->routeIs('admin.dashboard') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="layout-dashboard" class="{{ $iconBase }}"></i>
        <span>Dashboard</span>
      </a>

      <a wire:navigate href="{{ route('admin.pegawai.index') }}" @click="open = false"
         data-current="{{ request()->routeIs('admin.pegawai.*') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="users" class="{{ $iconBase }}"></i>
        <span>Pegawai</span>
      </a>

      <a wire:navigate href="{{ route('admin.kamar.index') }}" @click="open = false"
         data-current="{{ request()->routeIs('admin.kamar.*') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="bed-double" class="{{ $iconBase }}"></i>
        <span>Kamar</span>
      </a>

      <a wire:navigate href="{{ route('admin.booking.index') }}" @click="open = false"
         data-current="{{ request()->routeIs('admin.booking.*') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="calendar-check" class="{{ $iconBase }}"></i>
        <span>Booking</span>
      </a>

      <a wire:navigate href="{{ route('admin.calendar.index') }}" @click="open = false"
         data-current="{{ request()->routeIs('admin.calendar.*') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="calendar" class="{{ $iconBase }}"></i>
        <span>Kalender</span>
      </a>

      <a wire:navigate href="{{ route('admin.reports.revenue.index') }}" @click="open = false"
         data-current="{{ request()->routeIs('admin.reports.revenue.*') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="wallet" class="{{ $iconBase }}"></i>
        <span>Laporan Pendapatan</span>
      </a>
    @endif

    @if($isPegawai)
      <a wire:navigate href="{{ route('pegawai.dashboard') }}" @click="open = false"
         data-current="{{ request()->routeIs('pegawai.dashboard') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="layout-dashboard" class="{{ $iconBase }}"></i>
        <span>Dashboard</span>
      </a>

      <a wire:navigate href="{{ route('pegawai.booking.index') }}" @click="open = false"
         data-current="{{ request()->routeIs('pegawai.booking.*') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="calendar-check" class="{{ $iconBase }}"></i>
        <span>Booking</span>
      </a>

      <a wire:navigate href="{{ route('pegawai.calendar.index') }}" @click="open = false"
         data-current="{{ request()->routeIs('pegawai.calendar.*') ? 'true' : 'false' }}"
         class="{{ $linkBase }} {{ $linkInactive }} {{ $linkActive }}">
        <i data-lucide="calendar" class="{{ $iconBase }}"></i>
        <span>Kalender</span>
      </a>
    @endif
  </nav>

  <div class="mt-auto px-3 pb-6 pt-6">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit"
              class="group w-full flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-600">
        <i data-lucide="log-out" class="h-5 w-5 text-slate-500 group-hover:text-rose-600"></i>
        <span>Keluar</span>
      </button>
    </form>
  </div>
</div>
