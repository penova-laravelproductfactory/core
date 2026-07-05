<?php

namespace App\Modules\Store\Models;

/**
 * Modules\Store — product kinds. Backed by the exact strings stored in
 * store_products.type; the column stays a string (see the migration).
 *
 *   Physical     → shippable goods (stock applies)
 *   Virtual      → services / licenses (no stock, no download)
 *   Downloadable → digital files (download_url applies)
 */
enum ProductType: string
{
    case Physical = 'physical';
    case Virtual = 'virtual';
    case Downloadable = 'downloadable';

    /**
     * All backing values — for validation rules and select options.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
