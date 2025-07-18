<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cập nhật yêu cầu đổi/trả</title>
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
        .product-name-box {
    font-size: 24px;
    font-weight: bold;
    color: #1a73e8;
    letter-spacing: 1px;
    margin: 20px 0;
    padding: 10px 20px;
    background-color: #f1f3f4;
    border-radius: 4px;
    display: inline-block;
}

    </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="https://www.passionjewelry.co.id/uploads/logo-passion-360x145.png" alt="Logo">
      <h1>Cập nhật yêu cầu đổi/trả</h1>
    </div>

    <div class="content">
      <h2>Xin chào {{ $returnRequest->user->name }}</h2>
      <p>Yêu cầu đổi/trả của bạn cho sản phẩm:</p>
<div class="product-name-box">{{ $returnRequest->orderItem->product->name }}</div>

      <p>
       Yêu cầu của bạn đã được tiếp nhận
        @if ($returnRequest->status === 'approved')
          <strong style="color: green;">và đã được chấp nhận vui lòng gửi lại hàng cho người bán</strong>
        @else
          <strong style="color: red;">và không được chấp nhận</strong>
        @endif
      </p>

      @if ($returnRequest->admin_note)
        <p><strong>Ghi chú từ người xử lý:</strong><br>{{ $returnRequest->admin_note }}</p>
      @endif

      

      <p>Nếu có bất kỳ thắc mắc nào, bạn có thể liên hệ lại với chúng tôi qua email hỗ trợ.</p>
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
