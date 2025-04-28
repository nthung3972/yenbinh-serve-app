<?php

namespace App\Http\Requests\BuildingRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:buildings,name,'.$this->route('id'). ',building_id',
            ],
            'address' => 'required|string|max:255',
            'floors' => 'required|integer|min:1',
            'image' => 'nullable|string',
            'total_area' => 'required|numeric|min:0',
            'management_fee_per_m2' => 'required|numeric|min:0',
            'management_board_fee_per_m2' => 'required|numeric|min:0',
            'status' => 'required|integer|in:0,1',
            'building_type' => 'required|string|in:residential,commercial,mixed'
        ];
    }
}
