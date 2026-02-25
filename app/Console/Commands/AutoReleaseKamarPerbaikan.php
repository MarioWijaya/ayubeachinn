<?php

namespace App\Console\Commands;

use App\Models\Kamar;
use App\Models\KamarPerbaikan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoReleaseKamarPerbaikan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-release-kamar-perbaikan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-release kamar dari status perbaikan setelah tanggal selesai terlewati 1 hari';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subDay()->toDateString();
        $today = now()->toDateString();

        $query = KamarPerbaikan::query()
            ->whereNotNull('selesai')
            ->whereDate('selesai', '<=', $cutoff);

        $count = 0;

        $query->chunkById(200, function ($rows) use (&$count, $today) {
            foreach ($rows as $row) {
                $kamar = Kamar::query()->find($row->kamar_id);
                if (!$kamar) {
                    continue;
                }

                if ($kamar->status_kamar !== 'perbaikan') {
                    continue;
                }

                $hasActiveBooking = DB::table('booking')
                    ->where('kamar_id', $kamar->id)
                    ->whereIn('status_booking', ['menunggu', 'check_in'])
                    ->whereDate('tanggal_check_in', '<=', $today)
                    ->whereDate('tanggal_check_out', '>', $today)
                    ->exists();

                $newStatus = $hasActiveBooking ? 'terisi' : 'tersedia';

                $kamar->update([
                    'status_kamar' => $newStatus,
                    'updated_at' => now(),
                ]);

                $count++;
            }
        });

        $this->info("Auto-release kamar perbaikan selesai. Total diupdate: {$count}");

        return self::SUCCESS;
    }
}
