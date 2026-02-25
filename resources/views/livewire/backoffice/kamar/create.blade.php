<div class="w-full space-y-6 pb-10">

  <x-page-header
    title="Tambah Kamar"
    subtitle="Tambahkan kamar baru untuk sistem."
  >
    <x-slot:rightSlot>
      <a
        wire:navigate
        href="{{ route('admin.kamar.index') }}"
        class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition sm:w-auto"
      >
        ← Kembali
      </a>
    </x-slot:rightSlot>
  </x-page-header>

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

  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
      <div class="text-sm font-semibold text-slate-900">Informasi Kamar</div>
      <div class="mt-0.5 text-xs text-slate-500">Pastikan nomor kamar unik.</div>
    </div>

    <form method="POST" action="{{ route('admin.kamar.store') }}" class="space-y-6 p-5 sm:p-6">
      @csrf

      <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Nomor Kamar</label>
          <input
            name="nomor_kamar"
            value="{{ old('nomor_kamar') }}"
            placeholder="Contoh: 1A / 12 / 201"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]
                   transition"
          >
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Tipe Kamar</label>
          <div class="relative mt-2">
            <select
              name="tipe_kamar"
              class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
            >
              <option value="">-- Pilih Tipe Kamar --</option>
              <option value="Standard Fan" {{ old('tipe_kamar')=='Standard Fan' ? 'selected' : '' }}>Standard Fan</option>
              <option value="Superior" {{ old('tipe_kamar')=='Superior' ? 'selected' : '' }}>Superior</option>
              <option value="Deluxe" {{ old('tipe_kamar')=='Deluxe' ? 'selected' : '' }}>Deluxe</option>
              <option value="Family Room" {{ old('tipe_kamar')=='Family Room' ? 'selected' : '' }}>Family Room</option>
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <i data-lucide="chevron-down" class="h-4 w-4"></i>
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Tarif</label>
          <input
            type="number"
            name="tarif"
            value="{{ old('tarif', 0) }}"
            min="0"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]
                   transition"
          >
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Kapasitas</label>
          <input
            type="number"
            name="kapasitas"
            value="{{ old('kapasitas', 1) }}"
            min="1"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]
                   transition"
          >
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700">Status Kamar</label>
        <div class="relative mt-2">
            <select
              id="statusKamarSelect"
              name="status_kamar"
              class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
            >
              <option value="tersedia"  {{ old('status_kamar','tersedia')=='tersedia' ? 'selected' : '' }}>tersedia</option>
              <option value="perbaikan" {{ old('status_kamar')=='perbaikan' ? 'selected' : '' }}>perbaikan</option>
            </select>
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
            <i data-lucide="chevron-down" class="h-4 w-4"></i>
          </span>
        </div>
      </div>

      <div id="perbaikanFields" class="grid grid-cols-1 gap-5 md:grid-cols-2 {{ old('status_kamar','tersedia') === 'perbaikan' ? '' : 'hidden' }}">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Mulai Perbaikan</label>
          <input
            type="date"
            name="perbaikan_mulai"
            value="{{ old('perbaikan_mulai') }}"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]
                   transition"
          >
          @error('perbaikan_mulai') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700">Selesai Perbaikan</label>
          <input
            type="date"
            name="perbaikan_selesai"
            value="{{ old('perbaikan_selesai') }}"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]
                   transition"
          >
          @error('perbaikan_selesai') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-slate-700">Catatan Perbaikan (opsional)</label>
          <textarea
            name="perbaikan_catatan"
            rows="3"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]
                   transition"
            placeholder="Contoh: perbaikan AC, penggantian sprei, dll."
          >{{ old('perbaikan_catatan') }}</textarea>
          @error('perbaikan_catatan') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end sm:gap-3 border-t border-slate-200 pt-5">
        <a wire:navigate
           href="{{ route('admin.kamar.index') }}"
           class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
          Batal
        </a>

        <button
          type="submit"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-[#FFB22C] px-4 py-2.5 text-sm font-semibold text-slate-900
                 shadow hover:opacity-95 active:scale-[0.98] transition"
        >
          Simpan
        </button>
      </div>
    </form>
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

    const togglePerbaikan = () => {
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

    statusSelect.addEventListener('change', togglePerbaikan);
    togglePerbaikan();
  });
</script>
@endpush
