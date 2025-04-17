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
            '*.license_plate' => 'required|unique:vehicles,license_plate',
            '*.vehicle_type_id' => 'required|exists:vehicle_types,vehicle_type_id',
            '*.parking_slot' => 'nullable|string',
            '*.created_at' => 'required|date',
            '*.status' => 'required|integer|in:0,1',
            '*.vehicle_company' => 'required|string',
            '*.vehicle_model' => 'required|string',
            '*.vehicle_color' => 'required|string',

        ];
    }
}
