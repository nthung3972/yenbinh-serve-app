<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $table = 'daily_reports';

    protected $primaryKey = 'daily_report_id';

    protected $fillable = [
        'building_id',
        'report_date',
        'created_by',
        'status',
        'notes'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function shiftReports()
    {
        return $this->hasMany(ShiftReport::class, 'daily_report_id', 'daily_report_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    // public function scopeFilterByDate($query, $date)
    // {
    //     return $query->where('report_date', $date);
    // }
}
