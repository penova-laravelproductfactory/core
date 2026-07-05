<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Order;
use App\Modules\Store\Requests\UpdateOrderRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Modules\Store — applies lifecycle changes to an order
 * (store.orders.update): status and/or payment_status only.
 */
class OrderUpdateController extends Controller
{
    public function __invoke(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->validated());

        return back()->with('success', __('Order updated.'));
    }
}
