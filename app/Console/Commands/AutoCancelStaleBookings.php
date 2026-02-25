<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class AutoCancelStaleBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-cancel-stale-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel booking menunggu yang sudah lewat 2 hari tanpa perubahan status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subDays(2);

        $query = Booking::query()
            ->where('status_booking', 'menunggu')
            ->whereNotNull('status_updated_at')
            ->where('status_updated_at', '<=', $cutoff);

        $count = 0;

        $query->chunkById(200, function ($bookings) use (&$count) {
            foreach ($bookings as $booking) {
                $booking->updateStatus('batal', 'auto_cancel_timeout');
                $count++;
            }
        });

        $this->info("Auto-cancel selesai. Total dibatalkan: {$count}");

        return self::SUCCESS;
    }
}
