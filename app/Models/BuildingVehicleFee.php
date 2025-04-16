<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingVehicleFee extends Model
{
    use HasFactory;

    protected $table = 'building_vehicle_fees';

    protected $primaryKey = 'building_vehicle_fee_id';

    protected $fillable = [
        'building_id',
        'vehicle_type_id',
        'parking_fee'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id', 'vehicle_type_id');
    }
}
