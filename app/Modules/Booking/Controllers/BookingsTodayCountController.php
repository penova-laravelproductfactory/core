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
 * Response shape is guaranteed:
 *   { "count": number, "yesterday_count": number }
 * count is today's bookings; yesterday_count feeds the widget's trend
 * indicator (consumers must treat it as optional — the widget hides
 * the trend when it is absent).
 */
class BookingsTodayCountController extends Controller
{
    public function __invoke(): JsonResponse
    {
        // Day boundaries in the application timezone. whereBetween keeps
        // the starts_at index usable — whereDate would wrap the column
        // in DATE() and force a scan.
        return response()->json([
            'count' => $this->countBetween(today()->startOfDay(), today()->endOfDay()),
            'yesterday_count' => $this->countBetween(
                today()->subDay()->startOfDay(),
                today()->subDay()->endOfDay(),
            ),
        ]);
    }

    private function countBetween($from, $to): int
    {
        return Booking::whereBetween('starts_at', [$from, $to])->count();
    }
}
