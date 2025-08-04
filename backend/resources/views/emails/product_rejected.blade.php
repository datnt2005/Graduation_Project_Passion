<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Từ chối duyệt cửa hàng</title>
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
            background-color: #e53935;
            /* đỏ nhẹ nhàng */
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
            <h1>Thông báo từ chối cửa hàng</h1>
        </div>
        <div class="content">
            <h2 style="color: #e53935;">Xin chào {{ $seller->user->name }},</h2>

            <p>
                Rất tiếc! Sau quá trình kiểm tra và đánh giá kỹ lưỡng, sản phẩm <em>{{ $product->name }}</em> (#{{ $product->id }}) của cửa hàng
                <strong>{{ $seller->store_name }}</strong> hiện tại <strong>chưa được duyệt</strong>.
            </p>

            <p><strong>Lý do từ chối:</strong> <em>{{ $reason }}</em></p>

            <p>
                Chúng tôi hiểu rằng điều này có thể khiến bạn cảm thấy thất vọng, tuy nhiên việc này nhằm đảm bảo chất lượng sản phẩm và trải nghiệm tốt nhất cho người dùng trên hệ thống.
            </p>

            <p>
                Bạn hoàn toàn có thể <strong>chỉnh sửa lại thông tin</strong> sản phẩm hoặc hồ sơ đăng ký để đáp ứng đầy đủ các tiêu chí cần thiết.
                Sau khi hoàn thiện, hãy gửi lại yêu cầu xét duyệt và chúng tôi sẽ nhanh chóng hỗ trợ bạn.
            </p>

            <p>
                Nếu cần bất kỳ sự trợ giúp nào, vui lòng liên hệ với đội ngũ hỗ trợ của chúng tôi để được hướng dẫn cụ thể hơn.
            </p>

            <p style="margin-top: 24px;">
                Chúc bạn sớm hoàn thiện và sẵn sàng cho hành trình kinh doanh thành công cùng <strong>{{ $seller->store_name }}</strong>! 💪
            </p>
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