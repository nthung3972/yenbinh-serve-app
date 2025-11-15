<?php

namespace App\Http\Requests\FeeTypeRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateFeeTypeRequest extends FormRequest
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
            'code' => 'required|string|unique:fee_types,code',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ];
    }
}
