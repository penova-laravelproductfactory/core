<?php

namespace App\Modules\Booking\Requests;

use App\Modules\Booking\Models\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modules\Booking — validation for creating a booking.
 *
 * status is optional: BookingStoreController defaults it to "pending"
 * (a new booking always starts its lifecycle there).
 */
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware (auth) is the gate for the demo module; a real
        // product adds "permission:booking.manage" + a policy here.
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'status' => ['nullable', Rule::in(BookingStatus::values())],
        ];
    }
}
