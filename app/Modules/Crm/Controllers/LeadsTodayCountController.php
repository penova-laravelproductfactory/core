<?php

namespace App\Modules\Crm\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Models\Lead;
use Illuminate\Http\JsonResponse;

/**
 * Modules\Crm — tiny JSON endpoint backing the dashboard widget
 * (crm.leads.today-count): how many leads were created today, in the
 * application timezone.
 *
 * Response shape is guaranteed: { "count": number }.
 * LeadsTodayCard.vue fetches this on mount.
 */
class LeadsTodayCountController extends Controller
{
    public function __invoke(): JsonResponse
    {
        // whereBetween keeps the created_at index usable (whereDate
        // would wrap the column in DATE() and force a scan).
        $count = Lead::whereBetween('created_at', [
            now()->startOfDay(),
            now()->endOfDay(),
        ])->count();

        return response()->json(['count' => $count]);
    }
}
