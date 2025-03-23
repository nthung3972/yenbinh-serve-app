<?php

namespace App\Http\Requests\ApartmentRequest;

use Illuminate\Foundation\Http\FormRequest;

class AddMultipleResidentRequest extends FormRequest
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
            'residents' => 'required|array|min:1',
            'residents.*.full_name' => 'required|string|max:255',
            'residents.*.date_of_birth' => 'required|date',
            'residents.*.phone_number' => 'required|string|max:20',
            'residents.*.email' => 'nullable|email|max:255',
            'residents.*.registration_date' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'residents.required' => 'Danh sách cư dân không được để trống.',
            'residents.*.full_name.required' => 'Vui lòng nhập họ và tên cho từng cư dân.',
            'residents.*.date_of_birth.required' => 'Vui lòng nhập ngày sinh cho từng cư dân.',
            'residents.*.phone_number.required' => 'Vui lòng nhập số điện thoại cho từng cư dân.',
            'residents.*.registration_date.required' => 'Vui lòng nhập ngày đăng ký cho từng cư dân.',
        ];
    }
}
