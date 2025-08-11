<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Mã OTP xác thực</title>
    <style type="text/css">
        body, table, td, a, p, h1, h2, h3, h4, h5, h6 {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
        }
        body {
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }
        .logo-container {
            background-color: #1a73e8;
            text-align: center;
            padding: 10px 15px;
        }
        .logo-container img {
            max-width: 120px;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: brightness(0) invert(1);
        }
        .header {
            background-color: #1a73e8;
            color: #ffffff;
            text-align: center;
            padding: 10px 15px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 10px 0 0;
        }
        .content {
            padding: 30px;
            text-align: center;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 4px;
            margin: 20px 0;
            padding: 10px 20px;
            background-color: #f1f3f4;
            border-radius: 4px;
            display: inline-block;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #555555;
        }
        .footer a {
            color: #1a73e8;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .container { width: 100%; }
            .content { padding: 20px; }
            .otp-code { font-size: 24px; }
            .header { padding: 15px; }
            .header h1 { font-size: 20px; }
            .logo-container { padding: 15px; }
            .logo-container img { max-width: 100px; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Logo section -->
    <div class="logo-container">
        <img src="https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/logo_passion_white.png"
             alt="Logo Passion"
             style="max-width: 120px; height: auto; filter: brightness(0) invert(1);">
    </div>

    <!-- Header section -->
    <div class="header">
        <h1>Quên mật khẩu</h1>
    </div>

    <!-- Content section -->
    <div class="content">
        <h2>Xin chào{{ isset($user) && $user->name ? ', '.$user->name : '' }}!</h2>
        <p>Bạn (hoặc ai đó) vừa yêu cầu đặt lại mật khẩu cho tài khoản tại <strong>Passion</strong>.</p>
        <p>Vui lòng sử dụng mã OTP bên dưới để xác thực yêu cầu:</p>
        <div class="otp-code" aria-label="Mã OTP">{{ $otp }}</div>
        <p>Mã này chỉ có hiệu lực trong <strong>10 phút</strong>.</p>
        <p>Nếu bạn không yêu cầu, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi.</p>
    </div>

    <!-- Footer section -->
    <div class="footer">
        <p>Trân trọng,<br>Đội ngũ <strong>Passion</strong></p>
        <p>
            <a href="https://passionfpt.shop/">Website</a> |
            <a href="mailto:support@passion.com">Liên hệ hỗ trợ</a>
        </p>
        <p style="margin-top: 10px;">&copy; {{ date('Y') }} <strong>Passion</strong>. Mọi quyền được bảo lưu.</p>
    </div>

</div>
</body>
</html>
