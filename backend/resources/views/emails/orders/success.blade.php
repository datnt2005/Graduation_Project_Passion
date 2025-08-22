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
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 32px rgba(26, 115, 232, 0.10);
        }
        .logo-container {
            background-color: #1a73e8;
            text-align: center;
            padding: 18px 15px 10px 15px;
        }
        .logo-container img {
            max-width: 120px;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: brightness(0) invert(1);
        }
        .success-icon {
            width: 70px;
            height: 70px;
            background: #22c55e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px auto;
            box-shadow: 0 2px 12px rgba(34,197,94,0.12);
        }
        .success-icon svg {
            width: 38px;
            height: 38px;
            color: #fff;
        }
        .header {
            background: #fff;
            padding: 0 24px 18px 24px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        .header-title {
            color: #22c55e;
            font-size: 1.7rem;
            font-weight: bold;
            margin: 0 0 6px 0;
        }
        .header-desc {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        .tracking-section {
            padding: 20px 24px 12px 24px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
        }
        .tracking-code {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 2px;
            font-family: 'Segoe UI Mono', 'Consolas', monospace;
            margin-bottom: 4px;
        }
        .order-code {
            font-size: 1rem;
            color: #666;
            font-weight: 500;
        }
        .address-section {
            padding: 20px 24px 10px 24px;
            border-bottom: 1px solid #e0e0e0;
        }
        .address-row {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
            justify-content: space-between;
            flex-wrap: wrap;
            align-items: flex-start;
        }
        .address-box {
            flex: 1;
            padding: 15px;
border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9fafb;
            margin: 0 10px;
        }
        .address-label {
            font-weight: bold;
            color: #1a73e8;
            margin-bottom: 8px;
            font-size: 0.97rem;
        }
        .address-content {
            font-size: 0.93rem;
            color: #333;
            line-height: 1.5;
        }
        .order-content-section {
            padding: 20px 24px 10px 24px;
            border-bottom: 1px solid #e0e0e0;
        }
        .order-content-title {
            font-weight: bold;
            color: #1a73e8;
            margin-bottom: 10px;
            font-size: 1.08rem;
        }
        .order-items {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .order-item {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .payment-section {
            padding: 20px 24px 10px 24px;
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
            font-size: 1.08rem;
        }
        .payment-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a73e8;
        }
        .status-section {
            padding: 20px 24px 10px 24px;
            text-align: center;
            background: #fff;
        }
        .status-box {
            display: inline-block;
            padding: 10px 24px;
            background: #e8f5e8;
            border: 1px solid #22c55e;
            border-radius: 8px;
            color: #22c55e;
            font-weight: bold;
            font-size: 1.08rem;
        }
        .action-section {
            padding: 24px 24px 18px 24px;
            text-align: center;
            background: #fff;
        }
        .button {
            display: inline-block;
            padding: 14px 38px;
            background-color: #1a73e8;
            color: #fff !important;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.08rem;
            box-shadow: 0 2px 12px rgba(26, 115, 232, 0.13);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .button:hover {
            background: #1761c6;
            box-shadow: 0 4px 18px rgba(26, 115, 232, 0.18);
        }
        .footer {
            background: #f4f6fa;
            padding: 22px 24px 18px 24px;
text-align: center;
            font-size: 0.97rem;
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
                font-size: 1.2rem;
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
        <!-- Logo section -->
        <div class="logo-container">
            <img src="https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/logo_passion_white.png"
                alt="Logo Passion"
                style="max-width: 120px; height: auto; filter: brightness(0) invert(1);">
        </div>


        <!-- Success Icon & Header section -->
        <div class="header">
                                    <div class="success-icon">
                                            <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="30" cy="30" r="30" fill="#22c55e"/>
                                                <path d="M18 32 L27 41 L43 23" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                            </svg>
                                    </div>
            <div class="header-title">Đặt hàng thành công!</div>
            <div class="header-desc">Cảm ơn bạn đã đặt hàng tại <strong>PASSION</strong>.<br>Thông tin đơn hàng của bạn đã được ghi nhận.</div>
        </div>

        <!-- Tracking Code Section -->
        <div class="tracking-section">
            <div class="tracking-code">{{ $order->shipping->tracking_code }}</div>
            <div class="order-code">Mã đơn hàng: {{ $order->order_code }}</div>
        </div>

        <!-- Sender/Recipient Section -->
        <div class="address-section">
            <div class="address-row justify-content-between">
                <div class="address-box mx-3">
                    <div class="address-label">Người bán:</div>
                    <div class="address-content">
                        <strong>{{ $order->orderItems->first()->product->seller->store_name ?? 'PASSION' }}</strong><br>
                        SĐT: {{ $order->orderItems->first()->product->seller->phone_number ?? 'Liên hệ' }}
                    </div>
</div>
                <div class="address-box">
                    <div class="address-label">Người mua:</div>
                    <div class="address-content">
                        <strong>{{ $order->address->name }}</strong><br>
                        {{ $order->address->detail }}<br>
                        SĐT: {{ $order->address->phone }}<br>
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
               <p class="payment-label">Tổng tiền: {{ number_format($order->final_price, 0, ',', '.') }} đ</p>
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
            <a href="https://passionfpt.shop/order" class="button">Xem chi tiết đơn hàng</a>
            <div style="margin-top: 14px; color: #888; font-size: 0.97rem;">Bạn sẽ sớm nhận được thông báo khi đơn hàng được xác nhận và giao cho đơn vị vận chuyển.</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div style="margin-bottom: 10px;">
                <strong>Chỉ dẫn giao hàng:</strong> Được đồng kiểm<br>
                <small>Chuyển hoàn sau 3 lần phát; Lưu kho tối đa 5 ngày</small>
            </div>
            Nếu có thắc mắc, vui lòng liên hệ bộ phận CSKH của chúng tôi.<br>
            <a href="mailto:support@passion.com">support@passion.com</a> | <a href="https://passionfpt.shop/">Website</a>
            <div style="margin-top: 10px;">&copy; {{ date('Y') }} <strong>PASSION</strong>. Mọi quyền được bảo lưu.</div>
        </div>
    </div>
</body>

</html>