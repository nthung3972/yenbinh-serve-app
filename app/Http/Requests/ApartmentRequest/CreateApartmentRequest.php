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
            'ownership_type' => 'nullable|string|in:own,lease,lease_back,mortgage,shared_ownership',
            'apartment_type' => 'nullable|string|in:studio,1bedroom,2bedroom,3bedroom,penthouse,duplex,dualkey',
            'notes' => 'nullable|string|max:255',
        ];
    }
}
