<?php

namespace App\Modules\Crm\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Crm — the "new lead" form page (crm.leads.create).
 */
class LeadCreateController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Modules/Crm/Leads/Create', [
            // Suggested pipeline stages for the status select; the column
            // itself is a free string (see the migration).
            'statuses' => ['new', 'contacted', 'qualified'],
        ]);
    }
}
