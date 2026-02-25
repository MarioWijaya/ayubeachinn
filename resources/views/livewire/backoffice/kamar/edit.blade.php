<div class="w-full space-y-6 pb-10">

  <x-page-header
    title="Ubah Kamar"
    subtitle="Perbarui informasi nomor, tipe, tarif, kapasitas, dan status kamar."
  >
    <x-slot:rightSlot>
      <a
        wire:navigate
        href="{{ route('admin.kamar.index') }}"
        class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto"
      >
        ← Kembali
      </a>
    </x-slot:rightSlot>
  </x-page-header>

  {{-- ERROR --}}
  @if ($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
      <div class="font-semibold">Periksa kembali input:</div>
      <ul class="mt-2 list-disc space-y-1 pl-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM CARD --}}
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
      <div class="text-sm font-semibold text-slate-900">Informasi Kamar</div>
      <div class="mt-0.5 text-xs text-slate-500">Ubah data lalu klik Simpan.</div>
    </div>

    @php
      $routePrefix = request()->segment(1);
      $tanggalTerpakaiUrl = route($routePrefix . '.booking.tanggal_terpakai', ['kamarId' => $kamar->id]);
    @endphp

    <form method="POST" action="{{ route('admin.kamar.update', $kamar->id) }}" class="p-6">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        {{-- Nomor Kamar --}}
        <div class="sm:col-span-1">
          <label class="block text-sm font-semibold text-slate-700">Nomor Kamar</label>
          <input
            name="nomor_kamar"
            value="{{ old('nomor_kamar', $kamar->nomor_kamar) }}"
            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
            placeholder="Contoh: 101"
            autocomplete="off"
          >
        </div>

        {{-- Tipe Kamar --}}
        <div class="sm:col-span-1">
          <label class="block text-sm font-semibold text-slate-700">Tipe Kamar</label>
          <div class="relative mt-1">
            @php
              $selectedTipe = old('tipe_kamar', $kamar->tipe_kamar);
              $selectedTipe = $selectedTipe === 'Standart Fan' ? 'Standard Fan' : $selectedTipe;
            @endphp

            <select
              name="tipe_kamar"
              class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
            >
              <option value="">-- Pilih Tipe Kamar --</option>
              @foreach($tipeKamar as $tipe)
                <option value="{{ $tipe }}" {{ $selectedTipe == $tipe ? 'selected' : '' }}>
                  {{ $tipe }}
                </option>
              @endforeach
            </select>

            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
        </div>

        {{-- Tarif --}}
        <div class="sm:col-span-1">
          <label class="block text-sm font-semibold text-slate-700">Tarif</label>
          <div class="relative mt-1">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">
              Rp
            </span>
            <input
              type="number"
              name="tarif"
              min="0"
              value="{{ old('tarif', $kamar->tarif) }}"
              class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
              placeholder="Contoh: 300000"
            >
          </div>
          <div class="mt-1 text-xs text-slate-500">Contoh: 300000 (tanpa titik/koma).</div>
        </div>

        {{-- Kapasitas --}}
        <div class="sm:col-span-1">
          <label class="block text-sm font-semibold text-slate-700">Kapasitas</label>
          <div class="relative mt-1">
            <input
              type="number"
              name="kapasitas"
              min="1"
              value="{{ old('kapasitas', $kamar->kapasitas) }}"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
              placeholder="Contoh: 2"
            >
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs font-semibold text-slate-400">
              orang
            </span>
          </div>
        </div>

        {{-- Status --}}
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-slate-700">Status Kamar</label>
          <div class="relative mt-1">
            <select
              name="status_kamar"
              id="statusKamarSelect"
              class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
            >
              <option value="tersedia"  {{ old('status_kamar', $kamar->status_kamar)=='tersedia' ? 'selected' : '' }}>tersedia</option>
              <option value="perbaikan" {{ old('status_kamar', $kamar->status_kamar)=='perbaikan' ? 'selected' : '' }}>perbaikan</option>
            </select>

            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>

        </div>

        <div id="perbaikanFields" class="grid grid-cols-1 gap-4 md:col-span-2 md:grid-cols-2 {{ old('status_kamar', $kamar->status_kamar) === 'perbaikan' ? '' : 'hidden' }}">
          <div>
            <label class="block text-sm font-semibold text-slate-700">Mulai Perbaikan</label>
            <div class="relative mt-1">
              <input
                type="date"
                name="perbaikan_mulai"
                id="perbaikanMulaiInput"
                value="{{ old('perbaikan_mulai', $perbaikan?->mulai?->toDateString()) }}"
                data-url-template="{{ $tanggalTerpakaiUrl }}"
                min="{{ now()->toDateString() }}"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
                placeholder="Klik untuk memilih tanggal"
              >
              <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                <i data-lucide="calendar" class="h-4 w-4"></i>
              </span>
            </div>
            @error('perbaikan_mulai') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          </div>
          <div>
              <label class="block text-sm font-semibold text-slate-700">Selesai Perbaikan</label>
            <div class="relative mt-1">
              <input
                type="date"
                name="perbaikan_selesai"
                id="perbaikanSelesaiInput"
                value="{{ old('perbaikan_selesai', $perbaikan?->selesai?->toDateString()) }}"
                data-url-template="{{ $tanggalTerpakaiUrl }}"
                min="{{ now()->toDateString() }}"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
                placeholder="Klik untuk memilih tanggal"
              >
              <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                <i data-lucide="calendar" class="h-4 w-4"></i>
              </span>
            </div>
            @error('perbaikan_selesai') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-semibold text-slate-700">Catatan Perbaikan (opsional)</label>
            <textarea
              name="perbaikan_catatan"
              rows="3"
              class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
              placeholder="Contoh: perbaikan AC, penggantian sprei, dll."
            >{{ old('perbaikan_catatan', $perbaikan?->catatan) }}</textarea>
            @error('perbaikan_catatan') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
          </div>
        </div>

      </div>

      <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
        <a wire:navigate
           href="{{ route('admin.kamar.index') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
          Batal
        </a>

        <button
          type="submit"
          class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#FFB22C] px-5 py-2.5 text-sm font-semibold text-slate-900
                 shadow hover:opacity-95 active:scale-[0.98] transition">
          Simpan
        </button>
      </div>
    </form>
  </div>

  <div id="perbaikanAlertModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
      <div class="flex items-start justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
        <div>
          <div class="text-sm font-semibold text-slate-900">Perhatian</div>
          <div class="mt-0.5 text-xs text-slate-500" id="perbaikanAlertSubtitle">Tanggal perbaikan perlu dicek.</div>
        </div>
        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" data-modal-close>✕</button>
      </div>
      <div class="p-5">
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" id="perbaikanAlertMessage">
          Tanggal perbaikan berbenturan dengan booking aktif.
        </div>
        <div class="mt-5 flex items-center justify-end gap-2">
          <button type="button"
                  class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                  data-modal-cancel>
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
  document.addEventListener('livewire:navigated', () => {
    const statusSelect = document.getElementById('statusKamarSelect');
    const perbaikanFields = document.getElementById('perbaikanFields');
    if (!statusSelect || !perbaikanFields) return;

    const perbaikanMulai = perbaikanFields.querySelector('[name="perbaikan_mulai"]');
    const perbaikanSelesai = perbaikanFields.querySelector('[name="perbaikan_selesai"]');
    const perbaikanCatatan = perbaikanFields.querySelector('[name="perbaikan_catatan"]');
    const tanggalTerpakaiUrl = @js($tanggalTerpakaiUrl);
    const modal = document.getElementById('perbaikanAlertModal');
    const modalMessage = document.getElementById('perbaikanAlertMessage');
    const modalSubtitle = document.getElementById('perbaikanAlertSubtitle');
    const cancelButton = modal?.querySelector('[data-modal-cancel]');
    const closeButton = modal?.querySelector('[data-modal-close]');
    let cachedDisabledDates = [];

    const toggleFields = () => {
      const isPerbaikan = statusSelect.value === 'perbaikan';
      perbaikanFields.classList.toggle('hidden', !isPerbaikan);
      if (!isPerbaikan && perbaikanMulai) {
        perbaikanMulai.value = '';
      }
      if (!isPerbaikan && perbaikanSelesai) {
        perbaikanSelesai.value = '';
      }
      if (!isPerbaikan && perbaikanCatatan) {
        perbaikanCatatan.value = '';
      }
    };

    const openModal = (message, subtitle = 'Tanggal perbaikan perlu dicek.') => {
      if (!modal) return;
      modalMessage.textContent = message;
      modalSubtitle.textContent = subtitle;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    };

    const closeModal = () => {
      if (!modal) return;
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };

    const clearPerbaikanInputs = () => {
      if (perbaikanMulai) {
        perbaikanMulai.value = '';
      }
      if (perbaikanSelesai) {
        perbaikanSelesai.value = '';
      }
    };

    const handleCancel = () => {
      closeModal();
      clearPerbaikanInputs();
    };

    cancelButton?.addEventListener('click', handleCancel);
    closeButton?.addEventListener('click', handleCancel);
    modal?.addEventListener('click', (event) => {
      if (event.target === modal) {
        handleCancel();
      }
    });

    const fetchDisabledDates = async () => {
      const response = await fetch(tanggalTerpakaiUrl, { headers: { 'Accept': 'application/json' } });
      const data = await response.json();
      cachedDisabledDates = Array.isArray(data?.disabled) ? data.disabled : [];
      return cachedDisabledDates;
    };

    const ensureFlatpickrReady = (callback) => {
      if (window.flatpickr) {
        callback();
        return;
      }

      const existing = document.querySelector('script[data-flatpickr]');
      if (existing) {
        existing.addEventListener('load', callback, { once: true });
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
      script.setAttribute('data-flatpickr', 'true');
      script.addEventListener('load', callback, { once: true });
      document.head.appendChild(script);
    };

    const initPerbaikanDatepicker = async () => {
      if (!perbaikanMulai || typeof window.flatpickr !== 'function') {
        return;
      }

      if (perbaikanMulai._flatpickr) {
        perbaikanMulai._flatpickr.destroy();
      }
      if (perbaikanSelesai?._flatpickr) {
        perbaikanSelesai._flatpickr.destroy();
      }

      window.flatpickr(perbaikanMulai, {
        dateFormat: 'Y-m-d',
        disableMobile: true,
        allowInput: false,
        minDate: 'today',
        disable: [],
        onOpen: async (_selectedDates, _dateStr, instance) => {
          if (instance._updating) return;
          instance._updating = true;
          try {
            const disabledDates = cachedDisabledDates.length ? cachedDisabledDates : await fetchDisabledDates();
            instance.set('disable', disabledDates);
            instance.set('minDate', 'today');
            instance.redraw();
          } finally {
            instance._updating = false;
          }
        },
        onChange: () => handlePerbaikanChange(),
      });

      if (perbaikanSelesai) {
        window.flatpickr(perbaikanSelesai, {
          dateFormat: 'Y-m-d',
          disableMobile: true,
          allowInput: false,
          disable: [],
          minDate: perbaikanMulai.value || 'today',
          onOpen: async (_selectedDates, _dateStr, instance) => {
            if (instance._updating) return;
            instance._updating = true;
            try {
              const disabledDates = cachedDisabledDates.length ? cachedDisabledDates : await fetchDisabledDates();
              instance.set('disable', disabledDates);
              instance.set('minDate', perbaikanMulai.value || 'today');
              instance.redraw();
            } finally {
              instance._updating = false;
            }
          },
          onChange: () => handlePerbaikanChange(),
        });
      }

      try {
        const disabledDates = await fetchDisabledDates();
        if (perbaikanMulai?._flatpickr) {
          perbaikanMulai._flatpickr.set('disable', disabledDates);
        }
        if (perbaikanSelesai?._flatpickr) {
          perbaikanSelesai._flatpickr.set('disable', disabledDates);
        }
      } catch (error) {
        openModal('Gagal memeriksa jadwal booking. Silakan coba lagi.', 'Tidak bisa memuat jadwal booking.');
      }
    };

    const addDays = (dateString, days) => {
      const base = new Date(`${dateString}T00:00:00`);
      base.setDate(base.getDate() + days);
      return base.toISOString().slice(0, 10);
    };

    const isInRange = (date, start, end) => {
      if (!start) return false;
      return date >= start && date <= end;
    };

    const handlePerbaikanChange = async () => {
      if (!perbaikanMulai || perbaikanMulai.value === '') {
        return;
      }

      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const selected = new Date(`${perbaikanMulai.value}T00:00:00`);
      const selesaiValue = perbaikanSelesai?.value || '';

      if (selected < today) {
        clearPerbaikanInputs();
        openModal('Tanggal perbaikan sudah lewat. Silakan pilih tanggal lain.', 'Tanggal perbaikan di masa lalu.');
        return;
      }

      if (perbaikanSelesai && perbaikanSelesai.value && perbaikanSelesai.value < perbaikanMulai.value) {
        clearPerbaikanInputs();
        openModal('Tanggal selesai harus sama atau setelah tanggal mulai perbaikan.', 'Tanggal selesai tidak valid.');
        return;
      }

      if (perbaikanSelesai?._flatpickr) {
        perbaikanSelesai._flatpickr.set('minDate', perbaikanMulai.value);
      }

      try {
        const disabledDates = await fetchDisabledDates();
        const start = perbaikanMulai.value;
        const end = perbaikanSelesai?.value || '';
        const startBlocked = disabledDates.includes(start);
        const endBlocked = end !== '' && disabledDates.includes(end);
        const hasConflict = end !== '' && disabledDates.some((date) => isInRange(date, start, end));

        if (startBlocked || endBlocked || hasConflict) {
          clearPerbaikanInputs();
          openModal('Ada booking aktif pada tanggal ini. Silakan pilih tanggal lain.', 'Tanggal tidak tersedia.');
        }
      } catch (error) {
        clearPerbaikanInputs();
        openModal('Gagal memeriksa jadwal booking. Silakan coba lagi.', 'Tidak bisa memuat jadwal booking.');
      }
    };

    statusSelect.addEventListener('change', toggleFields);
    toggleFields();

    perbaikanMulai?.addEventListener('change', handlePerbaikanChange);
    perbaikanSelesai?.addEventListener('change', handlePerbaikanChange);
    ensureFlatpickrReady(initPerbaikanDatepicker);
  });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
