<!DOCTYPE html>
<html>
<head>
    <title>Xác thực tài khoản</title>
</head>
<body>
    <h2>Xin chào {{ $user->name }}!</h2>
    <p>Cảm ơn bạn đã đăng nhập vào hệ thống của chúng tôi. Vui lòng xác thực email của bạn để tiếp tục sử dụng ứng dụng:</p>
    <p>
        <a href="{{ url('/api/admin/auth/verify/' . $user->verification_token) }}">
            Xác thực tài khoản
        </a>
    </p>
    <p>Hoặc bạn có thể copy đường link sau vào trình duyệt:</p>
    <p>{{ url('/api/admin/auth/verify/' . $user->verification_token) }}</p>
    <p><strong>Lưu ý:</strong> Link xác thực này sẽ hết hạn sau {{ $expiry_minutes }} phút từ thời điểm bạn nhận được email này.</p>
    <p>Cảm ơn bạn!</p>
</body>
</html>
