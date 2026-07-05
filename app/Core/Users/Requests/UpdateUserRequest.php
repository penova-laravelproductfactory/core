<?php

namespace App\Core\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

/**
 * Core\Users — validation for updating a user from the panel.
 * Password is optional: blank means "keep the current one".
 * Authorization delegates to UserPolicy so there is one source of truth.
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
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
