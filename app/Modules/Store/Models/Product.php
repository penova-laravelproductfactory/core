<?php

namespace App\Modules\Store\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modules\Store — a sellable product.
 *
 * type is stored as a string; use typeEnum() for the type-safe view.
 * Kept as a manual mapping (not an enum cast) so unexpected legacy
 * values fail at the call site, not during model hydration — same
 * pattern as Order::statusEnum().
 */
class Product extends Model
{
    protected $table = 'store_products';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'price',
        'sku',
        'stock',
        'is_active',
        'description',
        'download_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    // Mirrors the DB defaults so a fresh (not yet refreshed) model
    // reports the same values the row will get.
    protected $attributes = [
        'type' => 'physical',
        'is_active' => true,
    ];

    /** The current type as its enum case. */
    public function typeEnum(): ProductType
    {
        return ProductType::from($this->type);
    }
}
