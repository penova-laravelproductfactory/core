<?php

namespace App\Modules\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\BookingStatus;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Booking — the "new booking" form page (booking.create).
 */
class BookingCreateController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Modules/Booking/Create', [
            // Options for the status select on the form.
            'statuses' => BookingStatus::values(),
        ]);
    }
}
