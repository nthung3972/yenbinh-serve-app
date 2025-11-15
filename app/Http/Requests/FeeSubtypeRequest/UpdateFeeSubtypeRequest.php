<?php

namespace App\Http\Requests\FeeSubtypeRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeSubtypeRequest extends FormRequest
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
            'fee_types_id' => 'required|exists:fee_types,fee_types_id',
            'code' => 'required|unique:fee_subtypes,code,' . $this->route('id') . ',fee_subtypes_id',
            'name' => 'required|string',
            'unit' => 'required|string',
            'note' => 'nullable|string',
        ];
    }
}
