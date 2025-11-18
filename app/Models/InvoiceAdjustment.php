<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAdjustment extends Model
{
    protected $table = 'invoice_adjustments';

    protected $primaryKey = 'invoice_adjustment_id';

    protected $fillable = [
        'invoice_id',
        'adjustment_type',
        'amount',
        'title',
        'description',
        'reason',
        'invoice_detail_id',
        'fee_type_id',
        'status',
        'requires_approval',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
        'attachment_url'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function invoiceDetail()
    {
        return $this->belongsTo(InvoiceDetail::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Helpers
    public function isDecrease(): bool
    {
        return $this->amount < 0;
    }

    public function isIncrease(): bool
    {
        return $this->amount > 0;
    }

    public function canApprove(): bool
    {
        return $this->status === 'pending' && $this->requires_approval;
    }
}
