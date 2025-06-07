<template>
<footer class="bg-white-100 text-black-700 text-sm pt-10 pb-6">

  <!-- Tầng giới thiệu thương hiệu -->
  <div class="text-center px-4 mb-10">
    <h2 class="text-3xl font-bold text-gray-800">PASSION</h2>
    <p class="text-sm text-gray-600 mt-2">Nền tảng thương mại điện tử kết nối đam mê và chất lượng đến từng khách hàng.</p>
  </div>


<!-- Cột 2 -->
  <div>
    <h2 class="text-lg font-semibold mb-4">Hỗ Trợ Khách Hàng</h2>
    <ul class="space-y-1 text-sm">
    
      <li><a href="#" class="hover:underline inline-flex items-center gap-1">Trung tâm trợ giúp</a></li>
     <li>
  <NuxtLink to="/contact" class="hover:underline inline-flex items-center gap-1">
    Liên hệ khách hàng
  </NuxtLink>
</li>

      <li><NuxtLink to="/support" class="hover:underline inline-flex items-center gap-1">Hỗ trợ khách hàng</NuxtLink></li>
      <li><a href="#" class="hover:underline inline-flex items-center gap-1">An toàn mua bán</a></li>
      
    </ul>
  </div>

  <!-- Cột 3 -->
  <div>
    <h2 class="text-lg font-semibold mb-4">Về Chúng Tôi</h2>
    <ul class="space-y-1 text-sm">
        <li><NuxtLink to="/post" href="#" class="hover:underline inline-flex items-center gap-1">Bài viết</NuxtLink></li>
      <li><NuxtLink to="/introduce" class="hover:underline inline-flex items-center gap-1">Giới Thiệu</NuxtLink></li>

    <!-- ĐỐI TÁC & BÁN HÀNG -->
    <div>
      <h3 class="font-semibold text-base mb-3">ĐỐI TÁC & BÁN HÀNG</h3>
      <ul class="space-y-2">
        <li><a href="#" class="hover:underline">Đăng ký bán hàng</a></li>
        <li><a href="#" class="hover:underline">Quy định bán hàng</a></li>
        <li><a href="#" class="hover:underline">Hỗ trợ vận hành</a></li>
        <li><a href="#" class="hover:underline">Chính sách thanh toán</a></li>
      </ul>
    </div>

    <!-- TÀI NGUYÊN & TIN TỨC -->
    <div>
      <h3 class="font-semibold text-base mb-3">TÀI NGUYÊN & TIN TỨC</h3>
      <ul class="space-y-2">
        <li><a href="#" class="hover:underline">Blog</a></li>
        <li><a href="#" class="hover:underline">Tin khuyến mãi</a></li>
        <li><a href="#" class="hover:underline">Cẩm nang mua sắm</a></li>
        <li><a href="#" class="hover:underline">Tải app Passion</a></li>
      </ul>
    </div>

    <!-- KẾT NỐI -->
    <div>
      <h3 class="font-semibold text-base mb-3">KẾT NỐI VỚI CHÚNG TÔI</h3>
      <ul class="space-y-2">
        <li><i class="fab fa-facebook mr-2"></i><a href="#" class="hover:underline">Facebook Passion</a></li>
        <li><i class="fab fa-instagram mr-2"></i><a href="#" class="hover:underline">Instagram</a></li>
        <li><i class="fas fa-envelope mr-2"></i><a href="mailto:hotro@passion.vn" class="hover:underline">hotro@passion.vn</a></li>
        <li><i class="fas fa-phone mr-2"></i>Hotline: 1900 1234</li>
      </ul>
    </div>

  </div>

  <!-- COPYRIGHT -->
  <div class="text-center text-xs text-gray-500 mt-8 border-t pt-4 border-gray-200">
    © 2025 Passion. All rights reserved.
  </div>

</footer>

</template>
