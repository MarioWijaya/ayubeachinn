@php
  $role = auth()->user()->level ?? 'pegawai';
  $routePrefix = in_array($role, ['owner', 'admin', 'pegawai'], true) ? $role : 'pegawai';
@endphp

<div class="mx-auto w-full max-w-6xl space-y-6 pb-10">

  <x-page-header
    title="Tambah Booking"
    subtitle="Isi data tamu, pilih kamar, lalu tentukan rentang tanggal menginap."
  />

  {{-- FLASH SUCCESS --}}
  @if (session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- ERRORS --}}
  @if ($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
      <div class="text-sm font-semibold">Terjadi kesalahan:</div>
      <ul class="mt-2 list-disc pl-5 text-sm">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    {{-- ✅ submit ke openConfirm saja (bukan save) --}}
    <form wire:submit.prevent="openConfirm" class="space-y-8 p-6 sm:p-8">

      {{-- DATA TAMU --}}
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-base font-semibold text-slate-900">Data Tamu</h2>
            <p class="mt-1 text-sm text-slate-600">Informasi dasar tamu dan kamar yang dipilih.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Nama Tamu</label>
          <input
            type="text"
            wire:model.defer="nama_tamu"
            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/40"
            placeholder="Contoh: I Made Arya"
          >
          @error('nama_tamu') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">No. Telepon</label>
          <input
            type="text"
            inputmode="tel"
            wire:model.defer="no_telp_tamu"
            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/40"
            placeholder="Contoh: 0812xxxxxxx"
          >
          @error('no_telp_tamu') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
        </div>

        {{-- TANGGAL --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Tanggal Menginap</label>

          <div wire:ignore class="relative mt-2">
            <input
              id="tanggalRange"
              type="text"
              placeholder="Klik untuk memilih rentang tanggal"
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/40"
              readonly
            >
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="calendar" class="h-4 w-4"></i>
            </span>
          </div>

          <div id="pesanTanggal" class="mt-2 text-sm text-rose-600 hidden"></div>

          <input id="tanggalRangeValue" type="hidden" wire:model.live="tanggal_range">
          @error('tanggal_range') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Tipe Kamar</label>
          <div class="relative mt-2" wire:ignore>
            <select
              id="selectTipeKamar"
              data-availability-url="{{ route($routePrefix.'.booking.kamar_availability') }}"
              data-selected-type="{{ $tipe_kamar }}"
              disabled
              class="w-full appearance-none rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/40 disabled:cursor-not-allowed disabled:bg-slate-100"
            >
              <option value="">Pilih tanggal menginap terlebih dahulu</option>
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
          <input id="selectedTipeKamarValue" type="hidden" wire:model.live="tipe_kamar">
          @error('tipe_kamar') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Nomor Kamar</label>
          <div class="relative mt-2" wire:ignore>
            <select
              id="selectKamar"
              data-selected-room="{{ $kamar_id }}"
              @disabled($tipe_kamar === '')
              class="w-full appearance-none rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/40 disabled:cursor-not-allowed disabled:bg-slate-100"
            >
              <option value="">Pilih tipe kamar terlebih dahulu</option>
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>

          <input id="selectedKamarValue" type="hidden" wire:model.live="kamar_id">
          @error('kamar_id') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          <p id="roomAvailabilityMessage" class="mt-2 text-xs text-rose-600 hidden">Tidak ada kamar tersedia pada tanggal tersebut.</p>
          <p class="mt-2 text-xs text-slate-500">Tip: nomor kamar menyesuaikan tanggal menginap dan tipe kamar.</p>
        </div>
        </div>
      </div>

      {{-- SUMBER + HARGA --}}
      <div class="space-y-4">
        <div>
          <h2 class="text-base font-semibold text-slate-900">Sumber & Harga</h2>
          <p class="mt-1 text-sm text-slate-600">Sesuaikan harga berdasarkan sumber booking.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        {{-- SUMBER BOOKING --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Sumber Booking</label>
          <div class="relative mt-2">
            <select
              wire:model.live="source_type"
              wire:change="applyWalkInTariff"
              required
              class="w-full appearance-none rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/40"
            >
              <option value="">-- Pilih Sumber --</option>
              <option value="walk_in">Walk-in</option>
              <option value="telepon_wa">Telepon/WA</option>
              <option value="ota">OTA</option>
              <option value="lainnya">Lainnya</option>
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
          @error('source_type') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          <p class="mt-2 text-xs text-slate-500">Walk-in, Telepon/WA, OTA, atau Lainnya.</p>

          @if(in_array($source_type, ['ota', 'lainnya'], true))
            <div class="mt-3">
              <label class="block text-sm font-medium text-slate-700">Detail Sumber</label>
              <input
                type="text"
                wire:model.live="source_detail"
                required
                class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/40"
                placeholder="Contoh: Agoda / Traveloka / Referral"
              >
              @error('source_detail') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
            </div>
          @endif
        </div>

        {{-- HARGA KAMAR (dibayar tamu) --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Harga Kamar (dibayar tamu)</label>
          <input
            type="number"
            min="0"
            wire:model.defer="harga_kamar"
            {{ $this->isWalkIn() ? 'readonly' : '' }}
            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                  focus:outline-none focus:ring-2 focus:ring-[#854836]/30 read-only:bg-slate-100"
            placeholder="Contoh: 350000"
          >
          <p class="mt-2 text-xs text-slate-500">
            Walk-in otomatis ambil dari tarif kamar. OTA bisa beda, isi manual.
          </p>
          @error('hargaKamar') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          @error('harga_kamar') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
        </div>
      </div>

      {{-- LAYANAN TAMBAHAN --}}
      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFB22C]/20 text-[#854836]">
              <i data-lucide="bed" class="h-5 w-5"></i>
            </span>
            <div>
              <h2 class="text-lg font-semibold text-slate-900">Layanan Tambahan</h2>
              <p class="mt-1 text-sm text-slate-600">
                Pilih extra bed (Normal/High Season). High season dipilih manual oleh resepsionis.
              </p>
            </div>
          </div>

          {{-- QTY --}}
          <div class="w-full sm:min-w-[240px] sm:w-auto">
            <label class="block text-sm font-semibold text-slate-700">Qty Extra Bed</label>

            <div class="mt-2 inline-flex w-full items-center overflow-hidden rounded-xl border border-slate-300 bg-white sm:w-auto">
              <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                wire:click="decQty"
                @disabled($layanan_id === null || $layanan_qty <= 1)
              >
                <i data-lucide="minus" class="h-4 w-4"></i>
              </button>

              <input
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                wire:model.live="layanan_qty"
                @disabled($layanan_id === null)
                class="h-10 w-16 border-x border-slate-300 bg-white px-2 text-center text-sm
                       focus:outline-none disabled:bg-slate-100"
              >

              <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                wire:click="incQty"
                @disabled($layanan_id === null || $layanan_qty >= 5)
              >
                <i data-lucide="plus" class="h-4 w-4"></i>
              </button>
            </div>

            @if($layanan_id === null)
              <p class="mt-2 text-xs text-slate-500">Pilih layanan terlebih dahulu.</p>
            @else
              <p class="mt-2 text-xs text-slate-500">Maksimal 5 unit.</p>
            @endif

            @error('layanan_qty')
              <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- PILIHAN LAYANAN --}}
        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Layanan tambahan">

          {{-- Tidak ada layanan --}}
          <label
            class="cursor-pointer flex w-full items-center gap-3 rounded-xl border p-4 text-left transition sm:col-span-2
              {{ $layanan_id === null
                  ? 'border-[#854836] bg-[#FFB22C]/10 ring-2 ring-[#FFB22C]/20'
                  : 'border-slate-200 bg-white hover:bg-[#854836]/10 hover:border-[#854836]/40' }}"
          >
            <input type="radio" class="sr-only" wire:model.live="layanan_id" value="">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
              <i data-lucide="ban" class="h-4 w-4"></i>
            </span>

            <div>
              <div class="font-semibold {{ $layanan_id === null ? 'text-[#854836]' : 'text-slate-900' }}">
                Tidak ada layanan
              </div>
              <div class="text-sm text-slate-600">Tanpa extra bed.</div>
            </div>

            @if($layanan_id === null)
              <span class="ml-auto rounded-full bg-[#854836] px-3 py-1 text-xs font-semibold text-white">
                Dipilih
              </span>
            @endif
          </label>

          {{-- Opsi layanan --}}
          @foreach($layananList as $l)
            @php $isSelected = ((string)$layanan_id === (string)$l->id); @endphp

            <label
              class="cursor-pointer flex w-full items-start justify-between gap-4 rounded-xl border p-4 text-left transition
                {{ $isSelected
                    ? 'border-[#854836] bg-[#FFB22C]/10 ring-2 ring-[#FFB22C]/20'
                    : 'border-slate-200 bg-white hover:bg-[#854836]/10 hover:border-[#854836]/40' }}"
            >
              <input type="radio" class="sr-only" wire:model.live="layanan_id" value="{{ $l->id }}">

              <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#FFB22C]/20 text-[#854836]">
                  <i data-lucide="bed" class="h-4 w-4"></i>
                </span>

                <div>
                  <div class="font-semibold {{ $isSelected ? 'text-[#854836]' : 'text-slate-900' }}">
                    {{ $l->nama }}
                  </div>
                  <div class="text-sm text-slate-600">Rp {{ number_format($l->harga, 0, ',', '.') }}</div>

                  @if(($l->kode ?? '') === 'extra_bed_high')
                    <div class="mt-2 text-xs text-slate-500">Pakai saat high season (pilih manual).</div>
                  @else
                    <div class="mt-2 text-xs text-slate-500">Pakai saat normal season.</div>
                  @endif
                </div>
              </div>

              @if($isSelected)
                <span class="rounded-full bg-[#854836] px-3 py-1 text-xs font-semibold text-white">
                  Dipilih
                </span>
              @endif
            </label>
          @endforeach

          @error('layanan_id')
            <div class="sm:col-span-2 mt-1 text-sm text-rose-600">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- CATATAN --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
        <textarea
          wire:model.defer="catatan"
          rows="3"
          class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                 focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/40"
          placeholder="Misal: minta extra bed, kamar dekat resepsionis, dll."
        ></textarea>
        @error('catatan') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
      </div>

      {{-- ACTIONS --}}
      <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
        <a
          href="{{ route($routePrefix.'.booking.index') }}"
          class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
          Kembali
        </a>

        {{-- ✅ submit -> openConfirm --}}
        <button
          type="submit"
          class="rounded-lg bg-[#854836] px-4 py-2 text-sm font-semibold text-white hover:opacity-95"
          wire:loading.attr="disabled"
        >
          <span wire:loading.remove>Review & Simpan</span>
          <span wire:loading>Memeriksa...</span>
        </button>
      </div>

      {{-- MODAL KONFIRMASI --}}
      <div
        class="fixed inset-0 z-50 {{ $confirmOpen ? 'flex' : 'hidden' }} items-center justify-center bg-black/40 p-4"
        wire:click.self="closeConfirm"
      >
        <div class="w-full max-w-6xl rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden">
          <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
            <div>
              <div class="text-sm font-semibold text-slate-900">Konfirmasi Booking</div>
              <div class="text-xs text-slate-500">Periksa detail sebelum menyimpan</div>
            </div>

            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-white hover:text-slate-900"
              wire:click="closeConfirm"
            >
              ✕
            </button>
          </div>

          <div class="p-4 text-sm">
            @php
              $kk = $this->selectedKamar;
              $ll = $this->selectedLayanan;
              $hargaPerMalam = (int) ($this->hargaKamar ?? 0);
            @endphp

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr,1.15fr]">
              <div class="space-y-3">
                <dl class="space-y-2">
                  <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Nama Tamu</dt>
                    <dd class="font-semibold text-slate-900 text-right">{{ $nama_tamu ?: '-' }}</dd>
                  </div>

                  <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">No. Telepon</dt>
                    <dd class="font-semibold text-slate-900 text-right">{{ $no_telp_tamu ?: '-' }}</dd>
                  </div>

                  <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Kamar</dt>
                    <dd class="font-semibold text-slate-900 text-right">
                      {{ $kk ? ($kk->nomor_kamar.' - '.$kk->tipe_kamar) : '-' }}
                    </dd>
                  </div>

                  <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Sumber Booking</dt>
                    <dd class="font-semibold text-slate-900 text-right">
                      @php
                        $sourceLabel = match($source_type) {
                          'walk_in' => 'Walk-in',
                          'telepon_wa' => 'Telepon/WA',
                          'ota' => 'OTA',
                          'lainnya' => 'Lainnya',
                          default => '-',
                        };
                      @endphp
                      {{ $sourceLabel }}
                    </dd>
                  </div>

                  @if(in_array($source_type, ['ota', 'lainnya'], true))
                    <div class="flex justify-between gap-4">
                      <dt class="text-slate-500">Detail Sumber</dt>
                      <dd class="font-semibold text-slate-900 text-right">{{ $source_detail ?: '-' }}</dd>
                    </div>
                  @endif

                  <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Tanggal</dt>
                    <dd class="font-semibold text-slate-900 text-right">
                      {{ $tanggal_range ?: '-' }}
                    </dd>
                  </div>

                  <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Extra Bed</dt>
                    <dd class="font-semibold text-slate-900 text-right">
                      {{ $ll ? $ll->nama : 'Tidak ada' }}
                      @if($ll)
                        <span class="text-slate-600 font-medium"> ({{ (int)$layanan_qty }}x)</span>
                      @endif
                    </dd>
                  </div>

                  <div class="pt-2 border-t border-slate-200">
                    <dt class="text-slate-500">Catatan</dt>
                    <dd class="mt-1 text-slate-900">{{ $catatan ?: '-' }}</dd>
                  </div>
                </dl>
              </div>

              <div class="space-y-3">
                <div class="rounded-2xl border border-[#FFB22C]/30 bg-white p-3">
                  <div class="text-sm font-semibold text-slate-900">Detail Pembayaran</div>

                  <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-[#FFB22C]/20 bg-white p-3">
                      <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                          <span class="text-slate-600">Durasi</span>
                          <span class="font-semibold text-slate-900">{{ (int) $this->nights }} malam</span>
                        </div>
                        <div class="flex items-center justify-between">
                          <span class="text-slate-600">Harga per malam</span>
                          <span class="font-semibold text-slate-900">
                            Rp {{ number_format($hargaPerMalam, 0, ',', '.') }}
                          </span>
                        </div>
                        <div class="flex items-center justify-between">
                          <span class="text-slate-600">Subtotal Kamar</span>
                          <span class="font-semibold text-slate-900">
                            Rp {{ number_format((int) $this->roomSubtotal, 0, ',', '.') }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <div class="rounded-xl border border-[#FFB22C]/20 bg-white p-3">
                      <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                          <span class="text-slate-600">Subtotal Extra Bed</span>
                          <span class="font-semibold text-slate-900">
                            Rp {{ number_format((int) $this->layananSubtotal, 0, ',', '.') }}
                          </span>
                        </div>
                        <div class="flex items-center justify-between">
                          <span class="text-slate-600">Total Bayar</span>
                          <span class="font-extrabold text-[#854836]">
                            Rp {{ number_format((int) $this->grandTotal, 0, ',', '.') }}
                          </span>
                        </div>
                        <div class="text-xs text-slate-500 pt-1">
                          Total = (harga per malam × durasi) + extra bed
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                  <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="inline-flex h-2 w-2 rounded-full bg-[#FFB22C]"></span>
                    <span>Harga kamar sesuai sumber</span>
                    <span class="inline-flex h-2 w-2 rounded-full bg-[#854836] ml-2"></span>
                    <span>Extra bed sesuai qty</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50 px-4 py-3">
            <button
              type="button"
              class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
              wire:click="closeConfirm"
            >
              Batal
            </button>

            <button
              type="button"
              class="rounded-xl bg-[#854836] px-4 py-2 text-sm font-semibold text-white hover:opacity-95"
              wire:click="save"
              wire:loading.attr="disabled"
            >
              <span wire:loading.remove>Simpan</span>
              <span wire:loading>Menyimpan...</span>
            </button>
          </div>
        </div>
      </div>

    </form>
  </div>
</div>

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    let fp = null;
    let availabilityRequestToken = 0;

    function setTanggalRangeValue(value) {
      const hidden = document.getElementById('tanggalRangeValue');
      if (!hidden) return;
      hidden.value = value;
      hidden.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function syncSelectedTypeValue(value) {
      const hidden = document.getElementById('selectedTipeKamarValue');
      if (!hidden) return;
      hidden.value = String(value || '');
      hidden.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function syncSelectedRoomValue(value) {
      const hidden = document.getElementById('selectedKamarValue');
      if (!hidden) return;
      hidden.value = String(value || '');
      hidden.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function toggleRoomAvailabilityMessage(show, text = 'Tidak ada kamar tersedia pada tanggal tersebut.') {
      const messageEl = document.getElementById('roomAvailabilityMessage');
      if (!messageEl) return;
      messageEl.textContent = text;
      messageEl.classList.toggle('hidden', !show);
    }

    function parseTanggalRange(value) {
      const parts = String(value || '').trim().split(/\s+to\s+/i);
      if (parts.length !== 2) {
        return null;
      }

      const startDate = parts[0].trim();
      const endDate = parts[1].trim();
      if (!startDate || !endDate || endDate <= startDate) {
        return null;
      }

      return { startDate, endDate };
    }

    function getCurrentTanggalRange() {
      const hidden = document.getElementById('tanggalRangeValue');
      const visible = document.getElementById('tanggalRange');

      const hiddenValue = hidden ? hidden.value : '';
      const visibleValue = visible ? visible.value : '';

      return parseTanggalRange(hiddenValue || visibleValue || '');
    }

    function resetTypeSelectPlaceholder(text, disabled = true) {
      const typeSelect = document.getElementById('selectTipeKamar');
      if (!typeSelect) return;

      typeSelect.innerHTML = '';
      const option = document.createElement('option');
      option.value = '';
      option.textContent = text;
      typeSelect.appendChild(option);
      typeSelect.value = '';
      typeSelect.disabled = disabled;
      syncSelectedTypeValue('');
    }

    function resetRoomSelectPlaceholder(text, disabled = true) {
      const roomSelect = document.getElementById('selectKamar');
      if (!roomSelect) return;

      roomSelect.innerHTML = '';
      const option = document.createElement('option');
      option.value = '';
      option.textContent = text;
      roomSelect.appendChild(option);
      roomSelect.value = '';
      roomSelect.disabled = disabled;
      syncSelectedRoomValue('');
    }

    function setTypeOptions(types, selectedType = '') {
      const typeSelect = document.getElementById('selectTipeKamar');
      if (!typeSelect) return;

      const safeSelected = String(selectedType || '');
      typeSelect.innerHTML = '';

      const defaultOption = document.createElement('option');
      defaultOption.value = '';
      defaultOption.textContent = '-- Pilih Tipe Kamar --';
      typeSelect.appendChild(defaultOption);

      types.forEach((typeName) => {
        const option = document.createElement('option');
        option.value = String(typeName);
        option.textContent = String(typeName);
        typeSelect.appendChild(option);
      });

      typeSelect.disabled = false;
      typeSelect.value = safeSelected;
      syncSelectedTypeValue(safeSelected);
    }

    function setRoomOptions(rooms, selectedRoom = '') {
      const roomSelect = document.getElementById('selectKamar');
      if (!roomSelect) return;

      const safeSelected = String(selectedRoom || '');
      roomSelect.innerHTML = '';

      const defaultOption = document.createElement('option');
      defaultOption.value = '';
      defaultOption.textContent = '-- Pilih Nomor Kamar --';
      roomSelect.appendChild(defaultOption);

      rooms.forEach((room) => {
        const option = document.createElement('option');
        option.value = String(room.id);
        option.textContent = `${room.nomor_kamar} - ${room.tipe_kamar}`;
        roomSelect.appendChild(option);
      });

      roomSelect.disabled = false;
      roomSelect.value = safeSelected;
      syncSelectedRoomValue(safeSelected);
    }

    async function fetchAvailability(startDate, endDate, typeName = '') {
      const typeSelect = document.getElementById('selectTipeKamar');
      if (!typeSelect) {
        return { types: [], rooms: [], message: null };
      }

      const url = new URL(typeSelect.dataset.availabilityUrl, window.location.origin);
      url.searchParams.set('start_date', startDate);
      url.searchParams.set('end_date', endDate);
      if (typeName) {
        url.searchParams.set('tipe_kamar', typeName);
      }

      const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
      if (!response.ok) {
        throw new Error('Failed to fetch room availability');
      }

      return await response.json();
    }

    async function loadRoomTypesForDateRange(startDate, endDate, selectedType = '', selectedRoom = '') {
      const requestToken = ++availabilityRequestToken;

      resetTypeSelectPlaceholder('Memuat tipe kamar...', true);
      resetRoomSelectPlaceholder('Pilih tipe kamar terlebih dahulu', true);
      toggleRoomAvailabilityMessage(false);

      try {
        const payload = await fetchAvailability(startDate, endDate);
        if (requestToken !== availabilityRequestToken) return;

        const types = Array.isArray(payload.types) ? payload.types : [];
        if (types.length === 0) {
          resetTypeSelectPlaceholder('Tidak ada kamar tersedia pada tanggal tersebut', true);
          resetRoomSelectPlaceholder('Pilih tipe kamar terlebih dahulu', true);
          toggleRoomAvailabilityMessage(true, payload.message || 'Tidak ada kamar tersedia pada tanggal tersebut.');
          return;
        }

        const effectiveType = types.includes(selectedType) ? selectedType : '';
        setTypeOptions(types, effectiveType);

        if (effectiveType !== '') {
          await loadRoomsForSelectedType(startDate, endDate, effectiveType, selectedRoom);
        } else {
          resetRoomSelectPlaceholder('Pilih tipe kamar terlebih dahulu', true);
        }
      } catch (error) {
        console.error(error);
        if (requestToken !== availabilityRequestToken) return;
        resetTypeSelectPlaceholder('Gagal memuat tipe kamar', true);
        resetRoomSelectPlaceholder('Pilih tipe kamar terlebih dahulu', true);
        toggleRoomAvailabilityMessage(true, 'Gagal memuat data kamar. Coba lagi.');
      }
    }

    async function loadRoomsForSelectedType(startDate, endDate, typeName, selectedRoom = '') {
      const requestToken = ++availabilityRequestToken;
      resetRoomSelectPlaceholder('Memuat nomor kamar...', true);
      toggleRoomAvailabilityMessage(false);

      try {
        const payload = await fetchAvailability(startDate, endDate, typeName);
        if (requestToken !== availabilityRequestToken) return;

        const rooms = Array.isArray(payload.rooms) ? payload.rooms : [];
        if (rooms.length === 0) {
          resetRoomSelectPlaceholder('Tidak ada kamar tersedia pada tanggal tersebut', true);
          toggleRoomAvailabilityMessage(true, payload.message || 'Tidak ada kamar tersedia pada tanggal tersebut.');
          return;
        }

        setRoomOptions(rooms, selectedRoom);
      } catch (error) {
        console.error(error);
        if (requestToken !== availabilityRequestToken) return;
        resetRoomSelectPlaceholder('Gagal memuat nomor kamar', true);
        toggleRoomAvailabilityMessage(true, 'Gagal memuat data kamar. Coba lagi.');
      }
    }

    async function handleTanggalRangeChange(rangeValue, preserveSelection = false) {
      setTanggalRangeValue(rangeValue);

      const parsedRange = parseTanggalRange(rangeValue);
      if (!parsedRange) {
        resetTypeSelectPlaceholder('Pilih tanggal menginap terlebih dahulu', true);
        resetRoomSelectPlaceholder('Pilih tipe kamar terlebih dahulu', true);
        toggleRoomAvailabilityMessage(false);
        return;
      }

      const typeSelect = document.getElementById('selectTipeKamar');
      const roomSelect = document.getElementById('selectKamar');
      const selectedType = preserveSelection && typeSelect ? (typeSelect.dataset.selectedType || '') : '';
      const selectedRoom = preserveSelection && roomSelect ? (roomSelect.dataset.selectedRoom || '') : '';

      await loadRoomTypesForDateRange(parsedRange.startDate, parsedRange.endDate, selectedType, selectedRoom);
    }

    function initTanggalPicker() {
      const input = document.getElementById('tanggalRange');
      const hidden = document.getElementById('tanggalRangeValue');
      if (!input || !hidden) return;
      if (input.dataset.boundTanggal === 'true') return;

      input.dataset.boundTanggal = 'true';

      fp = flatpickr(input, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        allowInput: false,
        clickOpens: true,
        onChange: (_, dateStr) => {
          handleTanggalRangeChange(dateStr);
        },
        onClose: (_, dateStr) => {
          handleTanggalRangeChange(dateStr);
        },
      });

      const initialRange = hidden.value || '';
      if (initialRange) {
        const parsed = parseTanggalRange(initialRange);
        if (parsed) {
          fp.setDate([parsed.startDate, parsed.endDate], false);
        }
      }

      handleTanggalRangeChange(initialRange, true);
    }

    function bindTypeRoomEvents() {
      const typeSelect = document.getElementById('selectTipeKamar');
      const roomSelect = document.getElementById('selectKamar');
      const tanggalHidden = document.getElementById('tanggalRangeValue');

      if (!typeSelect || !roomSelect || !tanggalHidden) return;
      if (typeSelect.dataset.boundAvailability === 'true') return;

      typeSelect.dataset.boundAvailability = 'true';

      typeSelect.addEventListener('change', async () => {
        const selectedType = typeSelect.value || '';
        syncSelectedTypeValue(selectedType);
        syncSelectedRoomValue('');

        const parsedRange = getCurrentTanggalRange();
        if (!parsedRange) {
          resetRoomSelectPlaceholder('Pilih tipe kamar terlebih dahulu', true);
          return;
        }

        if (!selectedType) {
          resetRoomSelectPlaceholder('Pilih tipe kamar terlebih dahulu', true);
          toggleRoomAvailabilityMessage(false);
          return;
        }

        await loadRoomsForSelectedType(parsedRange.startDate, parsedRange.endDate, selectedType);
      });

      roomSelect.addEventListener('change', () => {
        syncSelectedRoomValue(roomSelect.value || '');
      });

      const parsedRange = getCurrentTanggalRange();
      const initialType = typeSelect.value || typeSelect.dataset.selectedType || '';
      const initialRoom = roomSelect.value || roomSelect.dataset.selectedRoom || '';

      if (parsedRange && initialType) {
        loadRoomsForSelectedType(parsedRange.startDate, parsedRange.endDate, initialType, initialRoom);
      }
    }

    function initBookingCreateFlow() {
      initTanggalPicker();
      bindTypeRoomEvents();
    }

    document.addEventListener('DOMContentLoaded', initBookingCreateFlow);
    document.addEventListener('livewire:navigated', initBookingCreateFlow);
    initBookingCreateFlow();
    
  </script>
@endpush
