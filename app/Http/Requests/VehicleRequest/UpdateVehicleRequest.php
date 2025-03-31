<?php

namespace App\Http\Requests\VehicleRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
            'license_plate' => 'required|string|unique:vehicles,license_plate,'.$this->id.',vehicle_id',
            'apartment_number' => 'required',
            'vehicle_type' => 'required|string|in:car,motorbike,bicycle',
            'parking_slot' => 'nullable|string',
            'created_at' => 'required|date',
            'status' => 'required|integer|in:0,1'
        ];
    }
}
