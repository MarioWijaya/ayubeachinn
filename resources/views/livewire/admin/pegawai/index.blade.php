<div class="w-full space-y-6 pb-10">

  <x-page-header
    title="Kelola Pegawai"
    subtitle="Buat, ubah, dan atur status akun pegawai."
  >
    <x-slot:rightSlot>
      <a
        wire:navigate
        href="{{ route('admin.pegawai.create') }}"
        class="inline-flex w-full items-center justify-center rounded-xl bg-[#FFB22C] px-4 py-2.5 text-sm font-semibold text-slate-900 shadow hover:opacity-95 active:scale-[0.98] transition sm:w-auto"
      >
        + Tambah Pegawai
      </a>
    </x-slot:rightSlot>
  </x-page-header>

  {{-- FLASH --}}
  @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- FILTER BAR --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
      <div>
        <label class="block text-xs font-semibold text-slate-600">Cari</label>
        <input
          type="text"
          placeholder="Nama / username..."
          class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                 focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
          wire:model.live.debounce.400ms="q"
        >
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600">Status</label>
        <div class="relative mt-1">
          <select
            class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
            wire:model.live="status"
          >
            <option value="">Semua</option>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
          <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
            <i data-lucide="chevron-down" class="h-4 w-4"></i>
          </span>
        </div>
      </div>
    </div>

    <div class="mt-3 flex items-center justify-end">
      <button type="button"
              wire:click="resetFilters"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto">
        <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
        Reset
      </button>
    </div>
  </div>

  {{-- ===================== MOBILE: CARD LIST ===================== --}}
  <div class="space-y-3 md:hidden">
    @forelse($pegawai as $p)
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="truncate text-base font-semibold text-slate-900">{{ $p->nama }}</div>
            <div class="truncate text-sm text-slate-600">{{ $p->username }}</div>
          </div>

          @if($p->status_aktif)
            <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
              Aktif
            </span>
          @else
            <span class="inline-flex shrink-0 items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">
              Nonaktif
            </span>
          @endif
        </div>

        <div class="mt-4 space-y-2">
          <a wire:navigate
             href="{{ route('admin.pegawai.edit', $p->id) }}"
             class="w-full inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Edit
          </a>
        </div>
      </div>
    @empty
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500">
        Belum ada data pegawai.
      </div>
    @endforelse

    @if(method_exists($pegawai, 'links'))
      <div class="rounded-2xl border border-slate-200 bg-white p-4">
        {{ $pegawai->links() }}
      </div>
    @endif
  </div>

  {{-- ===================== DESKTOP: TABLE ===================== --}}
  <div class="hidden md:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
      <div class="text-sm font-semibold text-slate-900">Daftar Pegawai</div>
      <div class="mt-0.5 text-xs text-slate-500">Klik Edit untuk mengubah data.</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white">
          <tr class="text-left text-xs font-semibold text-slate-600">
            <th class="px-5 py-3">Nama</th>
            <th class="px-5 py-3">Username</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-200">
          @forelse($pegawai as $p)
            <tr class="hover:bg-slate-50/60">
              <td class="px-5 py-3">
                <div class="font-semibold text-slate-900">{{ $p->nama }}</div>
              </td>
              <td class="px-5 py-3 text-slate-700">{{ $p->username }}</td>
              <td class="px-5 py-3">
                @if($p->status_aktif)
                  <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    Aktif
                  </span>
                @else
                  <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">
                    Nonaktif
                  </span>
                @endif
              </td>

              <td class="px-5 py-3">
                <div class="flex items-center justify-end gap-2">
                  <a wire:navigate
                     href="{{ route('admin.pegawai.edit', $p->id) }}"
                     class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Edit
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-5 py-10 text-center text-slate-500">
                Belum ada data pegawai.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($pegawai, 'links'))
      <div class="border-t border-slate-200 bg-white px-5 py-4">
        <div class="mt-4" wire:key="pegawai-pagination">
          {{ $pegawai->links() }}
        </div>
      </div>
    @endif
  </div>

</div>
