<?php

namespace App\Core\Users\Controllers;

use App\Core\Roles\Models\Role;
use App\Core\Users\Models\User;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Users — the "edit user" form page (penova.users.edit).
 * See ListUsersController for the module-wide authorization notes.
 */
class EditUserController extends Controller
{
    public function __invoke(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('Core/Users/Edit', [
            'user' => $user->load('roles:id,name'),
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
