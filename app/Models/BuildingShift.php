<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingShift extends Model
{
    use HasFactory;

    protected $table = 'building_shifts';

    protected $primaryKey = 'building_shift_id';

    protected $fillable = [
        'building_id',
        'shift_id',
        'start_time',
        'end_time',
        'status'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'shift_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
