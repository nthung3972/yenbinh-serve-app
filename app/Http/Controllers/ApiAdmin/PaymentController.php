<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PaymentRequest\CreatePaymentRequest;

class PaymentController extends Controller
{
    public function create(CreatePaymentRequest $request)
    {
        $invoice = Invoice::find($request->invoice_id);
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Kiểm tra thanh toán không được vượt quá số tiền còn lại
        if ($request->amount > $invoice->remaining_balance) {
            return response()->json([
                'errors' => ['amount' => ['Số tiền thanh toán vượt quá số tiền còn lại của hóa đơn']]
            ], 422);
        }

        // Start transaction
        return DB::transaction(function () use ($request) {
            // Create payment
            $payment = Payment::create([
                'invoice_id' => $request->invoice_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Update invoice
            $invoice = Invoice::findOrFail($request->invoice_id);
            $invoice->total_paid += $request->amount;
            $invoice->remaining_balance = $invoice->total_amount - $invoice->total_paid;
            if ($invoice->remaining_balance <= 0) {
                $invoice->status = '1'; // Đã thanh toán đủ
            } elseif ($invoice->total_paid > 0) {
                $invoice->status = '3'; // Thanh toán một phần
            } else {
                $invoice->status = '0'; // Chưa thanh toán
            }
            $invoice->save();

            return response()->json([
                'message' => 'Payment recorded successfully',
                'data' => [
                    'payment' => $payment,
                    'invoice' => [
                        'invoice_id' => $invoice->invoice_id,
                        'total_amount' => $invoice->total_amount,
                        'total_paid' => $invoice->total_paid,
                        'remaining_balance' => $invoice->remaining_balance,
                        'status' => $invoice->status,
                    ],
                ],
            ], 201);
        });
    }
}
