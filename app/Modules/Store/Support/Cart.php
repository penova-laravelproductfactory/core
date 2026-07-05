<?php

namespace App\Modules\Store\Support;

use App\Modules\Store\Models\Product;
use Illuminate\Support\Collection;

/**
 * Modules\Store — the v0.1 cart: a session array of product_id => qty.
 *
 * Deliberately NOT a database table: guest checkout needs no cross-
 * device persistence, and a session structure costs nothing to build
 * or migrate away from. When accounts/persistent carts arrive, this
 * class keeps its API and swaps its storage.
 *
 * Prices are never stored in the cart — lines() reads the CURRENT
 * price from store_products every time, and checkout snapshots it into
 * order items. Inactive/deleted products silently drop out of lines().
 */
class Cart
{
    private const KEY = 'store.cart';

    public static function add(int $productId, int $quantity = 1): void
    {
        $items = session(self::KEY, []);
        $items[$productId] = ($items[$productId] ?? 0) + max(1, $quantity);

        session([self::KEY => $items]);
    }

    public static function remove(int $productId): void
    {
        $items = session(self::KEY, []);
        unset($items[$productId]);

        session([self::KEY => $items]);
    }

    /**
     * Resolved cart lines against live product data.
     *
     * @return Collection<int, array{product: Product, quantity: int, subtotal: float}>
     */
    public static function lines(): Collection
    {
        $items = session(self::KEY, []);

        if ($items === []) {
            return collect();
        }

        return Product::where('is_active', true)
            ->whereIn('id', array_keys($items))
            ->get()
            ->map(fn (Product $product) => [
                'product' => $product,
                'quantity' => $items[$product->id],
                'subtotal' => round($product->price * $items[$product->id], 2),
            ])
            ->values();
    }

    public static function total(): float
    {
        return round(self::lines()->sum('subtotal'), 2);
    }

    public static function count(): int
    {
        return (int) array_sum(session(self::KEY, []));
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }
}
