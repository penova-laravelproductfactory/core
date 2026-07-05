<?php

namespace App\Modules\Store\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modules\Store — checkout validation (authenticated users only; the
 * route sits behind auth middleware). Identity (name/email) is NOT
 * accepted from the form — it snapshots from the account server-side.
 * Everything money-related is computed server-side from the cart.
 */
class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer_phone' => ['nullable', 'string', 'max:32'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
