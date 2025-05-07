<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentResident extends Model
{
    use HasFactory;

    protected $table = 'apartment_resident';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'apartment_id',
        'resident_id',
        'role_in_apartment',
        'registration_date',
        'registration_status',
        'notes'
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function getKey()
    {
        return [
            'apartment_id' => $this->apartment_id,
            'resident_id' => $this->resident_id
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }
}
