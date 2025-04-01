<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvoiceRepository
{
    public function getInvoicesByBuilding($building_id, $perPage = '', $keyword = null)
    {
        $query = Invoice::with('updatedBy')->where('building_id', $building_id)
            ->with('apartment') // Load thông tin căn hộ
            ->orderBy('invoice_date', 'desc');

        // Tìm kiếm theo tên căn hộ nếu có keyword
        if (!empty($keyword)) {
            $query->whereHas('apartment', function ($q) use ($keyword) {
                $q->where('apartment_number', 'LIKE', "%$keyword%");
            });
        }

        // Phân trang
        $invoices = $query->paginate($perPage);

        return $invoices;
    }

    public function create(array $request)
    {
        $user = auth()->user();
        $apartment = Apartment::where('apartment_number', $request['apartment_number'])->first();
        if ($apartment) {
            $apartmentId =  $apartment->apartment_id;
        }
        $invoice = Invoice::create([
            'building_id' => $request['building_id'],
            'apartment_id' => $apartmentId,
            'invoice_date' => $request['invoice_date'],
            'due_date' => $request['due_date'],
            'total_amount' => $request['total_amount'],
            'status' => $request['status'],
            'updated_by' => $user->id
        ]);

        $invoiceId = $invoice->invoice_id;

        if ($invoice && $invoice->invoice_id) {
            foreach ($request['invoice_detail'] as $invoiceDetail) {
                InvoiceDetail::create([
                    'invoice_id' => $invoiceId,
                    'service_name' => $invoiceDetail['service_name'],
                    'quantity' => $invoiceDetail['quantity'],
                    'price' =>  $invoiceDetail['price'],
                    'amount' => $invoiceDetail['amount'],
                    'description' => $invoiceDetail['description'] ? $invoiceDetail['description'] : null,
                ]);
            }
        }
        return $invoice;
    }

    public function show(int $id)
    {
        $invoice = Invoice::with('invoiceDetails', 'apartment')->find($id);

        if (!$invoice) {
            // Ném ra một exception khi không tìm thấy hóa đơn
            throw new \Exception('Hóa đơn không tồn tại', 404);
        }

        $detai = [
            'invoice_id' => $invoice->invoice_id,
            'apartment_id' => $invoice->apartment_id,
            'apartment_number' => $invoice->apartment ? $invoice->apartment->apartment_number : null,
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

        $invoice->update([
            'invoice_date' => $request['invoice_date'],
            'due_date' => $request['due_date'],
            'total_amount' => $request['total_amount'],
            'status' => $request['status'],
            'updated_by' => $user->id,
        ]);

        InvoiceDetail::where('invoice_id', $invoice->invoice_id)->delete();

        foreach ($request['invoice_detail'] as $invoiceDetaill) {
            InvoiceDetail::create([
                'invoice_id' => $invoice->invoice_id,
                'service_name' => $invoiceDetaill['service_name'],
                'quantity' => $invoiceDetaill['quantity'],
                'price' => $invoiceDetaill['price'],
                'amount' => $invoiceDetaill['amount'],
                'description' => $invoiceDetaill['description'] ?? null
            ]);
        }

        return $invoice;
    }
}
