<?php

namespace App\Http\Controllers\ApiAdmin;


use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', config('constant.paginate'));
        $user = Auth::user();

        // Build base query for invoices
        $baseQuery = Invoice::query()
            ->with(['apartment', 'apartment.residents', 'apartment.building'])
            ->when($request->filled('status') || $request->status === '0' || $request->status === 0, function ($q) use ($request) {
                return $q->where('status', $request->status);
            });

        // Phân quyền
        if ($user->role !== 'admin') {
            $assignedBuildings = DB::table('staff_assignments')
                ->where('staff_id', $user->id)
                ->pluck('building_id')
                ->toArray();

            $baseQuery->whereHas('apartment', function ($q2) use ($assignedBuildings) {
                $q2->whereIn('building_id', $assignedBuildings);
            });
        } else {
            $baseQuery->when($request->building_id, fn($q) => $q->whereHas('apartment', fn($q2) => $q2->where('building_id', $request->building_id)));
        }

        // Lọc theo thời gian
        if ($request->has('period_type') && $request->has('period_value') && $request->period_value !== null) {
            if ($request->period_type === 'month') {
                $baseQuery->whereYear('invoice_date', substr($request->period_value, 0, 4))
                    ->whereMonth('invoice_date', substr($request->period_value, 5, 2));
            } elseif ($request->period_type === 'quarter') {
                [$year, $quarter] = explode('-Q', $request->period_value);
                $months = match ($quarter) {
                    '1' => [1, 2, 3],
                    '2' => [4, 5, 6],
                    '3' => [7, 8, 9],
                    '4' => [10, 11, 12]
                };
                $baseQuery->whereYear('invoice_date', $year)
                    ->whereIn(DB::raw('MONTH(invoice_date)'), $months);
            } elseif ($request->period_type === 'year') {
                $baseQuery->whereYear('invoice_date', $request->period_value);
            }
        }

        // Tổng phát hành và thanh toán
        $totals = (clone $baseQuery)->selectRaw('
        SUM(total_amount) as total_issued_amount,
        SUM(total_paid) as total_paid_amount
    ')->first();

        // Truy vấn phí quản lý & ban quản trị
        $feeQuery = DB::table('apartments as a')
            ->join('buildings as b', 'a.building_id', '=', 'b.building_id');

        if ($user->role !== 'admin') {
            $feeQuery->whereIn('b.building_id', $assignedBuildings);
        } else {
            $feeQuery->when($request->building_id, function ($query, $buildingId) {
                return $query->where('b.building_id', $buildingId);
            });
        }

        $feeQuery = $feeQuery->selectRaw('
        SUM(a.area) as total_area,
        AVG(b.management_fee_per_m2) as management_fee_per_m2,
        AVG(b.management_board_fee_per_m2) as management_board_fee_per_m2
    ')->first();

        $totalManagementFee = $feeQuery->total_area * $feeQuery->management_fee_per_m2;
        $managementDescription = 'Tổng diện tích ' . number_format($feeQuery->total_area, 2, ',', '.') . 'm², phí ' . number_format($feeQuery->management_fee_per_m2, 0, ',', '.') . 'đ/m²';

        $totalManagementBoardFee = $feeQuery->management_board_fee_per_m2
            ? $feeQuery->total_area * $feeQuery->management_board_fee_per_m2
            : 0;
        $managementBoardDescription = $feeQuery->management_board_fee_per_m2
            ? 'Tổng diện tích ' . number_format($feeQuery->total_area, 2, ',', '.') . 'm², thù lao ' . number_format($feeQuery->management_board_fee_per_m2, 0, ',', '.') . 'đ/m²'
            : 'Không có phí thù lao';

        // Truy vấn phí gửi xe
        $parkingFeesQuery = DB::table('vehicles as v')
            ->join('vehicle_types as vt', 'v.vehicle_type_id', '=', 'vt.vehicle_type_id')
            ->join('apartments as a', 'v.apartment_id', '=', 'a.apartment_id')
            ->join('building_vehicle_fees as bvf', function ($join) {
                $join->on('bvf.vehicle_type_id', '=', 'vt.vehicle_type_id')
                    ->on('bvf.building_id', '=', 'a.building_id');
            });

        if ($user->role !== 'admin') {
            $parkingFeesQuery->whereIn('a.building_id', $assignedBuildings);
        } else {
            $parkingFeesQuery->when($request->building_id, function ($query, $buildingId) {
                return $query->where('a.building_id', $buildingId);
            });
        }

        $parkingFees = $parkingFeesQuery->groupBy('vt.vehicle_type_name', 'bvf.parking_fee')
            ->selectRaw('
            vt.vehicle_type_name,
            COUNT(v.vehicle_id) as vehicle_count,
            bvf.parking_fee as parking_fee_per_vehicle
        ')
            ->get();

        $carParkingFee = 0;
        $motorbikeParkingFee = 0;
        $bicycleParkingFee = 0;
        $carDescriptionParts = [];
        $motorbikeDescriptionParts = [];
        $bicycleDescriptionParts = [];

        foreach ($parkingFees as $fee) {
            $amount = 0;
            if ($fee->vehicle_type_name === 'Ô tô' && $fee->vehicle_count > 0) {
                $firstCarFee = $fee->parking_fee_per_vehicle;
                $additionalCarFee = $firstCarFee * 1.2;
                $amount = $fee->vehicle_count == 1
                    ? $firstCarFee
                    : $firstCarFee + ($fee->vehicle_count - 1) * $additionalCarFee;
                $carParkingFee += $amount;
                $carDescriptionParts[] = "{$fee->vehicle_count} {$fee->vehicle_type_name} (1 x " . number_format($firstCarFee, 0, ',', '.') . "đ, " . ($fee->vehicle_count - 1) . " x " . number_format($additionalCarFee, 0, ',', '.') . "đ)";
            } elseif ($fee->vehicle_type_name === 'Xe máy - xe máy điện') {
                $amount = $fee->vehicle_count * $fee->parking_fee_per_vehicle;
                $motorbikeParkingFee += $amount;
                $motorbikeDescriptionParts[] = "{$fee->vehicle_count} {$fee->vehicle_type_name} x " . number_format($fee->parking_fee_per_vehicle, 0, ',', '.') . "đ";
            } elseif ($fee->vehicle_type_name === 'Xe đạp - xe đạp điện') {
                $amount = $fee->vehicle_count * $fee->parking_fee_per_vehicle;
                $bicycleParkingFee += $amount;
                $bicycleDescriptionParts[] = "{$fee->vehicle_count} {$fee->vehicle_type_name} x " . number_format($fee->parking_fee_per_vehicle, 0, ',', '.') . "đ";
            }
        }

        $totalParkingFee = $carParkingFee + $motorbikeParkingFee + $bicycleParkingFee;
        $carDescription = $carDescriptionParts ? implode(', ', $carDescriptionParts) : 'Không có ô tô';
        $motorbikeDescription = $motorbikeDescriptionParts ? implode(', ', $motorbikeDescriptionParts) : 'Không có xe máy';
        $bicycleDescription = $bicycleDescriptionParts ? implode(', ', $bicycleDescriptionParts) : 'Không có xe đạp';

        // Phân trang hóa đơn
        $paginatedInvoices = (clone $baseQuery)->paginate($perPage);

        // Duyệt danh sách hóa đơn trong trang hiện tại
        $invoices = collect($paginatedInvoices->items())->map(function ($invoice) {
            return [
                'invoice_id' => $invoice->invoice_id,
                'building_name' => $invoice->apartment->building->name ?? 'N/A',
                'apartment_number' => $invoice->apartment->apartment_number ?? 'N/A',
                'total_amount' => $invoice->total_amount,
                'remaining_balance' => $invoice->remaining_balance,
                'due_date' => $invoice->due_date,
                'status' => $invoice->status,
                'invoice_date' => $invoice->invoice_date,
                'is_overdue' => $invoice->due_date < now() && $invoice->remaining_balance > 0
            ];
        });

        // Tính nợ toàn bộ, không dựa theo trang
        $totalDebt = (clone $baseQuery)->sum('remaining_balance');
        $overdueDebt = (clone $baseQuery)
            ->where('due_date', '<', now())
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');
            
        $allInvoices = (clone $baseQuery)->get();

        // Đếm số lượng hóa đơn theo trạng thái
        $unpaidCount = $allInvoices->where('status', 0)->count();           
        $paidCount = $allInvoices->where('status', 1)->count();             
        $partiallyPaidCount = $allInvoices->where('status', 2)->count();    
        $overdueCount = $allInvoices->where('status', 3)->count();         

        // Trả về JSON
        return response()->json([
            'message' => 'Debts retrieved successfully',
            'data' => [
                'invoices' => $invoices,
                'total_debt' => $totalDebt,
                'overdue_debt' => $overdueDebt,
                'total_issued_amount' => $totals->total_issued_amount ?? 0,
                'total_paid_amount' => $totals->total_paid_amount ?? 0,
                'unpaid_count' => $unpaidCount,
                'paid_count' => $paidCount,
                'partially_paid_count' => $partiallyPaidCount,
                'overdue_count' => $overdueCount,
                'total_fees' => [
                    [
                        'type' => 'Phí quản lý vận hành',
                        'amount' => $totalManagementFee,
                        'description' => $managementDescription
                    ],
                    [
                        'type' => 'Phí gửi xe',
                        'amount' => $totalParkingFee,
                        'details' => [
                            [
                                'type' => 'Phí gửi xe ô tô',
                                'amount' => $carParkingFee,
                                'description' => $carDescription
                            ],
                            [
                                'type' => 'Phí gửi xe máy',
                                'amount' => $motorbikeParkingFee,
                                'description' => $motorbikeDescription
                            ],
                            [
                                'type' => 'Phí gửi xe đạp',
                                'amount' => $bicycleParkingFee,
                                'description' => $bicycleDescription
                            ]
                        ]
                    ],
                    [
                        'type' => 'Thù lao ban quản trị',
                        'amount' => $totalManagementBoardFee,
                        'description' => $managementBoardDescription
                    ]
                ],
                'pagination' => [
                    'current_page' => $paginatedInvoices->currentPage(),
                    'per_page' => $paginatedInvoices->perPage(),
                    'total' => $paginatedInvoices->total(),
                    'last_page' => $paginatedInvoices->lastPage()
                ]
            ]
        ], 200);
    }

    public function getDebtHistory(Request $request)
    {
        $query = Invoice::query()
            ->select(
                DB::raw('DATE_FORMAT(invoice_date, "%Y-%m") as month'),
                DB::raw('SUM(remaining_balance) as total_debt'),
                DB::raw('SUM(CASE WHEN due_date < NOW() THEN remaining_balance ELSE 0 END) as overdue_debt'),
                DB::raw('SUM(CASE WHEN status = "unpaid" THEN remaining_balance ELSE 0 END) as unpaid'),
                DB::raw('SUM(CASE WHEN status = "partially_paid" THEN remaining_balance ELSE 0 END) as partially_paid'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN remaining_balance ELSE 0 END) as paid')
            )
            ->groupBy('month');

        // Lọc theo thời gian
        if ($request->has('period_type') && $request->has('period_value')) {
            if ($request->period_type === 'month') {
                $query->whereYear('invoice_date', substr($request->period_value, 0, 4))
                    ->whereMonth('invoice_date', substr($request->period_value, 5, 2));
            } elseif ($request->period_type === 'quarter') {
                [$year, $quarter] = explode('-Q', $request->period_value);
                $months = match ($quarter) {
                    '1' => [1, 2, 3],
                    '2' => [4, 5, 6],
                    '3' => [7, 8, 9],
                    '4' => [10, 11, 12]
                };
                $query->whereYear('invoice_date', $year)
                    ->whereIn(DB::raw('MONTH(invoice_date)'), $months);
            } elseif ($request->period_type === 'year') {
                $query->whereYear('invoice_date', $request->period_value);
            }
        }

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $history = $query->get();

        return response()->json([
            'message' => 'Debt history retrieved successfully',
            'data' => $history
        ], 200);
    }

    public function getPeriods()
    {
        // Lấy danh sách tháng
        $months = Invoice::query()
            ->where('remaining_balance', '>', 0)
            ->select(DB::raw('DISTINCT DATE_FORMAT(invoice_date, "%Y-%m") as month'))
            ->orderBy('month', 'desc')
            ->pluck('month')
            ->map(function ($month) {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
                return [
                    'value' => $month,
                    'label' => 'Tháng ' . $date->month . '/' . $date->year
                ];
            });

        // Lấy danh sách quý
        $quarters = Invoice::query()
            ->where('remaining_balance', '>', 0)
            ->select(DB::raw('DISTINCT CONCAT(YEAR(invoice_date), "-Q", CEIL(MONTH(invoice_date)/3)) as quarter'))
            ->orderBy('quarter', 'desc')
            ->pluck('quarter')
            ->map(function ($quarter) {
                [$year, $q] = explode('-Q', $quarter);
                return [
                    'value' => $quarter,
                    'label' => 'Quý ' . $q . '/' . $year
                ];
            });

        // Lấy danh sách năm
        $years = Invoice::query()
            ->where('remaining_balance', '>', 0)
            ->select(DB::raw('DISTINCT YEAR(invoice_date) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(function ($year) {
                return [
                    'value' => (string) $year,
                    'label' => 'Năm ' . $year
                ];
            });

        return response()->json([
            'message' => 'Periods retrieved successfully',
            'data' => [
                'months' => $months,
                'quarters' => $quarters,
                'years' => $years
            ]
        ], 200);
    }
}
