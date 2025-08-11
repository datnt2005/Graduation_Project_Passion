# Thay đổi Frontend cho hệ thống Discount mới

## Tổng quan
Đã cập nhật frontend để hỗ trợ hệ thống discount mới với khả năng lưu trữ nhiều mã giảm giá (product + shipping) trong trường `discount_id` dưới dạng JSON.

## Các thay đổi chính

### 1. useDiscount.ts
- **Thêm interface**: `DiscountCheckResponse` để định nghĩa response từ API kiểm tra nhiều discount
- **Thêm phương thức**: `checkMultipleDiscounts()` để kiểm tra nhiều mã giảm giá cùng lúc
- **Tính năng**:
  - Kiểm tra tính hợp lệ của nhiều discount
  - Phân loại product discount và shipping discount
  - Trả về thông tin chi tiết về từng loại discount

### 2. useCheckout.js
- **Cập nhật placeOrder()**: Đã hỗ trợ gửi `discount_ids` array thay vì single discount_id
- **Tính năng**:
  - Gửi danh sách discount IDs đến backend
  - Backend sẽ xử lý việc lưu trữ dưới dạng JSON
  - Hỗ trợ cả product discount và shipping discount

### 3. checkout.vue
- **Cập nhật applyManualDiscount()**: Sử dụng `checkMultipleDiscounts()` để kiểm tra tính hợp lệ
- **Cập nhật selectCardPromotion()**: Tương tự như applyManualDiscount
- **Tính năng**:
  - Kiểm tra xem có thể áp dụng discount mới cùng lúc với discount hiện tại không
  - Xử lý riêng biệt cho từng loại discount (product/shipping)
  - Hiển thị thông báo phù hợp cho từng loại

### 4. ShippingSelector.vue
- **Đã có sẵn**: Logic xử lý shipping discount
- **Tính năng**:
  - Hiển thị shipping discount riêng biệt
  - Cho phép hủy shipping discount
  - Tính toán phí vận chuyển cuối cùng sau discount

## API Endpoints được sử dụng

### 1. Kiểm tra nhiều mã giảm giá
```typescript
POST /api/discounts/check-multiple
{
  discount_ids: [1, 2],
  user_id: 123,
  total_amount: 1000000,
  shipping_fee: 30000
}
```

### 2. Tạo đơn hàng với nhiều discount
```typescript
POST /api/orders
{
  discount_ids: [1, 2], // Array thay vì single discount_id
  // ... other fields
}
```

## Luồng xử lý mới

### 1. Khi người dùng áp dụng mã giảm giá
1. **Kiểm tra tính hợp lệ**: Gọi `checkMultipleDiscounts()` với tất cả discount hiện tại + discount mới
2. **Phân loại discount**: Backend trả về thông tin chi tiết về product discount và shipping discount
3. **Áp dụng discount**: 
   - Product discount: Chia đều cho các shop
   - Shipping discount: Được xử lý tự động bởi backend
4. **Cập nhật UI**: Hiển thị thông tin discount tương ứng

### 2. Khi tạo đơn hàng
1. **Thu thập discount IDs**: Lấy tất cả discount đã được áp dụng
2. **Gửi đến backend**: Backend sẽ lưu dưới dạng JSON format
3. **Xử lý thanh toán**: Tiếp tục với luồng thanh toán bình thường

## Cấu trúc dữ liệu mới

### Frontend gửi đến Backend
```javascript
{
  discount_ids: [1, 2], // Array thay vì single discount_id
  // ... other fields
}
```

### Backend lưu trữ
```json
{
  "discount_id": {
    "product": 1,    // ID của product discount
    "shipping": 2    // ID của shipping discount
  }
}
```

## Tính năng mới

### 1. Kiểm tra tính hợp lệ nhiều discount
- Kiểm tra xem có thể áp dụng discount mới cùng lúc với discount hiện tại không
- Tránh xung đột giữa các loại discount
- Đảm bảo không vượt quá giới hạn sử dụng

### 2. Phân loại discount
- **Product discount**: Giảm giá cho sản phẩm, chia đều cho các shop
- **Shipping discount**: Giảm giá cho phí vận chuyển, được xử lý tự động

### 3. UI cải tiến
- Hiển thị riêng biệt product discount và shipping discount
- Thông báo rõ ràng về loại discount được áp dụng
- Cho phép hủy từng loại discount riêng biệt

## Lưu ý khi triển khai

### 1. Migration
- Chạy migration để thay đổi cột `discount_id` thành JSON
- Chạy migration để thêm cột `shipping_discount` vào bảng shipping

### 2. Backward Compatibility
- Hệ thống vẫn tương thích với cấu trúc cũ
- Có thể xử lý cả single discount_id và JSON discount_id

### 3. Testing
- Test việc áp dụng nhiều discount cùng lúc
- Test việc hủy từng loại discount riêng biệt
- Test việc tạo đơn hàng với nhiều discount

## Cách sử dụng

### 1. Áp dụng mã giảm giá
```javascript
// Kiểm tra tính hợp lệ
const checkResult = await checkMultipleDiscounts(discountIds, userId, totalAmount, shippingFee);

if (checkResult.success) {
  // Áp dụng discount
  await applyDiscount(discount);
  
  // Xử lý theo loại
  if (discount.discount_type === 'shipping_fee') {
    // Shipping discount được xử lý tự động
  } else {
    // Product discount - chia đều cho các shop
  }
}
```

### 2. Tạo đơn hàng
```javascript
const orderData = {
  discount_ids: selectedDiscounts.value.map(d => d.id),
  // ... other fields
};
```

### 3. Hiển thị thông tin discount
```vue
<!-- Product discount -->
<div v-if="shop.discount > 0">
  Giảm giá sản phẩm: -{{ formatPrice(shop.discount) }} đ
</div>

<!-- Shipping discount -->
<div v-if="shop.shipping_discount > 0">
  Giảm giá phí ship: -{{ formatPrice(shop.shipping_discount) }} đ
</div>
```
