<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,staff',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'buildings' => 'required|array',
            'buildings.*.building_id' => 'required|exists:buildings,building_id',
            'buildings.*.role' => 'required|string|in:manager,monitor',
            'buildings.*.assigned_tasks' => 'nullable',
        ];
    }
}
