<?php

namespace App\Core\Roles\Controllers;

use App\Core\DataTable\DataTableBuilder;
use App\Core\Roles\Models\Permission;
use App\Core\Roles\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Roles — the roles index page (penova.roles.index), including the
 * permission catalogue for the inline create/edit modal.
 * Route-level guard: permission:roles.manage (see routes.php).
 */
class ListRolesController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Core/Roles/Index', [
            'roles' => DataTableBuilder::for(Role::query()->with('permissions:id')->withCount('users'))
                ->searchable(['name', 'slug'])
                ->sortable(['name', 'created_at'])
                ->paginate($request),
            'permissions' => Permission::orderBy('slug')->get(['id', 'name', 'slug']),
        ]);
    }
}
