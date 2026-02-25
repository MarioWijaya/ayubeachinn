<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q', '');
        $tipe = $request->get('tipe', '');
        $statusKamar = $request->get('statusKamar', '');
        $checkDate = $request->get('checkDate', date('Y-m-d'));

        $ongoingStatuses = ['menunggu', 'check_in'];

        // 1) tipe + total kamar
        $tipeList = DB::table('kamar')
            ->select('tipe_kamar', DB::raw('COUNT(*) as total'))
            ->whereNotNull('tipe_kamar')
            ->groupBy('tipe_kamar')
            ->orderBy('tipe_kamar')
            ->get();

        // 2) terisi per tipe pada checkDate (overlap)
        $terisiMap = DB::table('booking')
            ->join('kamar', 'kamar.id', '=', 'booking.kamar_id')
            ->whereIn('booking.status_booking', $ongoingStatuses)
            ->whereDate('booking.tanggal_check_in', '<=', $checkDate)
            ->whereDate('booking.tanggal_check_out', '>', $checkDate)
            ->select('kamar.tipe_kamar', DB::raw('COUNT(DISTINCT booking.kamar_id) as terisi'))
            ->groupBy('kamar.tipe_kamar')
            ->pluck('terisi', 'tipe_kamar');

        // 3) list kamar + status terisi (pakai left join booking overlap)
        $kamarQ = DB::table('kamar')
            ->leftJoin('booking as b', function ($join) use ($ongoingStatuses, $checkDate) {
                $join->on('b.kamar_id', '=', 'kamar.id')
                    ->whereIn('b.status_booking', $ongoingStatuses)
                    ->whereDate('b.tanggal_check_in', '<=', $checkDate)
                    ->whereDate('b.tanggal_check_out', '>', $checkDate);
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

        if ($tipe) {
            $kamarQ->where('kamar.tipe_kamar', $tipe);
        }

        if ($q) {
            $kamarQ->where(function ($w) use ($q) {
                $w->where('kamar.nomor_kamar', 'like', "%{$q}%")
                    ->orWhere('kamar.tipe_kamar', 'like', "%{$q}%");
            });
        }

        if ($statusKamar === 'tersedia') {
            $kamarQ->whereNull('b.id');
        }

        if ($statusKamar === 'terisi') {
            $kamarQ->whereNotNull('b.id');
        }

        $kamarPage = $kamarQ
            ->orderByRaw('CAST(kamar.nomor_kamar AS UNSIGNED) ASC')
            ->paginate(10)
            ->withQueryString();

        return view('livewire.app.dashboard', compact(
            'tipeList',
            'terisiMap',
            'kamarPage',
            'q',
            'tipe',
            'statusKamar',
            'checkDate'
        ));
    }
}
