```blade
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cảnh báo: Đơn hàng {{ $order->id }} bị từ chối nhận</title>
    <style>
        body {
            background-color: #f4f4f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
        }

        .body {
            padding: 20px;
        }

        .info {
            font-size: 14px;
            margin-bottom: 10px;
            color: #555;
        }

        .content {
            font-size: 15px;
            line-height: 1.6;
        }

        .content ul {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }

        .content ul li {
            margin-bottom: 8px;
        }

        .content ul li strong {
            display: inline-block;
            width: 120px;
        }

        .warning {
            color: #2563eb;
            font-weight: bold;
            margin: 20px 0;
        }

        .button {
            text-align: center;
            margin: 20px 0;
        }

        .btn {
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }

        .footer {
            font-size: 12px;
            text-align: center;
            color: #888;
            padding: 10px 20px;
            border-top: 1px solid #e2e8f0;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Cảnh báo: Đơn hàng của bạn đã bị từ chối nhận</h2>
        </div>
        <div class="body">
            <div class="info">
                Xin chào {{ $user->name }},
            </div>
            <div class="content">
                <p>Chúng tôi nhận thấy đơn hàng #{{ $order->id }} của bạn đã bị từ chối nhận. Dưới đây là thông tin chi tiết:</p>
                <ul>
                    <li><strong>Mã đơn hàng:</strong> {{ $order->id }}</li>
                    <li><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</li>
                    <li><strong>Lý do từ chối:</strong> {{ $order->failure_reason ?? 'Không được cung cấp' }}</li>
                </ul>
                <p class="warning">
                    Lưu ý: Nếu bạn tiếp tục từ chối nhận đơn hàng, tài khoản của bạn có thể bị hạn chế sử dụng phương thức thanh toán COD (sau 2 lần từ chối) hoặc bị khóa (sau 3 lần từ chối).
                </p>
                <p>Vui lòng liên hệ với chúng tôi qua email <a href="mailto:support@example.com" class="btn">support@example.com</a> nếu bạn có bất kỳ câu hỏi nào.</p>
                <div class="button">
                    <a href="{{ url('/orders/' . $order->id) }}" class="btn" target="_blank">Xem chi tiết đơn hàng</a>
                </div>
            </div>
        </div>
        <div class="footer">
            Đây là email tự động từ hệ thống. Vui lòng không trả lời.<br>
            © {{ date('Y') }} Passion Trading. Mọi quyền được bảo lưu.
        </div>
    </div>
</body>
</html>
```