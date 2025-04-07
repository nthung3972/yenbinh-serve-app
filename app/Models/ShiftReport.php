<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftReport extends Model
{
    use HasFactory;

    protected $table = 'shift_reports';

    protected $primaryKey = 'shift_report_id';

    protected $fillable = [
        'daily_report_id',
        'shift_id',
        'created_by',
        'notes',
        'status'
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id', 'daily_report_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'shift_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function shiftReportStaff()
    {
        return $this->hasMany(ShiftReportStaff::class, 'shift_report_id', 'shift_report_id');
    }
}
