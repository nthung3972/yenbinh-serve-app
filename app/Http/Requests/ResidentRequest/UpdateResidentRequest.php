<?php

namespace App\Http\Requests\ResidentRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResidentRequest extends FormRequest
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
            'full_name' => 'required|string',
            'id_card_number' => [
                'required',
                'string',
                'unique:residents,id_card_number,'.$this->resident_id. ',resident_id',
            ],
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'phone_number' => 'required|string',
            'email' => [
                'required', 
                'email', 
                'unique:residents,email,'.$this->resident_id. ',resident_id',
            ]
        ];
    }
}
