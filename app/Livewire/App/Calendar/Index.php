<?php

namespace App\Livewire\App\Calendar;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public array $days = [];      // key: Y-m-d -> summary + bookings
    public int $totalRooms = 0;

    public function mount(): void
    {
        $this->totalRooms = (int) DB::table('kamar')
            ->count();

        // default: bulan sekarang
        $start = now()->startOfMonth()->toDateString();
        $end   = now()->endOfMonth()->toDateString();

        $this->days = $this->buildDays($start, $end);
    }

    // dipanggil dari JS saat user pindah bulan (prev/next)
    public function loadRange(string $start, string $end): void
    {
        $this->totalRooms = (int) DB::table('kamar')
            ->count();

        $this->days = $this->buildDays($start, $end);

        // kirim ke browser supaya FullCalendar update tanpa reload
        $this->dispatch('calendar-days-updated', days: $this->days, totalRooms: $this->totalRooms);
    }

    private function buildDays(string $start, string $end): array
    {
        $startD = Carbon::parse($start)->startOfDay();
        $endD   = Carbon::parse($end)->endOfDay();

        $totalBaseRooms = (int) DB::table('kamar')->count();
        $maintenanceDiffs = [];
        if (Schema::hasTable('kamar_perbaikan')) {
            $maintenanceRows = DB::table('kamar_perbaikan')
                ->select(['kamar_id', 'mulai', 'selesai'])
                ->whereDate('mulai', '<=', $endD->toDateString())
                ->where(function ($query) use ($startD) {
                    $query->whereNull('selesai')
                        ->orWhereDate('selesai', '>=', $startD->toDateString());
                })
                ->get();

            foreach ($maintenanceRows as $row) {
                // Perbaikan aktif jika mulai <= D <= selesai (selesai inklusif).
                $rangeStart = Carbon::parse($row->mulai)->toDateString();
                $rangeEnd = $row->selesai
                    ? Carbon::parse($row->selesai)->addDay()->toDateString()
                    : $endD->copy()->addDay()->toDateString();

                $rangeStart = max($rangeStart, $startD->toDateString());
                $rangeEnd = min($rangeEnd, $endD->copy()->addDay()->toDateString());

                if ($rangeStart >= $rangeEnd) {
                    continue;
                }

                // Asumsi: tidak ada overlap per kamar di rentang yang sama.
                $maintenanceDiffs[$rangeStart] = ($maintenanceDiffs[$rangeStart] ?? 0) + 1;
                $maintenanceDiffs[$rangeEnd] = ($maintenanceDiffs[$rangeEnd] ?? 0) - 1;
            }
        }

        // ambil semua booking yang “bersinggungan” dengan range bulan
        $rows = DB::table('booking as b')
            ->join('kamar as k', 'k.id', '=', 'b.kamar_id')
            ->select([
                'b.id',
                'b.nama_tamu',
                'b.status_booking',
                'b.tanggal_check_in',
                'b.tanggal_check_out',
                'k.nomor_kamar',
                'k.tipe_kamar',
            ])
            ->whereDate('b.tanggal_check_in', '<=', $endD->toDateString())
            ->whereDate('b.tanggal_check_out', '>=', $startD->toDateString())
            ->get();

        // siapkan struktur tanggal
        $days = [];
        $cursor = $startD->copy();
        $activeMaintenance = 0;
        while ($cursor->lte($endD)) {
            $key = $cursor->toDateString();
            $activeMaintenance += $maintenanceDiffs[$key] ?? 0;
            $maintenanceCount = max(0, $activeMaintenance);
            // total_available(D) = total_base - maintenance_count
            $totalRoomsForDate = max(0, $totalBaseRooms - $maintenanceCount);
            $days[$key] = [
                'date' => $key,
                'total_rooms' => $totalRoomsForDate,
                'checkin_count' => 0,
                'checkout_count' => 0,
                'occupied_count' => 0,
                'empty_count' => $totalRoomsForDate,
                'checkins' => [],
                'checkouts' => [],
                'occupied' => [],
            ];
            $cursor->addDay();
        }

        foreach ($rows as $r) {
            $ci = Carbon::parse($r->tanggal_check_in)->toDateString();
            $co = Carbon::parse($r->tanggal_check_out)->toDateString();

            // checkin/checkouts (hanya tanggal pas)
            if (isset($days[$ci])) {
                $days[$ci]['checkin_count']++;
                $days[$ci]['checkins'][] = $this->mapBooking($r);
            }
            if (isset($days[$co])) {
                $days[$co]['checkout_count']++;
                $days[$co]['checkouts'][] = $this->mapBooking($r);
            }

            // occupied: setiap hari d dimana check_in <= d < check_out
            // batasi ke range bulan
            $from = Carbon::parse(max($ci, $startD->toDateString()));
            $exclusiveEnd = Carbon::parse($co)->subDay()->toDateString();
            $to = Carbon::parse(min($exclusiveEnd, $endD->toDateString()));

            $d = $from->copy();
            while ($d->lte($to)) {
                $k = $d->toDateString();
                if (isset($days[$k])) {
                    $days[$k]['occupied'][] = $this->mapBooking($r);
                }
                $d->addDay();
            }
        }

        // hitung occupied & empty
        foreach ($days as $k => $info) {
            // occupied_count = jumlah kamar unik terisi hari itu
            $uniqueRooms = collect($info['occupied'])->pluck('nomor_kamar')->unique()->count();
            $days[$k]['occupied_count'] = $uniqueRooms;
            $days[$k]['empty_count'] = max(0, ($info['total_rooms'] ?? 0) - $uniqueRooms);
        }

        return $days;
    }

    private function mapBooking($r): array
    {
        return [
            'id' => (int) $r->id,
            'nama_tamu' => $r->nama_tamu,
            'status_booking' => $r->status_booking,
            'nomor_kamar' => $r->nomor_kamar,
            'tipe_kamar' => $r->tipe_kamar,
            'tanggal_check_in' => Carbon::parse($r->tanggal_check_in)->format('d-m-Y'),
            'tanggal_check_out' => Carbon::parse($r->tanggal_check_out)->format('d-m-Y'),
        ];
    }

    public function render()
    {
        return view('livewire.app.calendar.index', [
            'daysJson' => $this->days,
            'totalRooms' => $this->totalRooms,
        ]);
    }
}
