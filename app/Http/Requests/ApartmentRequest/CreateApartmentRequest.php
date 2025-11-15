<?php

namespace App\Http\Requests\ApartmentRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateApartmentRequest extends FormRequest
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
            'apartment_number' => [
                'required',
                'string',
                'max:255',
                'unique:apartments,apartment_number',
            ],
            'building_id' => 'required|exists:buildings,building_id',
            'area' => 'required',
            'floor_number' => 'required',
            'apartment_type' => 'required',
            'notes' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'apartment_number.required' => 'Vui lòng điền số căn hộ.',
            'area.required' => 'Vui lòng điền diện tích căn hộ.',
            'floor_number.required' => 'Vui lòng điền số tầng.',
            'apartment_type.required' => 'Vui lòng chọn loại căn hộ.',
            'apartment_number.unique' => 'Số căn hộ đã tồn tại.'
        ];
    }
}
