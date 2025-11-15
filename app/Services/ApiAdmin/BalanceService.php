<?php

namespace App\Services\ApiAdmin;

use App\Models\ApartmentBalance;
use App\Models\ApartmentTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class BalanceService
{
    /**
     * Cập nhật số dư căn hộ (tạo mới nếu chưa có)
     *
     * @param int $apartmentId
     * @param int|null $buildingId
     * @param float $amount 
     * @param string $type 
     * @param int|null $userId
     * @param string|null $description
     * @param string|null $referenceType 
     * @param int|null $referenceId
     * @return ApartmentTransaction
     * @throws Exception
     */
    public function updateBalance(
        int $apartmentId,
        ?int $buildingId,
        float $amount,
        string $type,
        ?int $userId = null,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ApartmentTransaction {
        return DB::transaction(function () use (
            $apartmentId,
            $buildingId,
            $amount,
            $type,
            $userId,
            $description,
            $referenceType,
            $referenceId
        ) {
            // Lấy hoặc khởi tạo số dư
            $balance = ApartmentBalance::firstOrCreate(
                ['apartment_id' => $apartmentId],
                [
                    'building_id' => $buildingId,
                    'current_balance' => 0,
                    'total_charged' => 0,
                    'total_paid' => 0
                ]
            );

            $before = $balance->current_balance;

            $after = $before - $amount;

            // Cập nhật balance
            $balance->current_balance = $after;
            $balance->updated_at = now();

            if ($type === 'charge') {
                $balance->total_charged += abs($amount);
                $balance->last_charge_date = now();
            } elseif ($type === 'payment') {
                $balance->total_paid += abs($amount);
                $balance->last_payment_date = now();
            }

            $balance->save();

            // Ghi log giao dịch
            return ApartmentTransaction::create([
                'apartment_id' => $apartmentId,
                'transaction_type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $description,
                'created_by' => $userId,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Lấy số dư hiện tại của căn hộ
     */
    public function getCurrentBalance(int $apartmentId): float
    {
        return ApartmentBalance::where('apartment_id', $apartmentId)->value('current_balance') ?? 0;
    }

    /**
     * Đặt lại (reset) số dư căn hộ về 0 — chỉ admin mới dùng
     */
    public function resetBalance(int $apartmentId, int $userId, string $note = null)
    {
        return DB::transaction(function () use ($apartmentId, $userId, $note) {
            $balance = ApartmentBalance::where('apartment_id', $apartmentId)->firstOrFail();
            $before = $balance->current_balance;
            $balance->update([
                'current_balance' => 0,
                'updated_at' => now(),
            ]);

            // Ghi transaction loại adjust
            ApartmentTransaction::create([
                'apartment_id' => $apartmentId,
                'transaction_type' => 'adjust',
                'amount' => -$before,
                'balance_before' => $before,
                'balance_after' => 0,
                'description' => $note ?? 'Điều chỉnh lại công nợ về 0',
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            return $balance;
        });
    }
}
