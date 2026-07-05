<?php

namespace App\Http\Controllers;

use App\Core\Logs\Models\ActivityLog;
use App\Core\Roles\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Panel dashboard — application-level glue, not a Core module.
 *
 * Core Lite ships live counters and two small feeds built ONLY from
 * existing Core data (users, roles, activity log, notifications).
 * Product modules / the Pro edition add their own widgets to the same
 * grid; this controller then simply grows extra props (or gets replaced
 * per product).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Core/Dashboard/Index', [
            'stats' => [
                'users_count' => User::count(),
                'roles_count' => Role::count(),
            ],

            // Latest 3 audit entries (Core\Logs). Kept deliberately simple
            // for Lite — the full filterable trail lives at /admin/logs.
            'recentActivity' => ActivityLog::latest('created_at')
                ->take(3)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'label' => $log->action,
                    'time' => $log->created_at->format('Y-m-d H:i'),
                ]),

            // Latest 3 database notifications of the signed-in user.
            // Convention: notification classes put a human line in
            // data.message (fallbacks keep odd payloads presentable).
            'recentNotifications' => $request->user()
                ->notifications()
                ->take(3)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'label' => $notification->data['message']
                        ?? $notification->data['title']
                        ?? class_basename($notification->type),
                    'time' => $notification->created_at->format('Y-m-d H:i'),
                ]),
        ]);
    }
}
