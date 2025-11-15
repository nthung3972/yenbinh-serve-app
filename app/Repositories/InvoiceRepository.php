<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Payment;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvoiceRepository
{
    public function getInvoicesByBuilding($building_id, $perPage = '', $keyword = null, $status = null, $period = null)
    {
        $query = Invoice::with(['updatedBy', 'apartment'])
            ->where('building_id', $building_id)
            ->orderBy('issue_date', 'desc');

        // Tìm kiếm theo tên căn hộ nếu có keyword
        if (!empty($keyword)) {
            $query->whereHas('apartment', function ($q) use ($keyword) {
                $q->where('apartment_number', 'LIKE', "%$keyword%");
            });
        }

        if (!empty($period)) {
            $query->where('period', $period);
        }

        // Lọc theo trạng thái nếu có
        if (!is_null($status)) {
            $query->where('status', $status);
        }



        // Phân trang
        return $query->paginate($perPage);
    }

    public function show(int $id)
    {
        $invoice = Invoice::with('invoiceDetails', 'apartment', 'updatedBy', 'payments')->find($id);

        if (!$invoice) {
            // Ném ra một exception khi không tìm thấy hóa đơn
            throw new \Exception('Hóa đơn không tồn tại', 404);
        }

        $detai = [
            'invoice_id' => $invoice->invoice_id,
            'apartment_id' => $invoice->apartment_id,
            'apartment_number' => $invoice->apartment ? $invoice->apartment->apartment_number : '',
            'issue_date' => $invoice->issue_date,
            'due_date' => $invoice->due_date,
            'status' => $invoice->status,
            'total_amount' => $invoice->total_amount,
            'paid_amount' => $invoice->paid_amount,
            'opening_balance' => $invoice->opening_balance,
            'closing_balance' => $invoice->closing_balance,
            'invoice_details' => $invoice->invoiceDetails,
            'payments' => $invoice->payments,
        ];
        return $detai;
    }

    public function existingInvoice(array $request, $year, $month)
    {
        $existingInvoice = Invoice::where('apartment_id', $request['apartment_id'])
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->exists();

        return $existingInvoice;
    }

    public function delete(array $ids)
    {
        // Lấy tất cả hóa đơn theo mảng ID
        $invoices = Invoice::whereIn('invoice_id', $ids)->get();

        if ($invoices->isEmpty()) {
            throw new \Exception('Không tìm thấy hóa đơn nào', 404);
        }

        foreach ($invoices as $invoice) {
            // if ($invoice->status == 1) {
            //     // Nếu muốn bỏ qua hóa đơn đã thanh toán, thay throw bằng continue
            //     throw new \Exception("Hóa đơn #{$invoice->invoice_id} đã thanh toán không thể xóa!", 422);
            // }

            // Xóa chi tiết hóa đơn
            $invoice->invoiceDetails()->delete(); // nếu quan hệ hasMany đã định nghĩa

            // Xóa thanh toán
            $invoice->payments()->delete(); // nếu quan hệ hasMany đã định nghĩa

            // Xóa hóa đơn
            $invoice->delete();
        }

        return $invoices; 
    }
}
