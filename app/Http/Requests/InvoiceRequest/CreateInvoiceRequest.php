<?php

namespace App\Http\Requests\InvoiceRequest;

use App\Models\Apartment;
use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
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
            'apartment_number' => 'required',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after:invoice_date',
            'total_amount' => 'required|numeric',
            'status' => 'required|in:0,1,2',
            'invoice_detail' => 'required|array',
            'invoice_detail.*.service_name' => 'required|string',
            'invoice_detail.*.quantity' => 'required|numeric|min:0',
            'invoice_detail.*.price' => 'required|numeric|min:0',
            'invoice_detail.*.amount' => 'required|numeric|min:0',
            'invoice_detail.*.description' => 'string|nullable'
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
        });
    }
}
