# Tính năng Duyệt Payout Tự động

## Tổng quan
Tính năng này cho phép hệ thống tự động duyệt payout khi seller cập nhật trạng thái đơn hàng thành "Đã giao", giúp tăng tốc độ thanh toán cho seller.

## Cách hoạt động

### 1. Logic duyệt tự động
- **Tỷ lệ duyệt tự động**: 80% payout sẽ được duyệt tự động
- **Tỷ lệ duyệt thủ công**: 20% payout cần admin duyệt thủ công
- **Mục đích**: Đảm bảo an toàn và kiểm soát trong khi vẫn tăng tốc độ thanh toán

### 2. Quy trình
1. Seller cập nhật trạng thái đơn hàng thành "delivered"
2. Hệ thống tự động tạo payout cho đơn hàng
3. Hệ thống thực hiện duyệt tự động với xác suất 80%
4. Nếu được duyệt tự động:
   - Trạng thái payout chuyển thành "completed"
   - Thời gian chuyển khoản được cập nhật
   - Ghi chú được thêm "(Duyệt tự động)"
5. Nếu không được duyệt tự động:
   - Payout giữ nguyên trạng thái "pending"
   - Cần admin duyệt thủ công

### 3. Các file đã được cập nhật

#### Backend
- `app/Http/Controllers/PayoutController.php`
  - Thêm method `autoApprovePayout()`
  - Cập nhật method `stats()` để thống kê duyệt tự động
- `app/Http/Controllers/SellerOrderController.php`
  - Cập nhật logic tạo payout để gọi duyệt tự động
- `app/Http/Controllers/OrderController.php`
  - Cập nhật logic tạo payout để gọi duyệt tự động

#### Frontend
- `frontend/pages/seller/orders/list-order.vue`
  - Thêm thông báo giải thích tính năng
  - Hiển thị biểu tượng robot cho payout được duyệt tự động
  - Cập nhật giao diện hiển thị thông tin

## Cấu hình

### Thay đổi tỷ lệ duyệt tự động
Để thay đổi tỷ lệ duyệt tự động, chỉnh sửa dòng code trong `PayoutController.php`:

```php
// Thay đổi từ 80 thành giá trị mong muốn (1-100)
$shouldAutoApprove = rand(1, 100) <= 80;
```

### Ví dụ:
- `rand(1, 100) <= 90` = 90% duyệt tự động, 10% thủ công
- `rand(1, 100) <= 50` = 50% duyệt tự động, 50% thủ công

## Thống kê

### API thống kê mới
Endpoint: `GET /api/payouts/stats`

Response bao gồm:
```json
{
  "auto_approved_payouts": 150,
  "manual_approved_payouts": 38,
  "auto_approved_amount": 15000000,
  "manual_approved_amount": 3800000,
  "auto_approval_rate": 79.79
}
```

## Lợi ích

### Cho Seller
- Nhận tiền nhanh hơn (80% trường hợp)
- Giảm thời gian chờ admin duyệt
- Tăng trải nghiệm người dùng

### Cho Admin
- Giảm tải công việc duyệt payout
- Vẫn duy trì kiểm soát với 20% payout thủ công
- Có thể điều chỉnh tỷ lệ theo nhu cầu

### Cho Hệ thống
- Tăng hiệu quả xử lý
- Giảm backlog payout pending
- Cải thiện performance

## Lưu ý

1. **Logging**: Tất cả hoạt động duyệt tự động đều được log để theo dõi
2. **An toàn**: 20% payout thủ công đảm bảo admin vẫn có thể kiểm soát
3. **Linh hoạt**: Tỷ lệ duyệt tự động có thể điều chỉnh dễ dàng
4. **Transparency**: Seller có thể thấy rõ payout nào được duyệt tự động

## Troubleshooting

### Payout không được duyệt tự động
- Kiểm tra log để xem lý do
- Đảm bảo payout có trạng thái "pending"
- Kiểm tra quyền truy cập database

### Thay đổi tỷ lệ không có hiệu lực
- Clear cache nếu có
- Restart server
- Kiểm tra syntax PHP

## Tương lai

Có thể mở rộng tính năng này bằng cách:
1. Thêm điều kiện phức tạp hơn cho việc duyệt tự động
2. Tích hợp với hệ thống AI để đánh giá rủi ro
3. Thêm notification cho admin khi có payout cần duyệt thủ công
4. Tạo dashboard thống kê chi tiết hơn
