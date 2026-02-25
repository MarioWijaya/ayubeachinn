<?php

namespace App\Livewire\App\Booking;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detail Booking')]
class Show extends Component
{
    public int $bookingId;

    public function mount(int $id): void
    {
        $this->bookingId = $id;
    }

    public function render(): View
    {
        $booking = DB::table('booking')
            ->join('kamar', 'booking.kamar_id', '=', 'kamar.id')
            ->join('users as pegawai', 'booking.pegawai_id', '=', 'pegawai.id')
            ->leftJoin('booking_layanan as bl', 'bl.booking_id', '=', 'booking.id')
            ->leftJoin('layanan as l', 'l.id', '=', 'bl.layanan_id')
            ->select(
                'booking.*',
                'kamar.nomor_kamar',
                'kamar.tipe_kamar',
                'kamar.tarif',
                'pegawai.nama as nama_pegawai',
                'pegawai.username as username_pegawai',
                'l.nama as layanan_nama',
                'bl.qty as layanan_qty',
                'bl.subtotal as layanan_subtotal'
            )
            ->where('booking.id', $this->bookingId)
            ->first();

        abort_if(!$booking, 404);

        $checkIn = Carbon::parse($booking->tanggal_check_in)->startOfDay();
        $checkOut = Carbon::parse($booking->tanggal_check_out)->startOfDay();
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $hargaPerMalam = (int) ($booking->harga_kamar ?? 0);
        $layananSubtotal = (int) ($booking->layanan_subtotal ?? 0);

        $booking->nights = $nights;
        $booking->room_subtotal = $nights * $hargaPerMalam;
        $booking->total_bayar = $booking->room_subtotal + $layananSubtotal;

        $riwayatPerpanjangan = DB::table('perpanjangan_booking')
            ->where('booking_id', $this->bookingId)
            ->orderByDesc('id')
            ->get();

        $tipeListSidebar = DB::table('kamar')
            ->select('tipe_kamar')
            ->whereNotNull('tipe_kamar')
            ->groupBy('tipe_kamar')
            ->orderBy('tipe_kamar')
            ->get();

        return view('livewire.app.booking.show', compact(
            'booking',
            'riwayatPerpanjangan',
            'tipeListSidebar'
        ));
    }
}
