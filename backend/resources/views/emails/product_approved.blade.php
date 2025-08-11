<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo duyệt cửa hàng</title>
    <style type="text/css">
        body,
        table,
        td,
        a,
        p,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
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
        <!-- Logo section -->
        <div class="logo-container">
            <img src="https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/logo_passion_white.png"
                alt="Logo Passion"
                style="max-width: 120px; height: auto; filter: brightness(0) invert(1);">
        </div>

        <!-- Header section -->
        <div class="header">
            <h1>Sản phẩm của bạn đã được duyệt</h1>
        </div>

        <div class="content">
            <h2>Xin chào {{ $seller->user->name }},</h2>
            <p>🎉 <strong>Chúc mừng bạn!</strong></p>

            <p>
                Sản phẩm <strong>{{ $product->name }}</strong> (#{{ $product->id }}) của cửa hàng <strong>{{ $seller->store_name }}</strong> đã được chúng tôi
                <strong>duyệt thành công</strong> sau quá trình kiểm tra và đánh giá kỹ lưỡng.
            </p>

            <p>
                Điều này đồng nghĩa với việc sản phẩm của bạn sẽ sớm xuất hiện trên hệ thống và tiếp cận với hàng ngàn khách hàng tiềm năng.
                Hãy đảm bảo cập nhật đầy đủ thông tin, hình ảnh rõ nét và giá cả cạnh tranh để thu hút người mua!
            </p>

            <p>
                Chúng tôi cam kết luôn đồng hành cùng bạn trong suốt quá trình kinh doanh.
                Nếu bạn có bất kỳ thắc mắc hoặc cần hỗ trợ, đừng ngần ngại liên hệ với đội ngũ của chúng tôi.
            </p>

            <p>
                Một lần nữa, xin chúc mừng bạn vì sản phẩm đã được duyệt!
                Chúc {{ $seller->store_name }} kinh doanh phát đạt và ngày càng phát triển cùng hệ thống của chúng tôi.
            </p>


            <div class="footer">
                <p>Trân trọng,<br>Đội ngũ <strong>Passion</strong></p>
                <p>
                    <a href="https://passionfpt.shop">Website</a> |
                    <a href="mailto:support@passion.com">Liên hệ hỗ trợ</a>
                </p>
                <p style="margin-top: 10px;">&copy; {{ date('Y') }} <strong>Passion</strong>. Mọi quyền được bảo lưu.</p>
            </div>
        </div>
</body>

</html>
