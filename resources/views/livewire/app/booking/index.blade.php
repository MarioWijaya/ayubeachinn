@php
  $role = auth()->user()->level ?? 'pegawai';
  $routePrefix = in_array($role, ['owner', 'admin', 'pegawai'], true) ? $role : 'pegawai';

  $statusOptions = ['menunggu', 'check_in', 'check_out', 'batal', 'selesai'];

  $statusLabel = fn($s) => match($s) {
    'menunggu' => 'Menunggu',
    'check_in' => 'Check-in',
    'check_out' => 'Check-out',
    'batal' => 'Batal',
    'selesai' => 'Selesai',
    default => $s,
  };

  $badgeClass = fn($s) => match($s) {
    'menunggu' => 'bg-[#FFB22C]/15 text-[#9A5B00] border-[#FFB22C]/40',
    'check_in' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'check_out' => 'bg-sky-50 text-sky-700 border-sky-200',
    'batal' => 'bg-rose-50 text-rose-700 border-rose-200',
    'selesai' => 'bg-slate-100 text-slate-700 border-slate-200',
    default => 'bg-slate-50 text-slate-700 border-slate-200',
  };

  $today = now()->toDateString();
  $range7 = now()->addDays(6)->toDateString();
  $range30 = now()->addDays(29)->toDateString();

  $quickBase = array_filter([
    'q' => $q,
    'status' => $status,
  ]);

  $quickToday = array_merge($quickBase, ['from' => $today, 'to' => $today]);
  $quickWeek = array_merge($quickBase, ['from' => $today, 'to' => $range7]);
  $quickMonth = array_merge($quickBase, ['from' => $today, 'to' => $range30]);

  $isTodayRange = $from === $today && $to === $today;
  $isWeekRange = $from === $today && $to === $range7;
  $isMonthRange = $from === $today && $to === $range30;
  $fmtDash = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') : '-';
@endphp

<div class="space-y-6" x-data="{ detailOpen: false, detail: {} }">

  <x-page-header
    title="Booking Kamar"
    subtitle="Semua data booking dalam 30 hari terakhir (bisa diubah lewat filter tanggal)."
  >
    <x-slot:rightSlot>
      <a
        wire:navigate
        href="{{ route($routePrefix.'.booking.create') }}"
        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#FFB22C] px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm hover:opacity-95 md:w-auto"
      >
        <i data-lucide="plus" class="h-4 w-4"></i>
        Tambah Booking
      </a>
    </x-slot:rightSlot>
  </x-page-header>

  @if (session('error'))
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
      {{ session('error') }}
    </div>
  @endif

  {{-- FLASH SUCCESS --}}
  @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- FILTER BAR --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="p-4 sm:p-5">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative min-w-0 w-full lg:max-w-xl">
          <span wire:ignore class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            <i data-lucide="search" class="h-4 w-4"></i>
          </span>
          <input
            type="text"
            placeholder="Cari nama tamu / no kamar..."
            class="h-11 min-w-0 max-w-full w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm leading-normal
                   focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
            wire:model.live.debounce.300ms="q"
          >
        </div>

        <div class="flex w-full gap-2 lg:w-auto">
          <button
            type="button"
            wire:click="resetFilters"
            class="w-full lg:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300
                   bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
          >
            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
            Reset
          </button>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Status --}}
        <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <label class="block text-xs font-semibold text-slate-600">Status</label>
          <div class="relative mt-2">
            <select
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="status"
            >
              <option value="">Semua</option>
              @foreach($statusOptions as $s)
                <option value="{{ $s }}">{{ $statusLabel($s) }}</option>
              @endforeach
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
        </div>

        {{-- Dari --}}
        <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <label class="block text-xs font-semibold text-slate-600">Dari tanggal</label>
          <div class="relative mt-2">
            <input
              type="date"
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal [color-scheme:light]
                     [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:h-full
                     [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-0
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="from"
            >
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="calendar-days" class="h-4 w-4"></i>
            </span>
          </div>
        </div>

        {{-- Sampai --}}
        <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:col-span-2 lg:col-span-1">
          <label class="block text-xs font-semibold text-slate-600">Sampai tanggal</label>
          <div class="relative mt-2">
            <input
              type="date"
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal [color-scheme:light]
                     [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:h-full
                     [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-0
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="to"
            >
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="calendar-days" class="h-4 w-4"></i>
            </span>
          </div>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
        <span class="font-semibold text-slate-500">Aksi cepat:</span>

        <a
          wire:navigate
          href="{{ route($routePrefix.'.booking.index', $quickToday) }}"
          class="rounded-full border bg-white px-3 py-1.5 font-semibold transition
                 {{ $isTodayRange ? 'border-[#854836] text-[#854836]' : 'border-slate-200 text-slate-700 hover:border-[#854836]/40 hover:text-[#854836]' }}"
        >
          Hari ini
        </a>

        <a
          wire:navigate
          href="{{ route($routePrefix.'.booking.index', $quickWeek) }}"
          class="rounded-full border bg-white px-3 py-1.5 font-semibold transition
                 {{ $isWeekRange ? 'border-[#854836] text-[#854836]' : 'border-slate-200 text-slate-700 hover:border-[#854836]/40 hover:text-[#854836]' }}"
        >
          7 hari
        </a>

        <a
          wire:navigate
          href="{{ route($routePrefix.'.booking.index', $quickMonth) }}"
          class="rounded-full border bg-white px-3 py-1.5 font-semibold transition
                 {{ $isMonthRange ? 'border-[#854836] text-[#854836]' : 'border-slate-200 text-slate-700 hover:border-[#854836]/40 hover:text-[#854836]' }}"
        >
          30 hari
        </a>
      </div>
    </div>
  </div>

  {{-- LIST --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
      <div class="text-sm font-semibold text-slate-800">
        Daftar Booking ({{ $booking->total() }})
      </div>
    </div>

    {{-- MOBILE --}}
    <div class="md:hidden divide-y divide-slate-200">
      @forelse($booking as $b)
        @php
          $detailPayload = [
            'id' => $b->id,
            'nama_tamu' => $b->nama_tamu,
            'no_telp_tamu' => $b->no_telp_tamu ?: '-',
            'status' => $statusLabel($b->status_booking),
            'status_raw' => $b->status_booking,
            'nomor_kamar' => $b->nomor_kamar,
            'tipe_kamar' => $b->tipe_kamar,
            'check_in' => $fmtDash($b->tanggal_check_in),
            'check_out' => $fmtDash($b->tanggal_check_out),
            'pegawai' => trim(($b->nama_pegawai ?? '') . ' (' . ($b->username_pegawai ?? '-') . ')'),
            'harga_kamar' => 'Rp ' . number_format((int) ($b->harga_kamar ?? 0), 0, ',', '.'),
            'tarif' => 'Rp ' . number_format((int) ($b->tarif ?? 0), 0, ',', '.'),
            'nights' => (int) ($b->nights ?? 0),
            'room_subtotal' => 'Rp ' . number_format((int) ($b->room_subtotal ?? 0), 0, ',', '.'),
            'total_bayar' => 'Rp ' . number_format((int) ($b->total_bayar ?? 0), 0, ',', '.'),
            'layanan' => $b->layanan_nama ?: 'Tidak ada',
            'layanan_qty' => $b->layanan_qty ?: '-',
            'layanan_subtotal' => 'Rp ' . number_format((int) ($b->layanan_subtotal ?? 0), 0, ',', '.'),
            'denda' => $b->denda ? 'Rp ' . number_format((int) $b->denda, 0, ',', '.') : '-',
            'catatan' => $b->catatan ?: '-',
            'alasan_denda' => $b->alasan_denda ?: '-',
          ];
          $canCheckin = $b->status_booking === 'menunggu';
          $canCheckout = $b->status_booking === 'check_in' && empty($b->checkout_at);
          $canFinish = $b->status_booking === 'check_out';
          $isLocked = in_array($b->status_booking, ['check_out', 'selesai', 'batal'], true);
        @endphp

        <div class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="text-sm font-semibold text-slate-900">{{ $b->nama_tamu }}</div>
              <div class="mt-0.5 text-xs text-slate-500">
                Kamar {{ $b->nomor_kamar }} • {{ $b->tipe_kamar }}
              </div>
            </div>

            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClass($b->status_booking) }}">
              {{ $statusLabel($b->status_booking) }}
            </span>
          </div>

          <div class="mt-3 space-y-1 text-sm text-slate-700">
            <div>
              <span class="text-slate-500">Tanggal:</span>
              <span class="font-medium">{{ $fmtDash($b->tanggal_check_in) }}</span>
              <span class="text-slate-400">s/d</span>
              <span class="font-medium">{{ $fmtDash($b->tanggal_check_out) }}</span>
            </div>
            <div>
              <span class="text-slate-500">Total:</span>
              <span class="font-semibold text-slate-900">
                Rp {{ number_format((int)($b->total_bayar ?? 0), 0, ',', '.') }}
              </span>
            </div>
          </div>

          <div class="mt-4 flex justify-end gap-2">
            <button
              type="button"
              class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
              @click="detail = @js($detailPayload); detailOpen = true"
            >
              Detail
            </button>
            @if(!$isLocked)
              <a
                wire:navigate
                href="{{ route($routePrefix.'.booking.edit', $b->id) }}"
                class="rounded-xl bg-[#854836] px-3 py-2 text-xs font-semibold text-white hover:opacity-95"
              >
                Edit
              </a>
            @endif
            @if($canCheckin)
              <form method="POST" action="{{ route($routePrefix.'.booking.checkin', $b->id) }}">
                @csrf
                <button
                  type="submit"
                  class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 active:bg-emerald-200"
                >
                  Check-in
                </button>
              </form>
            @endif
            @if($canCheckout)
              <button
                type="button"
                class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-100 active:bg-sky-200"
                onclick="window.openCheckoutModal(this)"
                data-action="{{ route($routePrefix.'.booking.checkout', $b->id) }}"
                data-nama="@js($b->nama_tamu)"
                data-kamar="@js($b->nomor_kamar)"
                data-checkin="@js($fmtDash($b->tanggal_check_in))"
                data-checkout="@js($fmtDash($b->tanggal_check_out))"
                data-nights="{{ (int) ($b->nights ?? 0) }}"
                data-tarif="{{ (int) ($b->harga_kamar ?? $b->tarif ?? 0) }}"
                data-total-kamar="{{ (int) ($b->room_subtotal ?? 0) }}"
                data-total-layanan="{{ (int) ($b->layanan_subtotal ?? 0) }}"
              >
                Check-out
              </button>
            @endif
            @if($canFinish)
              <form method="POST" action="{{ route($routePrefix.'.booking.selesai', $b->id) }}">
                @csrf
                <button
                  type="submit"
                  class="rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 active:bg-slate-300"
                >
                  Selesai
                </button>
              </form>
            @endif
          </div>
        </div>
      @empty
        <div class="p-6 text-center text-slate-500">Tidak ada booking.</div>
      @endforelse
    </div>

    {{-- DESKTOP --}}
    <div class="hidden overflow-x-auto md:block">
      <table class="min-w-full text-sm">
        <thead class="bg-white border-b border-slate-200">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-medium">Tanggal</th>
            <th class="px-4 py-3 font-medium">Tamu</th>
            <th class="px-4 py-3 font-medium">Kamar</th>
            <th class="px-4 py-3 font-medium text-right">Total</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium text-right">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-200">
          @forelse($booking as $b)
            @php
          $detailPayload = [
            'id' => $b->id,
            'nama_tamu' => $b->nama_tamu,
            'no_telp_tamu' => $b->no_telp_tamu ?: '-',
            'status' => $statusLabel($b->status_booking),
            'status_raw' => $b->status_booking,
            'nomor_kamar' => $b->nomor_kamar,
            'tipe_kamar' => $b->tipe_kamar,
                'check_in' => $fmtDash($b->tanggal_check_in),
                'check_out' => $fmtDash($b->tanggal_check_out),
                'pegawai' => trim(($b->nama_pegawai ?? '') . ' (' . ($b->username_pegawai ?? '-') . ')'),
                'harga_kamar' => 'Rp ' . number_format((int) ($b->harga_kamar ?? 0), 0, ',', '.'),
                'tarif' => 'Rp ' . number_format((int) ($b->tarif ?? 0), 0, ',', '.'),
                'nights' => (int) ($b->nights ?? 0),
                'room_subtotal' => 'Rp ' . number_format((int) ($b->room_subtotal ?? 0), 0, ',', '.'),
                'total_bayar' => 'Rp ' . number_format((int) ($b->total_bayar ?? 0), 0, ',', '.'),
                'layanan' => $b->layanan_nama ?: 'Tidak ada',
                'layanan_qty' => $b->layanan_qty ?: '-',
                'layanan_subtotal' => 'Rp ' . number_format((int) ($b->layanan_subtotal ?? 0), 0, ',', '.'),
                'denda' => $b->denda ? 'Rp ' . number_format((int) $b->denda, 0, ',', '.') : '-',
                'catatan' => $b->catatan ?: '-',
                'alasan_denda' => $b->alasan_denda ?: '-',
              ];
              $canCheckin = $b->status_booking === 'menunggu';
              $canCheckout = $b->status_booking === 'check_in' && empty($b->checkout_at);
              $canFinish = $b->status_booking === 'check_out';
              $isLocked = in_array($b->status_booking, ['check_out', 'selesai', 'batal'], true);
            @endphp

            <tr class="hover:bg-[#854836]/[0.04]">
              <td class="px-4 py-3 text-slate-700">
                <div class="font-semibold">{{ $fmtDash($b->tanggal_check_in) }}</div>
                <div class="text-xs text-slate-500">s/d {{ $fmtDash($b->tanggal_check_out) }}</div>
              </td>

              <td class="px-4 py-3">
                <div class="font-semibold text-slate-900">{{ $b->nama_tamu }}</div>
                <div class="text-xs text-slate-500">{{ $b->tipe_kamar }}</div>
              </td>

              <td class="px-4 py-3 text-slate-700">
                <div class="font-semibold">{{ $b->nomor_kamar }}</div>
              </td>

              <td class="px-4 py-3 text-right font-semibold text-slate-900">
                Rp {{ number_format((int)($b->total_bayar ?? 0), 0, ',', '.') }}
              </td>

              <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClass($b->status_booking) }}">
                  {{ $statusLabel($b->status_booking) }}
                </span>
              </td>

              <td class="px-4 py-3">
                <div class="flex flex-wrap justify-end gap-2">
                  <button
                    type="button"
                    class="shrink-0 whitespace-nowrap rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                    @click="detail = @js($detailPayload); detailOpen = true"
                  >
                    Detail
                  </button>
                  @if(!$isLocked)
                    <a
                      wire:navigate
                      href="{{ route($routePrefix.'.booking.edit', $b->id) }}"
                      class="inline-flex shrink-0 items-center whitespace-nowrap rounded-xl bg-[#854836] px-3 py-2 text-xs font-semibold text-white hover:opacity-95"
                    >
                      Edit
                    </a>
                  @endif
                  @if($canCheckin)
                    <form method="POST" action="{{ route($routePrefix.'.booking.checkin', $b->id) }}" class="shrink-0">
                      @csrf
                      <button
                        type="submit"
                        class="whitespace-nowrap rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 active:bg-emerald-200"
                      >
                        Check-in
                      </button>
                    </form>
                  @endif
                  @if($canCheckout)
                    <button
                      type="button"
                      class="shrink-0 whitespace-nowrap rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-100 active:bg-sky-200"
                      onclick="window.openCheckoutModal(this)"
                      data-action="{{ route($routePrefix.'.booking.checkout', $b->id) }}"
                      data-nama="@js($b->nama_tamu)"
                      data-kamar="@js($b->nomor_kamar)"
                      data-checkin="@js($fmtDash($b->tanggal_check_in))"
                      data-checkout="@js($fmtDash($b->tanggal_check_out))"
                      data-nights="{{ (int) ($b->nights ?? 0) }}"
                      data-tarif="{{ (int) ($b->harga_kamar ?? $b->tarif ?? 0) }}"
                      data-total-kamar="{{ (int) ($b->room_subtotal ?? 0) }}"
                      data-total-layanan="{{ (int) ($b->layanan_subtotal ?? 0) }}"
                    >
                      Check-out
                    </button>
                  @endif
                  @if($canFinish)
                    <form method="POST" action="{{ route($routePrefix.'.booking.selesai', $b->id) }}" class="shrink-0">
                      @csrf
                      <button
                        type="submit"
                        class="whitespace-nowrap rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 active:bg-slate-300"
                      >
                        Selesai
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-10 text-center text-slate-500">Tidak ada booking.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  <div class="border-t border-slate-200 bg-white px-4 py-3">
      {{ $booking->links(data: ['scrollTo' => false]) }}
    </div>
  </div>

  {{-- CHECKOUT MODAL --}}
  <div id="checkoutModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4 w-screen h-screen">
    <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl max-h-[90vh]">
      <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4">
        <div>
          <div class="text-sm font-semibold text-slate-900">Checkout Booking</div>
          <div class="mt-0.5 text-xs text-slate-500">Pastikan data checkout sudah benar.</div>
        </div>
        <button type="button" class="rounded-lg p-2 hover:bg-slate-100" onclick="window.closeCheckoutModal()">✕</button>
      </div>

      <form id="checkoutForm" method="POST" class="p-5 space-y-4 overflow-y-auto max-h-[calc(90vh-72px)]">
        @csrf

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
          <div class="text-sm font-semibold text-slate-900" id="checkoutNama">-</div>
          <div class="text-xs text-slate-600">
            Kamar <span id="checkoutKamar">-</span>
          </div>
          <div class="mt-2 text-xs text-slate-600">
            Check-in: <span class="font-semibold text-slate-800" id="checkoutCheckIn">-</span>
            <span class="mx-1 text-slate-400">•</span>
            Check-out: <span class="font-semibold text-slate-800" id="checkoutCheckOut">-</span>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
            <div class="text-xs font-semibold text-slate-500">Jumlah Malam</div>
            <div class="mt-0.5 text-sm font-semibold text-slate-900" id="checkoutNights">-</div>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
            <div class="text-xs font-semibold text-slate-500">Tarif/Malam</div>
            <div class="mt-0.5 text-sm font-semibold text-slate-900" id="checkoutTarif">-</div>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
            <div class="text-xs font-semibold text-slate-500">Total Kamar</div>
            <div class="mt-0.5 text-sm font-semibold text-slate-900" id="checkoutTotalKamar">-</div>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
            <div class="text-xs font-semibold text-slate-500">Total Layanan</div>
            <div class="mt-0.5 text-sm font-semibold text-slate-900" id="checkoutTotalLayanan">-</div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div>
            <label class="block text-xs font-semibold text-slate-600">Denda (opsional)</label>
            <input
              id="checkoutDenda"
              name="denda"
              type="number"
              min="0"
              step="1"
              value="0"
              class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
            >
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            <div class="text-xs font-semibold text-slate-600">Total Akhir</div>
            <div class="mt-1 text-base font-semibold text-slate-900" id="checkoutTotalFinal">-</div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600">Alasan Denda (opsional)</label>
          <textarea
            id="checkoutAlasanDenda"
            name="alasan_denda"
            rows="3"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
            placeholder="Contoh: kerusakan linen, late checkout, dll."
          ></textarea>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end sm:gap-3 border-t border-slate-200 pt-4">
          <button
            type="button"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            onclick="window.closeCheckoutModal()"
          >
            Batal
          </button>
          <button
            type="submit"
            class="rounded-xl bg-[#854836] px-4 py-2 text-sm font-semibold text-white hover:bg-[#6f3b2d] active:bg-[#5f3226]"
          >
            Konfirmasi Checkout
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- DETAIL MODAL --}}
  <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/40" @click="detailOpen = false"></div>

    <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl max-h-[90vh] overflow-y-auto">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-sm font-semibold text-slate-900">Detail Booking</div>
          <div class="mt-1 text-xs text-slate-500" x-text="detail.id ? `ID #${detail.id}` : ''"></div>
        </div>
        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" @click="detailOpen = false">x</button>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tamu</div>
          <div class="mt-1 font-semibold text-slate-900" x-text="detail.nama_tamu"></div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">No. Telepon</div>
          <div class="mt-1 font-semibold text-slate-900" x-text="detail.no_telp_tamu"></div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</div>
          <div class="mt-1 font-semibold text-slate-900" x-text="detail.status"></div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kamar</div>
          <div class="mt-1 font-semibold text-slate-900" x-text="`${detail.nomor_kamar} (${detail.tipe_kamar})`"></div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pegawai</div>
          <div class="mt-1 font-semibold text-slate-900" x-text="detail.pegawai"></div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Check-in</div>
          <div class="mt-1 font-semibold text-slate-900" x-text="detail.check_in"></div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Check-out</div>
          <div class="mt-1 font-semibold text-slate-900" x-text="detail.check_out"></div>
        </div>
      </div>

      <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Tarif Kamar</span>
          <span class="font-semibold text-slate-900" x-text="detail.tarif"></span>
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span class="text-slate-500">Harga Dibayar</span>
          <span class="font-semibold text-slate-900" x-text="detail.harga_kamar"></span>
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span class="text-slate-500">Durasi</span>
          <span class="font-semibold text-slate-900" x-text="`${detail.nights || 0} malam`"></span>
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span class="text-slate-500">Subtotal Kamar</span>
          <span class="font-semibold text-slate-900" x-text="detail.room_subtotal"></span>
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span class="text-slate-500">Layanan</span>
          <span class="font-semibold text-slate-900" x-text="detail.layanan"></span>
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span class="text-slate-500">Qty Layanan</span>
          <span class="font-semibold text-slate-900" x-text="detail.layanan_qty"></span>
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span class="text-slate-500">Subtotal Layanan</span>
          <span class="font-semibold text-slate-900" x-text="detail.layanan_subtotal"></span>
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span class="text-slate-500">Total Bayar</span>
          <span class="font-semibold text-slate-900" x-text="detail.total_bayar"></span>
        </div>
        <div x-show="detail.status_raw === 'selesai'">
          <div class="mt-2 flex items-center justify-between">
            <span class="text-slate-500">Denda</span>
            <span class="font-semibold text-slate-900" x-text="detail.denda"></span>
          </div>
          <div class="mt-2 flex items-start justify-between gap-3">
            <span class="text-slate-500">Alasan Denda</span>
            <span class="font-semibold text-slate-900 text-right" x-text="detail.alasan_denda"></span>
          </div>
        </div>
      </div>

      <div class="mt-4 text-sm">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Catatan</div>
        <p class="mt-1 text-slate-600" x-text="detail.catatan"></p>
      </div>
    </div>
  </div>

</div>

<script>
  window.openCheckoutModal = function (button) {
    const modal = document.getElementById('checkoutModal');
    if (!modal) return;

    const getNumber = (value) => {
      const parsed = parseInt(value || '0', 10);
      return Number.isNaN(parsed) ? 0 : parsed;
    };

    const formatCurrency = (value) => new Intl.NumberFormat('id-ID').format(value);

    const nama = button.getAttribute('data-nama') || '-';
    const kamar = button.getAttribute('data-kamar') || '-';
    const checkIn = button.getAttribute('data-checkin') || '-';
    const checkOut = button.getAttribute('data-checkout') || '-';
    const nights = getNumber(button.getAttribute('data-nights'));
    const tarif = getNumber(button.getAttribute('data-tarif'));
    const totalKamar = getNumber(button.getAttribute('data-total-kamar'));
    const totalLayanan = getNumber(button.getAttribute('data-total-layanan'));
    const action = button.getAttribute('data-action') || '';

    document.getElementById('checkoutNama').textContent = nama;
    document.getElementById('checkoutKamar').textContent = kamar;
    document.getElementById('checkoutCheckIn').textContent = checkIn;
    document.getElementById('checkoutCheckOut').textContent = checkOut;
    document.getElementById('checkoutNights').textContent = `${nights} malam`;
    document.getElementById('checkoutTarif').textContent = `Rp ${formatCurrency(tarif)}`;
    document.getElementById('checkoutTotalKamar').textContent = `Rp ${formatCurrency(totalKamar)}`;
    document.getElementById('checkoutTotalLayanan').textContent = `Rp ${formatCurrency(totalLayanan)}`;

    const dendaInput = document.getElementById('checkoutDenda');
    dendaInput.value = '0';
    const alasanInput = document.getElementById('checkoutAlasanDenda');
    if (alasanInput) {
      alasanInput.value = '';
    }

    const updateTotalFinal = () => {
      const denda = getNumber(dendaInput.value);
      const totalFinal = totalKamar + totalLayanan + denda;
      document.getElementById('checkoutTotalFinal').textContent = `Rp ${formatCurrency(totalFinal)}`;
    };

    dendaInput.oninput = updateTotalFinal;
    updateTotalFinal();

    const form = document.getElementById('checkoutForm');
    if (action) {
      form.setAttribute('action', action);
    } else {
      form.removeAttribute('action');
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
  };

  window.closeCheckoutModal = function () {
    const modal = document.getElementById('checkoutModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  };

  document.addEventListener('click', (event) => {
    const modal = document.getElementById('checkoutModal');
    if (!modal || modal.classList.contains('hidden')) return;
    if (event.target === modal) window.closeCheckoutModal();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') window.closeCheckoutModal();
  });

  document.addEventListener('livewire:navigated', () => {
    window.closeCheckoutModal();
  });
</script>
