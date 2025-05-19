<?php

namespace App\Http\Requests\BuildingPersonnelRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBuildingPersonnelRequest extends FormRequest
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
            'start_date' => 'required|date',
            'status' => 'required|integer|in:0,1',
            'inactive_date' => [
                'nullable',
                'date',
                Rule::requiredIf(fn() => request('status') == 1),
            ],
            'personnel_phone' => 'required|string|max:15',
            'personnel_address' => 'nullable|string|max:255',
            'position' => 'required|string|in:accountant,cleaner,security,receptionist,technical,supervisor,assistant_manager,manager',
            'monthly_salary' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'inactive_date.required' => 'Trường ngày nghỉ việc là bắt buộc khi nhân sự đã nghỉ.',
        ];
    }
}
