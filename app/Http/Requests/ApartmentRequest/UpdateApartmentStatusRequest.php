<?php

namespace App\Http\Requests\ApartmentRequest;

use App\Models\Apartment;
use App\Models\ApartmentResident;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateApartmentStatusRequest extends FormRequest
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
            'apartment_id' => 'required|exists:apartments,apartment_id',
            'apartment_number' => 'required',
            'building_id' => 'required|exists:buildings,building_id',
            'area' => 'required',
            'floor_number' => 'required',
            'ownership_type' => 'required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Kiểm tra số lượng cư dân trong căn hộ
            $residentCount = ApartmentResident::where('apartment_id', $this->input('apartment_id'))->count();

            // Nếu muốn update status về 0 (không có người)
            if ($this->input('status') == 0) {
                // Kiểm tra nếu căn hộ có cư dân
                if ($residentCount > 0) {
                    $validator->errors()->add(
                        'status', 
                        'Không thể cập nhật trạng thái căn hộ khi vẫn còn cư dân.'
                    );
                }
            }
            
            // Nếu muốn update status về 1 (có người)
            if ($this->input('status') == 1 || $this->input('status') == 2) {
                // Kiểm tra nếu căn hộ không có cư dân
                if ($residentCount == 0) {
                    $validator->errors()->add(
                        'status', 
                        'Không thể cập nhật trạng thái căn hộ khi không có cư dân.'
                    );
                }
            }
        });
    }
}
