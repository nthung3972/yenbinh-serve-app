<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Building extends Model
{
    use HasFactory;

    protected $table = 'buildings';

    protected $primaryKey = 'building_id';

    protected $fillable = [
        'name',
        'address',
        'image',
        'floors',
        'status',
        'manager_id'
    ];

    // Một tòa nhà có nhiều căn hộ
    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class, 'building_id', 'building_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'building_id', 'building_id');
    }
}
