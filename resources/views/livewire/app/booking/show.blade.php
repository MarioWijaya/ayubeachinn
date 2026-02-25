@php
  $role = auth()->user()->level ?? 'pegawai';
  $routePrefix = in_array($role, ['owner', 'admin', 'pegawai'], true) ? $role : 'pegawai';
  $fmtDash = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') : '-';
  $fmtDateTime = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y H:i') : '-';
@endphp
<div class="mx-auto max-w-5xl space-y-6">
  <x-page-header
    title="Detail Booking"
    subtitle="Informasi lengkap booking tamu."
  >
    <x-slot:rightSlot>
      <div class="flex w-full items-center gap-2 sm:w-auto">
        <a
          wire:navigate
          href="{{ route($routePrefix.'.booking.index') }}"
          class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50 sm:w-auto"
        >
          ← Kembali
        </a>
        <a
          wire:navigate
          href="{{ route($routePrefix.'.booking.edit', $booking->id) }}"
          class="inline-flex w-full items-center justify-center rounded-lg bg-[#854836] px-3 py-2 text-sm font-semibold text-white hover:opacity-95 sm:w-auto"
        >
          Edit
        </a>
      </div>
    </x-slot:rightSlot>
  </x-page-header>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
      <div class="text-sm font-semibold text-slate-800">Ringkasan Booking</div>
      <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tamu</div>
          <div class="mt-1 text-sm font-semibold text-slate-900">{{ $booking->nama_tamu }}</div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">No. Telepon</div>
          <div class="mt-1 text-sm font-semibold text-slate-900">{{ $booking->no_telp_tamu ?: '-' }}</div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</div>
          <div class="mt-1 text-sm font-semibold text-slate-900">{{ $booking->status_booking }}</div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kamar</div>
          <div class="mt-1 text-sm font-semibold text-slate-900">
            {{ $booking->nomor_kamar }} ({{ $booking->tipe_kamar }})
          </div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pegawai</div>
          <div class="mt-1 text-sm font-semibold text-slate-900">
            {{ $booking->nama_pegawai }} ({{ $booking->username_pegawai }})
          </div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Check-in</div>
          <div class="mt-1 text-sm font-semibold text-slate-900">{{ $fmtDash($booking->tanggal_check_in) }}</div>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Check-out</div>
          <div class="mt-1 text-sm font-semibold text-slate-900">{{ $fmtDash($booking->tanggal_check_out) }}</div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="text-sm font-semibold text-slate-800">Biaya</div>
      <div class="mt-4 space-y-3 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Tarif Kamar</span>
          <span class="font-semibold text-slate-900">
            Rp {{ number_format((int) ($booking->tarif ?? 0), 0, ',', '.') }}
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Harga Dibayar</span>
          <span class="font-semibold text-slate-900">
            Rp {{ number_format((int) ($booking->harga_kamar ?? 0), 0, ',', '.') }}
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Durasi</span>
          <span class="font-semibold text-slate-900">
            {{ (int) ($booking->nights ?? 0) }} malam
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Subtotal Kamar</span>
          <span class="font-semibold text-slate-900">
            Rp {{ number_format((int) ($booking->room_subtotal ?? 0), 0, ',', '.') }}
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Layanan</span>
          <span class="font-semibold text-slate-900">
            {{ $booking->layanan_nama ?? 'Tidak ada' }}
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Subtotal Layanan</span>
          <span class="font-semibold text-slate-900">
            Rp {{ number_format((int) ($booking->layanan_subtotal ?? 0), 0, ',', '.') }}
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Total Bayar</span>
          <span class="font-semibold text-slate-900">
            Rp {{ number_format((int) ($booking->total_bayar ?? 0), 0, ',', '.') }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="text-sm font-semibold text-slate-800">Catatan</div>
    <p class="mt-2 text-sm text-slate-600">
      {{ $booking->catatan ?: '-' }}
    </p>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
      <div class="text-sm font-semibold text-slate-800">Riwayat Perpanjangan</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white border-b border-slate-200">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-medium">Out Lama</th>
            <th class="px-4 py-3 font-medium">Out Baru</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium">Alasan Batal</th>
            <th class="px-4 py-3 font-medium">Dibuat</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          @forelse($riwayatPerpanjangan as $p)
            <tr>
              <td class="px-4 py-3 text-slate-700">{{ $fmtDash($p->tanggal_check_out_lama) }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $fmtDash($p->tanggal_check_out_baru) }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $p->status_perpanjangan }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $p->alasan_batal ?? '-' }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $fmtDateTime($p->created_at) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada perpanjangan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
