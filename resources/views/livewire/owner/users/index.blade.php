<div class="w-full space-y-6 pb-10">

  <x-page-header
    title="Kelola User"
    subtitle="Buat, ubah, dan atur status akun owner/admin/pegawai."
  >
    <x-slot:rightSlot>
      <a
        wire:navigate
        href="{{ route('owner.users.create') }}"
        class="inline-flex w-full items-center justify-center rounded-xl bg-[#FFB22C] px-4 py-2.5 text-sm font-semibold text-slate-900 shadow hover:opacity-95 active:scale-[0.98] transition sm:w-auto"
      >
        + Tambah User
      </a>
    </x-slot:rightSlot>
  </x-page-header>

  {{-- FLASH --}}
  @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- FILTER BAR (opsional) --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
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
        <label class="block text-xs font-semibold text-slate-600">Level</label>
        <div class="relative mt-1">
          <select
            class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 focus:border-[#FFB22C]"
            wire:model.live="level"
          >
            <option value="">Semua</option>
            <option value="owner">Owner</option>
            <option value="admin">Admin</option>
            <option value="pegawai">Pegawai</option>
          </select>
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
              <path d="m6 9 6 6 6-6"></path>
            </svg>
          </span>
        </div>
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
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
              <path d="m6 9 6 6 6-6"></path>
            </svg>
          </span>
        </div>
      </div>
    </div>

    <div class="mt-3 flex items-center justify-end">
      <button type="button"
              wire:click="resetFilters"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
          <path d="M3 2v6h6"></path>
          <path d="M3 13a9 9 0 1 0 3-7.7L3 8"></path>
        </svg>
        Reset
      </button>
    </div>
  </div>

  {{-- ===================== MOBILE: CARD LIST ===================== --}}
  <div class="space-y-3 md:hidden">
    @forelse($users as $u)
      @php
        $lvl = strtolower($u->level);
        $lvlBadge = match($lvl) {
          'owner' => 'bg-[#FFB22C]/20 text-slate-900 border-[#FFB22C]/40',
          'admin' => 'bg-slate-100 text-slate-800 border-slate-200',
          default => 'bg-white text-slate-700 border-slate-200',
        };
      @endphp

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="truncate text-base font-semibold text-slate-900">{{ $u->nama }}</div>
            <div class="truncate text-sm text-slate-600">{{ $u->username }}</div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $lvlBadge }}">
              {{ ucfirst($lvl) }}
            </span>

            @if($u->status_aktif)
              <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                Aktif
              </span>
            @else
              <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">
                Nonaktif
              </span>
            @endif
          </div>
        </div>

        <div class="mt-4 space-y-2">
          <a wire:navigate
            href="{{ route('owner.users.edit', $u->id) }}"
            class="w-full inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Edit
          </a>
        </div>
      </div>
    @empty
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500">
        Belum ada user.
      </div>
    @endforelse

    @if(method_exists($users, 'links'))
      <div class="rounded-2xl border border-slate-200 bg-white p-4">
        {{ $users->links() }}
      </div>
    @endif
  </div>

  {{-- ===================== DESKTOP: TABLE ===================== --}}
  <div class="hidden md:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
      <div class="text-sm font-semibold text-slate-900">Daftar User</div>
      <div class="mt-0.5 text-xs text-slate-500">Klik Edit untuk mengubah data.</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white">
          <tr class="text-left text-xs font-semibold text-slate-600">
            <th class="px-5 py-3">Nama</th>
            <th class="px-5 py-3">Username</th>
            <th class="px-5 py-3">Level</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-200">
          @forelse($users as $u)
            @php
              $lvl = strtolower($u->level);
              $lvlBadge = match($lvl) {
                'owner' => 'bg-[#FFB22C]/20 text-slate-900 border-[#FFB22C]/40',
                'admin' => 'bg-slate-100 text-slate-800 border-slate-200',
                default => 'bg-white text-slate-700 border-slate-200',
              };
            @endphp

            <tr class="hover:bg-slate-50/60">
              <td class="px-5 py-3">
                <div class="font-semibold text-slate-900">{{ $u->nama }}</div>
              </td>
              <td class="px-5 py-3 text-slate-700">{{ $u->username }}</td>

              <td class="px-5 py-3">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $lvlBadge }}">
                  {{ ucfirst($lvl) }}
                </span>
              </td>

              <td class="px-5 py-3">
                @if($u->status_aktif)
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
                     href="{{ route('owner.users.edit', $u->id) }}"
                     class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Edit
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                Belum ada user.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($users, 'links'))
      <div class="border-t border-slate-200 bg-white px-5 py-4">
        <div class="mt-4" wire:key="users-pagination">
        {{ $users->links() }}
      </div></div>
    @endif
  </div>

  <script>
    function bindOwnerUsersHooks() {
      if (!window.Livewire || window.__ownerUsersHooksBound) {
        return;
      }

      window.__ownerUsersHooksBound = true;

      Livewire.hook("message.sent", () => {
        window.__lwScrollY = window.scrollY;
      });

      Livewire.hook("message.processed", () => {
        if (typeof window.__lwScrollY === "number") {
          window.scrollTo({ top: window.__lwScrollY, behavior: "instant" });
        }
      });
    }

    bindOwnerUsersHooks();

    document.addEventListener("livewire:initialized", bindOwnerUsersHooks, { once: true });
    document.addEventListener("livewire:navigated", () => {
      bindOwnerUsersHooks();
    });
  </script>
</div>
