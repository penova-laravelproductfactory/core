<?php

namespace App\Core\Users\Controllers;

use App\Core\DataTable\DataTableBuilder;
use App\Core\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Users — the users index page (penova.users.index).
 *
 * One invokable class per route action is the panel-wide convention
 * (Core and Modules alike); shared context for the Users module lives
 * in this docblock's sibling classes:
 *
 * Authorization is layered on purpose:
 *   - route level:  Users/routes.php wraps everything in
 *                    "permission:users.manage" (fast fail)
 *   - action level:  UserPolicy via $this->authorize() below
 *                    (the single source of truth; the "admin" role gets
 *                    users.manage from PenovaCoreSeeder)
 *
 * Product Modules never manage users themselves; they reference users
 * via relations and rely on this module for administration.
 *
 * Pro version hooks: user impersonation, bulk actions, CSV export,
 * per-column DataTable filters, soft-deletes with a trash view.
 */
class ListUsersController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('Core/Users/Index', [
            'users' => DataTableBuilder::for(User::query()->with('roles:id,name'))
                ->searchable(['name', 'email'])
                ->sortable(['name', 'email', 'created_at'])
                ->paginate($request),
        ]);
    }
}
