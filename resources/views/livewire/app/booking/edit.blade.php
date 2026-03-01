@php
  $role = auth()->user()->level ?? 'pegawai';
  $routePrefix = in_array($role, ['owner', 'admin', 'pegawai'], true) ? $role : 'pegawai';
  $oldCheckoutDate = \Illuminate\Support\Carbon::parse($booking->tanggal_check_out)->toDateString();
  $selectedKamarId = (int) old('kamar_id', $booking->kamar_id);
  $selectedKamar = collect($kamar)->firstWhere('id', $selectedKamarId);
  $originalKamar = collect($kamar)->firstWhere('id', (int) $booking->kamar_id);
  $selectedTipeKamar = (string) ($selectedKamar->tipe_kamar ?? '');
  $isRoomLocked = $booking->status_booking === 'check_in';
  $tipeKamarOptions = collect($kamar)
    ->pluck('tipe_kamar')
    ->filter(fn ($tipe) => filled($tipe))
    ->unique()
    ->sort()
    ->values();
@endphp

<div
  class="w-full space-y-6 pb-10"
  data-booking-edit-root
  data-old-checkout="{{ $oldCheckoutDate }}"
  data-harga-per-malam="{{ (int) ($booking->harga_kamar ?? 0) }}"
  data-current-total="{{ (int) ($currentTotal ?? 0) }}"
  data-current-nights="{{ (int) ($currentNights ?? 0) }}"
  data-original-room-id="{{ (int) $booking->kamar_id }}"
  data-original-room-number="{{ (string) ($originalKamar->nomor_kamar ?? '-') }}"
  data-original-layanan-id="{{ (string) ($selectedLayananId ?? '') }}"
  data-original-layanan-qty="{{ (int) ($selectedQty ?? 0) }}"
  data-rooms-url="{{ route($routePrefix.'.booking.kamar_tersedia') }}"
  data-exclude-booking-id="{{ (int) $booking->id }}"
  data-room-locked="{{ $isRoomLocked ? '1' : '0' }}"
>
  <x-page-header
    title="Ubah Booking"
    subtitle="Perbarui detail booking dan status tamu saat ini."
  >
    <x-slot:rightSlot>
      <a
        wire:navigate
        href="{{ route($routePrefix.'.booking.index') }}"
        class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition sm:w-auto"
      >
        ← Kembali
      </a>
    </x-slot:rightSlot>
  </x-page-header>

  {{-- FLASH SUCCESS --}}
  @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- ERRORS --}}
  @if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
      <div class="text-sm font-semibold">Terjadi kesalahan:</div>
      <ul class="mt-2 list-disc pl-5 text-sm">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM EDIT --}}
  <div
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
      <div class="text-sm font-semibold text-slate-900">Detail Booking</div>
      <div class="mt-0.5 text-xs text-slate-500">Ubah kamar, tanggal, status, layanan, dan catatan.</div>
    </div>

    <form
      method="POST"
      action="{{ route($routePrefix.'.booking.update', $booking->id) }}"
      class="space-y-8 p-5 sm:p-6"
      data-booking-edit-form
    >
      @csrf
      @method('PUT')

      {{-- GRID: TIPE KAMAR + NOMOR + DATA TAMU --}}
      <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Tipe Kamar</label>
          <div class="relative mt-2">
            <select
              id="tipeKamarSelect"
              data-selected-type="{{ $selectedTipeKamar }}"
              @disabled($isRoomLocked)
              class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836] disabled:cursor-not-allowed disabled:bg-slate-100
                     transition"
            >
              @if($isRoomLocked)
                <option value="{{ $selectedTipeKamar }}">{{ $selectedTipeKamar }}</option>
              @else
                @foreach($tipeKamarOptions as $tipeKamar)
                  <option value="{{ $tipeKamar }}" {{ $selectedTipeKamar === $tipeKamar ? 'selected' : '' }}>
                    {{ $tipeKamar }}
                  </option>
                @endforeach
              @endif
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Nomor Kamar</label>
          <div class="relative mt-2">
            <select
              id="kamarSelect"
              name="kamar_id"
              data-selected-room="{{ $selectedKamarId }}"
              @disabled($isRoomLocked)
              class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836] disabled:cursor-not-allowed disabled:bg-slate-100
                     transition"
            >
              @if($isRoomLocked && $selectedKamar)
                <option
                  value="{{ $selectedKamar->id }}"
                  data-tarif="{{ (int) ($selectedKamar->tarif ?? $booking->harga_kamar ?? 0) }}"
                >
                  {{ $selectedKamar->nomor_kamar }}
                </option>
              @else
                @foreach($kamar as $k)
                  <option
                    value="{{ $k->id }}"
                    data-tarif="{{ (int) ($k->tarif ?? 0) }}"
                    {{ $selectedKamarId === (int) $k->id ? 'selected' : '' }}
                  >
                    {{ $k->nomor_kamar }}
                  </option>
                @endforeach
              @endif
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
          @if($isRoomLocked)
            <input type="hidden" name="kamar_id" value="{{ $selectedKamarId }}">
          @endif
          @error('kamar_id') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          <div id="kamarAvailabilityHint" class="mt-1 text-xs text-slate-500">
            @if($isRoomLocked)
              Kamar tidak dapat diubah ketika status booking sudah check-in.
            @endif
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Nama Tamu</label>
          <input
            type="text"
            name="nama_tamu"
            value="{{ old('nama_tamu', $booking->nama_tamu) }}"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836]
                   transition"
            placeholder="Contoh: I Made Arya"
          >
          @error('nama_tamu') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">No. Telepon</label>
          <input
            type="text"
            name="no_telp_tamu"
            inputmode="tel"
            value="{{ old('no_telp_tamu', $booking->no_telp_tamu) }}"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836]
                   transition"
            placeholder="Contoh: 0812xxxxxxx"
          >
          @error('no_telp_tamu') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- TANGGAL CHECK-IN & CHECK-OUT --}}
      <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-start gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#854836] text-white shadow-sm">
            <i data-lucide="calendar" class="h-5 w-5"></i>
          </span>
          <div class="min-w-0">
            <div class="text-sm font-semibold text-slate-900">Tanggal Menginap</div>
            <div class="mt-0.5 text-xs text-slate-500">Perpanjangan tanggal menginap pada check-out</div>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="min-w-0">
            <label class="block text-sm font-semibold text-slate-700">Check-in</label>
            <div class="relative mt-2 min-w-0">
              <input
                id="checkInDate"
                name="tanggal_check_in"
                type="text"
                value="{{ old('tanggal_check_in', $booking->tanggal_check_in) }}"
                class="min-w-0 max-w-full w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836]
                       transition"
                data-url-template="{{ route($routePrefix.'.booking.tanggal_terpakai', ['kamarId' => '__KAMAR__', 'exclude_booking_id' => $booking->id]) }}"
              >
              <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                <i data-lucide="calendar" class="h-4 w-4"></i>
              </span>
            </div>
            @error('tanggal_check_in') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          </div>

          <div class="min-w-0">
            <label class="block text-sm font-semibold text-slate-700">Check-out</label>
            <div class="relative mt-2 min-w-0">
              <input
                id="checkOutDate"
                name="tanggal_check_out"
                type="text"
                value="{{ old('tanggal_check_out', $booking->tanggal_check_out) }}"
                class="min-w-0 max-w-full w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836]
                       transition"
                data-url-template="{{ route($routePrefix.'.booking.tanggal_terpakai', ['kamarId' => '__KAMAR__', 'exclude_booking_id' => $booking->id]) }}"
              >
              <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                <i data-lucide="calendar" class="h-4 w-4"></i>
              </span>
            </div>
            @error('tanggal_check_out') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      {{-- STATUS + CATATAN --}}
      <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Status Booking</label>
          <div class="relative mt-2">
            <select
              name="status_booking"
              class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836]
                     transition"
            >
              @foreach($statusList as $s)
                <option value="{{ $s }}" {{ old('status_booking', $booking->status_booking) == $s ? 'selected' : '' }}>
                  {{ $s }}
                </option>
              @endforeach
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
          @error('status_booking') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Sumber Booking</label>
          @php
            $sourceType = old('source_type', $booking->source_type ?? 'walk_in');
            $sourceDetail = old('source_detail', $booking->source_detail ?? '');
          @endphp
          <div class="relative mt-2">
            <select
              id="sourceTypeSelect"
              name="source_type"
              class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836]
                     transition"
            >
              <option value="walk_in" {{ $sourceType === 'walk_in' ? 'selected' : '' }}>Walk-in</option>
              <option value="telepon_wa" {{ $sourceType === 'telepon_wa' ? 'selected' : '' }}>Telepon/WA</option>
              <option value="ota" {{ $sourceType === 'ota' ? 'selected' : '' }}>OTA</option>
              <option value="lainnya" {{ $sourceType === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
          @error('source_type') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror

          <div id="sourceDetailWrap" class="mt-3 {{ in_array($sourceType, ['ota', 'lainnya'], true) ? '' : 'hidden' }}">
            <label class="block text-sm font-semibold text-slate-700">Detail Sumber</label>
            <input
              id="sourceDetailInput"
              type="text"
              name="source_detail"
              value="{{ $sourceDetail }}"
              class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836]
                     transition"
              placeholder="Contoh: Agoda / Traveloka / Referral"
            >
            @error('source_detail') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Catatan (opsional)</label>
          <textarea
            name="catatan"
            rows="4"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#854836]/25 focus:border-[#854836]
                   transition"
            placeholder="Misal: kamar dekat resepsionis, minta extra bed, dll."
          >{{ old('catatan', $booking->catatan) }}</textarea>
          @error('catatan') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- LAYANAN TAMBAHAN --}}
      @php
        $selectedLayananId = old('layanan_id', $selectedLayananId ?? '');
        $selectedQty = (int) old('layanan_qty', $selectedQty ?? 0);
      @endphp

      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
          <div class="text-sm font-semibold text-slate-900">Layanan Tambahan</div>
          <div class="mt-0.5 text-xs text-slate-500">Pilih extra bed (normal/high season) dan atur qty.</div>
        </div>

        <div class="p-5 space-y-4">
          {{-- GRID CARDS --}}
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            {{-- NONE --}}
            <label
              class="layanan-card flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4
                     hover:bg-[#854836]/[0.04] hover:border-[#854836]/40 transition"
              data-layanan-card="none"
            >
              <input
                type="radio"
                class="sr-only"
                name="layanan_id"
                value=""
                {{ empty($selectedLayananId) ? 'checked' : '' }}
              >

              <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                <i data-lucide="ban" class="h-5 w-5"></i>
              </span>

              <div class="min-w-0">
                <div class="font-semibold text-slate-900">Tidak ada layanan</div>
                <div class="text-sm text-slate-600">Tanpa extra bed</div>
              </div>
            </label>

            {{-- ITEMS --}}
            @foreach($layananList as $l)
              @php $isSelected = ((string)$selectedLayananId === (string)$l->id); @endphp

              <label
                class="layanan-card flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4
                       hover:bg-[#854836]/[0.04] hover:border-[#854836]/40 transition"
                data-layanan-card="{{ $l->id }}"
              >
                <input
                  type="radio"
                  class="sr-only"
                  name="layanan_id"
                  value="{{ $l->id }}"
                  data-layanan-name="{{ $l->nama }}"
                  data-layanan-price="{{ (int) $l->harga }}"
                  {{ $isSelected ? 'checked' : '' }}
                >

                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFB22C]/20 text-[#854836]">
                  <i data-lucide="bed" class="h-5 w-5"></i>
                </span>

                <div class="min-w-0">
                  <div class="flex items-center gap-2">
                    <div class="font-semibold text-slate-900">{{ $l->nama }}</div>
                    @if(str_contains(strtolower($l->nama), 'high'))
                      <span class="inline-flex items-center rounded-full border border-[#FFB22C]/40 bg-[#FFB22C]/15 px-2 py-0.5 text-[11px] font-semibold text-[#9A5B00]">
                        High
                      </span>
                    @endif
                  </div>
                  <div class="mt-1 text-sm text-slate-600">
                    Rp {{ number_format($l->harga, 0, ',', '.') }}
                  </div>
                  <div class="mt-2 text-xs text-slate-500">
                    {{ str_contains(strtolower($l->nama), 'high') ? 'Dipakai saat high season' : 'Dipakai saat normal season.' }}
                  </div>
                </div>
              </label>
            @endforeach
          </div>

          {{-- QTY --}}
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <div class="text-sm font-semibold text-slate-900">Jumlah Extra Bed</div>
                <div class="text-xs text-slate-600">0 jika tidak memilih layanan. Maksimal 5.</div>
              </div>

              <div class="inline-flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-600">Qty</span>

                <div class="inline-flex items-center overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
                  <button
                    type="button"
                    class="h-9 w-9 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                    data-qty-minus
                  >
                    −
                  </button>
                  

                  <input
                    id="layananQty"
                    name="layanan_qty"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    value="{{ $selectedQty }}"
                    class="h-9 w-14 border-x border-slate-300 bg-white text-center text-sm font-semibold text-slate-800 outline-none"
                  >

                  <button
                    type="button"
                    class="h-9 w-9 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                    data-qty-plus
                  >
                    +
                  </button>
                </div>
              </div>
            </div>

            <div id="qtyHint" class="mt-2 text-xs text-slate-600"></div>

            @error('layanan_id') <div class="mt-2 text-sm text-rose-600">{{ $message }}</div> @enderror
            @error('layanan_qty') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      {{-- ACTIONS --}}
      <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end sm:gap-3 border-t border-slate-200 pt-5">
        <a
          wire:navigate
          href="{{ route($routePrefix.'.booking.index') }}"
          class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700
                 hover:bg-slate-50 transition"
        >
          Batal
        </a>

        <button
          type="submit"
          class="inline-flex items-center justify-center rounded-xl bg-[#854836] px-4 py-2.5 text-sm font-semibold text-white
                 shadow hover:bg-[#6f3b2b] active:scale-[0.98] transition"
        >
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

  {{-- MODAL KONFIRMASI PERPANJANG (via edit checkout) --}}
  <div id="extendModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-[1px]" data-extend-close></div>

    <div class="relative mx-4 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-sm font-semibold text-slate-900">Konfirmasi Perpanjangan</div>
          <div class="mt-1 text-xs text-slate-500">Periksa biaya tambahan sebelum simpan.</div>
        </div>
        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" data-extend-close>×</button>
      </div>

      <div class="mt-4 space-y-3 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Check-out Lama</span>
          <span class="font-semibold text-slate-900" data-extend-old>-</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Check-out Baru</span>
          <span class="font-semibold text-slate-900" data-extend-new>-</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Tambahan Malam</span>
          <span class="font-semibold text-slate-900" data-extend-nights>0 malam</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Harga per Malam</span>
          <span class="font-semibold text-slate-900" data-extend-rate>-</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Biaya Tambahan</span>
          <span class="font-semibold text-slate-900" data-extend-extra>-</span>
        </div>
        <div class="mt-1 rounded-xl border border-[#854836]/25 bg-[#854836]/5 px-3 py-2.5">
          <div class="flex items-center justify-between gap-3">
            <span class="text-xs font-semibold uppercase tracking-wide text-[#854836]">Total Baru</span>
            <span class="text-xl font-extrabold text-[#854836] sm:text-2xl" data-extend-total>-</span>
          </div>
        </div>
      </div>

      <div class="mt-5 flex items-center justify-end gap-2">
        <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-extend-close>
          Batal
        </button>
        <button type="button" class="rounded-xl bg-[#854836] px-4 py-2 text-sm font-semibold text-white hover:opacity-95" data-extend-confirm>
          Simpan
        </button>
      </div>
    </div>
  </div>

  {{-- MODAL KONFIRMASI TOTAL SAAT GANTI KAMAR + TAMBAH LAYANAN --}}
  <div id="roomServicePreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-[1px]" data-room-service-close></div>

    <div class="relative mx-4 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-sm font-semibold text-slate-900">Konfirmasi Total Booking Baru</div>
          <div class="mt-1 text-xs text-slate-500">Periksa perubahan biaya sebelum simpan.</div>
        </div>
        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" data-room-service-close>×</button>
      </div>

      <div class="mt-4 space-y-3 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Kamar Lama</span>
          <span class="font-semibold text-slate-900" data-preview-old-room>-</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Kamar Baru</span>
          <span class="font-semibold text-slate-900" data-preview-new-room>-</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Durasi Menginap</span>
          <span class="font-semibold text-slate-900" data-preview-nights>0 malam</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Subtotal Kamar Baru</span>
          <span class="font-semibold text-slate-900" data-preview-room-subtotal>-</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Layanan Tambahan</span>
          <span class="font-semibold text-slate-900" data-preview-service-subtotal>-</span>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 pt-3">
          <span class="text-slate-500">Total Saat Ini</span>
          <span class="font-semibold text-slate-900" data-preview-current-total>-</span>
        </div>
        <div class="mt-1 rounded-xl border border-[#854836]/25 bg-[#854836]/5 px-3 py-2.5">
          <div class="flex items-center justify-between gap-3">
            <span class="text-xs font-semibold uppercase tracking-wide text-[#854836]">Total Baru</span>
            <span class="text-xl font-extrabold text-[#854836] sm:text-2xl" data-preview-new-total>-</span>
          </div>
        </div>
      </div>

      <div class="mt-5 flex items-center justify-end gap-2">
        <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-room-service-close>
          Batal
        </button>
        <button type="button" class="rounded-xl bg-[#854836] px-4 py-2 text-sm font-semibold text-white hover:opacity-95" data-room-service-confirm>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
  (() => {
    if (window.__bookingEditScriptLoaded) {
      window.bootBookingEdit?.();
      return;
    }

    window.__bookingEditScriptLoaded = true;

    // ========= LAYANAN + QTY (PURE JS, CLEAN) =========
    (function () {
    const min = 0; // 0 jika tidak ada layanan
    const max = 5;

    const qty = document.getElementById('layananQty');
    const btnMinus = document.querySelector('[data-qty-minus]');
    const btnPlus  = document.querySelector('[data-qty-plus]');
    const hint = document.getElementById('qtyHint');

    function clamp(n) {
      n = parseInt(n || '0', 10);
      if (isNaN(n)) n = 0;
      return Math.min(max, Math.max(min, n));
    }

    function getSelectedLayananId() {
      const checked = document.querySelector('input[name="layanan_id"]:checked');
      return checked ? checked.value : '';
    }

    function paintCards() {
      document.querySelectorAll('.layanan-card').forEach(card => {
        const id = card.getAttribute('data-layanan-card');
        const selected = getSelectedLayananId();
        const isActive = (id === 'none' && selected === '') || (id !== 'none' && id === selected);

        card.classList.toggle('border-[#854836]', isActive);
        card.classList.toggle('bg-[#854836]/[0.04]', isActive);
        card.classList.toggle('ring-1', isActive);
        card.classList.toggle('ring-[#854836]/20', isActive);

        if (!isActive) {
          card.classList.add('border-slate-200');
        } else {
          card.classList.remove('border-slate-200');
        }
      });
    }

    function syncQtyState() {
      const layananId = getSelectedLayananId();

      // tidak pilih layanan => qty = 0 dan disable
      if (!layananId) {
        qty.value = '0';
        qty.disabled = true;
        btnMinus.disabled = true;
        btnPlus.disabled = true;
        qty.classList.add('bg-slate-100');
        hint && (hint.textContent = 'Tidak memilih layanan → Qty otomatis 0.');
        return;
      }

      // pilih layanan => qty minimal 1
      let v = clamp(qty.value);
      if (v === 0) v = 1;
      qty.value = String(v);

      qty.disabled = false;
      btnMinus.disabled = false;
      btnPlus.disabled = false;
      qty.classList.remove('bg-slate-100');
      hint && (hint.textContent = 'Gunakan tombol + / − untuk mengubah jumlah.');
    }

    // klik card => pilih radio
    document.addEventListener('click', (e) => {
      const card = e.target.closest('.layanan-card');
      if (!card) return;

      const radio = card.querySelector('input[type="radio"]');
      if (radio) {
        radio.checked = true;
        paintCards();
        syncQtyState();
      }
    });

    // tombol + -
    btnMinus?.addEventListener('click', () => {
      if (qty.disabled) return;
      qty.value = String(Math.max(1, clamp(qty.value) - 1)); // min 1 jika layanan dipilih
    });

    btnPlus?.addEventListener('click', () => {
      if (qty.disabled) return;
      qty.value = String(Math.min(max, clamp(qty.value) + 1));
    });

    // ketik manual
    qty?.addEventListener('input', () => {
      qty.value = qty.value.replace(/\D/g, '');
    });
    qty?.addEventListener('blur', () => {
      const layananId = getSelectedLayananId();
      let v = clamp(qty.value);
      if (layananId && v === 0) v = 1;
      qty.value = String(v);
    });

    const initLayananQty = () => {
      if (window.__bookingEditQtyInit) return;
      window.__bookingEditQtyInit = true;
      paintCards();
      syncQtyState();
    };

      document.addEventListener('DOMContentLoaded', initLayananQty);
      document.addEventListener('livewire:navigated', initLayananQty);
    })();
  })();
</script>

<script>
  // ========= FLATPICKR (EDIT) =========
  (() => {
    if (window.__bookingEditFlatpickrLoaded) {
      window.bootBookingEdit?.();
      return;
    }

    window.__bookingEditFlatpickrLoaded = true;

    window.ensureFlatpickrReady = window.ensureFlatpickrReady || ((callback) => {
      if (window.flatpickr) {
        callback();
        return;
      }

      const existing = document.querySelector('script[data-flatpickr]');
      if (existing) {
        existing.addEventListener('load', () => callback(), { once: true });
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
      script.async = true;
      script.setAttribute('data-flatpickr', 'true');
      script.addEventListener('load', () => callback());
      document.head.appendChild(script);
    });

    const renderLucideIcons = () => {
      if (window.lucide) {
        window.lucide.createIcons();
      }
    };

    const bindLucideHooks = () => {
      if (window.__bookingEditLucideHookBound) {
        return;
      }

      window.__bookingEditLucideHookBound = true;

      document.addEventListener('livewire:navigated', renderLucideIcons);
      document.addEventListener('livewire:initialized', () => {
        if (window.Livewire) {
          Livewire.hook('message.processed', renderLucideIcons);
        }
      });
    };

    const initBookingEdit = () => {
      const root = document.querySelector('[data-booking-edit-root]');
      const checkInInput = document.getElementById('checkInDate');
      const checkOutInput = document.getElementById('checkOutDate');
      const tipeKamarSelect = document.getElementById('tipeKamarSelect');
      const kamarSelect = document.getElementById('kamarSelect');
      const kamarAvailabilityHint = document.getElementById('kamarAvailabilityHint');
      const sourceTypeSelect = document.getElementById('sourceTypeSelect');
      const sourceDetailWrap = document.getElementById('sourceDetailWrap');
      const sourceDetailInput = document.getElementById('sourceDetailInput');
      const roomsUrl = root?.dataset.roomsUrl || '';
      const excludeBookingId = parseInt(root?.dataset.excludeBookingId || '0', 10);
      const isRoomLocked = root?.dataset.roomLocked === '1';

      if (!root || !checkInInput || !checkOutInput || !tipeKamarSelect || !kamarSelect) {
        return;
      }

      let checkInFp = null;
      let checkOutFp = null;
      let openLock = false;
      let initialized = false;
      let availableRooms = [];
      let pendingPreferredType = null;
      let pendingPreferredRoom = null;

      const formatDate = (date) => flatpickr.formatDate(date, 'Y-m-d');
      const parseDate = (value) => flatpickr.parseDate(value, 'Y-m-d');

      const computeMinDate = () => {
        const today = formatDate(new Date());
        const currentCheckIn = checkInInput.value;

        if (!currentCheckIn) {
          return 'today';
        }

        return currentCheckIn < today ? currentCheckIn : 'today';
      };

      const computeCheckOutMinDate = () => {
        if (!checkInInput.value) {
          return 'today';
        }

        const baseDate = parseDate(checkInInput.value) ?? new Date();
        const nextDate = new Date(baseDate);
        nextDate.setDate(nextDate.getDate() + 1);
        return formatDate(nextDate);
      };

      const buildUrl = (template) => {
        const kamarId = parseInt(kamarSelect.value || '0', 10);

        if (!template || !kamarId) {
          return null;
        }

        return template.replace('__KAMAR__', String(kamarId));
      };

      const fetchDisabledDates = async (template) => {
        const url = buildUrl(template);
        if (!url) {
          return [];
        }

        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();

        return Array.isArray(json.disabled ?? json.disabledDates) ? (json.disabled ?? json.disabledDates) : [];
      };

      const destroyFlatpickr = (inputEl) => {
        if (inputEl && inputEl._flatpickr) {
          inputEl._flatpickr.destroy();
        }
      };

      const normalizeDisabled = (disabledDates) =>
        Array.isArray(disabledDates) ? disabledDates : [];

      const setKamarHint = (message = '', isError = false) => {
        if (!kamarAvailabilityHint) {
          return;
        }

        kamarAvailabilityHint.textContent = message;
        kamarAvailabilityHint.classList.toggle('text-rose-600', isError);
        kamarAvailabilityHint.classList.toggle('text-slate-500', !isError);
      };

      const normalizedType = (value) => String(value || '').trim().toLowerCase();

      const collectTypeOptions = (rooms) => {
        const typeMap = new Map();

        rooms.forEach((room) => {
          const key = normalizedType(room.tipe_kamar);
          if (!key || typeMap.has(key)) {
            return;
          }

          typeMap.set(key, String(room.tipe_kamar));
        });

        return Array.from(typeMap.values()).sort((a, b) => a.localeCompare(b));
      };

      const applyTypeOptions = (rooms, preferredType = '') => {
        const types = collectTypeOptions(rooms);
        const currentType = preferredType || tipeKamarSelect.value || tipeKamarSelect.dataset.selectedType || '';

        tipeKamarSelect.innerHTML = '';

        if (types.length === 0) {
          const option = document.createElement('option');
          option.value = '';
          option.textContent = 'Tidak ada tipe kamar tersedia';
          tipeKamarSelect.appendChild(option);
          tipeKamarSelect.value = '';
          tipeKamarSelect.disabled = true;

          return '';
        }

        types.forEach((typeName) => {
          const option = document.createElement('option');
          option.value = typeName;
          option.textContent = typeName;
          tipeKamarSelect.appendChild(option);
        });

        tipeKamarSelect.disabled = false;

        const selectedType = types.find((typeName) => normalizedType(typeName) === normalizedType(currentType)) ?? types[0];
        tipeKamarSelect.value = selectedType;
        tipeKamarSelect.dataset.selectedType = selectedType;

        return selectedType;
      };

      const applyRoomOptionsByType = (rooms, typeName, preferredRoom = '') => {
        const matchingRooms = rooms.filter((room) => normalizedType(room.tipe_kamar) === normalizedType(typeName));

        kamarSelect.innerHTML = '';

        if (matchingRooms.length === 0) {
          const option = document.createElement('option');
          option.value = '';
          option.textContent = 'Tidak ada nomor kamar tersedia';
          kamarSelect.appendChild(option);
          kamarSelect.value = '';
          kamarSelect.disabled = true;
          kamarSelect.dataset.selectedRoom = '';

          return false;
        }

        matchingRooms.forEach((room) => {
          const option = document.createElement('option');
          option.value = String(room.id);
          option.textContent = String(room.nomor_kamar);
          option.dataset.tarif = String(parseInt(room.tarif || 0, 10) || 0);
          kamarSelect.appendChild(option);
        });

        kamarSelect.disabled = false;

        const selectedRoom = matchingRooms.find((room) => String(room.id) === String(preferredRoom))
          ? String(preferredRoom)
          : String(matchingRooms[0].id);

        kamarSelect.value = selectedRoom;
        kamarSelect.dataset.selectedRoom = selectedRoom;

        return selectedRoom === String(preferredRoom);
      };

      const refreshKamarTersedia = async () => {
        if (isRoomLocked) {
          return;
        }

        const checkInValue = checkInInput.value;
        const checkOutValue = checkOutInput.value;

        if (!roomsUrl || !checkInValue || !checkOutValue || checkOutValue <= checkInValue) {
          return;
        }

        const params = new URLSearchParams({
          tanggal_check_in: checkInValue,
          tanggal_check_out: checkOutValue,
        });

        if (excludeBookingId > 0) {
          params.set('exclude_booking_id', String(excludeBookingId));
        }

        const forcedType = pendingPreferredType;
        const forcedRoom = pendingPreferredRoom;
        pendingPreferredType = null;
        pendingPreferredRoom = null;

        const currentRoomValue = forcedRoom !== null
          ? String(forcedRoom)
          : (kamarSelect.value || kamarSelect.dataset.selectedRoom || '');
        const currentTypeValue = forcedType !== null
          ? String(forcedType)
          : (tipeKamarSelect.value || tipeKamarSelect.dataset.selectedType || '');

        try {
          const response = await fetch(`${roomsUrl}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
          });

          if (!response.ok) {
            throw new Error('Gagal memuat daftar kamar.');
          }

          const json = await response.json();
          const rooms = Array.isArray(json.rooms) ? json.rooms : [];
          availableRooms = rooms;

          if (rooms.length === 0) {
            applyTypeOptions([], '');
            applyRoomOptionsByType([], '', '');
            setKamarHint('Semua kamar sudah terpakai pada rentang tanggal ini.', true);
            return;
          }

          const selectedType = applyTypeOptions(rooms, currentTypeValue);
          const stillAvailable = applyRoomOptionsByType(rooms, selectedType, currentRoomValue);

          if (!stillAvailable && currentRoomValue !== '') {
            setKamarHint('Kamar sebelumnya tidak tersedia di rentang tanggal ini. Silakan pilih kamar lain.', true);
          } else {
            setKamarHint('');
          }

          initCheckInFp();
          initCheckOutFp();
        } catch (error) {
          console.error(error);
          setKamarHint('Daftar kamar tersedia gagal dimuat. Coba beberapa saat lagi.', true);
        }
      };

      const refreshKamarTersediaDebounced = (() => {
        let timerId = null;

        return () => {
          window.clearTimeout(timerId);
          timerId = window.setTimeout(() => {
            refreshKamarTersedia();
          }, 180);
        };
      })();

      const initCheckInFp = () => {
        destroyFlatpickr(checkInInput);

        checkInFp = flatpickr(checkInInput, {
          dateFormat: 'Y-m-d',
          minDate: computeMinDate(),
          disable: [],
          disableMobile: true,
          allowInput: false,
          clickOpens: true,
          defaultDate: checkInInput.value || null,
          onOpen: async (_selectedDates, _dateStr, instance) => {
            if (instance._updating) return;
            instance._updating = true;
            try {
              const disabledDates = await fetchDisabledDates(checkInInput.dataset.urlTemplate);
              instance.set('disable', normalizeDisabled(disabledDates));
              instance.set('minDate', computeMinDate());
              instance.redraw();
            } catch (e) {
              console.error(e);
            } finally {
              instance._updating = false;
            }
          },
          onChange: (dates) => {
            if (dates[0]) {
              checkInInput.value = formatDate(dates[0]);
            }
            if (checkOutFp) {
              checkOutFp.set('minDate', computeCheckOutMinDate());
            }
            refreshKamarTersediaDebounced();
          },
        });
      };

      const initCheckOutFp = () => {
        destroyFlatpickr(checkOutInput);

        checkOutFp = flatpickr(checkOutInput, {
          dateFormat: 'Y-m-d',
          minDate: computeCheckOutMinDate(),
          disable: [],
          disableMobile: true,
          allowInput: false,
          clickOpens: true,
          defaultDate: checkOutInput.value || null,
          onOpen: async (_selectedDates, _dateStr, instance) => {
            if (instance._updating) return;
            instance._updating = true;
            try {
              const disabledDates = await fetchDisabledDates(checkOutInput.dataset.urlTemplate);
              instance.set('disable', normalizeDisabled(disabledDates));
              instance.set('minDate', computeCheckOutMinDate());
              instance.redraw();
            } catch (e) {
              console.error(e);
            } finally {
              instance._updating = false;
            }
          },
          onChange: (dates) => {
            if (dates[0]) {
              checkOutInput.value = formatDate(dates[0]);
            }
            refreshKamarTersediaDebounced();
          },
        });
      };

      const refreshAndOpen = async (inputEl, fpInstance) => {
        if (!fpInstance || openLock || inputEl.dataset.loading === 'true') {
          return;
        }

        openLock = true;
        inputEl.dataset.loading = 'true';
        const oldPh = inputEl.placeholder;
        inputEl.placeholder = 'Memuat tanggal...';
        inputEl.classList.add('opacity-70');

        try {
          const disabledDates = await fetchDisabledDates(inputEl.dataset.urlTemplate);
          fpInstance.set('disable', normalizeDisabled(disabledDates));
          if (inputEl === checkOutInput) {
            fpInstance.set('minDate', computeCheckOutMinDate());
          } else {
            fpInstance.set('minDate', computeMinDate());
          }
          fpInstance.open();
        } catch (e) {
          console.error(e);
        } finally {
          inputEl.placeholder = oldPh;
          inputEl.classList.remove('opacity-70');
          inputEl.dataset.loading = 'false';
          setTimeout(() => {
            openLock = false;
          }, 200);
        }
      };

      const bindHandlers = () => {
        if (initialized) return;
        if (checkInInput.dataset.bound === 'true' && checkOutInput.dataset.bound === 'true') {
          return;
        }

        initialized = true;
        checkInInput.dataset.bound = 'true';
        checkOutInput.dataset.bound = 'true';

        checkInInput.addEventListener('focus', () => {
          if (checkInFp) {
            checkInFp.open();
          }
        });

        checkOutInput.addEventListener('focus', () => {
          if (checkOutFp) {
            checkOutFp.open();
          }
        });

        if (!isRoomLocked) {
          tipeKamarSelect.addEventListener('change', () => {
            pendingPreferredType = tipeKamarSelect.value || '';
            pendingPreferredRoom = '';
            kamarSelect.dataset.selectedRoom = '';
            kamarSelect.value = '';
            setKamarHint('');
            refreshKamarTersediaDebounced();
          });

          kamarSelect.addEventListener('change', () => {
            kamarSelect.dataset.selectedRoom = kamarSelect.value || '';
            initCheckInFp();
            initCheckOutFp();
          });
        }
        checkInInput.addEventListener('change', refreshKamarTersediaDebounced);
        checkOutInput.addEventListener('change', refreshKamarTersediaDebounced);

        if (sourceTypeSelect && sourceDetailWrap) {
          const toggleSourceDetail = () => {
            const value = sourceTypeSelect.value;
            const show = value === 'ota' || value === 'lainnya';
            sourceDetailWrap.classList.toggle('hidden', !show);
            if (!show && sourceDetailInput) {
              sourceDetailInput.value = '';
            }
          };

          sourceTypeSelect.addEventListener('change', toggleSourceDetail);
          toggleSourceDetail();
        }
      };

      initCheckInFp();
      initCheckOutFp();
      bindHandlers();
      if (!isRoomLocked) {
        refreshKamarTersedia();
      }
      renderLucideIcons();

      const form = root.querySelector('[data-booking-edit-form]');
      const extendModal = document.getElementById('extendModal');
      const extendCloseButtons = extendModal?.querySelectorAll('[data-extend-close]') || [];
      const extendConfirmButton = extendModal?.querySelector('[data-extend-confirm]');
      const roomServiceModal = document.getElementById('roomServicePreviewModal');
      const roomServiceCloseButtons = roomServiceModal?.querySelectorAll('[data-room-service-close]') || [];
      const roomServiceConfirmButton = roomServiceModal?.querySelector('[data-room-service-confirm]');
      const layananQtyInput = document.getElementById('layananQty');

      const formatCurrency = (value) =>
        new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0,
        }).format(Number(value || 0));

      const parseYmd = (value) => {
        if (!value) {
          return null;
        }

        const matched = String(value).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!matched) {
          return null;
        }

        const parsed = new Date(Date.UTC(
          Number(matched[1]),
          Number(matched[2]) - 1,
          Number(matched[3]),
        ));

        return Number.isNaN(parsed.getTime()) ? null : parsed;
      };

      const oldCheckout = root.dataset.oldCheckout;
      const originalRoomId = String(root.dataset.originalRoomId || '');
      const originalRoomNumber = String(root.dataset.originalRoomNumber || '-');
      const originalLayananId = String(root.dataset.originalLayananId || '');
      const originalLayananQty = parseInt(root.dataset.originalLayananQty || '0', 10) || 0;
      const hargaPerMalam = Number(root.dataset.hargaPerMalam || 0);
      const currentTotal = Number(root.dataset.currentTotal || 0);
      const currentNights = Number(root.dataset.currentNights || 0);

      const getNights = () => {
        const checkInDate = parseYmd(checkInInput.value);
        const checkOutDate = parseYmd(checkOutInput.value);
        if (!checkInDate || !checkOutDate) {
          return Math.max(1, currentNights);
        }

        const diffMs = checkOutDate.getTime() - checkInDate.getTime();
        return Math.max(1, Math.round(diffMs / 86400000));
      };

      const getSelectedRoomOption = () => kamarSelect?.selectedOptions?.[0] ?? null;

      const getSelectedRoomRate = () => {
        const selectedOption = getSelectedRoomOption();
        const rate = parseInt(selectedOption?.dataset?.tarif || '', 10);
        return Number.isNaN(rate) ? hargaPerMalam : rate;
      };

      const getSelectedRoomNumber = () => {
        const selectedOption = getSelectedRoomOption();
        return selectedOption?.textContent?.trim() || '-';
      };

      const getSelectedLayananInput = () => document.querySelector('input[name="layanan_id"]:checked');

      const getSelectedLayananId = () => String(getSelectedLayananInput()?.value || '');

      const getSelectedLayananName = () => {
        const selectedInput = getSelectedLayananInput();
        return String(selectedInput?.dataset?.layananName || 'Tanpa layanan');
      };

      const getSelectedLayananPrice = () => {
        const selectedInput = getSelectedLayananInput();
        const price = parseInt(selectedInput?.dataset?.layananPrice || '', 10);
        return Number.isNaN(price) ? 0 : price;
      };

      const getSelectedLayananQty = () => {
        const qty = parseInt(layananQtyInput?.value || '0', 10);
        return Number.isNaN(qty) ? 0 : qty;
      };

      const shouldOpenRoomServicePreview = () => {
        const selectedRoomId = String(kamarSelect.value || '');
        const roomChanged = selectedRoomId !== '' && selectedRoomId !== originalRoomId;
        const selectedLayananId = getSelectedLayananId();
        const selectedQty = getSelectedLayananQty();
        const layananChanged = selectedLayananId !== originalLayananId || selectedQty !== originalLayananQty;

        return roomChanged || layananChanged;
      };

      const openExtendModal = (newCheckout) => {
        if (!extendModal) return;
        const oldDate = parseYmd(oldCheckout);
        const newDate = parseYmd(newCheckout);
        if (!oldDate || !newDate) return;

        const diffMs = newDate.getTime() - oldDate.getTime();
        const nights = Math.max(0, Math.round(diffMs / 86400000));
        const extra = nights * hargaPerMalam;
        const total = currentTotal + extra;

        extendModal.querySelector('[data-extend-old]').textContent = oldCheckout || '-';
        extendModal.querySelector('[data-extend-new]').textContent = newCheckout || '-';
        extendModal.querySelector('[data-extend-nights]').textContent = `${nights} malam`;
        extendModal.querySelector('[data-extend-rate]').textContent = formatCurrency(hargaPerMalam);
        extendModal.querySelector('[data-extend-extra]').textContent = formatCurrency(extra);
        extendModal.querySelector('[data-extend-total]').textContent = formatCurrency(total);

        extendModal.classList.remove('hidden');
        extendModal.classList.add('flex');
      };

      const closeExtendModal = () => {
        if (!extendModal) return;
        extendModal.classList.add('hidden');
        extendModal.classList.remove('flex');
      };

      const openRoomServiceModal = () => {
        if (!roomServiceModal) {
          return false;
        }

        const nights = getNights();
        if (nights <= 0) {
          return false;
        }

        const roomRate = getSelectedRoomRate();
        const roomSubtotal = nights * roomRate;
        const layananName = getSelectedLayananName();
        const layananPrice = getSelectedLayananPrice();
        const layananQty = getSelectedLayananQty();
        const layananSubtotal = layananPrice * layananQty;
        const newTotal = roomSubtotal + layananSubtotal;
        const layananLabel = getSelectedLayananId() !== ''
          ? `${layananName} x${layananQty} (${formatCurrency(layananSubtotal)})`
          : `Tanpa layanan (${formatCurrency(0)})`;

        roomServiceModal.querySelector('[data-preview-old-room]').textContent = originalRoomNumber;
        roomServiceModal.querySelector('[data-preview-new-room]').textContent = getSelectedRoomNumber();
        roomServiceModal.querySelector('[data-preview-nights]').textContent = `${nights} malam`;
        roomServiceModal.querySelector('[data-preview-room-subtotal]').textContent = formatCurrency(roomSubtotal);
        roomServiceModal.querySelector('[data-preview-service-subtotal]').textContent = layananLabel;
        roomServiceModal.querySelector('[data-preview-current-total]').textContent = formatCurrency(currentTotal);
        roomServiceModal.querySelector('[data-preview-new-total]').textContent = formatCurrency(newTotal);

        roomServiceModal.classList.remove('hidden');
        roomServiceModal.classList.add('flex');

        return true;
      };

      const closeRoomServiceModal = () => {
        if (!roomServiceModal) {
          return;
        }

        roomServiceModal.classList.add('hidden');
        roomServiceModal.classList.remove('flex');
      };

      extendCloseButtons.forEach((btn) => btn.addEventListener('click', closeExtendModal));
      extendConfirmButton?.addEventListener('click', () => {
        closeExtendModal();
        form?.submit();
      });
      roomServiceCloseButtons.forEach((btn) => btn.addEventListener('click', closeRoomServiceModal));
      roomServiceConfirmButton?.addEventListener('click', () => {
        closeRoomServiceModal();
        form?.submit();
      });

      form?.addEventListener('submit', (event) => {
        if (shouldOpenRoomServicePreview()) {
          event.preventDefault();
          if (openRoomServiceModal()) {
            return;
          }
        }

        if (!oldCheckout || !checkOutInput.value) {
          return;
        }

        const oldDate = parseYmd(oldCheckout);
        const newDate = parseYmd(checkOutInput.value);

        if (!oldDate || !newDate) {
          return;
        }

        if (newDate.getTime() > oldDate.getTime()) {
          event.preventDefault();
          openExtendModal(checkOutInput.value);
        }
      });
    };

    window.bootBookingEdit = () => window.ensureFlatpickrReady(initBookingEdit);

    bindLucideHooks();
    document.addEventListener('DOMContentLoaded', window.bootBookingEdit);
    document.addEventListener('livewire:navigated', window.bootBookingEdit);
  })();
</script>
@endpush
