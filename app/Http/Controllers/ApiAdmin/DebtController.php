<?php
namespace App\Http\Controllers\ApiAdmin;


use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', config('constant.paginate'));

        $query = Invoice::query()
            ->with(['apartment', 'apartment.residents', 'apartment.building'])
            ->where('remaining_balance', '>', 0) // Loại bỏ hóa đơn đã thanh toán hết
            ->when($request->filled('status') || $request->status === '0' || $request->status === 0, function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->building_id, fn($q) => $q->whereHas('apartment', fn($q2) => $q2->where('building_id', $request->building_id)));
        // Lọc theo thời gian
        if ($request->has('period_type') && $request->has('period_value')) {
            if ($request->period_type === 'month') {
                $query->whereYear('invoice_date', substr($request->period_value, 0, 4))
                      ->whereMonth('invoice_date', substr($request->period_value, 5, 2));
            } elseif ($request->period_type === 'quarter') {
                [$year, $quarter] = explode('-Q', $request->period_value);
                $months = match($quarter) {
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

        // Phân trang hóa đơn
        $paginatedInvoices = $query->paginate($perPage);

        // Xử lý danh sách hóa đơn
        $invoices = collect($paginatedInvoices->items())->map(function ($invoice) {
            return [
                'invoice_id' => $invoice->invoice_id,
                'building_name' => $invoice->apartment->building->name ?? 'N/A',
                'apartment_number' => $invoice->apartment->apartment_number ?? 'N/A',
                'resident_name' => $invoice->apartment->resident->name ?? 'N/A',
                'total_amount' => $invoice->total_amount,
                'remaining_balance' => $invoice->remaining_balance,
                'due_date' => $invoice->due_date,
                'status' => $invoice->status,
                'invoice_date' => $invoice->invoice_date,
                'is_overdue' => $invoice->due_date < now() && $invoice->remaining_balance > 0
            ];
        });

        // Tính tổng công nợ và công nợ quá hạn
        $totalDebt = $invoices->sum('remaining_balance');
        $overdueDebt = $invoices->where('due_date', '<', now())->sum('remaining_balance');

        return response()->json([
            'message' => 'Debts retrieved successfully',
            'data' => [
                'invoices' => $invoices,
                'total_debt' => $totalDebt,
                'overdue_debt' => $overdueDebt,
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
                $months = match($quarter) {
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
                    'value' => (string)$year,
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