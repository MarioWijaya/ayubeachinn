@php
  $fmtDash = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') : '-';
@endphp

<div id="kamar-index" class="w-full space-y-6 pb-10">

  <x-page-header
    title="Data Kamar"
    subtitle="Kelola nomor, tipe, tarif, kapasitas, dan status kamar."
  >
    <x-slot:rightSlot>
      <a
        wire:navigate
        href="{{ route('admin.kamar.create') }}"
        class="inline-flex w-full items-center justify-center rounded-xl bg-[#FFB22C] px-4 py-2.5 text-sm font-semibold text-slate-900 shadow hover:opacity-95 active:scale-[0.98] transition sm:w-auto"
      >
        + Tambah Kamar
      </a>
    </x-slot:rightSlot>
  </x-page-header>

  {{-- FLASH --}}
  @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- FILTERS --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="p-4 sm:p-5 space-y-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative min-w-0 w-full lg:max-w-2xl">
          <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            <i data-lucide="search" class="h-4 w-4"></i>
          </span>
          <input
            type="text"
            placeholder="Cari nomor / tipe kamar..."
            class="h-11 min-w-0 max-w-full w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm leading-normal
                   focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
            wire:model.live.debounce.300ms="q"
          >
        </div>

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

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4">
          <div class="text-xs font-semibold text-slate-600">Status</div>
          <div class="relative mt-3">
            <select
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="status"
            >
              <option value="">Semua status</option>
              <option value="tersedia">Tersedia</option>
              <option value="terisi">Terisi</option>
              <option value="perbaikan">Perbaikan</option>
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
        </div>

        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4">
          <div class="text-xs font-semibold text-slate-600">Dari tanggal</div>
          <div class="relative mt-3">
            <input
              type="date"
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal [color-scheme:light]
                     [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:h-full
                     [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-0
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="checkFrom"
            >
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="calendar-days" class="h-4 w-4"></i>
            </span>
          </div>
        </div>

        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 sm:col-span-2 lg:col-span-1">
          <div class="text-xs font-semibold text-slate-600">Sampai tanggal</div>
          <div class="relative mt-3">
            <input
              type="date"
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal [color-scheme:light]
                     [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:h-full
                     [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-0
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="checkTo"
            >
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="calendar-days" class="h-4 w-4"></i>
            </span>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
        <span>Aksi cepat:</span>
        <button
          type="button"
          wire:click="setQuickRange('today')"
          class="rounded-full border px-3 py-1.5 text-xs font-semibold transition
                 {{ $quickRange === 'today'
                   ? 'border-[#854836] text-[#854836] bg-white'
                   : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
        >
          Hari ini
        </button>
        <button
          type="button"
          wire:click="setQuickRange('week')"
          class="rounded-full border px-3 py-1.5 text-xs font-semibold transition
                 {{ $quickRange === 'week'
                   ? 'border-[#854836] text-[#854836] bg-white'
                   : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
        >
          7 hari
        </button>
        <button
          type="button"
          wire:click="setQuickRange('month')"
          class="rounded-full border px-3 py-1.5 text-xs font-semibold transition
                 {{ $quickRange === 'month'
                   ? 'border-[#854836] text-[#854836] bg-white'
                   : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
        >
          30 hari
        </button>
      </div>
    </div>
  </div>

  {{-- ===================== MOBILE: CARD LIST ===================== --}}
  <div class="space-y-3 md:hidden">
    @forelse($kamar as $k)
      @php
        $hasPerbaikanToday = (int) ($k->has_perbaikan_today ?? 0) === 1;
        $hasBookingToday = !empty($k->active_booking_id);
        $displayStatus = $hasPerbaikanToday
          ? 'perbaikan'
          : ($hasBookingToday ? 'terisi' : 'tersedia');
        $statusBadge = match($displayStatus) {
          'tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
          'terisi' => 'bg-slate-100 text-slate-700 border-slate-200',
          default => 'bg-amber-50 text-amber-800 border-amber-200',
        };
      @endphp

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-base font-semibold text-slate-900">
              Kamar {{ $k->nomor_kamar }}
            </div>
            <div class="mt-0.5 text-sm text-slate-600 truncate">
              {{ $k->tipe_kamar }}
            </div>
          </div>

          <span class="inline-flex shrink-0 items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">
            {{ ucfirst($displayStatus) }}
          </span>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
            <div class="text-xs font-semibold text-slate-500">Tarif</div>
            <div class="mt-1 font-semibold text-slate-900">
              Rp {{ number_format((int) $k->tarif, 0, ',', '.') }}
            </div>
          </div>

          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
            <div class="text-xs font-semibold text-slate-500">Kapasitas</div>
            <div class="mt-1 font-semibold text-slate-900">{{ $k->kapasitas }} orang</div>
          </div>
        </div>

        <div class="mt-4 flex gap-2">
          <button
            type="button"
            class="w-full inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            data-kamar-detail
            data-nomor="{{ $k->nomor_kamar }}"
            data-booking-active-id="{{ $k->active_booking_id ?? '-' }}"
            data-booking-active-nama="{{ $k->active_booking_nama ?? '-' }}"
            data-booking-active-checkin="{{ $fmtDash($k->active_booking_check_in) }}"
            data-booking-active-checkout="{{ $fmtDash($k->active_booking_check_out) }}"
            data-booking-next-id="{{ $k->next_booking_id ?? '-' }}"
            data-booking-next-nama="{{ $k->next_booking_nama ?? '-' }}"
            data-booking-next-checkin="{{ $fmtDash($k->next_booking_check_in) }}"
            data-booking-next-checkout="{{ $fmtDash($k->next_booking_check_out) }}"
            data-perbaikan-mulai="{{ $fmtDash($k->perbaikan_mulai) }}"
            data-perbaikan-selesai="{{ $fmtDash($k->perbaikan_selesai) }}"
            data-perbaikan-catatan="{{ $k->perbaikan_catatan ?? '-' }}"
          >
            Detail
          </button>
          <a wire:navigate
             href="{{ route('admin.kamar.edit', $k->id) }}"
             class="w-full inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Edit
          </a>
        </div>
      </div>
    @empty
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500">
        Belum ada data kamar.
      </div>
    @endforelse

    {{-- pagination mobile --}}
    @if(method_exists($kamar, 'links'))
      <div class="rounded-2xl border border-slate-200 bg-white p-4" wire:key="kamar-pagination-mobile" data-kamar-pagination>
        {{ $kamar->onEachSide(1)->links(data: ['scrollTo' => false]) }}
      </div>
    @endif
  </div>

  {{-- ===================== DESKTOP: TABLE ===================== --}}
  <div class="hidden md:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
      <div class="text-sm font-semibold text-slate-900">Daftar Kamar</div>
      <div class="mt-0.5 text-xs text-slate-500">Klik Edit untuk mengubah data.</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white">
          <tr class="text-left text-xs font-semibold text-slate-600">
            <th class="px-5 py-3">Nomor</th>
            <th class="px-5 py-3">Tipe</th>
            <th class="px-5 py-3">Tarif</th>
            <th class="px-5 py-3">Kapasitas</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-200">
          @forelse($kamar as $i => $k)
            @php
              $hasPerbaikanToday = (int) ($k->has_perbaikan_today ?? 0) === 1;
              $hasBookingToday = !empty($k->active_booking_id);
              $displayStatus = $hasPerbaikanToday
                ? 'perbaikan'
                : ($hasBookingToday ? 'terisi' : 'tersedia');
              $statusBadge = match($displayStatus) {
                'tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'terisi' => 'bg-slate-100 text-slate-700 border-slate-200',
                default => 'bg-amber-50 text-amber-800 border-amber-200',
              };
            @endphp

            <tr class="hover:bg-slate-50/60">
              <td class="px-5 py-3">
                <div class="font-semibold text-slate-900">{{ $k->nomor_kamar }}</div>
              </td>
              <td class="px-5 py-3 text-slate-700">{{ $k->tipe_kamar }}</td>
              <td class="px-5 py-3 text-slate-700">
                Rp {{ number_format((int) $k->tarif, 0, ',', '.') }}
              </td>
              <td class="px-5 py-3 text-slate-700">{{ $k->kapasitas }}</td>
              <td class="px-5 py-3">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">
                  {{ ucfirst($displayStatus) }}
                </span>
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2">
                  <button
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    data-kamar-detail
                    data-nomor="{{ $k->nomor_kamar }}"
                    data-booking-active-id="{{ $k->active_booking_id ?? '-' }}"
                    data-booking-active-nama="{{ $k->active_booking_nama ?? '-' }}"
                    data-booking-active-checkin="{{ $fmtDash($k->active_booking_check_in) }}"
                    data-booking-active-checkout="{{ $fmtDash($k->active_booking_check_out) }}"
                    data-booking-next-id="{{ $k->next_booking_id ?? '-' }}"
                    data-booking-next-nama="{{ $k->next_booking_nama ?? '-' }}"
                    data-booking-next-checkin="{{ $fmtDash($k->next_booking_check_in) }}"
                    data-booking-next-checkout="{{ $fmtDash($k->next_booking_check_out) }}"
                    data-perbaikan-mulai="{{ $fmtDash($k->perbaikan_mulai) }}"
                    data-perbaikan-selesai="{{ $fmtDash($k->perbaikan_selesai) }}"
                    data-perbaikan-catatan="{{ $k->perbaikan_catatan ?? '-' }}"
                  >
                    Detail
                  </button>
                  <a wire:navigate
                     href="{{ route('admin.kamar.edit', $k->id) }}"
                     class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Edit
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                Belum ada data kamar.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- pagination desktop --}}
    @if(method_exists($kamar, 'links'))
      <div class="border-t border-slate-200 bg-white px-5 py-4" wire:key="kamar-pagination-desktop" data-kamar-pagination>
        {{ $kamar->onEachSide(1)->links(data: ['scrollTo' => false]) }}
      </div>
    @endif
  </div>
  <div id="perbaikanDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
      <div class="flex items-start justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
        <div>
          <div class="text-sm font-semibold text-slate-900">Detail Kamar</div>
          <div class="mt-0.5 text-xs text-slate-500" id="perbaikanDetailSubtitle">Kamar -</div>
        </div>
        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" data-perbaikan-close>✕</button>
      </div>
      <div class="p-5 space-y-3 text-sm">
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
          <div class="text-xs font-semibold text-slate-500">Booking</div>
          <div class="mt-1 text-slate-700">
            <span class="font-semibold" id="bookingPrimaryNama">-</span>
            <span class="text-slate-500" id="bookingPrimaryRange"></span>
          </div>
          <div class="mt-1 text-xs font-semibold text-slate-500" id="bookingPrimaryLabel">Hari ini</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
          <div class="text-xs font-semibold text-slate-500">Perbaikan</div>
          <div class="mt-1 font-semibold text-slate-900" id="perbaikanDetailRange">-</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
          <div class="text-xs font-semibold text-slate-500">Catatan Perbaikan</div>
          <div class="mt-1 text-slate-700 whitespace-pre-line" id="perbaikanDetailCatatan">-</div>
        </div>
        <div class="flex justify-end">
          <button
            type="button"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            data-perbaikan-close
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  (() => {
    if (window.__kamarPaginationScrollLockInit) {
      return;
    }

    window.__kamarPaginationScrollLockInit = true;

    const getWrapper = () => document.getElementById('kamar-index');
    let lastScrollY = 0;
    let restorePending = false;

    const saveScroll = () => {
      lastScrollY = window.scrollY || window.pageYOffset || 0;
      restorePending = true;
    };

    const restoreScroll = () => {
      if (!restorePending) {
        return;
      }

      const wrapper = getWrapper();
      if (!wrapper) {
        return;
      }

      restorePending = false;
      const targetY = lastScrollY;

      requestAnimationFrame(() => {
        window.scrollTo(0, targetY);
        setTimeout(() => window.scrollTo(0, targetY), 0);
      });
    };

    document.addEventListener('click', (event) => {
      const wrapper = getWrapper();
      if (!wrapper || !wrapper.contains(event.target)) {
        return;
      }

      const pagination = event.target.closest('[data-kamar-pagination]');
      if (!pagination) {
        return;
      }

      const link = event.target.closest('a,button');
      if (!link) {
        return;
      }

      saveScroll();
    }, true);

    document.addEventListener('livewire:initialized', restoreScroll);
    document.addEventListener('livewire:navigated', restoreScroll);
  })();

  (() => {
    const modal = document.getElementById('perbaikanDetailModal');
    const subtitle = document.getElementById('perbaikanDetailSubtitle');
    const catatanEl = document.getElementById('perbaikanDetailCatatan');
    const perbaikanDetailRange = document.getElementById('perbaikanDetailRange');

    if (!modal || !subtitle || !catatanEl || !perbaikanDetailRange) {
      return;
    }

    const openModal = (payload) => {
      subtitle.textContent = `Kamar ${payload.nomor || '-'}`;
      catatanEl.textContent = payload.catatan || '-';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    };

    const closeModal = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };

    const bookingPrimaryNama = document.getElementById('bookingPrimaryNama');
    const bookingPrimaryRange = document.getElementById('bookingPrimaryRange');
    const bookingPrimaryLabel = document.getElementById('bookingPrimaryLabel');

    document.addEventListener('click', (event) => {
      const trigger = event.target.closest('[data-kamar-detail]');
      if (trigger) {
        openModal({
          nomor: trigger.getAttribute('data-nomor'),
          mulai: trigger.getAttribute('data-perbaikan-mulai'),
          selesai: trigger.getAttribute('data-perbaikan-selesai'),
          catatan: trigger.getAttribute('data-perbaikan-catatan'),
        });

        if (bookingPrimaryNama && bookingPrimaryRange && bookingPrimaryLabel) {
          const activeId = trigger.getAttribute('data-booking-active-id') || '-';
          const activeNama = trigger.getAttribute('data-booking-active-nama') || '-';
          const activeCheckIn = trigger.getAttribute('data-booking-active-checkin') || '-';
          const activeCheckOut = trigger.getAttribute('data-booking-active-checkout') || '-';

          const nextId = trigger.getAttribute('data-booking-next-id') || '-';
          const nextNama = trigger.getAttribute('data-booking-next-nama') || '-';
          const nextCheckIn = trigger.getAttribute('data-booking-next-checkin') || '-';
          const nextCheckOut = trigger.getAttribute('data-booking-next-checkout') || '-';

          if (activeId !== '-') {
            bookingPrimaryNama.textContent = activeNama;
            bookingPrimaryRange.textContent = ` (${activeCheckIn} s/d ${activeCheckOut})`;
            bookingPrimaryLabel.textContent = 'Hari ini';
          } else if (nextId !== '-') {
            bookingPrimaryNama.textContent = nextNama;
            bookingPrimaryRange.textContent = ` (${nextCheckIn} s/d ${nextCheckOut})`;
            bookingPrimaryLabel.textContent = 'Terdekat';
          } else {
            bookingPrimaryNama.textContent = '-';
            bookingPrimaryRange.textContent = '';
            bookingPrimaryLabel.textContent = 'Hari ini';
          }
        }

        const mulai = trigger.getAttribute('data-perbaikan-mulai') || '-';
        const selesai = trigger.getAttribute('data-perbaikan-selesai') || '-';
        perbaikanDetailRange.textContent = mulai === '-' && selesai === '-'
          ? '-'
          : `${mulai} s/d ${selesai}`;
        return;
      }

      if (event.target.closest('[data-perbaikan-close]') || event.target === modal) {
        closeModal();
      }
    });
  })();
</script>
@endpush
