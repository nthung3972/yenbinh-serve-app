<?php

namespace App\Http\Requests\ResidentRequest;

use App\Models\Apartment;
use App\Models\ApartmentResident;
use Illuminate\Foundation\Http\FormRequest;

class AddResidentToApartment extends FormRequest
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
            'apartment_number' => 'required|string',
            'role_in_apartment' => 'required|in:0,1,2'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Kiểm tra tồn tại của căn hộ
            $apartment = Apartment::where('apartment_number', $this->input('apartment_number'))->first();

            if (!$apartment) {
                $validator->errors()->add('apartment_number', "Căn hộ {$this->input('apartment_number')} không tồn tại");
                return;
            }

            // Kiểm tra xem cư dân đã sở hữu căn hộ này chưa
            $existingOwnership = ApartmentResident::where('resident_id', $this->input('resident_id'))
                ->where('apartment_id', $apartment->apartment_id)
                ->exists();

            if ($existingOwnership) {
                $validator->errors()->add('apartment_number', "Cư dân đã sở hữu căn hộ {$apartment->apartment_number}");
                return;
            }

            // Kiểm tra căn hộ đã có chủ sở hữu chưa (role = 0)
            $existingOwner = ApartmentResident::where('apartment_id', $apartment->apartment_id)
                ->where('role_in_apartment', 0)
                ->exists();

            if ($existingOwner && $this->input('role_in_apartment') == 0) {
                $validator->errors()->add('role_in_apartment', "Căn hộ {$apartment->apartment_number} đã có chủ sở hữu");
                return;
            }
        });
    }
}
