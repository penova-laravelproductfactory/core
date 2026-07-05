<?php

namespace App\Modules\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Modules\Store — a placed order (guest checkout, v0.1).
 *
 * The order is a historical document: its items snapshot product name
 * and price at placement (see OrderItem). Admin edits are deliberately
 * limited to status/payment_status — customer data and totals are what
 * the customer submitted and what the math said, not editable state.
 */
class Order extends Model
{
    protected $table = 'store_orders';

    protected $fillable = [
        'number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'status',
        'payment_status',
        'total',
        'notes',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ];

    protected static function booted(): void
    {
        // Customer-facing reference, e.g. ORD-260705-K3TZ. Generated
        // here so every creation path gets one.
        static::creating(function (Order $order) {
            $order->number ??= self::generateNumber();
        });
    }

    private static function generateNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('number', $number)->exists());

        return $number;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function statusEnum(): OrderStatus
    {
        return OrderStatus::from($this->status);
    }

    public function paymentStatusEnum(): PaymentStatus
    {
        return PaymentStatus::from($this->payment_status);
    }
}
