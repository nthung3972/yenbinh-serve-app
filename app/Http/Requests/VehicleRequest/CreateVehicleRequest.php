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
            '*.building_id' => 'required|exists:buildings,building_id',
            '*.resident_id' => 'required|exists:residents,resident_id',
            '*.apartment_number' => 'required',
            '*.license_plate' => 'nullable|unique:vehicles,license_plate',
            '*.vehicle_type_id' => 'required|exists:vehicle_types,vehicle_type_id',
            '*.parking_slot' => 'nullable|string',
            '*.created_at' => 'nullable|date',
            '*.status' => 'nullable|integer|in:0,1',
            '*.vehicle_company' => 'nullable|string',
            '*.vehicle_model' => 'nullable|string',
            '*.vehicle_color' => 'nullable|string',

        ];
    }
}
