<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use App\Http\Requests\DashboardChartRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardChartController extends Controller
{
    public function data(DashboardChartRequest $request): JsonResponse
    {
        [$start, $end] = $this->resolveDateRange($request);
        $dateRange = $this->generateDays($start, $end);
        $today = now()->toDateString();

        $layananSubquery = DB::table('booking_layanan')
            ->select('booking_id', DB::raw('SUM(subtotal) as layanan_subtotal'))
            ->groupBy('booking_id');

        $amountExpression = $this->resolveAmountExpression();

        $activeRoomIds = $this->resolveActiveRoomIds($today);
        $totalRooms = $activeRoomIds->count();

        $activeBookings = DB::table('booking')
            ->select(['kamar_id', 'tanggal_check_in', 'tanggal_check_out'])
            ->whereIn('kamar_id', $activeRoomIds)
            ->whereIn('status_booking', ['menunggu', 'check_in'])
            ->whereDate('tanggal_check_in', '<=', $end->toDateString())
            ->whereDate('tanggal_check_out', '>', $start->toDateString())
            ->get();

        $occupiedSeries = [];
        $emptySeries = [];
        $labels = [];

        $diffs = [];
        foreach ($activeBookings as $booking) {
            $checkIn = Carbon::parse($booking->tanggal_check_in)->toDateString();
            $checkOut = Carbon::parse($booking->tanggal_check_out)->toDateString();

            $diffs[$checkIn] = ($diffs[$checkIn] ?? 0) + 1;
            $diffs[$checkOut] = ($diffs[$checkOut] ?? 0) - 1;
        }

        $currentOccupied = 0;
        foreach ($dateRange as $date) {
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            $currentOccupied += $diffs[$date] ?? 0;
            $occupiedRooms = max(0, $currentOccupied);
            $occupiedSeries[] = $occupiedRooms;
            $emptySeries[] = max(0, $totalRooms - $occupiedRooms);
        }

        $granularity = $this->resolveGranularity($start, $end);
        if ($granularity !== 'day') {
            [$labels, $occupiedSeries, $emptySeries] = $this->aggregatePeriod($dateRange, $occupiedSeries, $emptySeries, $granularity);
        }

        // Okupansi harian: kamar unik yang sedang ditempati hari ini (status check_in).
        $occupiedToday = DB::table('booking')
            ->whereIn('kamar_id', $activeRoomIds)
            ->where('status_booking', 'check_in')
            ->whereDate('tanggal_check_in', '<=', $today)
            ->whereDate('tanggal_check_out', '>', $today)
            ->distinct('kamar_id')
            ->count('kamar_id');
        $occupancyRate = $totalRooms > 0
            ? (int) round(($occupiedToday / $totalRooms) * 100)
            : 0;

        $bookingsCount = DB::table('booking')
            ->whereDate('tanggal_check_in', '<=', $end->toDateString())
            ->whereDate('tanggal_check_out', '>=', $start->toDateString())
            ->count();

        $dateColumn = $this->resolveRevenueDateColumn();

        $revenueRows = DB::table('booking')
            ->leftJoinSub($layananSubquery, 'bl', 'bl.booking_id', '=', 'booking.id')
            ->select([$dateColumn])
            ->selectRaw($amountExpression . ' as total_amount')
            ->whereIn('status_booking', ['selesai', 'check_out'])
            ->whereDate($dateColumn, '>=', $start->toDateString())
            ->whereDate($dateColumn, '<=', $end->toDateString())
            ->get();

        $revenueByDate = collect($dateRange)
            ->mapWithKeys(fn ($date) => [$date => 0])
            ->all();

        foreach ($revenueRows as $row) {
            $dateValue = $row->{$dateColumn} ?? null;
            if (!$dateValue) {
                continue;
            }

            $dateKey = Carbon::parse($dateValue)->toDateString();
            if (!array_key_exists($dateKey, $revenueByDate)) {
                continue;
            }

            $revenueByDate[$dateKey] += (int) ($row->total_amount ?? 0);
        }

        $revenueLabels = array_map(
            fn ($date) => Carbon::parse($date)->translatedFormat('d M'),
            array_keys($revenueByDate)
        );
        $revenueTotals = array_values($revenueByDate);

        if ($granularity !== 'day') {
            [$revenueLabels, $revenueTotals] = $this->aggregatePeriodTotals($revenueByDate, $granularity);
        }

        $statusCounts = DB::table('booking')
            ->select('status_booking', DB::raw('COUNT(*) as total'))
            ->whereDate('tanggal_check_in', '<=', $end->toDateString())
            ->whereDate('tanggal_check_out', '>=', $start->toDateString())
            ->groupBy('status_booking')
            ->pluck('total', 'status_booking');

        $statusLabels = ['menunggu', 'check_in', 'check_out', 'selesai', 'batal'];
        $statusTotals = array_map(
            fn ($status) => (int) ($statusCounts[$status] ?? 0),
            $statusLabels
        );

        $topRooms = DB::table('booking')
            ->join('kamar', 'booking.kamar_id', '=', 'kamar.id')
            ->select('kamar.nomor_kamar', 'kamar.tipe_kamar', DB::raw('COUNT(*) as total'))
            ->whereDate('booking.tanggal_check_in', '<=', $end->toDateString())
            ->whereDate('booking.tanggal_check_out', '>=', $start->toDateString())
            ->groupBy('kamar.nomor_kamar', 'kamar.tipe_kamar')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topRoomLabels = $topRooms->map(
            fn ($room) => trim($room->nomor_kamar . ' ' . $room->tipe_kamar)
        )->all();

        $topRoomTotals = $topRooms->pluck('total')->map(fn ($total) => (int) $total)->all();

        $latestBookings = DB::table('booking')
            ->join('kamar', 'booking.kamar_id', '=', 'kamar.id')
            ->leftJoinSub($layananSubquery, 'bl', 'bl.booking_id', '=', 'booking.id')
            ->select([
                'booking.id',
                'booking.nama_tamu',
                'booking.tanggal_check_in',
                'booking.tanggal_check_out',
                'booking.status_booking',
                'kamar.nomor_kamar',
                'kamar.tipe_kamar',
            ])
            ->selectRaw($amountExpression . ' as total_amount')
            ->whereDate('booking.tanggal_check_in', '<=', $end->toDateString())
            ->whereDate('booking.tanggal_check_out', '>=', $start->toDateString())
            ->orderByDesc('booking.created_at')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'nama_tamu' => (string) $row->nama_tamu,
                'kamar' => trim($row->nomor_kamar . ' ' . $row->tipe_kamar),
                'check_in' => Carbon::parse($row->tanggal_check_in)->format('d-m-Y'),
                'check_out' => Carbon::parse($row->tanggal_check_out)->format('d-m-Y'),
                'status' => (string) $row->status_booking,
                'total' => (int) ($row->total_amount ?? 0),
            ]);

        return response()->json([
            'range' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            'stats' => [
                'total_rooms' => $totalRooms,
                'occupied_today' => $occupiedToday,
                'occupancy_rate' => $occupancyRate,
                'bookings_count' => $bookingsCount,
                'revenue_total' => array_sum($revenueByDate),
            ],
            'charts' => [
                'occupancy' => [
                    'labels' => $labels,
                    'occupied' => $occupiedSeries,
                    'empty' => $emptySeries,
                    'total_rooms' => $totalRooms,
                    'granularity' => $granularity,
                ],
                'revenue_daily' => [
                    'labels' => $revenueLabels,
                    'totals' => $revenueTotals,
                    'granularity' => $granularity,
                ],
                'status_distribution' => [
                    'labels' => $statusLabels,
                    'totals' => $statusTotals,
                ],
                'top_rooms' => [
                    'labels' => $topRoomLabels,
                    'totals' => $topRoomTotals,
                ],
            ],
            'bookings' => $latestBookings,
        ]);
    }

    protected function resolveDateRange(DashboardChartRequest $request): array
    {
        $startInput = $request->input('start_date');
        $endInput = $request->input('end_date');

        if ($startInput === null || $endInput === null) {
            $bounds = DB::table('booking')
                ->selectRaw('MIN(tanggal_check_in) as min_in, MAX(tanggal_check_out) as max_out')
                ->first();

            if ($bounds?->min_in && $bounds?->max_out) {
                $startInput = (string) $bounds->min_in;
                $endInput = (string) $bounds->max_out;
            } else {
                $startInput = now()->toDateString();
                $endInput = now()->toDateString();
            }
        }

        $start = Carbon::parse($startInput)->startOfDay();
        $end = Carbon::parse($endInput)->startOfDay();

        if ($start->gt($end)) {
            return [$end, $start];
        }

        return [$start, $end];
    }

    protected function resolveGranularity(CarbonInterface $start, CarbonInterface $end): string
    {
        if ($start->diffInYears($end) >= 2) {
            return 'year';
        }

        if ($start->diffInMonths($end) >= 2) {
            return 'month';
        }

        if ($start->diffInDays($end) > 10) {
            return 'week';
        }

        return 'day';
    }

    protected function generateDays(CarbonInterface $start, CarbonInterface $end): Collection
    {
        $days = collect();
        $cursor = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->startOfDay();

        while ($cursor->lte($endDate)) {
            $days->push($cursor->toDateString());
            $cursor->addDay();
        }

        return $days;
    }

    protected function resolveAmountExpression(): string
    {
        $layananAlias = 'COALESCE(bl.layanan_subtotal, 0)';
        $computed = 'COALESCE(booking.harga_kamar, 0) + ' . $layananAlias;

        if (Schema::hasColumn('booking', 'total_final')) {
            return 'COALESCE(booking.total_final, ' . $computed . ')';
        }

        if (Schema::hasColumn('booking', 'total_akhir')) {
            return 'COALESCE(booking.total_akhir, ' . $computed . ')';
        }

        if (Schema::hasColumn('booking', 'total')) {
            return 'COALESCE(booking.total, ' . $computed . ')';
        }

        return $computed;
    }

    protected function resolveRevenueDateColumn(): string
    {
        if (Schema::hasColumn('booking', 'checkout_at')) {
            return 'checkout_at';
        }

        return 'tanggal_check_out';
    }

    protected function resolveActiveRoomIds(string $today): Collection
    {
        if (! Schema::hasTable('kamar_perbaikan')) {
            return DB::table('kamar')
                ->where('status_kamar', '!=', 'perbaikan')
                ->pluck('id');
        }

        return DB::table('kamar')
            ->whereNotExists(function ($subquery) use ($today): void {
                $subquery->select(DB::raw('1'))
                    ->from('kamar_perbaikan as kp')
                    ->whereColumn('kp.kamar_id', 'kamar.id')
                    ->whereDate('kp.mulai', '<=', $today)
                    ->whereDate('kp.selesai', '>=', $today);
            })
            ->pluck('id');
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, int>, 2: array<int, int>}
     */
    protected function aggregatePeriod(Collection $dateRange, array $occupied, array $empty, string $granularity): array
    {
        $buckets = [];

        foreach ($dateRange as $index => $date) {
            $dateCarbon = Carbon::parse($date);
            $periodKey = match ($granularity) {
                'week' => $dateCarbon->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                'year' => $dateCarbon->format('Y'),
                default => $dateCarbon->format('Y-m'),
            };

            if (!isset($buckets[$periodKey])) {
                $buckets[$periodKey] = [
                    'start_date' => $dateCarbon->toDateString(),
                    'end_date' => $dateCarbon->toDateString(),
                    'occupied' => 0,
                    'empty' => 0,
                ];
            }

            if ($date > $buckets[$periodKey]['end_date']) {
                $buckets[$periodKey]['end_date'] = $date;
            }

            $buckets[$periodKey]['occupied'] += (int) ($occupied[$index] ?? 0);
            $buckets[$periodKey]['empty'] += (int) ($empty[$index] ?? 0);
        }

        $labels = [];
        $occupiedSeries = [];
        $emptySeries = [];

        foreach ($buckets as $bucket) {
            $labels[] = $this->formatPeriodLabel(
                $granularity,
                Carbon::parse($bucket['start_date']),
                Carbon::parse($bucket['end_date']),
            );
            $occupiedSeries[] = (int) $bucket['occupied'];
            $emptySeries[] = (int) $bucket['empty'];
        }

        return [$labels, $occupiedSeries, $emptySeries];
    }

    /**
     * @param array<string, int> $revenueByDate
     * @return array{0: array<int, string>, 1: array<int, int>}
     */
    protected function aggregatePeriodTotals(array $revenueByDate, string $granularity): array
    {
        $buckets = [];

        foreach ($revenueByDate as $date => $total) {
            $dateCarbon = Carbon::parse($date);
            $periodKey = match ($granularity) {
                'week' => $dateCarbon->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                'year' => $dateCarbon->format('Y'),
                default => $dateCarbon->format('Y-m'),
            };

            if (!isset($buckets[$periodKey])) {
                $buckets[$periodKey] = [
                    'start_date' => $dateCarbon->toDateString(),
                    'end_date' => $dateCarbon->toDateString(),
                    'total' => 0,
                ];
            }

            if ($date > $buckets[$periodKey]['end_date']) {
                $buckets[$periodKey]['end_date'] = $date;
            }

            $buckets[$periodKey]['total'] += (int) $total;
        }

        $labels = [];
        $totals = [];

        foreach ($buckets as $bucket) {
            $labels[] = $this->formatPeriodLabel(
                $granularity,
                Carbon::parse($bucket['start_date']),
                Carbon::parse($bucket['end_date']),
            );
            $totals[] = $bucket['total'];
        }

        return [$labels, $totals];
    }

    protected function formatPeriodLabel(string $granularity, CarbonInterface $startDate, CarbonInterface $endDate): string
    {
        return match ($granularity) {
            'week' => $startDate->translatedFormat('d M') . ' - ' . $endDate->translatedFormat('d M'),
            'year' => $startDate->translatedFormat('Y'),
            default => $startDate->translatedFormat('M Y'),
        };
    }
}
