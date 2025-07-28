<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-7xl mx-auto px-4 py-12">
      <!-- Header Section -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl mb-6 shadow-xl">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h1 class="text-5xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-4">
          Các Câu Hỏi Thường Gặp
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Dưới đây là những câu hỏi thường gặp từ khách hàng khi sử dụng Passion. 
          Nếu bạn không tìm thấy câu trả lời, vui lòng liên hệ qua 
          <span class="font-semibold text-blue-600">hotline 1900 1234</span>.
        </p>
      </div>

      <!-- Table of Contents -->
      <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 mb-12 hover:shadow-2xl transition-all duration-300">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-800">Mục lục</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <a href="#account" class="group flex items-center gap-3 p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 transition-all duration-300 border border-blue-100 hover:border-blue-200">
            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <span class="font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">Tài khoản & Đăng nhập</span>
          </a>
          
          <a href="#order" class="group flex items-center gap-3 p-4 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 transition-all duration-300 border border-green-100 hover:border-green-200">
            <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
              </svg>
            </div>
            <span class="font-semibold text-gray-700 group-hover:text-green-600 transition-colors">Đặt hàng & Thanh toán</span>
          </a>
          
          <a href="#delivery" class="group flex items-center gap-3 p-4 rounded-2xl bg-gradient-to-r from-purple-50 to-pink-50 hover:from-purple-100 hover:to-pink-100 transition-all duration-300 border border-purple-100 hover:border-purple-200">
            <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <span class="font-semibold text-gray-700 group-hover:text-purple-600 transition-colors">Vận chuyển & Giao hàng</span>
          </a>
          
          <a href="#return" class="group flex items-center gap-3 p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 hover:from-amber-100 hover:to-orange-100 transition-all duration-300 border border-amber-100 hover:border-amber-200">
            <div class="w-8 h-8 bg-gradient-to-r from-amber-500 to-orange-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </div>
            <span class="font-semibold text-gray-700 group-hover:text-amber-600 transition-colors">Đổi trả & Hoàn tiền</span>
          </a>
          
          <a href="#support" class="group flex items-center gap-3 p-4 rounded-2xl bg-gradient-to-r from-rose-50 to-red-50 hover:from-rose-100 hover:to-red-100 transition-all duration-300 border border-rose-100 hover:border-rose-200 md:col-span-2 lg:col-span-1">
            <div class="w-8 h-8 bg-gradient-to-r from-rose-500 to-red-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <span class="font-semibold text-gray-700 group-hover:text-rose-600 transition-colors">Hỗ trợ Khách hàng</span>
          </a>
        </div>
      </div>

      <!-- FAQ Sections -->
      <div class="space-y-12">
        <!-- Account Section -->
        <section id="account" class="scroll-mt-20">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Tài khoản & Đăng nhập</h2>
          </div>
          
          <div class="space-y-4">
            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('account-1')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-blue-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                  Làm thế nào để đăng ký tài khoản?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('account-1') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('account-1')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-blue-100"
              >
                Bạn có thể đăng ký tài khoản trên Passion bằng nhiều cách khác nhau:
<br><br>
<strong>Cách 1: Đăng ký bằng Email/SĐT</strong><br>
• Nhấp vào nút "Đăng ký" trên góc phải trang chủ<br>
• Nhập email hoặc số điện thoại hợp lệ<br>
• Tạo mật khẩu mạnh (ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số)<br>
• Xác nhận mật khẩu và đồng ý với điều khoản sử dụng<br>
• Kiểm tra email/SMS để xác thực tài khoản<br><br>
<strong>Cách 2: Đăng ký nhanh qua mạng xã hội</strong><br>
• Chọn Đăng nhập bằng Google<br>
• Cho phép Passion truy cập thông tin cơ bản<br>
• Hoàn tất thông tin cá nhân còn thiếu<br><br>
<em>Lưu ý: Sau khi đăng ký thành công, bạn sẽ nhận được 50.000đ voucher chào mừng và miễn phí vận chuyển cho đơn hàng đầu tiên!</em>
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('account-2')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-blue-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                  Tôi quên mật khẩu, làm sao để khôi phục?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('account-2') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('account-2')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-blue-100"
              >
                Đừng lo lắng! Passion cung cấp nhiều cách để bạn khôi phục mật khẩu một cách an toàn:
<br><br>
<strong>Bước 1: Truy cập trang khôi phục</strong><br>
• Vào trang đăng nhập và nhấp "Quên mật khẩu?"<br>
• Hoặc truy cập trực tiếp: passion.vn/forgot-password<br><br>
<strong>Bước 2: Xác thực danh tính</strong><br>
• Nhập email hoặc số điện thoại đã đăng ký<br>
• Chọn phương thức nhận mã xác thực (Email/SMS)<br>
• Nhập mã OTP được gửi đến (có hiệu lực trong 5 phút)<br><br>
<strong>Bước 3: Tạo mật khẩu mới</strong><br>
• Tạo mật khẩu mới đáp ứng yêu cầu bảo mật<br>
• Xác nhận mật khẩu và lưu thay đổi<br>
• Đăng nhập lại với mật khẩu mới<br><br>
<em>Mẹo bảo mật: Sử dụng mật khẩu duy nhất, kết hợp chữ, số và ký tự đặc biệt. Tránh sử dụng thông tin cá nhân dễ đoán.</em>
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('account-3')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-blue-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                  Làm sao để cập nhật thông tin tài khoản?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('account-3') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('account-3')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-blue-100"
              >
                Vào "Tài khoản của tôi" trên trang cá nhân, chọn "Chỉnh sửa thông tin" để cập nhật email, số điện thoại, hoặc địa chỉ giao hàng.
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('account-4')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-blue-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                  Làm sao để liên kết tài khoản với mạng xã hội?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('account-4') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('account-4')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-blue-100"
              >
                Trong mục "Cài đặt tài khoản", chọn "Liên kết mạng xã hội" và đăng nhập bằng Facebook hoặc Google để đồng bộ hóa.
              </div>
            </div>
          </div>
        </section>

        <!-- Order Section -->
        <section id="order" class="scroll-mt-20">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Đặt hàng & Thanh toán</h2>
          </div>
          
          <div class="space-y-4">
            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('order-1')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-green-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600 transition-colors">
                  Làm thế nào để đặt hàng?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('order-1') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('order-1')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-green-50 to-emerald-50 border-t border-green-100"
              >
                Đặt hàng trên Passion rất đơn giản với quy trình 5 bước:
<br><br>
<strong>Bước 1: Tìm kiếm sản phẩm</strong><br>
• Sử dụng thanh tìm kiếm hoặc duyệt theo danh mục<br>
• Lọc sản phẩm theo giá, thương hiệu, đánh giá<br>
• Đọc mô tả chi tiết và xem hình ảnh sản phẩm<br><br>
<strong>Bước 2: Thêm vào giỏ hàng</strong><br>
• Chọn màu sắc, kích thước (nếu có)<br>
• Chọn số lượng mong muốn<br>
• Nhấp "Thêm vào giỏ hàng" hoặc "Mua ngay"<br><br>
<strong>Bước 3: Kiểm tra giỏ hàng</strong><br>
• Xem lại các sản phẩm đã chọn<br>
• Áp dụng mã giảm giá (nếu có)<br>
• Kiểm tra tổng tiền và phí vận chuyển<br><br>
<strong>Bước 4: Nhập thông tin giao hàng</strong><br>
• Chọn địa chỉ giao hàng có sẵn hoặc thêm địa chỉ mới<br>
• Chọn thời gian giao hàng phù hợp<br>
• Để lại ghi chú cho shipper (nếu cần)<br><br>
<strong>Bước 5: Thanh toán và xác nhận</strong><br>
• Chọn phương thức thanh toán<br>
• Kiểm tra lại toàn bộ đơn hàng<br>
• Nhấp "Đặt hàng" để hoàn tất<br><br>
<em>Sau khi đặt hàng thành công, bạn sẽ nhận được email xác nhận và có thể theo dõi đơn hàng trong mục "Đơn hàng của tôi".</em>
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('order-2')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-green-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600 transition-colors">
                  Passion hỗ trợ thanh toán bằng cách nào?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('order-2') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('order-2')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-green-50 to-emerald-50 border-t border-green-100"
              >
                Chúng tôi hỗ trợ thanh toán bằng thẻ tín dụng, chuyển khoản, ví điện tử, và tiền mặt khi giao hàng (COD).
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('order-3')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-green-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600 transition-colors">
                  Làm sao để kiểm tra đơn hàng đã đặt?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('order-3') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('order-3')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-green-50 to-emerald-50 border-t border-green-100"
              >
                Vào "Đơn hàng của tôi" trên tài khoản, bạn có thể xem trạng thái và chi tiết từng đơn hàng.
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('order-4')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-green-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600 transition-colors">
                  Tôi có thể hủy đơn hàng không?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('order-4') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('order-4')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-green-50 to-emerald-50 border-t border-green-100"
              >
                Bạn có thể hủy đơn hàng trước khi giao hàng trong vòng 2 giờ kể từ khi đặt, qua mục "Quản lý đơn hàng" hoặc liên hệ hotline.
              </div>
            </div>
          </div>
        </section>

        <!-- Delivery Section -->
        <section id="delivery" class="scroll-mt-20">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Vận chuyển & Giao hàng</h2>
          </div>
          
          <div class="space-y-4">
            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('delivery-1')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-purple-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">
                  Thời gian giao hàng là bao lâu?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('delivery-1') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('delivery-1')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-purple-50 to-pink-50 border-t border-purple-100"
              >
                Thời gian giao hàng tại Passion được tối ưu để mang đến trải nghiệm tốt nhất:
<br><br>
<strong>🚀 Giao hàng nhanh (Fast Delivery)</strong><br>
• Nội thành Hà Nội, TP.HCM: 2-4 giờ<br>
• Các quận lân cận: 4-8 giờ<br>
• Phí: 25.000đ - 35.000đ<br><br>
<strong>📦 Giao hàng tiêu chuẩn (Standard)</strong><br>
• Nội thành các tỉnh thành: 1-2 ngày<br>
• Các tỉnh khác: 2-3 ngày<br>
• Vùng sâu, vùng xa: 3-5 ngày<br>
• Phí: 15.000đ - 30.000đ<br><br>
<strong>🎁 Giao hàng miễn phí</strong><br>
• Đơn hàng từ 299.000đ trở lên<br>
• Thành viên VIP: miễn phí với đơn từ 199.000đ<br>
• Sản phẩm có nhãn "Freeship"<br><br>
<strong>⏰ Khung giờ giao hàng</strong><br>
• Sáng: 8:00 - 12:00<br>
• Chiều: 13:30 - 18:00<br>
<strong>⏰ Khung giờ giao hàng</strong><br>
• Sáng: 8:00 - 12:00<br>
• Chiều: 13:30 - 18:00<br>
• Tối: 18:00 - 21:00 (chỉ khu vực nội thành)<br><br>
<em>Lưu ý: Thời gian có thể thay đổi trong các ngày lễ, Tết hoặc do thời tiết bất thường. Chúng tôi sẽ thông báo trước nếu có sự thay đổi.</em>
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('delivery-2')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-purple-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">
                  Làm sao để theo dõi đơn hàng?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('delivery-2') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('delivery-2')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-purple-50 to-pink-50 border-t border-purple-100"
              >
                Vào "Theo dõi đơn hàng" trên trang cá nhân, nhập mã vận đơn để xem trạng thái giao hàng.
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('delivery-3')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-purple-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">
                  Tôi có thể đổi địa chỉ giao hàng không?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('delivery-3') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('delivery-3')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-purple-50 to-pink-50 border-t border-purple-100"
              >
                Bạn có thể thay đổi địa chỉ trước khi đơn hàng được giao, liên hệ hotline hoặc chỉnh sửa trong "Quản lý đơn hàng".
              </div>
            </div>
          </div>
        </section>

        <!-- Return Section -->
        <section id="return" class="scroll-mt-20">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Đổi trả & Hoàn tiền</h2>
          </div>
          
          <div class="space-y-4">
            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('return-1')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-amber-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-amber-600 transition-colors">
                  Chính sách đổi trả của Passion là gì?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('return-1') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('return-1')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-amber-50 to-orange-50 border-t border-amber-100"
              >
                Passion cam kết chính sách đổi trả linh hoạt và thuận tiện cho khách hàng:
<br><br>
<strong>📋 Điều kiện đổi trả</strong><br>
• Thời gian: Trong vòng 7 ngày kể từ khi nhận hàng<br>
• Tình trạng: Sản phẩm còn nguyên vẹn, chưa qua sử dụng<br>
• Bao bì: Giữ nguyên tem mác, nhãn hiệu, hộp đựng<br>
• Phụ kiện: Đầy đủ phụ kiện, sách hướng dẫn đi kèm<br><br>
<strong>✅ Các trường hợp được đổi trả</strong><br>
• Sản phẩm bị lỗi từ nhà sản xuất<br>
• Giao sai sản phẩm so với đơn hàng<br>
• Sản phẩm bị hư hỏn trong quá trình vận chuyển<br>
• Không hài lòng với chất lượng sản phẩm<br>
• Kích thước/màu sắc không phù hợp<br><br>
<strong>❌ Các trường hợp không được đổi trả</strong><br>
• Sản phẩm đã qua sử dụng hoặc có dấu hiệu sử dụng<br>
• Sản phẩm bị hư hỏng do lỗi của khách hàng<br>
• Sản phẩm thuộc danh mục không đổi trả (mỹ phẩm đã mở, đồ lót...)<br>
• Quá thời hạn 7 ngày<br><br>
<strong>💰 Chính sách hoàn tiền</strong><br>
• Hoàn tiền 100% nếu lỗi từ shop<br>
• Khách hàng chịu phí ship hoàn trả nếu đổi ý<br>
• Hoàn tiền qua phương thức thanh toán ban đầu<br><br>
<em>Đặc biệt: Thành viên VIP được miễn phí ship đổi trả và ưu tiên xử lý trong 24h!</em>
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('return-2')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-amber-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-amber-600 transition-colors">
                  Làm sao để yêu cầu đổi trả?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('return-2') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('return-2')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-amber-50 to-orange-50 border-t border-amber-100"
              >
                Vào "Đơn hàng của tôi", chọn đơn hàng cần đổi trả, và làm theo hướng dẫn hoặc liên hệ hotline.
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('return-3')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-amber-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-amber-600 transition-colors">
                  Thời gian hoàn tiền là bao lâu?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('return-3') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('return-3')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-amber-50 to-orange-50 border-t border-amber-100"
              >
                Thời gian hoàn tiền thường từ 3-5 ngày làm việc, tùy phương thức thanh toán ban đầu.
              </div>
            </div>
          </div>
        </section>

        <!-- Support Section -->
        <section id="support" class="scroll-mt-20">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-gradient-to-r from-rose-500 to-red-500 rounded-2xl flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Hỗ trợ Khách hàng</h2>
          </div>
          
          <div class="space-y-4">
            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('support-1')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-rose-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-rose-600 transition-colors">
                  Làm sao để liên hệ hỗ trợ?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('support-1') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('support-1')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-rose-50 to-red-50 border-t border-rose-100"
              >
                Passion cung cấp đa dạng kênh hỗ trợ khách hàng 24/7:
<br><br>
<strong>📞 Hotline hỗ trợ</strong><br>
• Số điện thoại: 1900 1234 (miễn phí)<br>
• Thời gian: 24/7 tất cả các ngày trong tuần<br>
• Ngôn ngữ: Tiếng Việt và Tiếng Anh<br>
• Thời gian chờ trung bình: < 30 giây<br><br>
<strong>💬 Chat trực tuyến</strong><br>
• Truy cập passion.vn và nhấp vào icon chat<br>
• Hỗ trợ tức thời từ 6:00 - 24:00<br>
• AI Chatbot hỗ trợ ngoài giờ hành chính<br>
• Có thể gửi hình ảnh, video minh họa<br><br>
<strong>📧 Email hỗ trợ</strong><br>
• Email chung: hotro@passion.vn<br>
• Khiếu nại: complaint@passion.vn<br>
• Hợp tác: partnership@passion.vn<br>
• Thời gian phản hồi: < 2 giờ trong giờ hành chính<br><br>
<strong>📱 Ứng dụng di động</strong><br>
• Tính năng "Hỗ trợ" trong app Passion<br>
• Gọi video trực tiếp với chuyên viên<br>
• Gửi ticket hỗ trợ kèm hình ảnh<br><br>
<strong>🏢 Trung tâm hỗ trợ</strong><br>
• Hà Nội: 123 Phố Huế, Hai Bà Trưng<br>
• TP.HCM: 456 Nguyễn Huệ, Quận 1<br>
• Đà Nẵng: 789 Trần Phú, Hải Châu<br>
• Giờ làm việc: 8:00 - 20:00 (T2-CN)<br><br>
<em>Mẹo: Để được hỗ trợ nhanh nhất, hãy chuẩn bị sẵn mã đơn hàng và mô tả chi tiết vấn đề bạn gặp phải!</em>
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('support-2')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-rose-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-rose-600 transition-colors">
                  Tôi có thể khiếu nại như thế nào?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('support-2') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('support-2')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-rose-50 to-red-50 border-t border-rose-100"
              >
                Gửi khiếu nại qua mục "Hỗ trợ" trên trang web hoặc email với thông tin đơn hàng và mô tả vấn đề.
              </div>
            </div>

            <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
              <button 
                @click="toggleFaq('support-3')"
                class="w-full p-6 text-left flex items-center justify-between hover:bg-rose-50 transition-colors duration-300"
              >
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-rose-600 transition-colors">
                  Có hỗ trợ trực tuyến không?
                </h3>
                <svg 
                  class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                  :class="{ 'rotate-180': openFaqs.includes('support-3') }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div 
                v-show="openFaqs.includes('support-3')"
                class="px-6 pb-6 text-gray-600 leading-relaxed bg-gradient-to-r from-rose-50 to-red-50 border-t border-rose-100"
              >
                Có, bạn có thể chat với chúng tôi qua ô chat trực tuyến trên trang chủ trong giờ hành chính.
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Contact CTA -->
      <div class="mt-16 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl p-8 text-center text-white shadow-2xl">
        <div class="max-w-3xl mx-auto">
          <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <h3 class="text-3xl font-bold mb-4">Vẫn cần hỗ trợ?</h3>
          <p class="text-xl text-blue-100 mb-8">
            Đội ngũ hỗ trợ khách hàng của chúng tôi luôn sẵn sàng giúp đỡ bạn 24/7
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="tel:19001234" class="inline-flex items-center justify-center gap-3 bg-white text-blue-600 px-8 py-4 rounded-2xl font-bold hover:bg-blue-50 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              Gọi 1900 1234
            </a>
            <a href="mailto:hotro@passion.vn" class="inline-flex items-center justify-center gap-3 bg-white/20 backdrop-blur-sm text-white px-8 py-4 rounded-2xl font-bold hover:bg-white/30 transition-all duration-300 border border-white/30 hover:border-white/50 transform hover:scale-105">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Email hỗ trợ
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const openFaqs = ref([])

const toggleFaq = (faqId) => {
  const index = openFaqs.value.indexOf(faqId)
  if (index > -1) {
    openFaqs.value.splice(index, 1)
  } else {
    openFaqs.value.push(faqId)
  }
}
</script>

<style scoped>
html {
  scroll-behavior: smooth;
}

a {
  color: inherit;
  text-decoration: none;
}
</style>
