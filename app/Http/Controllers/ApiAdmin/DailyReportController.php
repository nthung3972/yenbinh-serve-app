<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use App\Models\BuildingShift;
use App\Models\BuildingPersonnel;
use App\Models\ShiftReport;
use App\Models\ShiftReportStaff;
use App\Models\StaffAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    public function getFormInfo($building_id)
    {
        $user = Auth::user();

        // 1. Kiểm tra quyền của staff với tòa nhà
        $hasAccess = StaffAssignment::where('staff_id', $user->id)
            ->where('building_id', $building_id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập tòa nhà này.'
            ], 403);
        }

        // 2. Lấy danh sách ca làm việc trong tòa nhà
        $shifts = BuildingShift::with('shift')
            ->where('building_id', $building_id)
            ->where('is_active', true)
            ->get()
            ->map(function ($item) {
                return [
                    'shiftId' => $item->shift->shift_id,
                    'shiftName' => $item->shift->name,
                    'startTime' => $item->shift->start_time,
                    'endTime' => $item->shift->end_time,
                    'type' => $item->shift->type,
                ];
            });

        // 3. Lấy danh sách nhân viên của tòa nhà
        $personnel = BuildingPersonnel::where('building_id', $building_id)
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->building_personnel_id,
                    'name' => $staff->personnel_name,
                    'position' => $staff->position,
                ];
            });

        return response()->json([
            'date' => now()->toDateString(),
            'building_id' => $building_id,
            'shifts' => $shifts,
            'personnel' => $personnel
        ]);
    }

    public function createDailyReport(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'building_id' => 'required|exists:buildings,building_id',
            'report_date' => 'required|date|unique:daily_reports,report_date',
            'notes' => 'nullable|string',
            'shifts' => 'required|array|min:1',
        ]);

        // 1. Kiểm tra quyền của staff với tòa nhà
        $hasAccess = StaffAssignment::where('staff_id', $user->id)
            ->where('building_id', $request->building_id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập tòa nhà này.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // 1. Tạo báo cáo ngày
            $dailyReport = DailyReport::create([
                'building_id' => $request->building_id,
                'report_date' => $request->report_date,
                'created_by' => $user->id,
                'status' => 'draft',
                'notes' => $request->notes,
            ]);

            // 2. Lặp từng ca và tạo shift_report
            foreach ($request->shifts as $shiftData) {
                $shiftReport = ShiftReport::create([
                    'daily_report_id' => $dailyReport->daily_report_id,
                    'shift_id' => $shiftData['shiftId'],
                    'created_by' => $user->id,
                    'notes' => null,
                    'status' => 'pending',
                ]);

                // 3. Tạo shift_report_staff cho từng nhân viên trong ca
                foreach ($shiftData['staffList'] as $person) {
                    ShiftReportStaff::create([
                        'shift_report_id' => $shiftReport->shift_report_id,
                        'building_personnel_id' => $person['id'],
                        'status' => 'present', // mặc định là 'present'
                        'working_hours' => null,
                        'performance_note' => null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Tạo báo cáo ngày thành công',
                'data' => $dailyReport->load('shiftReports.shiftReportStaff'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi tạo báo cáo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
