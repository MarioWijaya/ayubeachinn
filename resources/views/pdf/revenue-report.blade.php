<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Pendapatan</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
    .muted { color: #475569; }
    .header { margin-bottom: 16px; }
    .title { font-size: 18px; font-weight: 700; margin: 0; }
    .meta { margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: top; }
    th { background: #f8fafc; text-align: left; }
    .right { text-align: right; }
    .total { margin-top: 12px; font-weight: 700; }
  </style>
</head>
<body>
  <div class="header">
    <p class="title">Laporan Pendapatan — Ayu Beach Inn</p>
    <div class="meta muted">
      Periode: {{ $fromLabel }} s/d {{ $toLabel }}
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 60px;">ID</th>
        <th>Tamu</th>
        <th style="width: 90px;">Kamar</th>
        <th style="width: 95px;">Check-in</th>
        <th style="width: 95px;">Check-out</th>
        <th style="width: 120px;" class="right">Pendapatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $row)
        @php
          $total = (int) ($row->total_final ?? 0);
        @endphp
        <tr>
          <td><strong>#{{ $row->id }}</strong></td>
          <td>{{ $row->nama_tamu }}</td>
          <td>{{ $row->nomor_kamar ?? '-' }}</td>
          <td>{{ $row->tanggal_check_in }}</td>
          <td>{{ $row->tanggal_check_out }}</td>
          <td class="right"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="muted" style="text-align:center; padding: 16px;">
            Tidak ada data pendapatan.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="total right">
    Total: Rp {{ number_format($grandTotal, 0, ',', '.') }}
  </div>

  <div class="muted" style="margin-top: 18px; font-size: 10px;">
    Dicetak pada: {{ now()->format('d/m/Y H:i') }}
  </div>
</body>
</html>
