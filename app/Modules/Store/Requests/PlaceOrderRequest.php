<?php

namespace App\Modules\Store\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modules\Store — guest checkout validation. Only what fulfilment
 * actually needs: who, how to reach them, where to ship. Everything
 * money-related is computed server-side from the cart, never accepted
 * from the client.
 */
class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public guest checkout
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:32'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
