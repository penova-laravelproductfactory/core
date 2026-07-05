<?php

namespace App\Core\Users\Controllers;

use App\Core\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * Core\Users — deletes a user (penova.users.destroy).
 * See ListUsersController for the module-wide authorization notes.
 */
class DeleteUserController extends Controller
{
    public function __invoke(User $user): RedirectResponse
    {
        // Policy also blocks self-deletion (see UserPolicy::delete).
        $this->authorize('delete', $user);

        $user->delete();

        return back()->with('success', __('User deleted.'));
    }
}
