<?php
namespace App\Services\ApiAdmin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserService
{
    // public function __construct(
    //     public BuildingRepository $buildingRepository,
    // ) {
    // }

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
        // if ($user) {
        //     $user->email_verified_at = now();
        //     $user->verification_token = null;
        //     $user->save();

        //     return true;
        // }

        // return false;
    }

}