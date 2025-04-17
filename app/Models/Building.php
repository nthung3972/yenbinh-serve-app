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
        'total_area',
        'building_type',
        'management_fee_per_m2'
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

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'building_id', 'building_id');
    }

    public function getAllResidents()
    {
        return $this->apartments()
            ->join('apartment_resident', 'apartments.apartment_id', '=', 'apartment_resident.apartment_id')
            ->join('residents', 'apartment_resident.resident_id', '=', 'residents.resident_id')
            ->select('residents.*', 'apartments.apartment_number')
            ->get();
    }

    public function residents()
    {
        return $this->hasManyThrough(
            Resident::class,
            Apartment::class,
            'building_id', // Khóa ngoại trong bảng apartments trỏ tới buildings
            'resident_id', // Khóa chính trong bảng residents
            'building_id', // Khóa chính trong bảng buildings
            'apartment_id' // Khóa chính trong bảng apartments
        )->join('apartment_resident', 'resident.resident_id', '=', 'apartment_resident.resident_id')
            ->select('residents.*');
    }

    public function staffs()
    {
        return $this->belongsToMany(User::class, 'staff_assignments', 'building_id', 'staff_id');
    }

    public function shiftReports()
    {
        return $this->hasManyThrough(
            ShiftReport::class,
            DailyReport::class,
            'building_id', // Khóa ngoại trong bảng daily_reports trỏ tới buildings
            'daily_report_id', // Khóa chính trong bảng shift_reports
            'building_id', // Khóa chính trong bảng buildings
            'daily_report_id' // Khóa chính trong bảng daily_reports
        );
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class, 'building_id', 'building_id');
    }

    public function buildingShifts()
    {
        return $this->hasMany(BuildingShift::class, 'building_id', 'building_id');
    }

    public function buildingPersonnel()
    {
        return $this->hasMany(BuildingPersonnel::class, 'building_id', 'building_id');
    }

    public function buildingVehicleFees()
    {
        return $this->hasMany(BuildingVehicleFee::class, 'building_id', 'building_id');
    }
}
