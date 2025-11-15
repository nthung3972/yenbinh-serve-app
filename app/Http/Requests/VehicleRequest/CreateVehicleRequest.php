<?php

namespace App\Http\Requests\VehicleRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateVehicleRequest extends FormRequest
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
            '*.vehicle_type_id' => 'required|exists:vehicle_types,vehicle_type_id',
            '*.building_id' => 'required|exists:buildings,building_id',
            '*.resident_id' => 'required|exists:residents,resident_id',
            '*.apartment_number' => 'required|exists:apartments,apartment_number',
            '*.license_plate' => 'required|string',
            '*.parking_slot' => 'nullable|string',
            '*.vehicle_company' => 'nullable|string',
            '*.vehicle_model' => 'nullable|string',
            '*.vehicle_color' => 'nullable|string',
            '*.status' => 'nullable|integer|in:0,1',
            '*.note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            '*.vehicle_type_id.required' => 'Vui lòng chọn loại xe.',
            '*.resident_id.required' => 'Vui lòng chọn cư dân.',
            '*.apartment_number.required' => 'Vui lòng chọn căn hộ.',
            '*.license_plate.required' => 'Vui lòng nhập biển số xe.',
        ];
    }
}
