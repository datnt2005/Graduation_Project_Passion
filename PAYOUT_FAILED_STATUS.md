# Cập nhật Status Payout từ 'refunded' sang 'failed'

## Tổng quan
Đã cập nhật logic xử lý payout khi đơn hàng bị hoàn tiền/hủy từ status 'refunded' sang 'failed' để sử dụng status có sẵn trong hệ thống.

## Thay đổi Backend

### 1. PayoutController.php
- **Method `handleOrderRefund`**: 
  - Thay đổi từ `status = 'refunded'` thành `status = 'failed'`
  - Cập nhật logic tìm kiếm payout: bỏ điều kiện `where('status', 'completed')` để tìm tất cả payout bất kể trạng thái
  - Cải thiện ghi log với thông tin `old_status` và `new_status`

- **Method `approvedList`**:
  - Cập nhật query để hiển thị cả payouts có status 'failed': `whereIn('status', ['completed', 'failed'])`

### 2. WithdrawRequestController.php
- **Method `getAvailableBalance`**:
  - Đổi tên biến từ `$refundedPayouts` thành `$failedPayouts`
  - Cập nhật logic tính toán: `where('status', 'failed')`
  - Cập nhật response JSON: `'failed_payouts'` thay vì `'refunded_payouts'`

- **Method `store`**:
  - Tương tự, cập nhật logic tính toán số dư khả dụng với `$failedPayouts`

## Thay đổi Frontend

### 1. Seller Page (list-order.vue)
- **Thêm thông báo cho payout thất bại**:
  - Thêm alert box hiển thị khi có payout với status 'failed'
  - Thêm computed property `hasFailedPayouts`
  - Thêm thông báo "Đã hoàn lại cho khách hàng" cho từng payout có status 'failed'

- **Cập nhật status labels**:
  - `payoutStatusLabel`: thêm case cho 'failed' → 'Thất bại'
  - `payoutStatusClass`: thêm style cho 'failed' → 'text-red-600 font-bold'

### 2. Admin Page (list-order.vue)
- **Cập nhật status labels**:
  - `payoutStatusLabel`: loại bỏ case 'refunded', giữ lại 'failed'
  - `payoutStatusClass`: loại bỏ case 'refunded', giữ lại 'failed'
  - `payoutStatusText`: loại bỏ case 'refunded', giữ lại 'failed'

## Lợi ích của thay đổi

1. **Sử dụng status có sẵn**: Không cần tạo status mới 'refunded', tận dụng status 'failed' đã có
2. **Tính nhất quán**: Status 'failed' đã được sử dụng trong hệ thống cho các trường hợp thất bại
3. **Đơn giản hóa**: Giảm số lượng status cần quản lý
4. **Tương thích**: Không cần thay đổi database schema

## Cách hoạt động

1. **Khi đơn hàng bị hoàn tiền/hủy**:
   - Hệ thống tìm payout liên quan (bất kể trạng thái)
   - Cập nhật status thành 'failed'
   - Thêm ghi chú với lý do và timestamp

2. **Hiển thị cho seller**:
   - Payout với status 'failed' được hiển thị trong danh sách
   - Có thông báo đặc biệt "Đã hoàn lại cho khách hàng"
   - Số tiền này bị trừ khỏi số dư khả dụng

3. **Tính toán số dư rút tiền**:
   - Số dư = Tổng payout completed - Tổng payout failed - Tổng rút tiền đã hoàn thành - Tổng rút tiền đang chờ

## Lưu ý quan trọng

- Status 'failed' giờ đây có 2 ý nghĩa:
  1. Payout thất bại do lỗi kỹ thuật
  2. Payout bị hủy do đơn hàng hoàn tiền
- Cần kiểm tra ghi chú (note) để phân biệt 2 trường hợp này
- Không ảnh hưởng đến dữ liệu hiện có vì chỉ thay đổi logic xử lý mới
