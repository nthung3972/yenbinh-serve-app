<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_detail_id';
    protected $appends = ['fee_subtype_name'];

    protected $fillable = [
        'invoice_id',
        'fee_types_id',
        'fee_subtypes_id',
        'building_fee_id',
        'description',
        'quantity',
        'unit_price',
        'amount'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function feeTypes()
    {
        return $this->belongsTo(FeeType::class, 'fee_types_id', 'fee_types_id');
    }

    public function feeSubtypes()
    {
        return $this->belongsTo(FeeSubtype::class, 'fee_subtypes_id', 'fee_subtypes_id');
    }

    public function getFeeSubtypeNameAttribute()
    {
        return $this->feeSubtypes->name ?? null;
    }
}
