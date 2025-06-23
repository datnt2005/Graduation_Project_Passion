<x-mail::message>
# Đặt hàng thành công!

Xin chào {{ $order->user->name }},

Cảm ơn bạn đã đặt hàng tại {{ config('app.name') }}.

**Mã đơn hàng:** {{ $order->id }}
**Tổng tiền:** {{ $order->final_price }} đ
**Trạng thái:** {{ $order->status }}

Bạn sẽ sớm nhận được thông tin giao hàng qua email hoặc điện thoại.

<x-mail::button :url="url('/orders/' . $order->id)">
Xem chi tiết đơn hàng
</x-mail::button>

Nếu có thắc mắc, vui lòng liên hệ bộ phận CSKH của chúng tôi.

Cảm ơn bạn,<br>
{{ config('app.name') }}
</x-mail::message>
