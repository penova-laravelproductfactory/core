<?php

namespace App\Modules\Crm\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Models\Lead;
use App\Modules\Crm\Requests\StoreLeadRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Modules\Crm — persists a new lead (crm.leads.store).
 */
class LeadStoreController extends Controller
{
    public function __invoke(StoreLeadRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Lead::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'status' => $validated['status'] ?? 'new',
        ]);

        return redirect()->route('crm.leads.index')->with('success', __('Lead created.'));
    }
}
