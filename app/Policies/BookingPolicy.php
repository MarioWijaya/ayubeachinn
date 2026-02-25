<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Booking $booking): Response
    {
        if ($booking->status_booking === 'selesai') {
            return Response::deny('Booking sudah selesai dan tidak dapat diedit.');
        }

        if ($booking->status_booking === 'batal') {
            return Response::deny('Booking sudah dibatalkan dan tidak dapat diedit.');
        }

        return Response::allow();
    }
}
