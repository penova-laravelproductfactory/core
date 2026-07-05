<?php

namespace App\Modules\Store\Requests;

use App\Modules\Store\Models\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modules\Store — validation for creating a product.
 * download_url is only required for downloadable products.
 */
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware (permission:store.manage) is the gate.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:store_products,slug'],
            'type' => ['required', Rule::in(ProductType::values())],
            'price' => ['required', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
            'download_url' => ['nullable', 'required_if:type,downloadable', 'url', 'max:2048'],
        ];
    }
}
