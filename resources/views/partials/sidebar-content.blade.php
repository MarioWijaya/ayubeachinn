@php
  $user = auth()->user();
  $isOwner = $user && $user->level === 'owner';
  $isAdmin = $user && $user->level === 'admin';
  $isPegawai = $user && $user->level === 'pegawai';

  $tipeListSidebar = $tipeListSidebar ?? collect();
@endphp

<div class="h-full flex flex-col bg-white">

  {{-- HEADER --}}
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
    {{-- USER --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
      <div class="text-sm font-semibold text-slate-900">
        {{ $user->nama ?? $user->username ?? 'User' }}
      </div>
      <div class="text-xs text-slate-600">
        ({{ ucfirst($user->level ?? '-') }})
      </div>
    </div>
  </div>

  {{-- MENU --}}
  <nav class="mt-6 px-3 space-y-1">

    {{-- ================= OWNER ================= --}}
    @if($isOwner)

      {{-- Dashboard --}}
      <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('owner.dashboard') }}" @click="open = false"
         class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
         text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
         data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
        <i data-lucide="layout-dashboard"
           class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
        <span class="group-data-[current=true]:!text-[#854836]">Owner Dashboard</span>
      </a>

      {{-- Users --}}
      <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('owner.users.index') }}" @click="open = false"
         class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
         text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
         data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
        <i data-lucide="user-cog"
           class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
        <span class="group-data-[current=true]:!text-[#854836]"> User</span>
      </a>

    @endif

    {{-- ================= ADMIN ================= --}}
    @if($isAdmin || $isOwner)

      {{-- Dashboard --}}
      <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('admin.dashboard') }}" @click="open = false"
         class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
         text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
         data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
        <i data-lucide="layout-dashboard"
           class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
        <span class="group-data-[current=true]:!text-[#854836]">Dashboard</span>
      </a>

      {{-- Pegawai (admin only) --}}
      @if($isAdmin)
        <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('admin.pegawai.index') }}" @click="open = false"
           class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
           text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
           data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
          <i data-lucide="users"
             class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
          <span class="group-data-[current=true]:!text-[#854836]">Pegawai</span>
        </a>
      @endif

      {{-- Kamar --}}
      <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('admin.kamar.index') }}" @click="open = false"
         class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
         text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
         data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
        <i data-lucide="bed-double"
           class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
        <span class="group-data-[current=true]:!text-[#854836]">Kamar</span>
      </a>

      {{-- Booking --}}
      <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('admin.booking.index') }}" @click="open = false"
         class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
         text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
         data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
        <i data-lucide="calendar-check"
           class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
        <span class="group-data-[current=true]:!text-[#854836]">Monitoring Booking</span>
      </a>

    @endif

    {{-- ================= PEGAWAI ================= --}}
    @if($isPegawai)

      {{-- Dashboard --}}
      <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('pegawai.dashboard') }}" @click="open = false"
         class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
         text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
         data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
        <i data-lucide="home"
           class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
        <span class="group-data-[current=true]:!text-[#854836]">Dashboard</span>
      </a>

      {{-- Booking --}}
      <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('pegawai.booking.index') }}" @click="open = false"
         class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
         text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
         data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
        <i data-lucide="clipboard-list"
           class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
        <span class="group-data-[current=true]:!text-[#854836]">Booking Kamar</span>
      </a>

       {{-- Kalender --}}
      <a wire:navigate.hover wire:navigate wire:current="bg-[#854836]/10 text-[#854836]" href="{{ route('pegawai.calendar.index') }}" @click="open = false"
        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
        text-slate-600 hover:bg-[#854836]/10 hover:text-[#854836]
        data-[current=true]:bg-[#854836]/10 data-[current=true]:!text-[#854836]">
        <i data-lucide="calendar"
           class="h-5 w-5 text-slate-500 group-hover:text-[#854836] group-data-[current=true]:!text-[#854836]"></i>
        <span class="group-data-[current=true]:!text-[#854836]">Kalender</span>
      </a>
    @endif

  </nav>

  {{-- LOGOUT --}}
  <div class="mt-auto px-3 pb-6 pt-6">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit"
              class="group w-full flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-600">
        <i data-lucide="log-out"
           class="h-5 w-5 text-slate-500 group-hover:text-rose-600"></i>
        <span>Keluar</span>
      </button>
    </form>
  </div>

</div>
