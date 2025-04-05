<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\ApiAdmin\UserService;
use App\Mail\SendPasswordOtpMail;
use Illuminate\Http\Request;
use App\Http\Requests\PasswordRequest\ChangePasswordRequest;
use App\Http\Requests\PasswordRequest\VerifyPasswordRequest;
use Illuminate\Support\Str;

class PasswordChangeController extends Controller
{
    public function __construct(
        public UserService $userService,
    ) {}

    /**
     * Gửi mã OTP để xác thực đổi mật khẩu
     */
    public function requestChange(ChangePasswordRequest $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Mật khẩu hiện tại không đúng'], 403);
        }

        $otp = random_int(100000, 999999);

        $createOtp = $this->userService->createOtp($user, $otp, $request->new_password);
        if (!$createOtp) {
            return response()->json(['error' => 'Không thể tạo mã xác nhận'], 500);
        }

        Mail::to($user->email)->send(new SendPasswordOtpMail($user, $otp));

        return response()->json(['message' => 'Mã xác nhận đã được gửi đến email của bạn']);
    }

    /**
     * Xác thực mã OTP và đổi mật khẩu
     */
    public function verifyChange(VerifyPasswordRequest $request)
    {
        $user = auth()->user();

        $otpRequest = $this->userService->checkOtp($user, $request->otp);

        if (!$otpRequest) {
            return response()->json(['error' => 'Mã OTP không hợp lệ hoặc đã hết hạn'], 400);
        }

        // Đổi mật khẩu
        $changePassword = $this->userService->changePassword($user, $otpRequest['new_password']);
        if (!$changePassword) {
            return response()->json(['error' => 'Không thể đổi mật khẩu'], 500);
        }

        // Xóa bản ghi OTP
        $this->userService->deleteOtp($user);

        return response()->json(['message' => 'Đổi mật khẩu thành công']);
    }

    /**
     * Gửi lại mã OTP
     */
    public function resendPasswordChange()
    {
        $user = auth()->user();

        // Tạo mã OTP mới (hoặc giữ nguyên nếu muốn)
        $otp = random_int(100000, 999999);

        // Cập nhật mã OTP và thời gian hết hạn
        $recreatedOtp = $this->userService->recreatedOtp($user, $otp);

        if (!$recreatedOtp) {
            return response()->json(['error' => 'Không thể tạo lại mã xác nhận'], 500);
        }

        // Gửi mail OTP
        Mail::to($user->email)->send(new SendPasswordOtpMail($user, $otp));

        return response()->json([
            'message' => 'Mã OTP mới đã được gửi đến email của bạn.'
        ]);
    }

    /**
     * Gửi link quên mật khẩu
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = $this->userService->getUserByEmail($request->email);
        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại'], 404);
        }
        // Tạo token
        $token = Str::random(64);

        // Lưu token vào bảng password_resets
        $createForgotToken = $this->userService->createForgotToken($user->email, $token);
        if (!$createForgotToken) {
            return response()->json(['message' => 'Không thể tạo token'], 500);
        }

        // Gửi email chứa link reset
        Mail::to($user->email)->send(new \App\Mail\SendLinkForgotPasswordMail($user, $token));

        return response()->json(['message' => 'Đã gửi link đặt lại mật khẩu, vui lòng kiểm tra email của bạn']);
    }

    /**
     * Reset mật khẩu
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);



        $checkResetToken = $this->userService->checkResetToken($request->email, $request->token);
        if (!$checkResetToken ||!Hash::check($request->token, $checkResetToken->token)) {
            return response()->json(['message' => 'Token không hợp lệ hoặc đã hết hạn!'], 404);
        }

        //reset mật khẩu
        $reset = $this->userService->resetPassword($request->email, $request->password);
        if (!$reset) {
            return response()->json(['message' => 'Không thể đặt lại mật khẩu'], 500);
        }
    
        // Xóa token sau khi dùng
        $deleteToken = $this->userService->deleteResetToken($request->email);
        if (!$deleteToken) {
            return response()->json(['message' => 'Không thể xóa token'], 500);
        }
    
        return response()->json(['message' => 'Đặt lại mật khẩu thành công']);
    }
}
