<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'gender',
        'address',
        'date_of_birth',
        'phone_number',
        'password',
        'role',
        'verification_token',
        'token_expiry'
    ];

    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role, // Thêm role vào token
        ];
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class, 'created_by');
    }

    public function shiftReports()
    {
        return $this->hasMany(ShiftReport::class, 'created_by');
    }

    public function staffAssignment()
    {
        return $this->hasMany(StaffAssignment::class, 'staff_id');
    }
}
