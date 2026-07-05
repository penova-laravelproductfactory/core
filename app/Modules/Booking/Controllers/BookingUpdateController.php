<?php

namespace App\Modules\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Requests\UpdateBookingRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Modules\Booking — applies edits to a booking (booking.update).
 */
class BookingUpdateController extends Controller
{
    public function __invoke(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $booking->update($request->validated());

        return redirect()->route('booking.index')->with('success', __('Booking updated.'));
    }
}
