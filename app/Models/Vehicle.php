<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    protected $primaryKey = 'vehicle_id';

    protected $appends = ['apartment_number'];

    protected $fillable = [
        'license_plate',
        'vehicle_type',
        'parking_slot',
        'status',
        'apartment_id',
        'building_id',
        'updated_by'
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'apartment_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function getApartmentNumberAttribute()
    {
        return $this->apartment->apartment_number ?? null;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
