<?php

namespace App\Console\Commands;

use App\Models\Kamar;
use App\Models\KamarPerbaikan;
use Illuminate\Console\Command;

class AutoStartKamarPerbaikan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-start-kamar-perbaikan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-set status kamar menjadi perbaikan saat tanggal mulai perbaikan tiba';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        $query = KamarPerbaikan::query()
            ->whereDate('mulai', '<=', $today)
            ->where(function ($sub) use ($today) {
                $sub->whereNull('selesai')
                    ->orWhereDate('selesai', '>=', $today);
            });

        $count = 0;

        $query->chunkById(200, function ($rows) use (&$count) {
            foreach ($rows as $row) {
                $kamar = Kamar::query()->find($row->kamar_id);
                if (!$kamar) {
                    continue;
                }

                if ($kamar->status_kamar === 'perbaikan') {
                    continue;
                }

                $kamar->update([
                    'status_kamar' => 'perbaikan',
                    'updated_at' => now(),
                ]);

                $count++;
            }
        });

        $this->info("Auto-start perbaikan selesai. Total diupdate: {$count}");

        return self::SUCCESS;
    }
}
