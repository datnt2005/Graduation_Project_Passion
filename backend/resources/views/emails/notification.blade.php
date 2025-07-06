
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>{{ $notification->title }}</title>
  <style>
    body {
      background-color: #f4f4f7;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 20px;
      color: #333;
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

    .header h1 {
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

    .image {
      text-align: center;
      margin: 20px 0;
    }

    .image img {
      max-width: 100%;
      border-radius: 8px;
    }

    .content {
      font-size: 15px;
      line-height: 1.6;
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
      <h1>{{ $notification->title }}</h1>
    </div>
    <div class="body">
      <div class="info">
        Thời gian: {{ \Carbon\Carbon::parse($notification->sent_at)->format('H:i d/m/Y') }}
      </div>

      @if($notification->image_url)
      <div class="image">
        <img src="{{ $notification->image_url }}" alt="Hình ảnh thông báo">
      </div>
      @endif

      <div class="content">
        {!! $notification->content !!}
      </div>

      @if($notification->link)
      <div class="button">
        <a href="{{ $notification->link }}" class="btn" target="_blank">Xem chi tiết</a>
      </div>
      @endif
    </div>

    <div class="footer">
      Đây là email tự động từ hệ thống. Vui lòng không trả lời.<br>
      © {{ date('Y') }} Passion. Mọi quyền được bảo lưu.
    </div>
  </div>
</body>

</html>