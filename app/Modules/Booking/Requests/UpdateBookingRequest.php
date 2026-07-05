<?php

namespace App\Modules\Booking\Requests;

use App\Modules\Booking\Models\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modules\Booking — validation for editing a booking.
 *
 * "sometimes" on every field: the edit form may submit any subset;
 * whatever IS present must be valid (and non-empty where required).
 */
class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // See StoreBookingRequest — same demo-module note.
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', Rule::in(BookingStatus::values())],
        ];
    }
}
