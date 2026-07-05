<?php

namespace App\Modules\Booking\Models;

/**
 * Modules\Booking — the booking lifecycle states.
 *
 * Backed by the exact strings stored in bookings.status; the column
 * itself stays a string (see the migration), this enum is the domain
 * vocabulary on top of it.
 */
enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    /**
     * All backing values — handy for validation rules ("in:" lists)
     * and select options.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
