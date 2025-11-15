<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'building_id',
        'invoice_number',
        'building_id',
        'apartment_id',
        'period',
        'issue_date',
        'due_date',
        'status',
        'total_amount',
        'paid_amount',
        'opening_balance',
        'closing_balance'
    ];

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id', 'invoice_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'apartment_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'invoice_id');
    }
}
