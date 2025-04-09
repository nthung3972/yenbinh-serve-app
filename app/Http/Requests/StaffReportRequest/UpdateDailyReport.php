<?php

namespace App\Http\Requests\StaffReportRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDailyReport extends FormRequest
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
        $id = $this->route('id'); // Lấy ID từ route parameter

        return [
            'building_id' => 'required|exists:buildings,building_id',
            'report_date' => [
                'required',
                'date',
                Rule::unique('daily_reports')
                    ->where(function ($query) {
                        return $query->where('building_id', $this->building_id);
                    })
                    ->ignore($id, 'daily_report_id'), // Bỏ qua bản ghi hiện tại
            ],
            'notes' => 'nullable|string',
            'shifts' => 'required|array',
            'shifts.*.shiftId' => 'required|exists:shifts,shift_id',
            'shifts.*.staffList' => 'required|array',
            'shifts.*.staffList.*.id' => 'required|exists:building_personnel,building_personnel_id',
            'shifts.*.staffList.*.workTimeStart' => 'required|date_format:H:i',
            'shifts.*.staffList.*.workTimeEnd' => 'required|date_format:H:i',
            'shifts.*.staffList.*.isLate' => 'boolean',
            'shifts.*.staffList.*.isOvertime' => 'boolean',
            'shifts.*.staffList.*.performanceNote' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'building_id.required' => 'Vui lòng chọn tòa nhà.',
            'building_id.exists' => 'Tòa nhà không tồn tại.',
            'report_date.required' => 'Vui lòng chọn ngày báo cáo.',
            'report_date.date' => 'Ngày báo cáo không hợp lệ.',
            'report_date.unique' => 'Đã tồn tại báo cáo cho ngày này tại tòa nhà này.',
            'shifts.required' => 'Vui lòng cung cấp danh sách ca làm việc.',
            'shifts.*.shiftId.required' => 'ID ca làm việc là bắt buộc.',
            'shifts.*.shiftId.exists' => 'Ca làm việc không tồn tại.',
            'shifts.*.staffList.required' => 'Danh sách nhân viên là bắt buộc.',
            'shifts.*.staffList.*.id.required' => 'ID nhân viên là bắt buộc.',
            'shifts.*.staffList.*.id.exists' => 'Nhân viên không tồn tại.',
            'shifts.*.staffList.*.workTimeStart.required' => 'Giờ bắt đầu không được để trống.',
            'shifts.*.staffList.*.workTimeEnd.required' => 'Giờ kết thúc không được để trống.',
            'shifts.*.staffList.*.workTimeStart.date_format' => 'Giờ bắt đầu phải có định dạng H:i (VD: 08:00).',
            'shifts.*.staffList.*.workTimeEnd.date_format' => 'Giờ kết thúc phải có định dạng H:i (VD: 17:00).',
        ];
    }
}
