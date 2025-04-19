<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvoiceRepository
{
    public function getInvoicesByBuilding($building_id, $perPage = '', $keyword = null, $status = null, $invoice_date_from = null, $invoice_date_to = null)
    {
        $query = Invoice::with(['updatedBy', 'apartment']) // Load thông tin liên quan
            ->where('building_id', $building_id)
            ->orderBy('invoice_date', 'desc');

        // Tìm kiếm theo tên căn hộ nếu có keyword
        if (!empty($keyword)) {
            $query->whereHas('apartment', function ($q) use ($keyword) {
                $q->where('apartment_number', 'LIKE', "%$keyword%");
            });
        }

        // Lọc theo trạng thái nếu có
        if (!is_null($status)) {
            $query->where('status', $status);
        }

        // Lọc từ ngày hóa đơn
        if (!empty($invoice_date_from)) {
            $query->whereDate('invoice_date', '>=', $invoice_date_from);
        }

        // Lọc đến ngày hóa đơn
        if (!empty($invoice_date_to)) {
            $query->whereDate('invoice_date', '<=', $invoice_date_to);
        }

        // Phân trang
        return $query->paginate($perPage);
    }

    public function create(array $request)
    {
        $user = auth()->user();
        $invoice = Invoice::create([
            'building_id' => $request['building_id'],
            'apartment_id' => $request['apartment_id'],
            'invoice_date' => $request['invoice_date'],
            'due_date' => $request['due_date'],
            'total_amount' => $request['total_amount'],
            'status' => 0,
            'updated_by' => $user->id
        ]);

        $invoiceId = $invoice->invoice_id;

        if ($invoice && $invoice->invoice_id) {
            foreach ($request['fees'] as $invoiceDetail) {
                InvoiceDetail::create([
                    'invoice_id' => $invoiceId,
                    'fee_type_id' => $invoiceDetail['fee_type_id'],
                    'quantity' => $invoiceDetail['quantity'] ?? null,
                    'price' =>  $invoiceDetail['price'] ?? null,
                    'amount' => $invoiceDetail['amount'],
                    'description' => $invoiceDetail['description']
                ]);
            }
        }
        return $invoice;
    }

    public function show(int $id)
    {
        $invoice = Invoice::with('invoiceDetails', 'apartment', 'updatedBy')->find($id);

        if (!$invoice) {
            // Ném ra một exception khi không tìm thấy hóa đơn
            throw new \Exception('Hóa đơn không tồn tại', 404);
        }

        $detai = [
            'invoice_id' => $invoice->invoice_id,
            'apartment_id' => $invoice->apartment_id,
            'apartment_number' => $invoice->apartment ? $invoice->apartment->apartment_number : '',
            'updated_by' => $invoice->updatedBy ? $invoice->updatedBy->name : '',
            'invoice_date' => $invoice->invoice_date,
            'due_date' => $invoice->due_date,
            'status' => $invoice->status,
            'total_amount' => $invoice->total_amount,
            'invoice_details' => $invoice->invoiceDetails,
        ];
        return $detai;
    }

    public function update(array $request, int $id)
    {
        $user = auth()->user();

        $invoice = Invoice::find($id);

        if (!$invoice) {
            throw new \Exception('Hóa đơn không tồn tại', 404);
        }

        if ($invoice->status == 1) {
            throw new \Exception('Hóa đơn đã thanh toán không thể sửa!', 404);
        }

        $invoice->update([
            'invoice_date' => $request['invoice_date'],
            'due_date' => $request['due_date'],
            'total_amount' => $request['total_amount'],
            'status' => $request['status'],
            'payment_method' => $request['payment_method'] ?? '',
            'updated_by' => $user->id,
        ]);

        InvoiceDetail::where('invoice_id', $invoice->invoice_id)->delete();

        foreach ($request['fees'] as $invoiceDetaill) {
            InvoiceDetail::create([
                'invoice_id' => $invoice->invoice_id,
                'fee_type_id' => $invoiceDetaill['fee_type_id'],
                'quantity' => $invoiceDetaill['quantity'] ?? null,
                'price' => $invoiceDetaill['price'] ?? null,
                'amount' => $invoiceDetaill['amount'],
                'description' => $invoiceDetaill['description']
            ]);
        }

        return $invoice;
    }

    public function existingInvoice(array $request, $year, $month) {
        $existingInvoice = Invoice::where('apartment_id', $request['apartment_id'])
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->exists();

        return $existingInvoice;
    }
}
