<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftReportStaff extends Model
{
    use HasFactory;

    protected $table = 'shift_report_staff';

    protected $primaryKey = 'shift_report_staff_id';

    protected $fillable = [
        'shift_report_id',
        'building_personnel_id',
        'status',
        'working_hours',
        'performance_note'
    ];

    public function shiftReport()
    {
        return $this->belongsTo(ShiftReport::class, 'shift_report_id', 'shift_report_id');
    }

    public function buildingPersonnel()
    {
        return $this->belongsTo(BuildingPersonnel::class, 'building_personnel_id', 'building_personnel_id');
    }
}
