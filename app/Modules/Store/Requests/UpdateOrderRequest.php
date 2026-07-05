<?php

namespace App\Modules\Store\Requests;

use App\Modules\Store\Models\OrderStatus;
use App\Modules\Store\Models\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modules\Store — admin order update, deliberately narrow: ONLY the
 * lifecycle fields (status, payment_status). Customer data, items and
 * totals are a historical record of what was ordered — free-editing
 * them would silently rewrite history (v0.1 integrity guarantee).
 */
class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware (permission:store.manage) is the gate.
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::in(OrderStatus::values())],
            'payment_status' => ['sometimes', 'required', Rule::in(PaymentStatus::values())],
        ];
    }
}
