<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $table = 'residents';

    protected $primaryKey = 'resident_id';

    protected $fillable = [
        'resident_id',
        'full_name',
        'date_of_birth',
        'phone_number',
        'email',
        'registration_date',
        'is_owner'
    ];

    public function apartments()
    {
        return $this->belongsToMany(Apartment::class, 'apartment_resident', 'resident_id', 'apartment_id');
    }
}
