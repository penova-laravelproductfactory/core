<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modules\Booking — a single booking.
 *
 * status is stored as a string; use statusEnum()/setStatusEnum() when
 * you want the type-safe BookingStatus view of it. Kept as a manual
 * mapping (not an enum cast) so unexpected legacy values fail at the
 * call site, not during model hydration.
 */
class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = ['customer_name', 'starts_at', 'status'];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    /** The current status as its enum case. */
    public function statusEnum(): BookingStatus
    {
        return BookingStatus::from($this->status);
    }

    /** Set the status from an enum case (still persisted as a string). */
    public function setStatusEnum(BookingStatus $status): void
    {
        $this->status = $status->value;
    }
}
