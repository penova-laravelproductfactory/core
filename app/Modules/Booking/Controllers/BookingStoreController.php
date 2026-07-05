<?php

namespace App\Modules\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Models\BookingStatus;
use App\Modules\Booking\Requests\StoreBookingRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Modules\Booking — persists a new booking (booking.store).
 */
class BookingStoreController extends Controller
{
    public function __invoke(StoreBookingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Booking::create([
            'customer_name' => $validated['customer_name'],
            'starts_at' => $validated['starts_at'],
            // New bookings start their lifecycle at "pending" unless the
            // form explicitly picked a status.
            'status' => $validated['status'] ?? BookingStatus::Pending->value,
        ]);

        return redirect()->route('booking.index')->with('success', __('Booking created.'));
    }
}
