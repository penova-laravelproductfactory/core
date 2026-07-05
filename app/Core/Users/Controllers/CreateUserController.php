<?php

namespace App\Core\Users\Controllers;

use App\Core\Roles\Models\Role;
use App\Core\Users\Models\User;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Users — the "new user" form page (penova.users.create).
 * See ListUsersController for the module-wide authorization notes.
 */
class CreateUserController extends Controller
{
    public function __invoke(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Core/Users/Create', [
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
