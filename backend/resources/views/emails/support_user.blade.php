<!-- filepath: c:\Users\ntai6\Desktop\passion1\Graduation_Project_Passion\backend\resources\views\emails\support_user.blade.php -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phản hồi hỗ trợ từ Passion</title>
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
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(26, 115, 232, 0.10);
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
            background: #1a73e8;
            color: #ffffff;
            text-align: center;
            padding: 32px 20px 24px 20px;
        }
        .header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 0;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1a73e8;
            margin-bottom: 12px;
        }
        .support-info {
            background-color: #f1f3f4;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 24px;
            border-left: 4px solid #1a73e8;
        }
        .support-info p {
            margin: 8px 0;
            font-size: 16px;
        }
        .admin-reply {
            font-size: 17px;
            color: #185abc;
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 24px;
            border-left: 4px solid #4285f4;
            box-shadow: 0 2px 8px rgba(66, 133, 244, 0.08);
        }
        .admin-reply strong {
            font-size: 18px;
            color: #1a73e8;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 24px;
            text-align: center;
            font-size: 15px;
            color: #555555;
            border-top: 1px solid #e0e0e0;
        }
        .footer a {
            color: #1a73e8;
            text-decoration: none;
            margin: 0 8px;
        }
        @media only screen and (max-width: 600px) {
            .container { width: 100%; }
            .content { padding: 18px; }
            .header { padding: 20px 10px 16px 10px; }
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
        <h1>Phản hồi hỗ trợ</h1>
    </div>

        <div class="content">
            <div class="greeting">
                Xin chào{{ isset($support->name) ? ', '.$support->name : '' }}!
            </div>
            <p style="font-size:16px;">Cảm ơn bạn đã liên hệ hỗ trợ với <strong>Passion</strong>. Chúng tôi đã nhận được yêu cầu của bạn và phản hồi như sau:</p>
            <div class="support-info">
                <p><strong>Tiêu đề:</strong> {{ $support->subject ?? '(Không có tiêu đề)' }}</p>
                <p><strong>Nội dung yêu cầu:</strong> {{ $support->content }}</p>
            </div>
            <div class="admin-reply">
                <strong>Phản hồi từ quản trị viên:</strong>
                <br>
                {{ $admin_reply }}
            </div>
            <p style="font-size:15px;">Nếu bạn cần thêm hỗ trợ, vui lòng phản hồi lại email này hoặc liên hệ với chúng tôi qua các kênh bên dưới.</p>
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