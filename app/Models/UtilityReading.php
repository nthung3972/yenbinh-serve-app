<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityReading extends Model
{
    use HasFactory;

    protected $table = 'utility_readings';

    protected $primaryKey = 'utility_reading_id';

    protected $fillable = [
        'apartment_id',
        'building_id',
        'utility_type',
        'period',
        'previous_reading',
        'current_reading',
        'consumption',
        'unit_price',
        'amount',
        'reading_date',
        'reader_name',
        'photo_url',
        'note',
        'status'
    ];
}
