<?php

namespace App\Modules\Store\Models;

/**
 * Modules\Store — order lifecycle states (string-backed, same pattern
 * as ProductType/PaymentStatus).
 *
 *   Pending   → placed by the customer, awaiting review
 *   Confirmed → accepted by the admin
 *   Completed → fulfilled/delivered
 *   Cancelled → will not be fulfilled
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
