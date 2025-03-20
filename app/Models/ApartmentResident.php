<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentResident extends Model
{
    use HasFactory;

    protected $table = 'apartment_resident';

    protected $fillable = [
        'apartment_id',
        'resident_id',
        'relationship',
        'move_in_date',
        'move_out_date',
        'is_primary_resident'
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
