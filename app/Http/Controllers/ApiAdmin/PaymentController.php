<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create(Request $request)
    {
        // // Validate request
        // $validator = Validator::make($request->all(), [
        //     'invoice_id' => 'required|exists:invoices,invoice_id',
        //     'amount' => 'required|numeric|min:0.01',
        //     'payment_date' => 'required|date',
        //     'payment_method' => 'nullable|string|max:50',
        //     'note' => 'nullable|string',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'error' => $validator->errors(),
        //     ], 422);
        // }

        // Start transaction
        return DB::transaction(function () use ($request) {
            // Create payment
            $payment = Payment::create([
                'invoice_id' => $request->invoice_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'note' => $request->note,
            ]);

            // Update invoice
            $invoice = Invoice::findOrFail($request->invoice_id);
            $invoice->total_paid += $request->amount;
            $invoice->remaining_balance = $invoice->total_amount - $invoice->total_paid;
            $invoice->status = $invoice->remaining_balance <= 0 ? '0' : ($invoice->total_paid > 0 ? '3' : '1');
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
