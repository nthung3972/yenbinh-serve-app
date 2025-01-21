<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\UserService;
use App\Helper\Response;

class AuthController extends Controller
{

    public function __construct(
        public UserService $userService,
    ) {
    }

    public function login(Request $request)
    {   
        try {
            $login = $this->userService->loginWeb($request->only('email', 'password'));
            if (isset($login['error']) && $login['error']) {
                return Response::dataError(config('constant.code.reverse_code_status.AUTHENTICATE'), [], __('message.M23'));
            }
            $userInfo = [
                'name' => auth('api')->user()->name,
                'email' => auth('api')->user()->email,
            ];
            return Response::data(['token' => $login['token'], 'user' => $userInfo]);
        }catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error'=>[$th->getMessage()]], $th->getMessage());
        }
    }
}
