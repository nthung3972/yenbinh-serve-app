<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;

class ThrottleResendVerification
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Nếu chưa đăng nhập thì từ chối luôn
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Key để định danh người dùng (bạn có thể dùng $user->email thay cho id nếu muốn)
        $key = 'resend-verification:' . $user->id;

        // Giới hạn: 2 lần trong 60 giây
        if (RateLimiter::tooManyAttempts($key, 2)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau ' . $seconds . ' giây.',
                'status' => 429,
            ], 429);
        }

        // Ghi nhận 1 lần gửi
        RateLimiter::hit($key, 300); // reset sau 60 giây

        return $next($request);
    }
}
