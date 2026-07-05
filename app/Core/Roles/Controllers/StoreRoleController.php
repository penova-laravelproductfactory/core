<?php

namespace App\Core\Roles\Controllers;

use App\Core\Roles\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Core\Roles — creates a role with its permission set (penova.roles.store).
 */
class StoreRoleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create($validated);
        $role->permissions()->sync($validated['permissions'] ?? []);

        return back()->with('success', __('Role created.'));
    }
}
