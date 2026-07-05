<?php

namespace App\Modules\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Models\BookingStatus;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Booking — the "edit booking" form page (booking.edit).
 */
class BookingEditController extends Controller
{
    public function __invoke(Booking $booking): Response
    {
        return Inertia::render('Modules/Booking/Edit', [
            'booking' => $booking,
            'statuses' => BookingStatus::values(),
        ]);
    }
}
