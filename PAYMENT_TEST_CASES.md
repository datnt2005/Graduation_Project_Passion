# TEST CASES - HỆ THỐNG THANH TOÁN

## 1. TEST CASES - TRANG CHECKOUT

### 1.1 Hiển thị thông tin địa chỉ giao hàng

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC001 | Hiển thị địa chỉ mặc định | User đã đăng nhập và có địa chỉ mặc định | 1. Truy cập trang checkout<br>2. Chờ component SelectedAddress load | Hiển thị tên, số điện thoại, địa chỉ chi tiết với link "Thay đổi" | Đạt |
| TC002 | Hiển thị khi chưa có địa chỉ | User đã đăng nhập nhưng chưa có địa chỉ | 1. Truy cập trang checkout | Hiển thị thông báo "Bạn chưa có địa chỉ giao hàng" với link "Thêm địa chỉ" | Đạt |
| TC003 | Link thay đổi địa chỉ | User có địa chỉ mặc định | 1. Click vào link "Thay đổi" | Chuyển hướng đến trang /address | Đạt |
| TC004 | Link thêm địa chỉ | User chưa có địa chỉ | 1. Click vào link "Thêm địa chỉ" | Chuyển hướng đến trang /address | Đạt |

### 1.2 Hiển thị thông tin giỏ hàng

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC005 | Hiển thị sản phẩm từ nhiều shop | Giỏ hàng có sản phẩm từ 2+ shop | 1. Truy cập trang checkout | Hiển thị từng shop riêng biệt với danh sách sản phẩm | Đạt |
| TC006 | Hiển thị thông tin sản phẩm | Sản phẩm có đầy đủ thông tin | 1. Xem thông tin sản phẩm trong checkout | Hiển thị: hình ảnh, tên, thuộc tính, số lượng, giá | Đạt |
| TC007 | Tính tổng tiền từng shop | Shop có nhiều sản phẩm | 1. Xem tổng tiền của shop | Tổng = Σ(số lượng × giá sản phẩm) | Đạt |
| TC008 | Hiển thị khi giỏ hàng trống | Giỏ hàng không có sản phẩm | 1. Truy cập checkout với giỏ hàng trống | Hiển thị thông báo "Giỏ hàng trống" | Đạt |

### 1.3 Tính phí vận chuyển

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC009 | Tính phí vận chuyển tự động | Có địa chỉ giao hàng và địa chỉ shop | 1. Truy cập checkout<br>2. Chờ tính phí ship | Hiển thị phí vận chuyển cho từng shop | Đạt |
| TC010 | Cache phí vận chuyển | Đã tính phí ship trước đó | 1. Thay đổi địa chỉ<br>2. Quay lại địa chỉ cũ | Sử dụng cache, không gọi API lại | Đạt |
| TC011 | Lỗi tính phí vận chuyển | API GHN không khả dụng | 1. Truy cập checkout khi API lỗi | Hiển thị thông báo lỗi với nút "Thử lại" | Đạt |
| TC012 | Phí ship cho hàng nặng | Sản phẩm có cân nặng ≥ 2000g | 1. Thêm sản phẩm nặng vào giỏ | Sử dụng service_id 100039 cho hàng nặng | Đạt |
| TC013 | Phí ship cho hàng nhẹ | Sản phẩm có cân nặng < 2000g | 1. Thêm sản phẩm nhẹ vào giỏ | Sử dụng service_id 53321/53322 cho hàng nhẹ | Đạt |
| TC014 | Cân nặng tối thiểu | Sản phẩm có cân nặng < 50g | 1. Thêm sản phẩm rất nhẹ | Hiển thị cảnh báo "Cân nặng quá thấp" | Đạt |

### 1.4 Mã giảm giá

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC015 | Hiển thị popup mã giảm giá | User có voucher cho shop | 1. Click "+ Chọn mã giảm giá" | Hiển thị popup với danh sách voucher | Đạt |
| TC016 | Tìm kiếm mã giảm giá | Có nhiều voucher | 1. Nhập từ khóa tìm kiếm | Lọc voucher theo code hoặc description | Đạt |
| TC017 | Áp dụng mã giảm giá phần trăm | Voucher giảm 10% | 1. Chọn voucher phần trăm<br>2. Click áp dụng | Giảm giá = 10% × tổng tiền shop | Đạt |
| TC018 | Áp dụng mã giảm giá cố định | Voucher giảm 50,000đ | 1. Chọn voucher cố định<br>2. Click áp dụng | Giảm giá = 50,000đ (không vượt quá tổng tiền) | Đạt |
| TC019 | Kiểm tra điều kiện tối thiểu | Voucher yêu cầu đơn tối thiểu 100,000đ | 1. Áp dụng voucher cho đơn < 100,000đ | Hiển thị thông báo lỗi "Đơn hàng tối thiểu..." | Đạt |
| TC020 | Huỷ mã giảm giá | Đã áp dụng mã giảm giá | 1. Click nút "Huỷ" | Xóa giảm giá, cập nhật tổng tiền | Đạt |
| TC021 | Giảm giá phí ship | Voucher giảm phí vận chuyển | 1. Áp dụng voucher ship<br>2. Xem phí ship | Phí ship được giảm theo voucher | Đạt |

### 1.5 Phương thức thanh toán

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC022 | Hiển thị phương thức thanh toán | User đã đăng nhập | 1. Xem phần thanh toán | Hiển thị: COD, VNPAY, MOMO | Đạt |
| TC023 | Ẩn COD khi bị cấm | User có quá nhiều đơn từ chối | 1. Kiểm tra điều kiện COD | Không hiển thị tùy chọn COD | Đạt |
| TC024 | Chọn phương thức thanh toán | Chưa chọn phương thức | 1. Click chọn một phương thức | Radio button được check | Đạt |
| TC025 | Validation phương thức thanh toán | Chưa chọn phương thức | 1. Click "Đặt hàng" | Hiển thị lỗi "Vui lòng chọn phương thức thanh toán" | Đạt |

### 1.6 Ghi chú cho shop

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC026 | Nhập ghi chú cho shop | Có nhiều shop trong giỏ | 1. Nhập text vào textarea ghi chú | Text được lưu cho shop tương ứng | Đạt |
| TC027 | Ghi chú trống | Không nhập ghi chú | 1. Để trống ghi chú<br>2. Đặt hàng | Đơn hàng vẫn được tạo bình thường | Đạt |

## 2. TEST CASES - ĐẶT HÀNG

### 2.1 Validation đặt hàng

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC028 | Đặt hàng thành công COD | Đầy đủ thông tin hợp lệ | 1. Chọn COD<br>2. Click "Đặt hàng" | Chuyển đến trang order-success | Đạt |
| TC029 | Đặt hàng thành công VNPAY | Đầy đủ thông tin hợp lệ | 1. Chọn VNPAY<br>2. Click "Đặt hàng" | Chuyển đến trang thanh toán VNPAY | Đạt |
| TC030 | Đặt hàng thành công MOMO | Đầy đủ thông tin hợp lệ | 1. Chọn MOMO<br>2. Click "Đặt hàng" | Chuyển đến trang thanh toán MOMO | Đạt |
| TC031 | Validation giỏ hàng trống | Giỏ hàng không có sản phẩm | 1. Click "Đặt hàng" | Hiển thị lỗi "Giỏ hàng trống" | Đạt |
| TC032 | Validation chưa chọn phương thức | Chưa chọn phương thức thanh toán | 1. Click "Đặt hàng" | Hiển thị lỗi "Vui lòng chọn phương thức thanh toán" | Đạt |
| TC033 | Validation chưa đăng nhập | User chưa đăng nhập | 1. Click "Đặt hàng" | Hiển thị modal đăng nhập | Đạt |
| TC034 | Validation thiếu địa chỉ | Chưa có địa chỉ giao hàng | 1. Click "Đặt hàng" | Hiển thị lỗi "Vui lòng thêm địa chỉ" | Đạt |
| TC035 | Validation phí vận chuyển | Phí ship = 0 hoặc null | 1. Click "Đặt hàng" | Hiển thị lỗi "Không thể tính phí vận chuyển" | Đạt |

### 2.2 Xử lý lỗi đặt hàng

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC036 | Lỗi token hết hạn | Access token đã hết hạn | 1. Click "Đặt hàng" | Chuyển đến trang đăng nhập | Đạt |
| TC037 | Lỗi tài khoản bị khóa | User bị ban do từ chối nhiều đơn | 1. Click "Đặt hàng" | Hiển thị lỗi "Tài khoản bị khóa" | Đạt |
| TC038 | Lỗi API tạo đơn hàng | Server lỗi khi tạo đơn | 1. Click "Đặt hàng" | Hiển thị lỗi "Lỗi khi tạo đơn hàng" | Đạt |
| TC039 | Lỗi không đủ tồn kho | Sản phẩm hết hàng | 1. Click "Đặt hàng" | Hiển thị lỗi "Sản phẩm không đủ tồn kho" | Đạt |

### 2.3 Buy Now

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC040 | Đặt hàng Buy Now | Có dữ liệu buy_now trong localStorage | 1. Truy cập checkout?buyNow=true | Hiển thị sản phẩm từ buy_now | Đạt |
| TC041 | Buy Now hết hạn | Dữ liệu buy_now > 30 phút | 1. Truy cập checkout?buyNow=true | Hiển thị lỗi "Dữ liệu Buy Now đã hết hạn" | Đạt |
| TC042 | Buy Now không hợp lệ | Dữ liệu buy_now bị lỗi format | 1. Truy cập checkout?buyNow=true | Hiển thị lỗi "Dữ liệu Buy Now không hợp lệ" | Đạt |

## 3. TEST CASES - THANH TOÁN ONLINE

### 3.1 VNPAY

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC043 | Tạo thanh toán VNPAY thành công | Đơn hàng hợp lệ | 1. Chọn VNPAY<br>2. Đặt hàng | Chuyển đến trang thanh toán VNPAY | Đạt |
| TC044 | Thanh toán VNPAY thành công | Thanh toán thành công trên VNPAY | 1. Thanh toán xong<br>2. Quay về vnpay-return | Chuyển đến order-success | Đạt |
| TC045 | Thanh toán VNPAY thất bại | Thanh toán thất bại trên VNPAY | 1. Hủy thanh toán<br>2. Quay về vnpay-return | Hiển thị thông báo lỗi | Đạt |
| TC046 | Lỗi tạo thanh toán VNPAY | API VNPAY không khả dụng | 1. Chọn VNPAY<br>2. Đặt hàng | Hiển thị lỗi "Không thể tạo thanh toán VNPAY" | Đạt |

### 3.2 MOMO

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC047 | Tạo thanh toán MOMO thành công | Đơn hàng hợp lệ | 1. Chọn MOMO<br>2. Đặt hàng | Chuyển đến trang thanh toán MOMO | Đạt |
| TC048 | Thanh toán MOMO thành công | Thanh toán thành công trên MOMO | 1. Thanh toán xong<br>2. Quay về momo-return | Chuyển đến order-success | Đạt |
| TC049 | Thanh toán MOMO thất bại | Thanh toán thất bại trên MOMO | 1. Hủy thanh toán<br>2. Quay về momo-return | Hiển thị thông báo lỗi | Đạt |
| TC050 | Lỗi tạo thanh toán MOMO | API MOMO không khả dụng | 1. Chọn MOMO<br>2. Đặt hàng | Hiển thị lỗi "Không thể tạo thanh toán MOMO" | Đạt |

## 4. TEST CASES - TRANG THÀNH CÔNG

### 4.1 Hiển thị thông tin đơn hàng

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC051 | Hiển thị thông tin đơn hàng | Có order_ids trong URL | 1. Truy cập order-success?ids=1,2,3 | Hiển thị danh sách đơn hàng với thông tin chi tiết | Đạt |
| TC052 | Hiển thị khi không có order_ids | Không có order_ids | 1. Truy cập order-success | Hiển thị thông báo "Không tìm thấy đơn hàng" | Đạt |
| TC053 | In đơn hàng | Có đơn hàng hợp lệ | 1. Click nút "In đơn hàng" | Mở popup in với thông tin đơn hàng | Đạt |
| TC054 | Link quay về trang chủ | Đang ở trang order-success | 1. Click "Quay về trang chủ" | Chuyển đến trang chủ | Đạt |

## 5. TEST CASES - PERFORMANCE

### 5.1 Cache và tối ưu

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC055 | Cache phí vận chuyển | Đã tính phí ship trước đó | 1. Thay đổi địa chỉ<br>2. Quay lại địa chỉ cũ | Sử dụng cache, thời gian load < 100ms | Đạt |
| TC056 | Cache địa chỉ seller | Đã fetch địa chỉ seller trước đó | 1. Truy cập checkout lần 2 | Sử dụng cache địa chỉ seller | Đạt |
| TC057 | Cache services GHN | Đã fetch services trước đó | 1. Tính phí ship cho cùng route | Sử dụng cache services | Đạt |
| TC058 | Cooldown tính phí ship | Tính phí ship liên tục | 1. Thay đổi địa chỉ nhanh | Chỉ tính phí ship sau 1 giây | Đạt |

### 5.2 Loading states

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC059 | Loading tính phí vận chuyển | Đang tính phí ship | 1. Thay đổi địa chỉ | Hiển thị spinner "Đang tính..." | Đạt |
| TC060 | Loading đặt hàng | Đang xử lý đặt hàng | 1. Click "Đặt hàng" | Disable button, hiển thị loading | Đạt |
| TC061 | Loading fetch voucher | Đang lấy danh sách voucher | 1. Mở popup mã giảm giá | Hiển thị spinner trong popup | Đạt |

## 6. TEST CASES - ERROR HANDLING

### 6.1 Xử lý lỗi mạng

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC062 | Lỗi mạng khi tính phí ship | Mất kết nối internet | 1. Thay đổi địa chỉ | Hiển thị lỗi với nút "Thử lại" | Đạt |
| TC063 | Lỗi mạng khi đặt hàng | Mất kết nối khi đặt hàng | 1. Click "Đặt hàng" | Hiển thị lỗi "Lỗi kết nối" | Đạt |
| TC064 | Retry tính phí ship | Lỗi tính phí ship | 1. Click "Thử lại" | Tính lại phí vận chuyển | Đạt |

### 6.2 Validation dữ liệu

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC065 | Validation giá trị âm | Sản phẩm có giá âm | 1. Xem tổng tiền | Hiển thị lỗi "Giá sản phẩm không hợp lệ" | Đạt |
| TC066 | Validation số lượng | Số lượng sản phẩm = 0 | 1. Xem giỏ hàng | Không hiển thị sản phẩm có số lượng 0 | Đạt |
| TC067 | Validation địa chỉ không hợp lệ | Địa chỉ thiếu thông tin | 1. Đặt hàng | Hiển thị lỗi "Địa chỉ không hợp lệ" | Đạt |

## 7. TEST CASES - INTEGRATION

### 7.1 Tích hợp với Cart

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC068 | Xóa sản phẩm sau khi đặt hàng COD | Đặt hàng thành công với COD | 1. Đặt hàng COD thành công | Sản phẩm bị xóa khỏi giỏ hàng | Đạt |
| TC069 | Giữ sản phẩm sau khi đặt hàng online | Đặt hàng với VNPAY/MOMO | 1. Đặt hàng online thành công | Sản phẩm vẫn trong giỏ hàng | Đạt |
| TC070 | Cập nhật giỏ hàng real-time | Thay đổi giỏ hàng ở tab khác | 1. Thay đổi giỏ hàng<br>2. Quay lại checkout | Giỏ hàng được cập nhật | Đạt |

### 7.2 Tích hợp với User

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC071 | Kiểm tra quyền COD | User có lịch sử từ chối đơn | 1. Truy cập checkout | COD bị ẩn nếu có quá nhiều đơn từ chối | Đạt |
| TC072 | Lưu địa chỉ mặc định | User có nhiều địa chỉ | 1. Chọn địa chỉ khác làm mặc định | Địa chỉ được cập nhật làm mặc định | Đạt |
| TC073 | Lưu lịch sử đơn hàng | Đặt hàng thành công | 1. Đặt hàng xong | Đơn hàng xuất hiện trong lịch sử | Đạt |

## 8. TEST CASES - SECURITY

### 8.1 Bảo mật

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC074 | Validation token | Token không hợp lệ | 1. Sử dụng token giả | Chuyển đến trang đăng nhập | Đạt |
| TC075 | CSRF protection | Request không có CSRF token | 1. Gửi request POST không có token | Trả về lỗi 419 | Đạt |
| TC076 | Rate limiting | Gửi quá nhiều request | 1. Spam API đặt hàng | Trả về lỗi 429 | Đạt |
| TC077 | Validation input | Input chứa script | 1. Nhập script vào ghi chú | Script bị escape | Đạt |

## 9. TEST CASES - MOBILE RESPONSIVE

### 9.1 Responsive design

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC078 | Hiển thị trên mobile | Màn hình < 768px | 1. Mở checkout trên mobile | Layout responsive, dễ sử dụng | Đạt |
| TC079 | Popup mã giảm giá mobile | Mở popup trên mobile | 1. Click chọn mã giảm giá | Popup hiển thị đúng trên mobile | Đạt |
| TC080 | Touch friendly | Các button trên mobile | 1. Click các button | Button có kích thước phù hợp cho touch | Đạt |

## 10. TEST CASES - ACCESSIBILITY

### 10.1 Accessibility

| ID | Test Case | Điều kiện đầu vào | Các bước thực hiện | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|---|
| TC081 | Keyboard navigation | Sử dụng keyboard | 1. Tab qua các element | Có thể navigate bằng keyboard | Đạt |
| TC082 | Screen reader | Sử dụng screen reader | 1. Mở checkout với screen reader | Có alt text và aria labels | Đạt |
| TC083 | Color contrast | Kiểm tra contrast | 1. Xem text trên background | Contrast ratio >= 4.5:1 | Đạt |

---

## TỔNG KẾT

- **Tổng số test cases**: 83
- **Test cases đạt**: 83
- **Test cases lỗi**: 0
- **Tỷ lệ đạt**: 100%

### Phân loại theo module:
- Checkout: 27 test cases
- Đặt hàng: 12 test cases  
- Thanh toán online: 8 test cases
- Trang thành công: 4 test cases
- Performance: 7 test cases
- Error handling: 6 test cases
- Integration: 6 test cases
- Security: 4 test cases
- Mobile responsive: 3 test cases
- Accessibility: 3 test cases

### Các test case quan trọng nhất:
1. TC001-TC004: Hiển thị và xử lý địa chỉ giao hàng
2. TC009-TC014: Tính phí vận chuyển và cache
3. TC015-TC021: Mã giảm giá và validation
4. TC028-TC035: Validation đặt hàng
5. TC043-TC050: Thanh toán online VNPAY/MOMO
6. TC055-TC058: Performance và cache
7. TC074-TC077: Security validation 