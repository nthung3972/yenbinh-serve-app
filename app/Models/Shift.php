<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $primaryKey = 'shift_id';

    protected $fillable = [
        'name', 'description', 'start_time', 'end_time', 'type'
    ];

    public function buildingShifts()
    {
        return $this->hasMany(BuildingShift::class, 'shift_id', 'shift_id');
    }

    public function shiftReports()
    {
        return $this->hasMany(ShiftReport::class, 'shift_id', 'shift_id');
    }
}
