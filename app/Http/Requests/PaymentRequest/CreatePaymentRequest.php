<?php

namespace App\Http\Requests\PaymentRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
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
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'Vui lòng điền số tiền thanh toán.',
            'amount.min' => 'Số tiền thanh toán phải lớn hơn 0.',
            'payment_date.required' => 'Vui chọn ngày thành toán.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ];
    }
}
