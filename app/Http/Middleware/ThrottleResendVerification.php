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

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Xác định loại resend dựa vào đường dẫn của request
        $path = $request->path();
        $type = 'verification'; // Mặc định

        if (str_contains($path, 'resend-password-change')) {
            $type = 'password-change';
        }

        // Key để định danh người dùng và loại resend
        $key = 'resend-' . $type . ':' . $user->id;

        // Giới hạn: 1 lần trong 60 giây
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau ' . $seconds . ' giây.',
                'status' => 429,
            ], 429);
        }

        // Ghi nhận 1 lần gửi
        RateLimiter::hit($key, 60); // reset sau 60 giây

        return $next($request);
    }
}
