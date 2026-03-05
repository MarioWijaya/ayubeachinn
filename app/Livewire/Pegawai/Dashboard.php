<?php

namespace App\Livewire\Pegawai;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use WithPagination;

    public string $q = '';

    public string $tipe = '';

    public string $statusKamar = '';

    public string $checkFrom = '';

    public string $checkTo = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'tipe' => ['except' => ''],
        'statusKamar' => ['except' => ''],
        'checkFrom' => ['except' => ''],
        'checkTo' => ['except' => ''],
    ];

    public function mount(): void
    {
        $today = now()->toDateString();
        $this->checkFrom = $this->checkFrom !== '' ? $this->checkFrom : $today;
        $this->checkTo = $this->checkTo !== '' ? $this->checkTo : $today;
        $this->ensureChronologicalRange();
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedTipe(): void
    {
        $this->resetPage();
    }

    public function updatedStatusKamar(): void
    {
        $this->resetPage();
    }

    public function updatedCheckFrom(): void
    {
        $this->ensureChronologicalRange();
        $this->resetPage();
    }

    public function updatedCheckTo(): void
    {
        $this->ensureChronologicalRange();
        $this->resetPage();
    }

    public function setTipe(string $tipe): void
    {
        $this->tipe = $tipe;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->tipe = '';
        $this->statusKamar = '';
        $today = now()->toDateString();
        $this->checkFrom = $today;
        $this->checkTo = $today;
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    private function ensureChronologicalRange(): void
    {
        if ($this->checkFrom === '' || $this->checkTo === '') {
            return;
        }

        if ($this->checkTo < $this->checkFrom) {
            $this->checkTo = $this->checkFrom;
        }
    }

    public function render(): View
    {
        $ongoingStatuses = ['menunggu', 'check_in'];
        $checkFrom = $this->checkFrom;
        $checkTo = $this->checkTo;

        $tipeList = DB::table('kamar')
            ->select('tipe_kamar', DB::raw('COUNT(*) as total'))
            ->whereNotNull('tipe_kamar')
            ->groupBy('tipe_kamar')
            ->orderBy('tipe_kamar')
            ->get();

        $terisiMap = DB::table('booking')
            ->join('kamar', 'kamar.id', '=', 'booking.kamar_id')
            ->whereIn('booking.status_booking', $ongoingStatuses)
            ->whereDate('booking.tanggal_check_in', '<=', $this->checkTo)
            ->whereDate('booking.tanggal_check_out', '>', $this->checkFrom)
            ->select('kamar.tipe_kamar', DB::raw('COUNT(DISTINCT booking.kamar_id) as terisi'))
            ->groupBy('kamar.tipe_kamar')
            ->pluck('terisi', 'tipe_kamar');

        $perbaikanMap = DB::table('kamar')
            ->join('kamar_perbaikan as kp', function ($join) use ($checkFrom, $checkTo) {
                $join->on('kamar.id', '=', 'kp.kamar_id')
                    ->whereDate('kp.mulai', '<=', $checkTo)
                    ->where(function ($inner) use ($checkFrom) {
                        $inner->whereNull('kp.selesai')
                            ->orWhereDate('kp.selesai', '>=', $checkFrom);
                    });
            })
            ->select('kamar.tipe_kamar', DB::raw('COUNT(DISTINCT kamar.id) as perbaikan'))
            ->groupBy('kamar.tipe_kamar')
            ->pluck('perbaikan', 'tipe_kamar');

        $kamarQ = DB::table('kamar')
            ->leftJoin('booking as b', function ($join) use ($ongoingStatuses) {
                $join->on('b.kamar_id', '=', 'kamar.id')
                    ->whereIn('b.status_booking', $ongoingStatuses)
                    ->whereDate('b.tanggal_check_in', '<=', $this->checkTo)
                    ->whereDate('b.tanggal_check_out', '>', $this->checkFrom);
            })
            ->select([
                'kamar.id',
                'kamar.nomor_kamar',
                'kamar.tipe_kamar',
                DB::raw('CASE WHEN b.id IS NULL THEN 0 ELSE 1 END as is_terisi'),
                'b.nama_tamu',
                'b.tanggal_check_in',
                'b.tanggal_check_out',
                'b.status_booking',
            ])
            ->selectSub(function ($sub) use ($checkFrom, $checkTo) {
                $sub->from('kamar_perbaikan as kp')
                    ->selectRaw('1')
                    ->whereColumn('kp.kamar_id', 'kamar.id')
                    ->whereDate('kp.mulai', '<=', $checkTo)
                    ->where(function ($inner) use ($checkFrom) {
                        $inner->whereNull('kp.selesai')
                            ->orWhereDate('kp.selesai', '>=', $checkFrom);
                    })
                    ->limit(1);
            }, 'is_perbaikan')
            ->groupBy(
                'kamar.id',
                'kamar.nomor_kamar',
                'kamar.tipe_kamar',
                'b.id',
                'b.nama_tamu',
                'b.tanggal_check_in',
                'b.tanggal_check_out',
                'b.status_booking'
            );

        if ($this->tipe !== '') {
            $kamarQ->where('kamar.tipe_kamar', $this->tipe);
        }

        if ($this->q !== '') {
            $kamarQ->where(function ($w) {
                $w->where('kamar.nomor_kamar', 'like', '%'.$this->q.'%')
                    ->orWhere('kamar.tipe_kamar', 'like', '%'.$this->q.'%');
            });
        }

        if ($this->statusKamar === 'tersedia') {
            $kamarQ->whereNull('b.id')
                ->whereNotExists(function ($sub) use ($checkFrom, $checkTo) {
                    $sub->selectRaw('1')
                        ->from('kamar_perbaikan as kp')
                        ->whereColumn('kp.kamar_id', 'kamar.id')
                        ->whereDate('kp.mulai', '<=', $checkTo)
                        ->where(function ($inner) use ($checkFrom) {
                            $inner->whereNull('kp.selesai')
                                ->orWhereDate('kp.selesai', '>=', $checkFrom);
                        });
                });
        }

        if ($this->statusKamar === 'terisi') {
            $kamarQ->whereNotNull('b.id')
                ->whereNotExists(function ($sub) use ($checkFrom, $checkTo) {
                    $sub->selectRaw('1')
                        ->from('kamar_perbaikan as kp')
                        ->whereColumn('kp.kamar_id', 'kamar.id')
                        ->whereDate('kp.mulai', '<=', $checkTo)
                        ->where(function ($inner) use ($checkFrom) {
                            $inner->whereNull('kp.selesai')
                                ->orWhereDate('kp.selesai', '>=', $checkFrom);
                        });
                });
        }

        if ($this->statusKamar === 'perbaikan') {
            $kamarQ->whereExists(function ($sub) use ($checkFrom, $checkTo) {
                $sub->selectRaw('1')
                    ->from('kamar_perbaikan as kp')
                    ->whereColumn('kp.kamar_id', 'kamar.id')
                    ->whereDate('kp.mulai', '<=', $checkTo)
                    ->where(function ($inner) use ($checkFrom) {
                        $inner->whereNull('kp.selesai')
                            ->orWhereDate('kp.selesai', '>=', $checkFrom);
                    });
            });
        }

        $kamarPage = $kamarQ
            ->orderByRaw('CAST(kamar.nomor_kamar AS UNSIGNED) ASC')
            ->paginate(10);

        return view('livewire.pegawai.dashboard', compact(
            'tipeList',
            'terisiMap',
            'perbaikanMap',
            'kamarPage'
        ));
    }
}
