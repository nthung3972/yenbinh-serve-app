<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtLog extends Model
{
    use HasFactory;

    protected $table = 'debt_logs';

    protected $primaryKey = 'debt_log_id';

    protected $fillable = [
        'building_id',
        'apartment_id',
        'log_date',
        'total_debt',
        'notes'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'apartment_id');
    }

}
