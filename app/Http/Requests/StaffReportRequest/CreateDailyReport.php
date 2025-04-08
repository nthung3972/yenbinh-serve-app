<?php

namespace App\Http\Requests\StaffReportRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateDailyReport extends FormRequest
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
            'report_date' => [
                'required',
                'date',
                Rule::unique('daily_reports')->where(function ($query) {
                    return $query->where('building_id', $this->building_id);
                }),
            ],
            'building_id' => 'required|exists:buildings,building_id',
            'notes' => 'nullable|string',
            'shifts' => 'required|array|min:1',
        ];
    }

    public function messages()
    {
        return [
            'report_date.unique' => 'Tòa nhà này đã có báo cáo cho ngày được chọn.',
        ];
    }
}
