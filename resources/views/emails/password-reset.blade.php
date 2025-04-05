<!DOCTYPE html>
<html>

<head>
    <title>Đặt lại mật khẩu</title>
</head>

<body>
    <p>Chào {{ $user->name }},</p>
    <p>Bạn vừa yêu cầu đặt lại mật khẩu. Nhấn vào link bên dưới để đặt lại:</p>
    <p>
        <a href="http://localhost:3000/auth/password/reset?token={{ $token }}&email={{ urlencode($user->email) }}">
            Đặt lại mật khẩu
        </a>
    </p>
    <p>Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>
    <p>Cảm ơn bạn!</p>
</body>

</html>