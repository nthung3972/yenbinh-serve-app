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
            'full_name' => 'required|string|max:255',
            'id_card_number' => 'required|unique:residents,id_card_number',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'required|unique:residents,email',

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

                        // Nếu là chủ hộ (role_in_partment = 0)
                        if ($apartment['role_in_apartment'] == 0) {
                            // Kiểm tra căn hộ đã có chủ hộ chưa
                            $existingOwner = ApartmentResident::where('apartment_id', $existingApartment->apartment_id)
                                ->where('role_in_apartment', 0)
                                ->exists();
                                
                            if ($existingOwner) {
                                $fail("Căn hộ {$apartment['apartment_number']} đã có chủ hộ.");
                            }
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
        ];
    }
}
