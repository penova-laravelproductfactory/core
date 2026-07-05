<?php

namespace App\Modules\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Booking;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Booking — the bookings list page (booking.index).
 *
 * Deliberately plain pagination for the demo; swap in Core's
 * DataTableBuilder when the list needs search/sort.
 */
class BookingIndexController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Modules/Booking/Index', [
            'bookings' => Booking::latest()->paginate(10),
        ]);
    }
}
