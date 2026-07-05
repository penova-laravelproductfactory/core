<?php

namespace App\Modules\Crm\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modules\Crm — validation for creating a lead.
 * status defaults to "new" in LeadStoreController when omitted.
 */
class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware (auth) gates the demo CRM; a real product adds
        // "permission:crm.manage" + a policy here.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
