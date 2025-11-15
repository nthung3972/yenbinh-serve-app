<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PaymentRequest\CreatePaymentRequest;
use App\Services\ApiAdmin\BalanceService;
use Exception;
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    public function __construct(
        public BalanceService $balanceService
    ) {}

    public function create(CreatePaymentRequest $request)
    {
        $invoice = Invoice::with('apartment')->find($request->invoice_id);

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        //Chỉ kiểm tra số tiền > 0
        if ($request->amount <= 0) {
            return response()->json([
                'errors' => ['amount' => ['Số tiền thanh toán phải lớn hơn 0']]
            ], 422);
        }

        $actualRemaining = abs($invoice->closing_balance);

        //CẢNH BÁO nếu trả thừa (không chặn)
        $isDebt = $invoice->closing_balance < 0;
        $isOverpayment = $request->amount > $actualRemaining;
        $overpaymentAmount = $isOverpayment ? $request->amount - $actualRemaining : 0;

        return DB::transaction(function () use ($request, $invoice, $isOverpayment, $overpaymentAmount, $actualRemaining, $isDebt) {

            //Ghi thanh toán
            $payment = Payment::create([
                'invoice_id'     => $invoice->invoice_id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes'          => $request->notes,
            ]);

            //Cập nhật balance (giảm nợ)
            $this->balanceService->updateBalance(
                apartmentId: $invoice->apartment_id,
                buildingId: $invoice->building_id,
                amount: -$request->amount,
                type: 'payment',
                userId: auth()->id(),
                description: 'Thanh toán hóa đơn #' . $invoice->invoice_number,
                referenceType: 'payment',
                referenceId: $payment->payment_id
            );

            //Cập nhật invoice (chỉ tính tối đa = total_amount)
            $newPaidAmount = ($invoice->paid_amount ?? 0) + $request->amount;

            $currentBalance = DB::table('apartment_balances')
                ->where('apartment_id', $invoice->apartment_id)
                ->value('current_balance');

            DB::table('invoices')
                ->where('invoice_id', $invoice->invoice_id)
                ->update([
                    'paid_amount' => $newPaidAmount,
                    'closing_balance' => -$currentBalance,
                    'status' => $currentBalance >= 0 ? 'paid' : 'partial',
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => $isOverpayment
                    ? "Thanh toán thành công. Số tiền thừa " . number_format($overpaymentAmount) . "đ sẽ chuyển sang kỳ sau"
                    : 'Thanh toán thành công',
                'data' => [
                    'payment' => $payment,
                    'invoice' => [
                        'invoice_id' => $invoice->invoice_id,
                        'invoice_number' => $invoice->invoice_number,
                        'opening_balance' => $invoice->opening_balance,
                        'total_amount' => $invoice->total_amount,
                        'paid_amount' => $newPaidAmount,
                        'actual_remaining' => $actualRemaining,
                        'closing_balance' => $currentBalance,
                        'status' => $currentBalance >= 0 ? 'paid' : 'partial',
                    ],
                    'apartment_balance' => [
                        'current_balance' => $currentBalance,
                        'status' => $currentBalance >= 0 ? 'surplus' : 'debt',
                        'overpayment_amount' => $overpaymentAmount,
                    ],
                    'warning' => $isOverpayment ? "Bạn đã thanh toán thừa " . number_format($overpaymentAmount) . "đ. Số tiền này sẽ được trừ vào hóa đơn kỳ sau." : null
                ],
            ], 201);
        });
    }

    public function history($id)
    {
        try {
            $apartment = DB::table('apartments')->where('apartment_id', $id)->first();
            if (!$apartment) {
                throw new Exception('Căn hộ không tồn tại.', 422);
            }
            $query = DB::table('payments as p')
            ->join('invoices as i', 'i.invoice_id', 'p.invoice_id')
            ->where('i.apartment_id', $id)
            ->select(
                'p.payment_id',
                'p.invoice_id',
                'p.amount',
                'p.payment_date',
                'p.payment_method',
                'p.notes',
                'p.created_at',
                'i.invoice_number',
                'i.period'
            )
            ->get();

            return response()->json([
                'success' => true,
                'message' => "Lấy lịch sử thanh toán thành công.",
                'data' => $query,
                'code' => 200
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], $e->getCode());
        }
    }
}
