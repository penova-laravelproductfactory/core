<?php

namespace App\Modules\Store\Models;

/**
 * Modules\Store — payment states, v0.1: manual marking only. An online
 * gateway later adds cases (refunded, failed, …) without schema changes.
 */
enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
