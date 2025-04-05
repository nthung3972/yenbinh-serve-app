<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordOtpRequest extends Model
{
    use HasFactory;
    protected $table = 'password_otp_requests';

    protected $primaryKey = 'password_otp_request_id';

    protected $fillable = [
        'user_id', 'otp_code', 'new_password', 'expires_at'
    ];

    public $timestamps = true;
}
