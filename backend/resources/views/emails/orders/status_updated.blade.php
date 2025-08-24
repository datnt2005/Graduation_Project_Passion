<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trạng thái đơn hàng - {{ config('app.name') }}</title>
    <style type="text/css">
        body {
            background: #f4f6fa;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .container {
            max-width: 500px;
            margin: 32px auto;
            background: #fff;
            border-radius: 12px 12px 0 0;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
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
            padding: 32px 24px 18px 24px;
            text-align: center;
            border-radius: 12px 12px 0 0;
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
            font-size: 2.1rem;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }

        .content {
            padding: 32px 24px 16px 24px;
            text-align: center;
        }

        .greeting {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .desc {
            color: #222;
            font-size: 1rem;
            margin-bottom: 22px;
        }

        .tracking-box {
            background: #f1f3f4;
            border-radius: 10px;
            display: inline-block;
            padding: 22px 38px;
            margin-bottom: 24px;
        }

        .tracking-code {
            font-size: 2.3rem;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 8px;
            font-family: 'Segoe UI Mono', 'Consolas', monospace;
        }

        .order-info {
            margin: 0 auto 18px auto;
            font-size: 1.08rem;
            color: #222;
            text-align: center;
        }

        .order-info strong {
            font-weight: 600;
        }

        .note {
            color: #444;
            margin-bottom: 22px;
            font-size: 1rem;
        }

        .button {
            display: inline-block;
            padding: 15px 38px;
            background-color: #1a73e8;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 18px 0 0 0;
            box-shadow: 0 2px 8px rgba(26, 115, 232, 0.08);
            transition: background 0.2s;
        }

        .button:hover {
            background: #1761c6;
        }

        .footer {
            background: #f4f6fa;
            padding: 28px 18px 10px 18px;
            text-align: center;
            font-size: 15px;
            color: #555;
            border-radius: 0 0 12px 12px;
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
                border-radius: 0;
            }

            .content {
                padding: 18px 5px 10px 5px;
            }

            .tracking-box {
                padding: 16px 0;
                width: 100%;
            }

            .tracking-code {
                font-size: 1.3rem;
                letter-spacing: 4px;
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
        <div class="header-title">Cập nhật trạng thái đơn hàng</div>

        <div class="content">
            <div class="greeting">Xin chào {{ $order->user->name ?? 'Quý khách' }},</div>
            <div class="desc">
                Đơn hàng <b>#{{ $order->shipping->tracking_code ?? $order->id }}</b> của bạn vừa được cập nhật trạng thái.<br>
            </div>
            <div class="tracking-box">
                <div><strong>Trạng thái cũ:</strong>
                    @php
                        $statusMap = [
                            'pending'    => 'Chờ xác nhận',
                            'confirmed'  => 'Đã xác nhận',
                            'processing' => 'Đang xử lý',
                            'shipping'   => 'Đang giao',
                            'shipped'    => 'Đang giao',
                            'delivered'  => 'Đã giao',
                            'cancelled'  => 'Đã hủy',
                            'completed'  => 'Hoàn thành',
                            'returned'   => 'Đã trả hàng',
                            'refunded'   => 'Đã hoàn trả',
                            'failed'     => 'Thất bại',
                            'success'    => 'Thành công',
                            'paid'       => 'Đã thanh toán',
                            'unpaid'     => 'Chưa thanh toán',
                            'waiting'    => 'Đang chờ',
                            'error'      => 'Lỗi',
                        ];
                        $old = strtolower($oldStatus ?? '');
                        echo $statusMap[$old] ?? ucfirst(strtolower(str_replace('_',' ', (string)$oldStatus)));
                    @endphp
                </div>
                <div><strong>Trạng thái mới:</strong>
                    @php
                        $status = strtolower($order->status ?? '');
                        echo $statusMap[$status] ?? ucfirst(strtolower(str_replace('_',' ', (string)$order->status)));
                    @endphp
                </div>
            </div>
            <div class="order-info">
                <div><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</div>
                <div><strong>Tổng tiền:</strong> {{ number_format($order->final_price, 0, '', ',') }} đ</div>
            </div>
            <div class="note">Bạn có thể kiểm tra chi tiết đơn hàng hoặc liên hệ CSKH nếu cần hỗ trợ thêm.</div>
            <a href="https://passionfpt.shop/order" class="button">Xem chi tiết đơn hàng</a>
        </div>
        <div class="footer">
            Nếu có thắc mắc, vui lòng liên hệ bộ phận CSKH của chúng tôi.<br><br>
            Trân trọng,<br>
            Đội ngũ <strong>PASSION</strong><br>
            <a href="https://passionfpt.shop/">Website</a> |
            <a href="mailto:support@passion.com">Liên hệ hỗ trợ</a>
            <div style="margin-top: 10px;">&copy; {{ date('Y') }} <strong>PASSION</strong>. Mọi quyền được bảo lưu.</div>
        </div>
    </div>
</body>

</html>