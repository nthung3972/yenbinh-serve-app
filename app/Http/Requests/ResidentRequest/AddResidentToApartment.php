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
            $apartment = Apartment::where('apartment_number', $this->input('apartment_number'))->first();

            if (!$apartment) {
                $validator->errors()->add('apartment_number', "Căn hộ {$this->input('apartment_number')} không tồn tại");
                return;
            }

            // Kiểm tra xem cư dân đã sở hữu căn hộ này chưa
            $existingOwnership = ApartmentResident::where('resident_id', $this->input('resident_id'))
                ->where('apartment_id', $apartment->apartment_id)
                ->whereNull('move_out_date')
                ->exists();

            if ($existingOwnership) {
                $validator->errors()->add('apartment_number', "Cư dân đã sở hữu căn hộ {$apartment->apartment_number}");
                return;
            }

            $inputRole = $this->input('role_in_apartment');

            // Kiểm tra căn hộ đã có chủ hộ chưa (role = 0)
            $existingOwner = ApartmentResident::where('apartment_id', $apartment->apartment_id)
                ->where('role_in_apartment', 0)
                ->whereNull('move_out_date')
                ->exists();

            if ($existingOwner && $inputRole == 0) {
                $validator->errors()->add('role_in_apartment', "Căn hộ {$apartment->apartment_number} đã có chủ hộ.");
                return;
            }

            // Kiểm tra căn hộ đã có người thuê chính chưa (role = 1)
            $existingTenant = ApartmentResident::where('apartment_id', $apartment->apartment_id)
                ->where('role_in_apartment', 1)
                ->whereNull('move_out_date')
                ->exists();

            if ($existingTenant && $inputRole == 1) {
                $validator->errors()->add('role_in_apartment', "Căn hộ {$apartment->apartment_number} đã có người thuê chính.");
                return;
            }

            // Nếu chưa có người thuê chính nhưng thêm cư dân có role = 3 => lỗi
            if (!$existingTenant && $inputRole == 3) {
                $validator->errors()->add('role_in_apartment', "Căn hộ {$apartment->apartment_number} chưa có người thuê chính, người thuê đầu tiên phải là người thuê chính.");
                return;
            }

            // Nếu chưa có chủ hộ & chưa có người thuê chính nhưng role không phải là 0 hoặc 1 => lỗi
            if (!$existingOwner && !$existingTenant && !in_array($inputRole, [0, 1])) {
                $validator->errors()->add('role_in_apartment', "Căn hộ {$apartment->apartment_number} chưa có chủ hộ. Cư dân đầu tiên phải là chủ hộ hoặc người thuê chính.");
                return;
            }
        });
    }
}
