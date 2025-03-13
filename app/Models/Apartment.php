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
        'apartment_id',
        'number',
        'floor',
        'area',
        'status',
        'building_id',
        'resident_id'
    ];
}
