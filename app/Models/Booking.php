<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $table = 'booking';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_check_in' => 'date',
            'tanggal_check_out' => 'date',
            'checkout_at' => 'datetime',
            'status_updated_at' => 'datetime',
        ];
    }

    public function updateStatus(string $status, ?string $reason = null): void
    {
        $payload = [
            'status_booking' => $status,
            'status_updated_at' => now(),
        ];

        if ($reason !== null) {
            $payload['canceled_reason'] = $reason;
        }

        $this->forceFill($payload)->save();
    }
}
