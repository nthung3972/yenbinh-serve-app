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

    public function massPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,qr_code,other',
            'notes' => 'nullable|string'
        ]);

        $apartmentId = Invoice::where('invoice_id', $request->invoice_id)->value('apartment_id');
        if (!$apartmentId) {
            return response()->json(['error' => 'Không tìm thấy hóa đơn hoặc căn hộ'], 404);
        }

        $amount = $request->amount;

        DB::beginTransaction();
        try {
            // Lấy hóa đơn gần nhất chưa thanh toán hết (hóa đơn này đã tích lũy tất cả nợ cũ)
            $latestInvoice = Invoice::where('apartment_id', $apartmentId)
                ->where('remaining_balance', '>', 0)
                ->orderBy('invoice_date', 'desc')
                ->lockForUpdate()
                ->first();

            if (!$latestInvoice) {
                return response()->json([
                    'error' => 'Không tìm thấy hóa đơn nào cần thanh toán'
                ], 404);
            }

            // Số tiền thanh toán cho hóa đơn này
            $payAmount = min($latestInvoice->remaining_balance, $amount);

            // Tạo payment record
            $payment = Payment::create([
                'invoice_id' => $latestInvoice->invoice_id,
                'amount' => $payAmount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Cập nhật total_paid
            $latestInvoice->total_paid = ($latestInvoice->total_paid ?? 0) + $payAmount;

            // Cập nhật remaining_balance
            $latestInvoice->remaining_balance -= $payAmount;

            // Xử lý trường hợp thanh toán thừa
            if ($amount > $payAmount) {
                $latestInvoice->remaining_balance = - ($amount - $payAmount); 
            }
            // Cập nhật status
            if ($latestInvoice->remaining_balance > 0) {
                $latestInvoice->status = '3'; // Thanh toán một phần
            } else {
                $latestInvoice->status = '1'; // Đã thanh toán
            }

            // Đánh dấu tất cả hóa đơn cũ hơn là đã thanh toán (chỉ khi thanh toán hết)
            if ($latestInvoice->remaining_balance <= 0) {
                Invoice::where('apartment_id', $apartmentId)
                    ->where('invoice_date', '<', $latestInvoice->invoice_date)
                    ->where('remaining_balance', '>', 0)
                    ->update([
                        'total_paid' => DB::raw('total_amount'),
                        'remaining_balance' => 0,
                        'status' => '1'
                        // Không cập nhật previous_balance của các hóa đơn cũ
                    ]);
            }

            $latestInvoice->save();

            DB::commit();

            $responseData = [
                'payment' => $payment,
                'remaining_amount' => max(0, $amount - $payAmount),
                'invoice_status' => $latestInvoice->status,
                'current_balance' => $latestInvoice->remaining_balance
            ];

            return response()->json([
                'message' => 'Thanh toán hóa đơn thành công',
                'data' => $responseData
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'error' => 'Lỗi khi xử lý thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
}
