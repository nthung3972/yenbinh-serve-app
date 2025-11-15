<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentTransaction extends Model
{
    protected $table = 'apartment_transactions';
    protected $primaryKey = 'apartment_transaction_id';
    public $timestamps = false;

    protected $fillable = [
        'apartment_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Giao dịch thuộc về một căn hộ
     */
    public function balance(): BelongsTo
    {
        return $this->belongsTo(ApartmentBalance::class, 'apartment_id', 'apartment_id');
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper: Kiểm tra loại giao dịch
     */
    public function isPayment(): bool
    {
        return $this->transaction_type === 'payment';
    }

    public function isCharge(): bool
    {
        return $this->transaction_type === 'charge';
    }
}
