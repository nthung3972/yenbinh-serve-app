<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Exports\InvoiceExport;
use App\Models\Invoice;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesMultiSheetExport;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportPrintable($id)
    {
        return Excel::download(
            new InvoiceExport($id),
            "HoaDon_{$id}.xlsx", // Đuôi xlsx
            \Maatwebsite\Excel\Excel::XLSX, // Format chuẩn .xlsx
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="HoaDon_'.$id.'.xlsx"'
            ]
        );
    }

    public function exportInvoices(Request $request)
    {
        // Validate request
        $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,invoice_id',
        ]);

        // Lấy danh sách hóa đơn theo IDs
        $invoices = Invoice::with('invoiceDetails')
                          ->whereIn('invoice_id', $request->invoice_ids)
                          ->get();

        if ($invoices->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy hóa đơn'], 404);
        }

        // Tạo tên file dựa trên thời gian hiện tại
        $fileName = 'invoices_' . now()->format('YmdHis') . '.xlsx';

        // Return Excel file
        return Excel::download(new InvoicesMultiSheetExport($invoices), $fileName);
    }
}
