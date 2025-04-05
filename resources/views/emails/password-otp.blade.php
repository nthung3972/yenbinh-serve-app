<!DOCTYPE html>
<html>

<head>
    <title>Xác thực đổi mật khẩu</title>
</head>

<body>
    <h2>Xin chào {{ $user->name }}!</h2>
    <p>Bạn đã yêu cầu đổi mật khẩu. Mã OTP của bạn là:</p>
    <h2>{{ $otp }}</h2>
    <p>Mã này sẽ hết hạn sau 10 phút. Không chia sẻ mã này với bất kỳ ai.</p>
    <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
    <p>Cảm ơn bạn!</p>
</body>

</html>