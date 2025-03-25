<?php

namespace App\Http\Requests\ApartmentRequest;

use App\Models\ApartmentResident;
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
            '*.apartment_id' => 'required|exists:apartments,apartment_id',
            '*.full_name' => 'required|string|max:255',
            '*.id_card_number' => 'string|max:255',
            '*.date_of_birth' => 'required|date',
            '*.gender' => 'required',
            '*.phone_number' => 'required|string|max:20',
            '*.email' => 'required|email|max:255',
            '*.move_in_date' => 'required|date',
            '*.resident_type' => 'required',
            '*.registration_date' => 'required|date',
            '*.registration_status' => 'required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $residents = collect($this->all());

            // Nếu cần bắt buộc chỉ có 1 chủ hộ, bỏ comment dòng dưới
            if ($residents->where('resident_type', 0)->count() > 1) {
                $validator->errors()->add('general.resident_type', 'Chỉ có thể có một chủ hộ trong căn hộ.');
            }

            if ($residents->contains('resident_type', 0)) {
                $apartmentIds = collect($this->input())->pluck('apartment_id')->unique();

                $ownerCount = ApartmentResident::whereIn('apartment_id', $apartmentIds)
                    ->where('role_in_apartment', 0)
                    ->count();

                if ($ownerCount > 0) {
                    $validator->errors()->add('general.resident_type', 'Căn hộ này đã có chủ hộ.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            '*.full_name.required' => 'Vui lòng nhập họ và tên cho từng cư dân.',
        ];
    }
}
