<?php

namespace App\Http\Requests\BuildingPersonnelRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateBuildingPersonnelRequest extends FormRequest
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
            'building_id' => 'required|exists:buildings,building_id',
            'personnel_name' => 'required|string|max:255',
            'personnel_birth' => 'required|date',
            'personnel_phone' => 'required|string|max:15',
            'personnel_address' => 'nullable|string|max:255',
            'position' => 'required|string|in:accountant,cleaner,security,receptionist,technical,supervisor,assistant_manager,manager',
            'monthly_salary' => 'required|numeric'
        ];
    }
}
