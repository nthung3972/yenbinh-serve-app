<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Carbon\Carbon;
class DashboardController extends Controller
{
    public function getCollectionRateByYear(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year); // Mặc định là năm hiện tại

        $data = Invoice::selectRaw('MONTH(invoice_date) as month, 
                                SUM(CASE WHEN status = 1 THEN total_amount ELSE 0 END) as collected_amount, 
                                SUM(total_amount) as total_amount')
            ->whereYear('invoice_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Tính tỷ lệ thu phí
        $chartData = $data->map(function ($item) {
            return [
                'month' => $item->month,
                'collection_rate' => $item->total_amount > 0 ? round(($item->collected_amount / $item->total_amount) * 100, 2) : 0
            ];
        });

        return response()->json($chartData);
    }
}
