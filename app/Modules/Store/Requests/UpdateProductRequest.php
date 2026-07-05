<?php

namespace App\Modules\Store\Requests;

use App\Modules\Store\Models\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modules\Store — validation for editing a product. Same shape as
 * StoreProductRequest; the slug uniqueness rule ignores the product
 * being edited.
 */
class UpdateProductRequest extends FormRequest
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
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('store_products', 'slug')->ignore($this->route('product')),
            ],
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
