<?php

namespace App\Core\Logs\Controllers;

use App\Core\DataTable\DataTableBuilder;
use App\Core\Logs\Models\ActivityLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Logs — the read-only audit trail page (penova.logs.index).
 */
class ListActivityLogsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Core/Logs/Index', [
            'logs' => DataTableBuilder::for(ActivityLog::query()->with('user:id,name'))
                ->searchable(['action', 'subject_type'])
                ->sortable(['created_at', 'action'])
                ->paginate($request),
        ]);
    }
}
