<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    use HasFactory;
    protected $table = 'fee_types';

    protected $primaryKey = 'fee_type_id';

    protected $fillable = [
        'fee_name',
        'is_fixed'
    ];

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'fee_type_id', 'fee_type_id');
    }
}
