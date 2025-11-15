<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApartmentBalance extends Model
{
    protected $table = 'apartment_balances';
    protected $primaryKey = 'apartment_balance_id';
    public $timestamps = false; // vì chỉ có updated_at

    protected $fillable = [
        'apartment_id',
        'building_id',
        'current_balance',
        'total_charged',
        'total_paid',
        'last_charge_date',
        'last_payment_date',
        'updated_at',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'total_charged' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'last_charge_date' => 'date',
        'last_payment_date' => 'date',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ: 1 căn hộ có nhiều giao dịch
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(ApartmentTransaction::class, 'apartment_id', 'apartment_id')->with('createdBy');
    }

    /**
     * Cập nhật số dư hiện tại
     */
    public function adjustBalance(float $amount, string $type = 'charge', ?int $userId = null, ?string $desc = null)
    {
        $before = $this->current_balance;
        $after = $before + $amount;

        // Cập nhật balance
        $this->current_balance = $after;
        if ($amount > 0 && $type === 'charge') {
            $this->total_charged += $amount;
            $this->last_charge_date = now();
        } elseif ($amount < 0 && $type === 'payment') {
            $this->total_paid += abs($amount);
            $this->last_payment_date = now();
        }
        $this->updated_at = now();
        $this->save();

        // Ghi log transaction
        ApartmentTransaction::create([
            'apartment_id' => $this->apartment_id,
            'transaction_type' => $type,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'description' => $desc,
            'created_by' => $userId,
            'created_at' => now(),
        ]);

        return $this;
    }
}
