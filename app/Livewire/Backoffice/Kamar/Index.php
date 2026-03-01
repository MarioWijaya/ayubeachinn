<?php

namespace App\Livewire\Backoffice\Kamar;

use App\Models\Kamar;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Data Kamar')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $q = '';

    public string $status = '';

    public string $checkFrom = '';

    public string $checkTo = '';

    public string $quickRange = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'status' => ['except' => ''],
        'checkFrom' => ['except' => ''],
        'checkTo' => ['except' => ''],
        'quickRange' => ['except' => ''],
    ];

    public function mount(): void
    {
        $today = now()->toDateString();
        $this->checkFrom = $this->checkFrom !== '' ? $this->checkFrom : $today;
        $this->checkTo = $this->checkTo !== '' ? $this->checkTo : $today;
        $this->ensureChronologicalRange();
        if ($this->quickRange === '' && $this->checkFrom === $today && $this->checkTo === $today) {
            $this->quickRange = 'today';
        }
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCheckFrom(): void
    {
        $this->ensureChronologicalRange();
        $this->quickRange = '';
        $this->resetPage();
    }

    public function updatedCheckTo(): void
    {
        $this->ensureChronologicalRange();
        $this->quickRange = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->status = '';
        $today = now()->toDateString();
        $this->checkFrom = $today;
        $this->checkTo = $today;
        $this->quickRange = 'today';
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

        $this->checkFrom = $today;
        $this->checkTo = $to;
        $this->quickRange = $range;
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
        $today = Carbon::now()->toDateString();
        $from = $this->checkFrom !== '' ? $this->checkFrom : $today;
        $to = $this->checkTo !== '' ? $this->checkTo : $today;
        $toExclusive = Carbon::parse($to)->addDay()->toDateString();

        $query = Kamar::query()
            ->leftJoin('kamar_perbaikan as kp', 'kp.kamar_id', '=', 'kamar.id')
            ->select([
                'kamar.*',
                'kp.mulai as perbaikan_mulai',
                'kp.selesai as perbaikan_selesai',
                'kp.catatan as perbaikan_catatan',
            ])
            ->selectSub(function ($sub) use ($today) {
                $sub->from('kamar_perbaikan as kpr')
                    ->selectRaw('1')
                    ->whereColumn('kpr.kamar_id', 'kamar.id')
                    ->whereDate('kpr.mulai', '<=', $today)
                    ->where(function ($inner) use ($today) {
                        $inner->whereNull('kpr.selesai')
                            ->orWhereDate('kpr.selesai', '>=', $today);
                    })
                    ->limit(1);
            }, 'has_perbaikan_today')
            ->selectSub(function ($sub) use ($today) {
                $sub->from('booking as b')
                    ->select('b.id')
                    ->whereColumn('b.kamar_id', 'kamar.id')
                    ->whereIn('b.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('b.tanggal_check_in', '<=', $today)
                    ->whereDate('b.tanggal_check_out', '>', $today)
                    ->orderBy('b.tanggal_check_in')
                    ->limit(1);
            }, 'active_booking_id')
            ->selectSub(function ($sub) use ($today) {
                $sub->from('booking as b')
                    ->select('b.nama_tamu')
                    ->whereColumn('b.kamar_id', 'kamar.id')
                    ->whereIn('b.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('b.tanggal_check_in', '<=', $today)
                    ->whereDate('b.tanggal_check_out', '>', $today)
                    ->orderBy('b.tanggal_check_in')
                    ->limit(1);
            }, 'active_booking_nama')
            ->selectSub(function ($sub) use ($today) {
                $sub->from('booking as b')
                    ->select('b.tanggal_check_in')
                    ->whereColumn('b.kamar_id', 'kamar.id')
                    ->whereIn('b.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('b.tanggal_check_in', '<=', $today)
                    ->whereDate('b.tanggal_check_out', '>', $today)
                    ->orderBy('b.tanggal_check_in')
                    ->limit(1);
            }, 'active_booking_check_in')
            ->selectSub(function ($sub) use ($today) {
                $sub->from('booking as b')
                    ->select('b.tanggal_check_out')
                    ->whereColumn('b.kamar_id', 'kamar.id')
                    ->whereIn('b.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('b.tanggal_check_in', '<=', $today)
                    ->whereDate('b.tanggal_check_out', '>', $today)
                    ->orderBy('b.tanggal_check_in')
                    ->limit(1);
            }, 'active_booking_check_out')
            ->selectSub(function ($sub) use ($today) {
                $sub->from('booking as b')
                    ->select('b.id')
                    ->whereColumn('b.kamar_id', 'kamar.id')
                    ->whereIn('b.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('b.tanggal_check_in', '>=', $today)
                    ->orderBy('b.tanggal_check_in')
                    ->limit(1);
            }, 'next_booking_id')
            ->selectSub(function ($sub) use ($today) {
                $sub->from('booking as b')
                    ->select('b.nama_tamu')
                    ->whereColumn('b.kamar_id', 'kamar.id')
                    ->whereIn('b.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('b.tanggal_check_in', '>=', $today)
                    ->orderBy('b.tanggal_check_in')
                    ->limit(1);
            }, 'next_booking_nama')
            ->selectSub(function ($sub) use ($today) {
                $sub->from('booking as b')
                    ->select('b.tanggal_check_in')
                    ->whereColumn('b.kamar_id', 'kamar.id')
                    ->whereIn('b.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('b.tanggal_check_in', '>=', $today)
                    ->orderBy('b.tanggal_check_in')
                    ->limit(1);
            }, 'next_booking_check_in')
            ->selectSub(function ($sub) use ($today) {
                $sub->from('booking as b')
                    ->select('b.tanggal_check_out')
                    ->whereColumn('b.kamar_id', 'kamar.id')
                    ->whereIn('b.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('b.tanggal_check_in', '>=', $today)
                    ->orderBy('b.tanggal_check_in')
                    ->limit(1);
            }, 'next_booking_check_out')
            ->orderBy('nomor_kamar');

        if ($this->q !== '') {
            $q = $this->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('nomor_kamar', 'like', "%{$q}%")
                    ->orWhere('tipe_kamar', 'like', "%{$q}%");
            });
        }

        if ($this->status !== '') {
            if ($this->status === 'perbaikan') {
                $query->whereExists(function ($sub) use ($today) {
                    $sub->selectRaw('1')
                        ->from('kamar_perbaikan as kpr')
                        ->whereColumn('kpr.kamar_id', 'kamar.id')
                        ->whereDate('kpr.mulai', '<=', $today)
                        ->where(function ($inner) use ($today) {
                            $inner->whereNull('kpr.selesai')
                                ->orWhereDate('kpr.selesai', '>=', $today);
                        });
                });
            } elseif ($this->status === 'terisi') {
                $query->whereExists(function ($exists) use ($today) {
                    $exists->selectRaw('1')
                        ->from('booking as br')
                        ->whereColumn('br.kamar_id', 'kamar.id')
                        ->whereIn('br.status_booking', ['menunggu', 'check_in'])
                        ->whereDate('br.tanggal_check_in', '<=', $today)
                        ->whereDate('br.tanggal_check_out', '>', $today);
                })->whereNotExists(function ($sub) use ($today) {
                    $sub->selectRaw('1')
                        ->from('kamar_perbaikan as kpr')
                        ->whereColumn('kpr.kamar_id', 'kamar.id')
                        ->whereDate('kpr.mulai', '<=', $today)
                        ->where(function ($inner) use ($today) {
                            $inner->whereNull('kpr.selesai')
                                ->orWhereDate('kpr.selesai', '>=', $today);
                        });
                });
            } else {
                $query->whereNotExists(function ($sub) use ($today) {
                    $sub->selectRaw('1')
                        ->from('booking as br')
                        ->whereColumn('br.kamar_id', 'kamar.id')
                        ->whereIn('br.status_booking', ['menunggu', 'check_in'])
                        ->whereDate('br.tanggal_check_in', '<=', $today)
                        ->whereDate('br.tanggal_check_out', '>', $today);
                })->whereNotExists(function ($sub) use ($today) {
                    $sub->selectRaw('1')
                        ->from('kamar_perbaikan as kpr')
                        ->whereColumn('kpr.kamar_id', 'kamar.id')
                        ->whereDate('kpr.mulai', '<=', $today)
                        ->where(function ($inner) use ($today) {
                            $inner->whereNull('kpr.selesai')
                                ->orWhereDate('kpr.selesai', '>=', $today);
                        });
                });
            }
        }

        $kamar = $query->paginate(10);

        return view('livewire.backoffice.kamar.index', compact('kamar'));
    }
}
