<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeSubtype extends Model
{
    use HasFactory;
    protected $table = 'fee_subtypes';

    protected $primaryKey = 'fee_subtypes_id';

    protected $fillable = [
        'fee_types_id',
        'code',
        'name',
        'unit',
        'note'
    ];

    public function feeType()
    {
        return $this->belongsTo(FeeType::class, 'fee_types_id', 'fee_types_id');
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'fee_subtypes_id', 'fee_subtypes_id');
    }
}
