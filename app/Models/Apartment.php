<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $table = 'apartments';

    protected $primaryKey = 'apartment_id';

    protected $fillable = [
        'apartment_number',
        'floor_number',
        'area',
        'building_id',
        'ownership_type'
    ];

    public function residents()
    {
        return $this->belongsToMany(Resident::class, 'apartment_resident', 'apartment_id', 'resident_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'resident_id', 'resident_id');
    }
}
