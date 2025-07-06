<template>
  <footer class="bg-white text-gray-700 pt-10 pb-6 border-t border-gray-200">
    <div class="max-w-6xl mx-auto px-4">
      <!-- Logo + Slogan -->
      <div class="text-center mb-10">
        <h2 class="text-3xl font-extrabold text-gray-800 tracking-widest">PASSION</h2>
        <p class="text-sm text-gray-600 mt-2">
          Nền tảng thương mại điện tử kết nối đam mê và chất lượng đến từng khách hàng.
        </p>
      </div>

      <!-- Nội dung chính -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 text-sm text-gray-700">
        <!-- Hỗ trợ khách hàng -->
        <div>
          <h3 class="text-base font-semibold mb-3">Hỗ Trợ Khách Hàng</h3>
          <ul class="space-y-2">
            <li><a href="#" class="hover:underline">Trung tâm trợ giúp</a></li>
            <li><NuxtLink to="/contact" class="hover:underline">Liên hệ khách hàng</NuxtLink></li>
            <li><NuxtLink to="/support" class="hover:underline">Hỗ trợ khách hàng</NuxtLink></li>
            <li><a href="#" class="hover:underline">An toàn mua bán</a></li>
          </ul>
        </div>

        <!-- Về chúng tôi -->
        <div>
          <h3 class="text-base font-semibold mb-3">Về Chúng Tôi</h3>
          <ul class="space-y-2">
            <li><NuxtLink to="/post" class="hover:underline">Bài viết</NuxtLink></li>
            <li><NuxtLink to="/introduce" class="hover:underline">Giới Thiệu</NuxtLink></li>
          </ul>
          <h4 class="font-semibold mt-5 mb-2">Đối Tác & Bán Hàng</h4>
          <ul class="space-y-2">
            <li><a href="#" class="hover:underline">Đăng ký bán hàng</a></li>
            <li><a href="#" class="hover:underline">Quy định bán hàng</a></li>
            <li><a href="#" class="hover:underline">Hỗ trợ vận hành</a></li>
            <li><a href="#" class="hover:underline">Chính sách thanh toán</a></li>
          </ul>
        </div>

        <!-- Tin tức & tài nguyên -->
        <div>
          <h3 class="text-base font-semibold mb-3">Tài Nguyên & Tin Tức</h3>
          <ul class="space-y-2">
            <li><a href="#" class="hover:underline">Blog</a></li>
            <li><a href="#" class="hover:underline">Tin khuyến mãi</a></li>
            <li><a href="#" class="hover:underline">Cẩm nang mua sắm</a></li>
            <li><a href="#" class="hover:underline">Tải app Passion</a></li>
          </ul>
        </div>

        <!-- Kết nối -->
        <div>
          <h3 class="text-base font-semibold mb-3">Kết Nối Với Chúng Tôi</h3>
          <ul class="space-y-2">
            <li>
              <a href="#" class="flex items-center gap-2 hover:underline">
                <i class="fab fa-facebook text-blue-600"></i> Facebook Passion
              </a>
            </li>
            <li>
              <a href="#" class="flex items-center gap-2 hover:underline">
                <i class="fab fa-instagram text-pink-500"></i> Instagram
              </a>
            </li>
            <li>
              <a href="mailto:hotro@passion.vn" class="flex items-center gap-2 hover:underline">
                <i class="fas fa-envelope text-gray-500"></i> hotro@passion.vn
              </a>
            </li>
            <li class="flex items-center gap-2">
              <i class="fas fa-phone text-green-600"></i> Hotline: 1900 1234
            </li>
          </ul>
        </div>
      </div>

      <!-- Footer dưới cùng -->
      <div class="mt-10 border-t pt-4 flex flex-col sm:flex-row justify-between text-xs text-gray-500 text-center sm:text-left space-y-2 sm:space-y-0">
        <div>© 2025 Passion. All rights reserved.</div>
        <div class="flex gap-4 justify-center sm:justify-end">
          <NuxtLink to="/policy" class="hover:underline">Chính sách bảo mật</NuxtLink>
          <NuxtLink to="/terms" class="hover:underline">Điều khoản sử dụng</NuxtLink>
        </div>
      </div>
    </div>
  </footer>
</template>
