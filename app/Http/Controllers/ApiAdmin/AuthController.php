<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiAdmin\UserService;
use App\Http\Requests\UserLoginRequest;
use App\Helper\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function __construct(
        public UserService $userService,
    ) {
    }

    public function login(UserLoginRequest $request)
    {   
        try {
            $login = $this->userService->loginWeb($request->only('email', 'password'));
            if (isset($login['error']) && $login['error']) {
                return Response::dataError(config('constant.code.reverse_code_status.AUTHENTICATE'), [], __('message.M23'));
            }
            $userInfo = [
                'name' => auth('api')->user()->name,
                'email' => auth('api')->user()->email,
                'role' => auth('api')->user()->role,
            ];
            return Response::data(['token' => $login['token'], 'user' => $userInfo]);
        }catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error'=>[$th->getMessage()]], $th->getMessage());
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken()); // Vô hiệu hóa token

            // return Response::data(['token' => $login['token'], 'user' => $userInfo]);

            return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
