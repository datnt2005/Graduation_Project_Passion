<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Xác thực OTP</title>
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #1a73e8; /* Primary brand color (customizable) */
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .header img {
            max-width: 150px;
            height: auto;
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
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1a73e8;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 0;
            }
            .content {
                padding: 20px;
            }
            .otp-code {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://www.passionjewelry.co.id/uploads/logo-passion-360x145.png" alt="Logo" style="max-width: 150px; height: auto;">
            <h1>Xác thực tài khoản</h1>
        </div>

        <div class="content">
            <h2>Chào bạn</h2>
            <p>Cảm ơn bạn đã đăng ký tài khoản với chúng tôi!</p>
            <p>Để hoàn tất quá trình xác thực, vui lòng sử dụng mã OTP dưới đây:</p>
            <div class="otp-code" aria-label="Mã OTP">{{ $otp }}</div>
            <p>Mã này có hiệu lực trong <strong>5 phút</strong>.</p>
            <p>Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi.</p>
        </div>

        <div class="footer">
            <p>Trân trọng,<br>Đội ngũ <strong>Passion</strong></p>
            <p>
                <a href="https://passion.com">Website</a> |
                <a href="mailto:support@passion.com">Liên hệ hỗ trợ</a>
            </p>
            <p style="margin-top: 10px;">&copy; {{ date('Y') }} <strong>Passion</strong>. Mọi quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>

