<?php

namespace App\Modules\Crm\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Models\Lead;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Crm — the leads list page (crm.leads.index).
 * Plain pagination; swap in Core's DataTableBuilder when the list
 * needs search/sort.
 */
class LeadIndexController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Modules/Crm/Leads/Index', [
            'leads' => Lead::latest()->paginate(10),
        ]);
    }
}
