<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingPersonnel extends Model
{
    use HasFactory;

    protected $table = 'building_personnel';
    protected $primaryKey = 'building_personnel_id';

    protected $fillable = [
        'building_id',
        'personnel_name',
        'personnel_phone',
        'personnel_address',
        'position',
        'status'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function shiftReportStaff()
    {
        return $this->hasMany(ShiftReportStaff::class, 'building_personnel_id', 'building_personnel_id');
    }

    public function shiftReports()
    {
        return $this->hasMany(ShiftReport::class, 'building_personnel_id', 'building_personnel_id');
    }

    
    
}
