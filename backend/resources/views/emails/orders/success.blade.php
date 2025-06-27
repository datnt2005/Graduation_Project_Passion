<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - {{ config('app.name') }}</title>
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
        .order-info {
            margin: 20px 0;
            padding: 20px;
            background-color: #f1f3f4;
            border-radius: 4px;
            text-align: left;
        }
        .order-info p {
            margin: 10px 0;
        }
        .tracking-code {
            font-size: 24px;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 2px;
            margin: 20px 0;
            padding: 10px 20px;
            background-color: #f1f3f4;
            border-radius: 4px;
            display: inline-block;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1a73e8;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
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
            .tracking-code { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://www.passionjewelry.co.id/uploads/logo-passion-360x145.png" alt="Logo Passion" style="max-width: 150px; height: auto;">
            <h1>Đặt hàng thành công!</h1>
        </div>

        <div class="content">
            <h2>Xin chào {{ $order->user->name }}!</h2>
            <p>Cảm ơn bạn đã đặt hàng tại {{ config('app.name') }}.</p>
            
            <div class="order-info">
                <p><strong>Mã vận đơn:</strong></p>
                <div class="tracking-code">{{ $order->shipping->tracking_code }}</div>
                <p><strong>Tổng tiền:</strong> {{ number_format($order->final_price, 0, '', ',') }} đ</p>
                <p><strong>Trạng thái:</strong> {{ $order->status }}</p>
            </div>

            <p>Bạn sẽ sớm nhận được thông tin giao hàng qua email hoặc điện thoại.</p>
            
            <a href="{{ url('/orders/' . $order->id) }}" class="button">Xem chi tiết đơn hàng</a>
            
            <p>Nếu có thắc mắc, vui lòng liên hệ bộ phận CSKH của chúng tôi.</p>
        </div>

        <div class="footer">
            <p>Trân trọng,<br>Đội ngũ <strong>{{ config('app.name') }}</strong></p>
            <p>
                <a href="{{ config('app.url') }}">Website</a> |
                <a href="mailto:support@passion.com">Liên hệ hỗ trợ</a>
            </p>
            <p style="margin-top: 10px;">&copy; {{ date('Y') }} <strong>{{ config('app.name') }}</strong>. Mọi quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
