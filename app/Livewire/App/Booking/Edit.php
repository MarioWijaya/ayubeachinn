<?php

namespace App\Livewire\App\Booking;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Booking')]
class Edit extends Component
{
    use AuthorizesRequests;

    public int $bookingId;

    /**
     * @return array<int, string>
     */
    private function editableStatusList(Booking $booking): array
    {
        if ($booking->status_booking === 'check_in') {
            return ['menunggu', 'check_in'];
        }

        return ['menunggu', 'check_in', 'batal'];
    }

    public function mount(int $id): void
    {
        $this->bookingId = $id;
    }

    public function toJSON(): array
    {
        return [];
    }

    public function render(): View
    {
        $booking = Booking::query()->find($this->bookingId);
        abort_if(! $booking, 404);

        $this->authorize('update', $booking);

        $kamar = DB::table('kamar')->orderBy('nomor_kamar')->get();
        $statusList = $this->editableStatusList($booking);

        $layananList = DB::table('layanan')
            ->where('aktif', 1)
            ->orderBy('nama')
            ->get();

        $bl = DB::table('booking_layanan')->where('booking_id', $this->bookingId)->first();

        $selectedLayananId = $bl ? (string) $bl->layanan_id : '';
        $selectedQty = $bl ? (int) $bl->qty : 0;
        $layananSubtotal = $bl ? (int) $bl->subtotal : 0;

        $checkIn = Carbon::parse($booking->tanggal_check_in)->startOfDay();
        $checkOut = Carbon::parse($booking->tanggal_check_out)->startOfDay();
        $currentNights = max(1, $checkIn->diffInDays($checkOut));
        $hargaPerMalam = (int) ($booking->harga_kamar ?? 0);
        $currentRoomSubtotal = $currentNights * $hargaPerMalam;
        $currentTotal = $currentRoomSubtotal + $layananSubtotal;

        $tipeListSidebar = DB::table('kamar')
            ->select('tipe_kamar')
            ->whereNotNull('tipe_kamar')
            ->groupBy('tipe_kamar')
            ->orderBy('tipe_kamar')
            ->get();

        return view('livewire.app.booking.edit', compact(
            'booking',
            'kamar',
            'statusList',
            'layananList',
            'selectedLayananId',
            'selectedQty',
            'currentNights',
            'currentTotal',
            'tipeListSidebar'
        ));
    }
}
