<?php

namespace App\Livewire\Backoffice\Reports\Revenue;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;


#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $from = '';
    public string $to = '';
    public string $downloadType = '';
    public bool $showPreviewModal = false;
    public bool $previewReady = false;
    public array $previewRows = [];
    public int $previewCount = 0;
    public int $previewGrandTotal = 0;
    public bool $isAdmin = false;
    public bool $rangeClamped = false;

    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->isAdmin = (auth()->user()->level ?? '') === 'admin';

        if ($this->isAdmin) {
            $this->applyAdminRange($this->from !== '' || $this->to !== '');
            return;
        }

        $this->from = $this->from !== '' ? $this->from : now()->subDays(29)->toDateString();
        $this->to = $this->to !== '' ? $this->to : now()->toDateString();
    }

    public function updatedFrom(): void
    {
        $this->resetPreviewState();

        if ($this->isAdmin) {
            $this->applyAdminRange(true);
        }

        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->resetPreviewState();

        if ($this->isAdmin) {
            $this->applyAdminRange(true);
        }

        $this->resetPage();
    }

    public function render(): View
    {
        $rows = $this->baseQuery()
            ->paginate(10)
            ->withQueryString();

        $grandTotal = $this->grandTotal();

        return view('livewire.backoffice.reports.revenue.index', [
            'rows' => $rows,
            'grandTotal' => $grandTotal,
        ]);
    }

    public function requestDownload(string $type): void
    {
        if (!in_array($type, ['pdf', 'csv'], true)) {
            $this->addError('downloadType', 'Tipe download tidak valid.');
            return;
        }

        $this->ensureAdminRange();

        $this->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $this->downloadType = $type;

        $baseQuery = $this->baseQuery();
        $this->previewCount = $this->transactionCount();
        $previewItems = (clone $baseQuery)->limit(20)->get();

        $this->previewRows = $previewItems->map(function ($row) {
            return [
                'id' => $row->id,
                'nama_tamu' => $row->nama_tamu,
                'nomor_kamar' => $row->nomor_kamar ?? '-',
                'tanggal_check_in' => $row->tanggal_check_in,
                'tanggal_check_out' => $row->tanggal_check_out,
                'total_final' => (int) ($row->total_final ?? 0),
                'checkout_at' => $row->checkout_at,
            ];
        })->all();

        $this->previewGrandTotal = $this->grandTotal();
        $this->previewReady = true;
        $this->showPreviewModal = true;
    }

    public function confirmDownload()
    {
        $this->ensureAdminRange();

        if (!$this->previewReady || !in_array($this->downloadType, ['pdf', 'csv'], true)) {
            session()->flash('error', 'Silakan lihat preview terlebih dahulu sebelum download.');
            return;
        }

        return $this->downloadType === 'pdf'
            ? $this->downloadPdf()
            : $this->downloadCsv();
    }

    public function closePreview(): void
    {
        $this->resetPreviewState();
    }

    public function downloadPdf()
    {
        $this->ensureAdminRange();

        if (!$this->previewReady) {
            session()->flash('error', 'Silakan klik Preview terlebih dahulu sebelum download.');
            return;
        }

        $this->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $items = $this->baseQuery()->get();
        $grandTotal = $this->grandTotal();

        $fromLabel = Carbon::parse($this->from)->format('d-m-Y');
        $toLabel = Carbon::parse($this->to)->format('d-m-Y');

        $pdf = Pdf::loadView('pdf.revenue-report', [
            'items' => $items,
            'grandTotal' => $grandTotal,
            'fromLabel' => $fromLabel,
            'toLabel' => $toLabel,
        ])->setPaper('a4', 'portrait');

        $filename = "laporan-pendapatan_{$fromLabel}_sampai_{$toLabel}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    public function downloadCsv()
    {
        $this->ensureAdminRange();

        if (!$this->previewReady) {
            session()->flash('error', 'Silakan klik Preview terlebih dahulu sebelum download.');
            return;
        }

        $this->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $fromLabel = Carbon::parse($this->from)->format('d-m-Y');
        $toLabel = Carbon::parse($this->to)->format('d-m-Y');
        $filename = "laporan-pendapatan_{$fromLabel}_sampai_{$toLabel}.csv";

        $query = $this->exportQuery();

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'booking_id',
                'nama_tamu',
                'kamar',
                'check_in',
                'check_out',
                'pendapatan',
                'layanan',
            ]);

            foreach ($query->cursor() as $row) {
                $layanan = trim(($row->layanan_nama ?? '') . (($row->layanan_qty ?? 0) > 0 ? ' x' . $row->layanan_qty : ''));

                fputcsv($handle, [
                    $row->id,
                    $row->nama_tamu,
                    $row->nomor_kamar ?? '-',
                    $row->tanggal_check_in,
                    $row->tanggal_check_out,
                    (int) ($row->total_final ?? 0),
                    $layanan !== '' ? $layanan : '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Query utama untuk tabel & PDF (harus identik).
     * Range yang dipakai mengikuti tanggal checkout.
     */
    private function baseQuery()
    {
        return DB::table('booking')
            ->leftJoin('kamar', 'kamar.id', '=', 'booking.kamar_id')
            ->select(
                'booking.id',
                'booking.nama_tamu',
                'booking.tanggal_check_in',
                'booking.tanggal_check_out',
                'booking.total_final',
                'kamar.nomor_kamar',
                'booking.checkout_at'
            )
            ->where('booking.status_booking', 'selesai')
            ->whereNotNull('booking.checkout_at')
            ->whereBetween('booking.checkout_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->groupBy('booking.id', 'booking.nama_tamu', 'booking.tanggal_check_in', 'booking.tanggal_check_out', 'booking.total_final', 'kamar.nomor_kamar', 'booking.checkout_at')
            ->orderByDesc('booking.id');
    }

    private function exportQuery()
    {
        return DB::table('booking')
            ->leftJoin('kamar', 'kamar.id', '=', 'booking.kamar_id')
            ->leftJoin('booking_layanan as bl', 'bl.booking_id', '=', 'booking.id')
            ->leftJoin('layanan as l', 'l.id', '=', 'bl.layanan_id')
            ->select(
                'booking.id',
                'booking.nama_tamu',
                'booking.tanggal_check_in',
                'booking.tanggal_check_out',
                'booking.total_final',
                'kamar.nomor_kamar',
                DB::raw('COALESCE(l.nama, "") as layanan_nama'),
                DB::raw('COALESCE(bl.qty, 0) as layanan_qty')
            )
            ->where('booking.status_booking', 'selesai')
            ->whereNotNull('booking.checkout_at')
            ->whereBetween('booking.checkout_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->orderByDesc('booking.id');
    }

    private function resetPreviewState(): void
    {
        $this->downloadType = '';
        $this->showPreviewModal = false;
        $this->previewReady = false;
        $this->previewRows = [];
        $this->previewCount = 0;
        $this->previewGrandTotal = 0;
    }

    private function grandTotal(): int
    {
        return (int) DB::table('booking')
            ->where('status_booking', 'selesai')
            ->whereNotNull('checkout_at')
            ->whereBetween('checkout_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->sum('total_final');
    }

    private function transactionCount(): int
    {
        return (int) DB::table('booking')
            ->where('status_booking', 'selesai')
            ->whereNotNull('checkout_at')
            ->whereBetween('checkout_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->count('id');
    }

    private function applyAdminRange(bool $markClamped): void
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $changed = $this->from !== $start || $this->to !== $end;

        $this->from = $start;
        $this->to = $end;

        if ($markClamped && $changed) {
            $this->rangeClamped = true;
        }
    }

    private function ensureAdminRange(): void
    {
        if (!$this->isAdmin) {
            return;
        }

        $this->applyAdminRange(false);
    }
}
