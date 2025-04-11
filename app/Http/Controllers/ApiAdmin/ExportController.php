<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Exports\InvoiceExport;
use App\Models\Invoice;
use Maatwebsite\Excel\Facades\Excel;

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
}
