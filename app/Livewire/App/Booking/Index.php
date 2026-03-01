<?php

namespace App\Livewire\App\Booking;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';

    public string $status = '';

    public string $from = '';

    public string $to = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'status' => ['except' => ''],
        'from' => ['except' => ''],
        'to' => ['except' => ''],
    ];

    public function mount(): void
    {
        if ($this->from === '' && $this->to === '') {
            $this->from = now()->toDateString();
            $this->to = now()->addDays(29)->toDateString();
        }

        $this->ensureChronologicalRange();
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->ensureChronologicalRange();
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->ensureChronologicalRange();
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->status = '';
        $this->from = now()->toDateString();
        $this->to = now()->addDays(29)->toDateString();
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    public function setQuickRange(string $range): void
    {
        $today = now()->toDateString();

        $to = match ($range) {
            'today' => $today,
            'week' => now()->addDays(6)->toDateString(),
            'month' => now()->addDays(29)->toDateString(),
            default => null,
        };

        if ($to === null) {
            return;
        }

        $this->from = $today;
        $this->to = $to;
        $this->resetPage();
    }

    private function ensureChronologicalRange(): void
    {
        if ($this->from === '' || $this->to === '') {
            return;
        }

        if ($this->to < $this->from) {
            $this->to = $this->from;
        }
    }

    public function render(): View
    {
        $hasTotalFinal = Schema::hasColumn('booking', 'total_final');

        $select = [
            'booking.*',
            'kamar.nomor_kamar',
            'kamar.tipe_kamar',
            'kamar.tarif',
            DB::raw('COALESCE(pegawai.nama, "") as nama_pegawai'),
            DB::raw('COALESCE(pegawai.username, "") as username_pegawai'),
            DB::raw('COALESCE(l.nama, "") as layanan_nama'),
            DB::raw('COALESCE(bl.qty, 0) as layanan_qty'),
            DB::raw('COALESCE(bl.subtotal, 0) as layanan_subtotal'),
        ];

        if ($hasTotalFinal) {
            $select[] = 'booking.total_final';
        } else {
            $select[] = DB::raw('NULL as total_final');
        }

        $query = DB::table('booking')
            ->join('kamar', 'booking.kamar_id', '=', 'kamar.id')
            ->leftJoin('users as pegawai', 'booking.pegawai_id', '=', 'pegawai.id')
            ->leftJoin('booking_layanan as bl', 'bl.booking_id', '=', 'booking.id')
            ->leftJoin('layanan as l', 'l.id', '=', 'bl.layanan_id')
            ->select($select);

        if ($this->q !== '') {
            $q = $this->q;
            $query->where(function ($w) use ($q) {
                $w->where('booking.id', 'like', "%{$q}%")
                    ->orWhere('booking.nama_tamu', 'like', "%{$q}%")
                    ->orWhere('kamar.nomor_kamar', 'like', "%{$q}%")
                    ->orWhere('kamar.tipe_kamar', 'like', "%{$q}%");
            });
        }

        if ($this->status !== '') {
            $query->where('booking.status_booking', $this->status);
        }

        if ($this->from !== '' && $this->to !== '') {
            $query->whereDate('booking.tanggal_check_in', '<=', $this->to)
                ->whereDate('booking.tanggal_check_out', '>=', $this->from);
        }

        $booking = $query->orderByDesc('booking.id')->paginate(10)->withQueryString();

        $booking->getCollection()->transform(function ($item) {
            $checkIn = Carbon::parse($item->tanggal_check_in)->startOfDay();
            $checkOut = Carbon::parse($item->tanggal_check_out)->startOfDay();
            $nights = max(1, $checkIn->diffInDays($checkOut));

            $hargaPerMalam = (int) ($item->harga_kamar ?? 0);
            $layananSubtotal = (int) ($item->layanan_subtotal ?? 0);

            $item->nights = $nights;
            $item->room_subtotal = $nights * $hargaPerMalam;
            $totalBayar = $item->room_subtotal + $layananSubtotal;

            if (($item->status_booking ?? null) === 'selesai' && $item->total_final !== null) {
                $totalBayar = (int) $item->total_final;
            }

            $item->total_bayar = $totalBayar;

            return $item;
        });

        return view('livewire.app.booking.index', compact('booking'));
    }
}
