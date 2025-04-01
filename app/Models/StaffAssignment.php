<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffAssignment extends Model
{
    use HasFactory;

    protected $primaryKey = 'staff_assignment_id';

    protected $fillable = [
        'staff_id',
        'building_id',
        'role',
        'assigned_task',
    ];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'building_id', 'building_id');
    }
}
