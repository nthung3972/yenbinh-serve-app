<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $primaryKey = 'shift_id';

    protected $fillable = [
        'building_id', 'name', 'description', 'start_time', 'end_time', 'type'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function buildingShifts()
    {
        return $this->hasMany(BuildingShift::class, 'shift_id', 'shift_id');
    }

    public function shiftReports()
    {
        return $this->hasMany(ShiftReport::class, 'shift_id', 'shift_id');
    }

    public function getStartTimeAttribute($value)
    {
        return $this->formatTime($value);
    }

    public function getEndTimeAttribute($value)
    {
        return $this->formatTime($value);
    }

    private function formatTime($value)
    {
        if (!$value) return null;

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return $value; 
        }
    }
}
