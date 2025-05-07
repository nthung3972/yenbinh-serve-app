<?php

namespace App\Services\ApiAdmin;

use App\Models\PasswordOtpRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function loginWeb($userInfo)
    {
        $token = auth('api')->attempt($userInfo);
        if (!$token) {
            return ['error' => 1, 'message' => __('M41')];
        } else {
            $user = auth('api')->user();
            return [
                'token' => $token,
                'user' => $user
            ];
        }
    }

    public function createVerificationToken($user)
    {
        $verificationToken = Str::random(60);
        $user->verification_token = $verificationToken;
        $user->token_expiry = Carbon::now()->addMinutes(10);
        $user->save();

        return $user;
    }

    public function checkVerificationToken($token)
    {
        $user = User::where('verification_token', $token)->first();
        return $user;
    }

    public function createOtp($user, $otp, $newPassword)
    {
        // Kiểm tra xem OTP đã tồn tại chưa
        $existingOtp = PasswordOtpRequest::where('user_id', $user->id)->first();

        if ($existingOtp) {
            // Nếu đã tồn tại, xóa bản ghi cũ
            $existingOtp->delete();
        }

        // Tạo OTP mới
        $newOtp = PasswordOtpRequest::create([
            'user_id' => $user->id,
            'otp_code' => $otp,
            'new_password' => Hash::make($newPassword),
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        return $newOtp;
    }

    public function recreatedOtp($user, $otp)
    {
        // Tạo mã OTP mới
        $newOtp = PasswordOtpRequest::where('user_id', $user->id)->first();
        if ($newOtp) {
            $newOtp->update([
                'otp_code' => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]);
        }
        return $newOtp;
    }

    public function checkOtp($user, $otp)
    {
        $otpRequest = PasswordOtpRequest::where('user_id', $user->id)
            ->where('otp_code', $otp)
            ->where('expires_at', '>', now())
            ->first();

        return $otpRequest;
    }

    public function deleteOtp($user)
    {
        $otpRequest = PasswordOtpRequest::where('user_id', $user->id)->first();
        if ($otpRequest) {
            $otpRequest->delete();
        }
    }

    public function changePassword($user, $newPassword)
    {
        $update = User::findOrFail($user->id);
        $update->update([
            'password' => $newPassword,
        ]);
        return $update;
    }

    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function createForgotToken($email, $token)
    {
        return PasswordResetToken::updateOrCreate(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()->addMinutes(10),
            ]
        );
    }

    public function checkResetToken($email, $token)
    {
        return PasswordResetToken::where('email', $email)
            // ->where('token', Hash::make($token))
            ->where('created_at', '>', now())
            ->first();
    }

    public function resetPassword($email, $password)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
            return true;
        }
        return false;
    }

    public function deleteResetToken($email)
    {
        return DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    public function update($id, $request)
    {
        $user = User::findOrFail($id);
        return $user->update([
            'name'=> $request['name'],
            'gender'=> $request['gender'],
            'phone_number'=> $request['phone_number'],
            'date_of_birth'=> $request['date_of_birth'],
            'avatar'=> $request['avatar']
        ]);
    }
}
