<?php

namespace App\Core\Users\Controllers;

use App\Core\Users\Models\User;
use App\Core\Users\Requests\StoreUserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

/**
 * Core\Users — persists a new user (penova.users.store).
 * Authorization lives in StoreUserRequest + the users.manage route
 * middleware; see ListUsersController for the module-wide notes.
 */
class StoreUserController extends Controller
{
    public function __invoke(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('penova.users.index')->with('success', __('User created.'));
    }
}
