<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_detail_id';

    protected $appends = ['fee_name'];

    protected $fillable = [
        'invoice_id',
        'amount',
        'quantity',
        'price',
        'amount',
        'description',
        'fee_type_id'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function feeTypes()
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id', 'fee_type_id');
    }

    public function getFeeNameAttribute()
    {
        return $this->feeTypes->fee_name ?? null;
    }
}
