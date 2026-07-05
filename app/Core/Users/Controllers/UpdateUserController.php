<?php

namespace App\Core\Users\Controllers;

use App\Core\Users\Models\User;
use App\Core\Users\Requests\UpdateUserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

/**
 * Core\Users — applies edits to a user (penova.users.update).
 * Authorization lives in UpdateUserRequest + the users.manage route
 * middleware; see ListUsersController for the module-wide notes.
 */
class UpdateUserController extends Controller
{
    public function __invoke(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('penova.users.index')->with('success', __('User updated.'));
    }
}
