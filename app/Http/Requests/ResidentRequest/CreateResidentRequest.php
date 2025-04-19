<?php

namespace App\Http\Requests\ResidentRequest;

use App\Models\Apartment;
use App\Models\ApartmentResident;
use Illuminate\Foundation\Http\FormRequest;

class CreateResidentRequest extends FormRequest
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
            // Thông tin cư dân
            'full_name' => 'nullable|string|max:255',
            'id_card_number' => 'nullable|unique:residents,id_card_number',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|unique:residents,email',

            // Kiểm tra căn hộ
            'apartments' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    // Kiểm tra từng căn hộ
                    foreach ($value as $apartment) {
                        // Kiểm tra căn hộ có tồn tại không
                        $existingApartment = Apartment::where('apartment_number', $apartment['apartment_number'])->first();

                        if (!$existingApartment) {
                            $fail("Căn hộ {$apartment['apartment_number']} không tồn tại.");
                            continue;
                        }

                        // Kiểm tra căn hộ đã có chủ hộ chưa
                        $existingOwner = ApartmentResident::where('apartment_id', $existingApartment->apartment_id)
                            ->where('role_in_apartment', 0)
                            ->exists();

                        // Nếu đã có chủ hộ nhưng vẫn thêm cư dân có role = 0 => Báo lỗi
                        if ($existingOwner && $apartment['role_in_apartment'] == 0) {
                            $fail("Căn hộ {$apartment['apartment_number']} đã có chủ hộ.");
                        }

                        // Kiểm tra căn hộ đã người thuê chính hay chưa
                        $existingTenant = ApartmentResident::where('apartment_id', $existingApartment->apartment_id)
                            ->where('role_in_apartment', 1)
                            ->exists();

                        // Nếu đã có người thuê chính nhưng vẫn thêm cư dân có role = 1 => Báo lỗi
                        if ($existingTenant && $apartment['role_in_apartment'] == 1) {
                            $fail("Căn hộ {$apartment['apartment_number']} đã có người thuê chính.");
                        }

                         // Nếu chưa có chủ hộ nhưng cư dân đầu tiên không phải chủ hộ => Báo lỗi
                         if (!$existingOwner && !$existingTenant && $apartment['role_in_apartment'] != 0  && $apartment['role_in_apartment'] != 1) {
                            $fail("Căn hộ {$apartment['apartment_number']} chưa có chủ hộ. Cư dân đầu tiên phải là chủ hộ hoặc người thuê chính.");
                        }
                    }

                    // Kiểm tra trùng apartment_number
                    $uniqueApartments = collect($value)
                        ->pluck('apartment_number')
                        ->unique();

                    if ($uniqueApartments->count() !== count($value)) {
                        $fail('Các căn hộ không được trùng nhau.');
                    }
                }
            ],

            // Kiểm tra từng căn hộ
            'apartments.*.apartment_number' => 'required|string',
            'apartments.*.role_in_apartment' => 'required|in:0,1,2',
            'apartments.*.notes' => 'nullable|string',
        ];
    }
}
