<?php

namespace App\Services\ApiAdmin;

use App\Models\Invoice;
use App\Models\InvoiceAdjustment;
use Illuminate\Support\Facades\DB;
use Exception;

class InvoiceAdjustmentService
{
    const APPROVAL_THRESHOLD = 1000000;
    const MAX_ADJUSTMENT_PERCENT = 0.5;
    const MAX_ADJUSTMENTS_PER_INVOICE = 5;

    /**
     * Tạo điều chỉnh phí
     */
    public function createAdjustment(array $data): array
    {
        return DB::transaction(function () use ($data) {

            // 1️⃣ VALIDATE
            $invoice = Invoice::find($data['invoice_id']);
            // dd($invoice);
            $this->validateAdjustment($invoice, $data);

            // 2️⃣ KIỂM TRA CẦN APPROVAL
            $requiresApproval = $this->requiresApproval($data['amount'], auth()->user());

            // 3️⃣ TẠO ADJUSTMENT
            $adjustment = InvoiceAdjustment::create([
                'invoice_id' => $data['invoice_id'],
                'adjustment_type' => $data['adjustment_type'],
                'amount' => $data['amount'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'reason' => $data['reason'],
                'invoice_detail_id' => $data['invoice_detail_id'] ?? null,
                'fee_types_id' => $data['fee_types_id'] ?? null,
                'status' => $requiresApproval ? 'pending' : 'approved',
                'requires_approval' => $requiresApproval,
                'created_by' => auth()->id(),
                'attachment_url' => $data['attachment_url'] ?? null
            ]);

            // 4️⃣ NẾU KHÔNG CẦN APPROVAL → ÁP DỤNG NGAY
            if (!$requiresApproval) {
                $this->applyAdjustment($adjustment);
            }

            return [
                'success' => true,
                'message' => $requiresApproval
                    ? 'Đã tạo yêu cầu điều chỉnh. Chờ phê duyệt.'
                    : 'Đã điều chỉnh phí thành công',
                'data' => [
                    'adjustment' => $adjustment,
                    'requires_approval' => $requiresApproval,
                    'invoice' => $this->getInvoiceWithAdjustments($data['invoice_id'])
                ]
            ];
        });
    }

    /**
     * Validate điều chỉnh
     */
    private function validateAdjustment($invoice, $data): void
    {
        // Không điều chỉnh HĐ đã hủy
        if ($invoice->status === 'cancelled') {
            throw new Exception('Không thể điều chỉnh hóa đơn đã hủy');
        }

        // Không điều chỉnh HĐ đã thanh toán đủ
        if ($invoice->status === 'paid') {
            throw new Exception('Không thể điều chỉnh hóa đơn đã thanh toán đủ');
        }

        // Giới hạn số lần điều chỉnh
        if ($invoice->adjustment_count >= self::MAX_ADJUSTMENTS_PER_INVOICE) {
            throw new Exception('Hóa đơn đã đạt giới hạn ' . self::MAX_ADJUSTMENTS_PER_INVOICE . ' lần điều chỉnh');
        }

        // Kiểm tra % điều chỉnh
        $currentAdjustmentTotal = $invoice->adjustment_total ?? 0;
        $newAdjustmentTotal = $currentAdjustmentTotal + $data['amount'];
        $adjustmentPercent = abs($newAdjustmentTotal / $invoice->total_amount);

        if ($adjustmentPercent > self::MAX_ADJUSTMENT_PERCENT) {
            throw new Exception('Tổng điều chỉnh không được vượt quá 50% giá trị hóa đơn');
        }

        // Không cho giảm xuống âm
        $finalAmount = $invoice->total_amount + $newAdjustmentTotal;
        if ($finalAmount < 0) {
            throw new Exception('Số tiền sau điều chỉnh không được âm');
        }

        // Số tiền phải khác 0
        if ($data['amount'] == 0) {
            throw new Exception('Số tiền điều chỉnh phải khác 0');
        }

        // Danh sách các loại giảm phí → amount = âm
        $discountTypes = ['discount', 'waiver', 'credit'];

        // Danh sách các loại tăng phí → amount = dương
        $increaseTypes = ['penalty', 'extra_service', 'back_charge'];

        if (in_array($data['adjustment_type'], $discountTypes)) {
            $data['amount'] = -abs($data['amount']);   // ép âm
        } elseif (in_array($data['adjustment_type'], $increaseTypes)) {
            $data['amount'] = abs($data['amount']);    // ép dương
        } else {
            throw new \Exception("adjustment_type không hợp lệ", 422);
        }
    }

    /**
     * Kiểm tra cần approval không
     */
    private function requiresApproval(float $amount, $user): bool
    {
        $user = auth()->user();

        // ADMIN không cần approval
        if ($user->role === 'admin') {
            return false;
        }

        // Số tiền lớn cần approval
        if (abs($amount) >= self::APPROVAL_THRESHOLD) {
            return true;
        }

        // ACCOUNTANT chỉ được giảm < 500k
        if ($user->role === 'staff') {
            return true;
        }

        return false;
    }

    /**
     * Áp dụng điều chỉnh vào hóa đơn
     */
    private function applyAdjustment(InvoiceAdjustment $adjustment): void
    {
        $invoice = $adjustment->invoice;

        // 1️⃣ Cập nhật invoice
        $invoice->increment('adjustment_count');
        $invoice->increment('adjustment_total', $adjustment->amount);
        $invoice->update([
            'last_adjusted_at' => now(),
            'last_adjusted_by' => $adjustment->created_by
        ]);

        // 2️⃣ ✅ CẬP NHẬT APARTMENT BALANCE (QUAN TRỌNG!)
        app(BalanceService::class)->updateBalance(
            apartmentId: $invoice->apartment_id,
            buildingId: $invoice->building_id,
            amount: $adjustment->amount, // Âm = giảm nợ, Dương = tăng nợ
            type: 'adjustment',
            userId: $adjustment->approved_by ?? $adjustment->created_by,
            description: $this->getAdjustmentDescription($adjustment),
            referenceType: 'adjustment',
            referenceId: $adjustment->id
        );

        // 3️⃣ Cập nhật closing_balance của invoice
        // Lấy balance thực tế từ apartment_balances
        $currentBalance = DB::table('apartment_balances')
            ->where('apartment_id', $invoice->apartment_id)
            ->value('current_balance');

        $invoice->update(['closing_balance' => $currentBalance]);
    }

    /**
     * Lấy invoice với tất cả adjustments
     */
    private function getInvoiceWithAdjustments(int $invoiceId)
    {
        return Invoice::with(['adjustments' => function ($q) {
            $q->where('status', 'approved');
        }])->find($invoiceId);
    }

    /**
     * Tạo mô tả cho adjustment transaction
     */
    private function getAdjustmentDescription(InvoiceAdjustment $adjustment): string
    {
        $type = $adjustment->isDecrease() ? 'Giảm' : 'Tăng';
        $amountText = number_format(abs($adjustment->amount));

        return "{$type} phí {$amountText}đ - HĐ #{$adjustment->invoice->invoice_number} - {$adjustment->title}";
    }

    /**
     * Approve adjustment
     */
    public function approveAdjustment(int $adjustmentId, ?string $note = null): array
    {
        return DB::transaction(function () use ($adjustmentId, $note) {

            $adjustment = InvoiceAdjustment::find($adjustmentId);

            if (!$adjustment) {
                throw new Exception('Không tìm thấy yêu cầu điều chỉnh');
            }

            if (!$adjustment->canApprove()) {
                throw new Exception('Yêu cầu không thể phê duyệt');
            }

            // Cập nhật status
            $adjustment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'description' => $adjustment->description . ($note ? "\n[Admin note: {$note}]" : '')
            ]);

            // Áp dụng vào invoice
            $this->applyAdjustment($adjustment);

            return [
                'success' => true,
                'message' => 'Đã phê duyệt điều chỉnh',
                'data' => [
                    'adjustment' => $adjustment->fresh(),
                    'invoice' => $this->getInvoiceWithAdjustments($adjustment->invoice_id)
                ]
            ];
        });
    }

    /**
     * Reject adjustment
     */
    public function rejectAdjustment(int $adjustmentId, string $reason): array
    {
        $adjustment = InvoiceAdjustment::find($adjustmentId);

        if (!$adjustment || !$adjustment->canApprove()) {
            throw new Exception('Yêu cầu không hợp lệ');
        }

        $adjustment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $reason
        ]);

        return [
            'success' => true,
            'message' => 'Đã từ chối điều chỉnh',
            'data' => $adjustment
        ];
    }
}
