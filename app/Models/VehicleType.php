<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;
    protected $table = 'vehicle_types';
    protected $primaryKey = 'vehicle_type_id';

    protected $fillable = [
        'vehicle_type_name'
    ];

    public function buildingVehicleFees()
    {
        return $this->hasMany(BuildingVehicleFee::class, 'vehicle_type_id', 'vehicle_type_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_type_id', 'vehicle_type_id');
    }
}
