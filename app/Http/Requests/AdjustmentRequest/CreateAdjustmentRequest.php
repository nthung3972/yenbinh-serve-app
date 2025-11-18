<?php

namespace App\Http\Requests\AdjustmentRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdjustmentRequest extends FormRequest
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
            'invoice_id' => [
                'required',
                'integer',
                'exists:invoices,invoice_id'
            ],
            'adjustment_type' => [
                'required',
                'string',
                'in:discount,waiver,credit,penalty,extra_service,back_charge'
            ],
            'amount' => [
                'required',
                'numeric',
                'not_in:0',
            ],
            'title' => [
                'required',
                'string',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'reason' => [
                'required',
                'string',
                'max:500',
                'min:10'
            ],
            'invoice_detail_id' => [
                'nullable',
                'integer',
                'exists:invoice_details,invoice_detail_id'
            ],
            'fee_types_id' => [
                'nullable',
                'integer',
                'exists:fee_types,fee_types_id'
            ],
            'attachment_url' => [
                'nullable',
                'url',
                'max:500'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Vui lòng chọn hóa đơn',
            'invoice_id.exists' => 'Hóa đơn không tồn tại',
            'adjustment_type.required' => 'Vui lòng chọn loại điều chỉnh',
            'adjustment_type.in' => 'Loại điều chỉnh không hợp lệ',
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.not_in' => 'Số tiền không được bằng 0',
            'title.required' => 'Vui lòng nhập tiêu đề',
            'title.max' => 'Tiêu đề không được quá 255 ký tự',
            'reason.required' => 'Vui lòng nhập lý do điều chỉnh',
            'reason.min' => 'Lý do phải có ít nhất 10 ký tự',
            'reason.max' => 'Lý do không được quá 500 ký tự',
            'attachment_url.url' => 'URL đính kèm không hợp lệ',
        ];
    }
}
