<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực đơn hàng - {{ config('app.name') }}</title>
    <style type="text/css">
        body {
            background: #f4f6fa;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 32px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .header {
            background: #1a73e8;
            padding: 20px 24px;
            text-align: center;
            border-bottom: 2px solid #e0e0e0;
        }
        .header img {
            max-width: 60px;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header-title {
            color: #fff;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
        }
        
        /* Tracking Code Section */
        .tracking-section {
            padding: 20px 24px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
        }
        .tracking-code {
            font-size: 1.8rem;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 2px;
            font-family: 'Segoe UI Mono', 'Consolas', monospace;
            margin-bottom: 8px;
        }
        .order-code {
            font-size: 1rem;
            color: #666;
            font-weight: 500;
        }
        
        /* Sender/Recipient Section */
        .address-section {
            padding: 20px 24px;
            border-bottom: 1px solid #e0e0e0;
        }
        .address-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .address-box {
            flex: 1;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            background: #fafafa;
        }
        .address-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .address-content {
            font-size: 0.85rem;
            color: #555;
            line-height: 1.4;
        }
        .delivery-note {
            font-size: 0.8rem;
            color: #888;
            font-style: italic;
            margin-top: 5px;
        }
        
        /* Order Content Section */
        .order-content-section {
            padding: 20px 24px;
            border-bottom: 1px solid #e0e0e0;
        }
        .order-content-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        .order-items {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        .order-item {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        /* Payment Section */
        .payment-section {
            padding: 20px 24px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .payment-label {
            font-weight: bold;
            color: #333;
            font-size: 1rem;
        }
        .payment-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a73e8;
        }
        
        /* Status Section */
        .status-section {
            padding: 20px 24px;
            text-align: center;
            background: #fff;
        }
        .status-box {
            display: inline-block;
            padding: 10px 20px;
            background: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 6px;
            color: #2e7d32;
            font-weight: bold;
            font-size: 1rem;
        }
        
        /* Action Button */
        .action-section {
            padding: 20px 24px;
            text-align: center;
            background: #fff;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1a73e8;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(26,115,232,0.2);
            transition: background 0.2s;
        }
        .button:hover {
            background: #1761c6;
        }
        
        /* Footer */
        .footer {
            background: #f4f6fa;
            padding: 20px 24px;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
        .footer a {
            color: #1a73e8;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        
        @media only screen and (max-width: 600px) {
            .container { 
                width: 100%; 
                margin: 0;
                border-radius: 0;
            }
            .address-row {
                flex-direction: column;
                gap: 10px;
            }
            .tracking-code {
                font-size: 1.4rem;
                letter-spacing: 1px;
            }
            .payment-row {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://www.passionjewelry.co.id/uploads/logo-passion-360x145.png" alt="Logo Passion">
            <div class="header-title">Xác thực đơn hàng</div>
        </div>
        
        <!-- Tracking Code Section -->
        <div class="tracking-section">
            <div class="tracking-code">{{ $order->shipping->tracking_code }}</div>
            <div class="order-code">Mã đơn hàng: {{ $order->order_code }}</div>
        </div>
        
        <!-- Sender/Recipient Section -->
        <div class="address-section">
            <div class="address-row">
                <div class="address-box">
                    <div class="address-label">Từ:</div>
                    <div class="address-content">
                        <strong>PASSION - Thế giới trang sức</strong><br>
                        {{ $order->shipping->sender_address ?? 'Địa chỉ gửi hàng' }}<br>
                        SĐT: {{ $order->shipping->sender_phone ?? 'Liên hệ' }}
                    </div>
                </div>
                <div class="address-box">
                    <div class="address-label">Đến:</div>
                    <div class="address-content">
                        <div class="delivery-note">(Chỉ giao giờ hành chính)</div>
                        <strong>{{ $order->shipping->recipient_name }}</strong><br>
                        {{ $order->shipping->recipient_address }}<br>
                        SĐT: {{ $order->shipping->recipient_phone }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Content Section -->
        <div class="order-content-section">
            <div class="order-content-title">
                Nội dung hàng (Tổng SL sản phẩm: {{ $order->orderItems->count() }})
            </div>
            <div class="order-items">
                @foreach($order->orderItems as $index => $item)
                <div class="order-item">
                    {{ $index + 1 }}. {{ $item->product->name }}, SL: {{ $item->quantity }}
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Payment Section -->
        <div class="payment-section">
            <div class="payment-row">
                <div class="payment-label">Tổng tiền:</div>
                <div class="payment-amount">{{ number_format($order->final_price, 0, '', ',') }} đ</div>
            </div>
        </div>
        
        <!-- Status Section -->
        <div class="status-section">
            <div class="status-box">
                @php
                    $statusMap = [
                        'pending' => 'Chờ xác nhận',
                        'processing' => 'Đang xử lý',
                        'shipped' => 'Đang giao',
                        'delivered' => 'Đã giao',
                        'cancelled' => 'Đã hủy',
                        'completed' => 'Hoàn thành',
                    ];
                    $status = $order->status;
                    echo $statusMap[$status] ?? ucfirst($status);
                @endphp
            </div>
        </div>
        
        <!-- Action Section -->
        <div class="action-section">
            <a href="{{ url('/orders/' . $order->id) }}" class="button">Xem chi tiết đơn hàng</a>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div style="margin-bottom: 10px;">
                <strong>Chỉ dẫn giao hàng:</strong> Được đồng kiểm<br>
                <small>Chuyển hoàn sau 3 lần phát; Lưu kho tối đa 5 ngày</small>
            </div>
            Nếu có thắc mắc, vui lòng liên hệ bộ phận CSKH của chúng tôi.<br><br>
            Trân trọng,<br>
            Đội ngũ <strong>PASSION</strong><br>
            <a href="{{ config('app.url') }}">Website</a> |
            <a href="mailto:support@passion.com">Liên hệ hỗ trợ</a>
            <div style="margin-top: 10px;">&copy; {{ date('Y') }} <strong>PASSION</strong>. Mọi quyền được bảo lưu.</div>
        </div>
    </div>
</body>
</html>
