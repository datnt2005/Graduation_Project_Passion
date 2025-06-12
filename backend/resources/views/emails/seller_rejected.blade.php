<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Từ chối duyệt cửa hàng</title>
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
            background-color: #e53935; /* đỏ nhẹ nhàng */
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
            .container {
                width: 100%;
                margin: 0;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="https://www.passionjewelry.co.id/uploads/logo-passion-360x145.png" alt="Logo">
        <h1>Thông báo từ chối cửa hàng</h1>
    </div>

    <div class="content">
        <h2>Xin chào {{ $seller->user->name }},</h2>
        <p>Rất tiếc! Cửa hàng <strong>{{ $seller->store_name }}</strong> của bạn <strong>chưa được duyệt</strong>.</p>
        <p>Lý do từ chối: <em>{{ $reason }}</em></p>
        <p>Vui lòng kiểm tra lại thông tin và cập nhật hồ sơ nếu cần. Bạn hoàn toàn có thể gửi lại yêu cầu sau khi chỉnh sửa.</p>
        <p>Chúc bạn sớm được duyệt và kinh doanh thành công 💪</p>
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
