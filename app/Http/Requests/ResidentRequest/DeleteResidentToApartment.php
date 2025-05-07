<?php

namespace App\Http\Requests\ResidentRequest;

use Illuminate\Foundation\Http\FormRequest;

class DeleteResidentToApartment extends FormRequest
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
            'resident_id' => 'required|exists:residents,resident_id',
            'apartment_id' => 'required|exists:apartments,apartment_id',
        ];
    }
}
