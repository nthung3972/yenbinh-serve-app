<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Shift;
use App\Models\BuildingShift;
use App\Models\BuildingPersonnel;
use App\Models\StaffAssignment;
use Illuminate\Support\Facades\Auth;

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
}
