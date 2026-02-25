<div class="w-full space-y-6 pb-10">

  <x-page-header
    title="Tambah Pegawai"
    subtitle="Buat akun pegawai baru untuk akses sistem."
  >
    <x-slot:rightSlot>
      <a
        wire:navigate
        href="{{ route('admin.pegawai.index') }}"
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

  {{-- CARD FORM --}}
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
      <div class="text-sm font-semibold text-slate-900">Informasi Pegawai</div>
      <div class="mt-0.5 text-xs text-slate-500">Isi nama, username, dan password untuk akun baru.</div>
    </div>

    <form method="POST" action="{{ route('admin.pegawai.store') }}" class="space-y-8 p-5 sm:p-6">
      @csrf

      <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Nama</label>
          <input
            type="text"
            name="nama"
            value="{{ old('nama') }}"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C] transition"
            placeholder="Contoh: Ni Luh Putu"
            autocomplete="name"
          >
          @error('nama') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Username</label>
          <input
            type="text"
            name="username"
            value="{{ old('username') }}"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C] transition"
            placeholder="contoh: pegawai01"
            autocomplete="username"
          >
          @error('username') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Password</label>
          <input
            type="password"
            name="password"
            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C] transition"
            placeholder="Minimal 6 karakter"
            autocomplete="new-password"
          >
          <div class="mt-1 text-xs text-slate-500">Gunakan kombinasi huruf dan angka.</div>
          @error('password') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end sm:gap-3 border-t border-slate-200 pt-5">
        <a
          wire:navigate
          href="{{ route('admin.pegawai.index') }}"
          class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700
                 hover:bg-slate-50 transition"
        >
          Batal
        </a>

        <button
          type="submit"
          class="inline-flex items-center justify-center rounded-xl bg-[#FFB22C] px-5 py-2.5 text-sm font-semibold text-slate-900
                 shadow hover:opacity-90 active:scale-[0.98] transition"
        >
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
