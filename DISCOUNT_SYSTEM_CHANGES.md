# Thay đổi hệ thống Discount - Hỗ trợ nhiều mã giảm giá

## Tổng quan
Đã cập nhật hệ thống để hỗ trợ lưu trữ nhiều mã giảm giá trong trường `discount_id` dưới dạng JSON, bao gồm cả shipping discount và product discount.

## Các thay đổi chính

### 1. Model Order (backend/app/Models/Order.php)
- **Thêm cast**: `'discount_id' => 'array'` để lưu trữ JSON
- **Thêm phương thức mới**:
  - `discounts()`: Lấy tất cả discount
  - `getShippingDiscount()`: Lấy shipping discount
  - `getProductDiscount()`: Lấy product discount
  - `applyMultipleDiscounts()`: Áp dụng nhiều discount
- **Giữ tương thích ngược**: Relationship `discount()` vẫn hoạt động

### 2. Model Shipping (backend/app/Models/Shipping.php)
- **Thêm cột**: `shipping_discount` vào fillable và casts
- **Thêm phương thức**:
  - `getFinalShippingFee()`: Tính phí vận chuyển cuối cùng
  - `applyShippingDiscount()`: Áp dụng shipping discount
  - `getShippingDiscountInfo()`: Lấy thông tin discount từ order

### 3. OrderController (backend/app/Http/Controllers/OrderController.php)
- **Cập nhật logic xử lý discount**:
  - Hỗ trợ lưu trữ nhiều discount ID dưới dạng JSON
  - Tách biệt xử lý product discount và shipping discount
  - Lưu discount IDs vào `discount_id` dưới dạng `{'product': id, 'shipping': id}`

### 4. GHNController (backend/app/Http/Controllers/GHNController.php)
- **Thêm phương thức**: `calculateFeeWithDiscount()`
- **Tính năng**:
  - Tính phí vận chuyển gốc từ GHN
  - Áp dụng shipping discount
  - Trả về phí vận chuyển cuối cùng sau discount

### 5. DiscountController (backend/app/Http/Controllers/DiscountController.php)
- **Thêm phương thức**: `checkMultipleVouchers()`
- **Tính năng**:
  - Kiểm tra nhiều mã giảm giá cùng lúc
  - Phân loại product discount và shipping discount
  - Tính toán tổng discount cho từng loại

### 6. Routes
- **Thêm route**: `POST /api/discounts/check-multiple`
- **Thêm route**: `POST /api/ghn/shipping-fee-with-discount`

### 7. Migration
- **Tạo file**: `add_shipping_discount_to_shipping_table.php`
- **Thêm cột**: `shipping_discount` vào bảng `shipping`

## Cấu trúc dữ liệu mới

### Trường discount_id trong bảng orders
```json
{
  "product": 123,    // ID của product discount
  "shipping": 456    // ID của shipping discount
}
```

### Trường shipping_discount trong bảng shipping
- Kiểu: `decimal(10,2)`
- Mặc định: `0`
- Mô tả: Số tiền được giảm từ phí vận chuyển

## API Endpoints mới

### 1. Kiểm tra nhiều mã giảm giá
```
POST /api/discounts/check-multiple
```
**Request:**
```json
{
  "discount_ids": [1, 2],
  "user_id": 123,
  "total_amount": 1000000,
  "shipping_fee": 30000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "product_discount": {
      "id": 1,
      "code": "PRODUCT10",
      "discount_type": "percentage",
      "discount_value": 10,
      "discount_amount": 100000
    },
    "shipping_discount": {
      "id": 2,
      "code": "SHIP50",
      "discount_type": "fixed",
      "discount_value": 15000,
      "discount_amount": 15000
    },
    "total_product_discount": 100000,
    "total_shipping_discount": 15000,
    "total_discount": 115000
  }
}
```

### 2. Tính phí vận chuyển với discount
```
POST /api/ghn/shipping-fee-with-discount
```
**Request:**
```json
{
  "seller_id": 1,
  "from_district_id": 1454,
  "from_ward_code": "20109",
  "to_district_id": 1454,
  "to_ward_code": "20109",
  "service_id": 53321,
  "weight": 1000,
  "height": 25,
  "length": 25,
  "width": 25,
  "shipping_discount_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "original_fee": 30000,
    "discount_amount": 15000,
    "final_fee": 15000,
    "shipping_discount": {
      "id": 2,
      "code": "SHIP50",
      "discount_type": "fixed",
      "discount_value": 15000
    }
  }
}
```

## Cách sử dụng

### 1. Tạo đơn hàng với nhiều mã giảm giá
```php
// Trong OrderController::store()
$discountIds = [1, 2]; // 1: product discount, 2: shipping discount
$order->applyMultipleDiscounts($discountIds);
```

### 2. Lấy thông tin discount từ order
```php
$order = Order::find(1);

// Lấy shipping discount
$shippingDiscount = $order->getShippingDiscount();

// Lấy product discount
$productDiscount = $order->getProductDiscount();

// Lấy tất cả discount
$allDiscounts = $order->discounts();
```

### 3. Tính phí vận chuyển với discount
```php
$shipping = $order->shipping;
$finalFee = $shipping->getFinalShippingFee();
```

## Lưu ý
- Hệ thống vẫn tương thích ngược với cấu trúc cũ
- Có thể áp dụng cả product discount và shipping discount cùng lúc
- Shipping discount chỉ áp dụng cho phí vận chuyển
- Product discount áp dụng cho giá trị sản phẩm
