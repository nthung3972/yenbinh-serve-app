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
use App\Http\Requests\StaffReportRequest\CreateDailyReport;

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

    public function createDailyReport(CreateDailyReport $request)
    {
        $user = Auth::user();

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

    public function getAllReports(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $buildingId = $request->input('building_id');
        $status = $request->input('status');
        $reportDateFrom = $request->input('report_date_from');
        $reportDateTo = $request->input('report_date_to');

        $query  = DailyReport::with(['building', 'createdBy', 'shiftReports.shiftReportStaff'])
            ->latest('report_date');

        // Lọc theo building và status
        $query ->when($buildingId, fn($q) => $q->where('building_id', $buildingId))
                ->when($status, fn($q) => $q->where('status', $status));

        // Chỉ cần lọc theo khoảng ngày
        if ($reportDateFrom && $reportDateTo) {
            // Nếu muốn lọc chính xác 1 ngày, truyền cùng giá trị cho from và to
            $query->whereBetween('report_date', [$reportDateFrom, $reportDateTo]);
        } elseif ($reportDateFrom) {
            $query->where('report_date', '>=', $reportDateFrom);
        } elseif ($reportDateTo) {
            $query->where('report_date', '<=', $reportDateTo);
        };
        
        /** @var \Illuminate\Pagination\LengthAwarePaginator $reports */
        $reports = $query->paginate($perPage);

        // Biến đổi dữ liệu trong collection trước khi trả ra
        $reports->getCollection()->transform(function ($report) {
            $totalShifts = $report->shiftReports ? $report->shiftReports->count() : 0;
            $totalStaff = $report->shiftReports
                ? $report->shiftReports->sum(function ($shift) {
                    return $shift->shiftReportStaff ? $shift->shiftReportStaff->count() : 0;
                })
                : 0;

            return [
                'report_id' => $report->daily_report_id,
                'building' => $report->building->name ?? null,
                'report_date' => $report->report_date,
                'created_by' => $report->createdBy->name ?? null,
                'status' => $report->status,
                'notes' => $report->notes,
                'total_shifts' => $totalShifts,
                'total_staff' => $totalStaff,
                'created_at' => $report->created_at->toDateTimeString()
            ];
        });

        return response()->json($reports);
    }

    public function getDailyReportDetail($id)
    {
        $report = DailyReport::with([
            'building',
            'createdBy',
            'shiftReports.shift',
            'shiftReports.shiftReportStaff.buildingPersonnel'
        ])->where('daily_report_id', $id)->firstOrFail();

        return response()->json([
            'daily_report_id' => $report->daily_report_id,
            'building' => $report->building->name ?? null,
            'report_date' => $report->report_date,
            'created_by' => $report->createdBy->name ?? null,
            'status' => $report->status,
            'notes' => $report->notes,
            'shifts' => $report->shiftReports->map(function ($shiftReport) {
                return [
                    'shift_report_id' => $shiftReport->shift_report_id,
                    'shift_id' => $shiftReport->shift_id,
                    'shift_name' => $shiftReport->shift->name ?? null,
                    'start_time' => $shiftReport->shift->start_time ?? null,
                    'end_time' => $shiftReport->shift->end_time ?? null,
                    'status' => $shiftReport->status,
                    'notes' => $shiftReport->notes,
                    'staffs' => $shiftReport->shiftReportStaff->map(function ($staff) {
                        return [
                            'shift_report_staff_id' => $staff->shift_report_staff_id,
                            'building_personnel_id' => $staff->building_personnel_id,
                            'status' => $staff->status,
                            'working_hours' => $staff->working_hours,
                            'performance_note' => $staff->performance_note,
                            // Lấy thêm thông tin nhân viên
                            'personnel_name' => $staff->buildingPersonnel->personnel_name ?? null,
                            'position' => $staff->buildingPersonnel->position ?? null,
                            'phone' => $staff->buildingPersonnel->personnel_phone ?? null,
                            'email' => $staff->buildingPersonnel->personnel_address ?? null,
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function getReportsByStaff(Request $request)
    {
        $user = auth()->user();

        $reportDateFrom = $request->input('report_date_from');
        $reportDateTo = $request->input('report_date_to');

        $query = DailyReport::with('building', 'shiftReports.shiftReportStaff')
            ->where('created_by', $user->id);

        if ($reportDateFrom && $reportDateTo) {
            $query->whereBetween('report_date', [$reportDateFrom, $reportDateTo]);
        } elseif ($reportDateFrom) {
            $query->whereDate('report_date', '>=', $reportDateFrom);
        } elseif ($reportDateTo) {
            $query->whereDate('report_date', '<=', $reportDateTo);
        }

        $reports = $query->orderByDesc('report_date')->paginate(10);

        $reports->getCollection()->transform(function ($report) {
            return [
                'daily_report_id' => $report->daily_report_id,
                'report_date' => $report->report_date,
                'status' => $report->status,
                'notes' => $report->notes,
                'building_name' => $report->building->name ?? null,
                'shift_count' => $report->shiftReports->count(),
                'staff_count' => $report->shiftReports->sum(fn ($shift) => $shift->shiftReportStaff->count()),
            ];
        });

        return response()->json($reports);
    }
}
