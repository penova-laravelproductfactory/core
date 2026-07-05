<?php

namespace App\Modules\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\Store — one line of an order. product_name and price are
 * SNAPSHOTS taken at placement: the order must read the same forever,
 * even after the product is renamed, repriced, or deleted (product_id
 * then nulls out but the line survives).
 */
class OrderItem extends Model
{
    protected $table = 'store_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
