@php
  $fmtDash = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') : '-';
  $fmtSlash = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : '-';
@endphp

<div class="w-full space-y-6 pb-10">
  <x-page-header
    title="Laporan Pendapatan"
    subtitle="Ringkasan pendapatan booking dan layanan."
  >
    <x-slot:rightSlot>
      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm">
          <span class="text-slate-500">Total</span>
          <span class="mx-1">:</span>
          <span class="text-slate-900">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>

        <button
          type="button"
          wire:click="requestDownload('pdf')"
          wire:loading.attr="disabled"
          wire:target="requestDownload"
          class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#FFB22C] px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm
                 hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
        >
          <span wire:loading.remove wire:target="requestDownload">Download PDF</span>
          <span wire:loading wire:target="requestDownload">Membuat Preview…</span>
        </button>

        <button
          type="button"
          wire:click="requestDownload('csv')"
          wire:loading.attr="disabled"
          wire:target="requestDownload"
          class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm
                 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
        >
          <span wire:loading.remove wire:target="requestDownload">Download CSV</span>
          <span wire:loading wire:target="requestDownload">Membuat Preview…</span>
        </button>
      </div>
    </x-slot:rightSlot>
  </x-page-header>

  @if(session('error'))
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
      {{ session('error') }}
    </div>
  @endif

  @if($rangeClamped)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
      Rentang admin otomatis dibatasi ke bulan ini.
    </div>
  @endif

  {{-- Filters --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <label class="block text-xs font-semibold text-slate-600">Dari tanggal</label>
        <input
          type="date"
          class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                 focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
          wire:model.live="from"
          @disabled($isAdmin)
        >
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600">Sampai tanggal</label>
        <input
          type="date"
          class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm
                 focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
          wire:model.live="to"
          @disabled($isAdmin)
        >
      </div>

      {{-- Quick hint (optional) --}}
      <div class="hidden lg:block lg:col-span-2">
        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
          Tip: gunakan rentang tanggal checkout untuk melihat pendapatan yang sudah selesai.
        </div>
      </div>
    </div>

    @if($isAdmin)
      <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
        Admin hanya bisa melihat bulan ini: {{ $fmtSlash($from) }} s/d {{ $fmtSlash($to) }}.
      </div>
    @endif
  </div>

  {{-- Content --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    {{-- Mobile cards --}}
    <div class="block md:hidden">
      <div class="divide-y divide-slate-200">
        @forelse($rows as $row)
          @php
            $total = (int) ($row->total_final ?? 0);
          @endphp

          <div class="p-4 hover:bg-[#854836]/5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-semibold text-slate-900">#{{ $row->id }}</div>
                <div class="mt-0.5 text-xs text-slate-600">{{ $row->nama_tamu }}</div>
              </div>

              <div class="shrink-0 rounded-xl bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-800">
                Rp {{ number_format($total, 0, ',', '.') }}
              </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3">
              <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                <div class="text-[11px] font-semibold text-slate-500">Kamar</div>
                <div class="mt-0.5 text-sm font-semibold text-slate-900">{{ $row->nomor_kamar ?? '-' }}</div>
              </div>
              <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                <div class="text-[11px] font-semibold text-slate-500">Check-in</div>
                <div class="mt-0.5 text-sm font-semibold text-slate-900">{{ $fmtDash($row->tanggal_check_in) }}</div>
              </div>
              <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                <div class="text-[11px] font-semibold text-slate-500">Check-out</div>
                <div class="mt-0.5 text-sm font-semibold text-slate-900">{{ $fmtDash($row->tanggal_check_out) }}</div>
              </div>
              <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                <div class="text-[11px] font-semibold text-slate-500">Checkout</div>
                <div class="mt-0.5 text-sm font-semibold text-slate-900">{{ $row->checkout_at }}</div>
              </div>
            </div>

            <div class="mt-3 flex items-center justify-between text-xs">
              <div class="text-slate-500">Total: <span class="font-semibold text-slate-900">Rp {{ number_format($total, 0, ',', '.') }}</span></div>
            </div>
          </div>
        @empty
          <div class="p-8 text-center text-slate-500">
            Tidak ada data pendapatan.
          </div>
        @endforelse
      </div>

      <div class="border-t border-slate-200 bg-white px-4 py-3">
        {{ $rows->links(data: ['scrollTo' => false]) }}
      </div>
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr class="text-left">
              <th class="px-4 py-3 font-semibold">ID</th>
              <th class="px-4 py-3 font-semibold">Tamu</th>
              <th class="px-4 py-3 font-semibold">Kamar</th>
              <th class="px-4 py-3 font-semibold">Check-in</th>
              <th class="px-4 py-3 font-semibold">Check-out</th>
              <th class="px-4 py-3 font-semibold">Pendapatan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            @forelse($rows as $row)
              @php
                $total = (int) ($row->total_final ?? 0);
              @endphp

              <tr class="hover:bg-[#854836]/5">
                <td class="px-4 py-3 font-semibold text-slate-900">#{{ $row->id }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $row->nama_tamu }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $row->nomor_kamar ?? '-' }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $fmtDash($row->tanggal_check_in) }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $fmtDash($row->tanggal_check_out) }}</td>
                <td class="px-4 py-3 font-semibold text-slate-900">Rp {{ number_format($total, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Tidak ada data pendapatan.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="border-t border-slate-200 bg-white px-4 py-3">
        {{ $rows->links(data: ['scrollTo' => false]) }}
      </div>
    </div>
  </div>

  @if($showPreviewModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
      <div class="absolute inset-0 bg-black/40" wire:click="closePreview"></div>

      <div class="relative w-full max-w-4xl rounded-2xl bg-white shadow-xl max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4">
          <div>
            <div class="text-sm font-semibold text-slate-900">Preview Laporan</div>
            <div class="mt-0.5 text-xs text-slate-500">Periode {{ $fmtDash($from) }} s/d {{ $fmtDash($to) }}</div>
          </div>
          <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" wire:click="closePreview">✕</button>
        </div>

        <div class="p-5 space-y-4 overflow-y-auto max-h-[calc(90vh-140px)]">
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
              <div class="text-xs font-semibold text-slate-500">Total Pendapatan</div>
              <div class="mt-1 text-sm font-semibold text-slate-900">Rp {{ number_format($previewGrandTotal, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
              <div class="text-xs font-semibold text-slate-500">Jumlah Transaksi</div>
              <div class="mt-1 text-sm font-semibold text-slate-900">{{ $previewCount }}</div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-50 text-slate-600">
                <tr class="text-left">
                  <th class="px-4 py-3 font-semibold">ID</th>
                  <th class="px-4 py-3 font-semibold">Tamu</th>
                  <th class="px-4 py-3 font-semibold">Kamar</th>
                  <th class="px-4 py-3 font-semibold">Check-in</th>
                  <th class="px-4 py-3 font-semibold">Check-out</th>
                  <th class="px-4 py-3 font-semibold">Pendapatan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                @forelse($previewRows as $row)
                  <tr class="hover:bg-[#854836]/5">
                    <td class="px-4 py-3 font-semibold text-slate-900">#{{ $row['id'] }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $row['nama_tamu'] }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $row['nomor_kamar'] }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $fmtDash($row['tanggal_check_in']) }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $fmtDash($row['tanggal_check_out']) }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-900">Rp {{ number_format((int) $row['total_final'], 0, ',', '.') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">Tidak ada data pendapatan.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @if($previewCount > count($previewRows))
            <div class="text-xs text-slate-500">Dan seterusnya…</div>
          @endif
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-end">
          <button
            type="button"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            wire:click="closePreview"
          >
            Batal
          </button>
          <button
            type="button"
            wire:click="confirmDownload"
            wire:loading.attr="disabled"
            wire:target="confirmDownload"
            class="rounded-xl bg-[#FFB22C] px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <span wire:loading.remove wire:target="confirmDownload">
              {{ $downloadType === 'csv' ? 'Download CSV' : 'Download PDF' }}
            </span>
            <span wire:loading wire:target="confirmDownload">Menyiapkan file…</span>
          </button>
        </div>
      </div>
    </div>
  @endif
</div>
