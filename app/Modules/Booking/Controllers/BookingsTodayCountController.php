<?php

namespace App\Modules\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Booking;
use Illuminate\Http\JsonResponse;

/**
 * Modules\Booking — tiny JSON endpoint backing the dashboard widget
 * (booking.today-count): how many bookings start today.
 *
 * The BookingsTodayCard Vue widget fetches this on mount instead of
 * pushing booking data into the shared dashboard props — widgets own
 * their data needs, the dashboard controller stays module-agnostic.
 *
 * Response shape is guaranteed: { "count": number }.
 */
class BookingsTodayCountController extends Controller
{
    public function __invoke(): JsonResponse
    {
        // "Today" in the application timezone (config('app.timezone')).
        // whereBetween keeps the starts_at index usable — whereDate would
        // wrap the column in DATE() and force a scan.
        $count = Booking::whereBetween('starts_at', [
            today()->startOfDay(),
            today()->endOfDay(),
        ])->count();

        return response()->json(['count' => $count]);
    }
}
