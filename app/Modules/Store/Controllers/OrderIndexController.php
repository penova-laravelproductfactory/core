<?php

namespace App\Modules\Store\Controllers;

use App\Core\DataTable\DataTableBuilder;
use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — the admin orders list (store.orders.index).
 * DataTable contract + two lifecycle filters (?status, ?payment_status)
 * passed through the table's `params` prop.
 */
class OrderIndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $query = Order::query()
            ->with('user:id,name')
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->query('payment_status')));

        return Inertia::render('Modules/Store/Orders/Index', [
            'orders' => DataTableBuilder::for($query)
                ->searchable(['number', 'customer_name', 'customer_email'])
                ->sortable(['number', 'total', 'created_at'])
                ->paginate($request)
                ->through(fn (Order $order) => [
                    'id' => $order->id,
                    'number' => $order->number,
                    // The owning account (live) — links to Core user admin.
                    'user_id' => $order->user_id,
                    'user_name' => $order->user?->name,
                    // Snapshot identity at order time (may drift from the
                    // account later; that difference is a feature).
                    'customer_name' => $order->customer_name,
                    'customer_email' => $order->customer_email,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total' => $order->total,
                    'items_count' => $order->items_count,
                    'created_at_human' => $order->created_at?->format('Y-m-d H:i'),
                ]),

            'filters' => [
                'search' => (string) $request->query('search', ''),
                'status' => $request->query('status'),
                'payment_status' => $request->query('payment_status'),
            ],
        ]);
    }
}
