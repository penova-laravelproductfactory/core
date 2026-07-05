<?php

namespace App\Core\Roles\Controllers;

use App\Core\Roles\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Core\Roles — renames a role / resyncs its permissions (penova.roles.update).
 * The slug is immutable after creation (it is the stable identifier code
 * checks against), hence no slug rule here.
 */
class UpdateRoleController extends Controller
{
    public function __invoke(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update($validated);
        $role->permissions()->sync($validated['permissions'] ?? []);

        return back()->with('success', __('Role updated.'));
    }
}
