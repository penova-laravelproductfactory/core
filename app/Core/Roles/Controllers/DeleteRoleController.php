<?php

namespace App\Core\Roles\Controllers;

use App\Core\Roles\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * Core\Roles — deletes a role (penova.roles.destroy).
 * RolePolicy::delete guards protected roles (e.g. "admin").
 */
class DeleteRoleController extends Controller
{
    public function __invoke(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $role->delete();

        return back()->with('success', __('Role deleted.'));
    }
}
