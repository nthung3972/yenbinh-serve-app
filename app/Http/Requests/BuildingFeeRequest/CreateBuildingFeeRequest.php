<?php

namespace App\Http\Requests\BuildingFeeRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBuildingFeeRequest extends FormRequest
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
            'building_id' => 'required|exists:buildings,building_id',
            'fee_types_id' => 'required|exists:fee_types,fee_types_id',
            'fee_subtypes_id' => [
                'required',
                'exists:fee_subtypes,fee_subtypes_id',
                Rule::unique('building_fees')->where(function ($query) {
                return $query->where('building_id', $this->building_id)
                             ->where('fee_types_id', $this->fee_types_id)
                             ->where('fee_subtypes_id', $this->fee_subtypes_id);
            }),
            ],
            'price' => 'required',
            'unit' => 'required|string',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'fee_types_id.required' => 'Vui lòng chọn loại phí.',
            'fee_subtypes_id.required' => 'Vui lòng chọn loại phí con.',
            'price.required' => 'Vui lòng điền giá dịch vụ.',
            'unit.required' => 'Vui lòng điền đơn vị tính.',
            'effective_from.required' => 'Vui lòng chọn ngày áp dụng.',
            'fee_subtypes_id.unique' => 'Tòa nhà này đã có loại phí và loại phí con này rồi.'
        ];
    }
}
