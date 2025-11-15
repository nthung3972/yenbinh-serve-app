<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    use HasFactory;
    protected $table = 'fee_types';

    protected $primaryKey = 'fee_types_id';

    protected $fillable = [
        'code', 'name', 'description', 'is_active'
    ];

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'fee_types_id', 'fee_types_id');
    }

    public function feeSubtypes()
    {
        return $this->hasMany(FeeSubtype::class, 'fee_types_id', 'fee_types_id');
    }
}
