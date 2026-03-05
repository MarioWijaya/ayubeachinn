<?php

namespace App\Livewire\Backoffice\Kamar;

use App\Models\KamarPerbaikan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Kamar')]
class Edit extends Component
{
    public object $kamar;

    public function mount(int $id): void
    {
        $this->syncExpiredMaintenanceStatus($id);

        $kamar = DB::table('kamar')->where('id', $id)->first();
        abort_if(! $kamar, 404);

        $this->kamar = $kamar;
    }

    public function render(): View
    {
        $tipeKamar = ['Standard Fan', 'Superior', 'Deluxe', 'Family Room'];
        $perbaikan = KamarPerbaikan::query()
            ->where('kamar_id', $this->kamar->id)
            ->first();

        return view('livewire.backoffice.kamar.edit', [
            'kamar' => $this->kamar,
            'tipeKamar' => $tipeKamar,
            'perbaikan' => $perbaikan,
        ]);
    }

    private function syncExpiredMaintenanceStatus(int $kamarId): void
    {
        $kamar = DB::table('kamar')
            ->select('id', 'status_kamar')
            ->where('id', $kamarId)
            ->first();

        if (! $kamar || $kamar->status_kamar !== 'perbaikan') {
            return;
        }

        $today = now()->toDateString();
        $cutoff = now()->subDay()->toDateString();

        $hasActiveMaintenance = DB::table('kamar_perbaikan')
            ->where('kamar_id', $kamarId)
            ->whereDate('mulai', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('selesai')
                    ->orWhereDate('selesai', '>=', $today);
            })
            ->exists();

        if ($hasActiveMaintenance) {
            return;
        }

        $hasExpiredMaintenance = DB::table('kamar_perbaikan')
            ->where('kamar_id', $kamarId)
            ->whereNotNull('selesai')
            ->whereDate('selesai', '<=', $cutoff)
            ->exists();

        if (! $hasExpiredMaintenance) {
            return;
        }

        $hasActiveBooking = DB::table('booking')
            ->where('kamar_id', $kamarId)
            ->whereIn('status_booking', ['menunggu', 'check_in'])
            ->whereDate('tanggal_check_in', '<=', $today)
            ->whereDate('tanggal_check_out', '>', $today)
            ->exists();

        DB::table('kamar')
            ->where('id', $kamarId)
            ->update([
                'status_kamar' => $hasActiveBooking ? 'terisi' : 'tersedia',
                'updated_at' => now(),
            ]);
    }
}
