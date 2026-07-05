<?php

namespace App\Core\Users\Requests;

use App\Core\Users\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

/**
 * Core\Users — validation for creating a user from the panel.
 * Authorization delegates to UserPolicy so there is one source of truth.
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email address already exists.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
