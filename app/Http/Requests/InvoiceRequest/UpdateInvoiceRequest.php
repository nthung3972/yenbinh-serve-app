<?php

namespace App\Http\Requests\InvoiceRequest;

use App\Models\Apartment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after:invoice_date',
            'total_amount' => 'required|numeric',
            'fees' => 'required|array',
            'fees.*.fee_type_id' => 'required|exists:fee_types,fee_type_id',
            'fees.*.amount' => 'required|numeric|min:0',
            'fees.*.quantity' => 'nullable|numeric|min:0',
            'fees.*.price' => 'nullable|numeric|min:0',
            'fees.*.description' => 'string|required'
        ];
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         // Kiểm tra tồn tại của căn hộ
    //         $apartment = Apartment::where('apartment_number', $this->input('apartment_number'))->first();
            
    //         if (!$apartment) {
    //             $validator->errors()->add('apartment_number', "Căn hộ {$this->input('apartment_number')} không tồn tại");
    //             return;
    //         }
    //     });
    // }
}
