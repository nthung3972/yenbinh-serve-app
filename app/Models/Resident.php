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
        'full_name',
        'id_card_number',
        'date_of_birth',
        'gender',
        'phone_number',
        'email',
        'move_in_date',
        'move_out_date',
        'resident_type',
        'updated_by'
    ];

    public function apartments()
    {
        return $this->belongsToMany(Apartment::class, 'apartment_resident', 'resident_id', 'apartment_id')
            ->withPivot(['role_in_apartment', 'registration_date', 'registration_status', 'move_out_date']);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    public function currentApartments()
    {
        return $this->belongsToMany(Apartment::class, 'apartment_resident', 'resident_id', 'apartment_id')  
            ->withPivot('role_in_apartment', 'registration_date', 'move_out_date')
            ->wherePivotNull('move_out_date');
    }
}
