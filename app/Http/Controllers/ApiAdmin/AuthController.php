<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiAdmin\UserService;
use App\Http\Requests\UserLoginRequest;
use App\Helper\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    public function __construct(
        public UserService $userService,
    ) {}

    protected $tokenExpiryMinutes = 10;

    /**
     * Đăng nhập và xử lý đăng nhập lần đầu
     */
    public function login(UserLoginRequest $request)
    {
        try {
            $login = $this->userService->loginWeb($request->only('email', 'password'));
            if (isset($login['error']) && $login['error']) {
                return Response::dataError(config('constant.code.reverse_code_status.AUTHENTICATE'), [], __('message.M23'));
            }

            $user = Auth::user();

            if ($user->email_verified_at === null) {
                // Tạo verification token
                $createVerificationToken = $this->userService->createVerificationToken($user);
                if (!$createVerificationToken->verification_token) {
                    return Response::dataError(config('constant.code.reverse_code_status.AUTHENTICATE'), [], __('message.M23'));
                }
                // Gửi email xác thực
                $this->sendVerificationEmail($user);
            }

            $userInfo = [
                'id' => auth('api')->user()->id,
                'name' => auth('api')->user()->name,
                'email' => auth('api')->user()->email,
                'role' => auth('api')->user()->role,
                'email_verified_at' => auth('api')->user()->email_verified_at,
            ];
            return Response::data(['token' => $login['token'], 'user' => $userInfo]);
        } catch (\Throwable $th) {
            return response()->json([
                    'success' => false,
                    'message' => 'Login failed: ' . $th->getMessage(),
                ], 500);
            // return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
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

    /**
     * Gửi email xác thực
     */
    private function sendVerificationEmail($user)
    {
        Mail::send('emails.verify', [
            'user' => $user,
            'expiry_minutes' => $this->tokenExpiryMinutes
        ], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Xác thực tài khoản');
        });
    }

    /**
     * Xác thực email
     */
    public function verify(Request $request)
    {
        $token = $request->token;

        $user = $this->userService->checkVerificationToken($token);
        
        if (!$user) {
            return redirect('http://localhost:3000/auth/verify-failed');

            return response()->json([
                'message' => 'Token xác thực không hợp lệ!'
            ], 404);
        }

        if (Carbon::now()->isAfter($user->token_expiry)) {
            // Xóa token đã hết hạn
            $user->verification_token = null;
            $user->token_expiry = null;
            $user->save();

            return redirect('http://localhost:3000/auth/verify-failed');
            
            return response()->json([
                'message' => 'Token xác thực đã hết hạn! Vui lòng đăng nhập lại để nhận token mới.',
                'expired' => true
            ], 401);
        }

        $user->email_verified_at = Carbon::now();
        $user->verification_token = null;
        $user->token_expiry = null;
        $user->save();

        return redirect('http://localhost:3000/auth/email-verified-success');

        // Xác thực thành công
        return response()->json([
            'message' => 'Xác thực email thành công! Bạn có thể tiếp tục sử dụng ứng dụng.'
        ], 200);
    }

    /**
     * Gửi lại email xác thực
     */
    public function resendVerification()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if ($user->email_verified_at !== null) {
                return response()->json([
                    'message' => 'Email đã được xác thực trước đó.',
                    'status' => 422
                ], 422);
            }
            
            // Tạo token mới nếu không có
            if (empty($user->verification_token)) {
                $this->userService->createVerificationToken($user);
            }
            
            // Gửi lại email xác thực
            $this->sendVerificationEmail($user);
            
            return response()->json([
                'message' => 'Email xác thực đã được gửi lại! Vui lòng hoàn thành xác thực trong vòng ' . $this->tokenExpiryMinutes . ' phút.',
                'status' => 200,
                'token_expires_in' => Carbon::now()->diffInSeconds($user->token_expiry)
            ], 200);
        } 
        catch (\Throwable $th) {
            return response()->json([
                'message' => 'Token không hợp lệ',
                'status' => 401
            ], 401);
        }
    }
}
