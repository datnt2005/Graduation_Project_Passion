<template>
  <footer class="bg-[#1BA0E2] text-white py-10 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Hàng 1: các cột -->
     <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-6 text-white">
  <!-- Cột 1 -->
  <div>
    <h2 class="text-lg font-semibold mb-4">Liên Hệ Passion</h2>
    <ul class="space-y-1 text-sm">
      <li><a href="tel:0333678333" class="hover:underline inline-flex items-center gap-1">Hotline: 0333678333</a></li>
      <li >Cửa hàng: Phan Chu Trinh, TP Buôn Ma Thuột, DakLak</li>
      <li><a href="mailto:Pasion@gmail.com" class="hover:underline inline-flex items-center gap-1">Email: Pasion@gmail.com</a></li>
      <li >Mở cửa: 8h - 17h từ T2 - T7</li>
    </ul>
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

      <li><NuxtLink href="/support" class="hover:underline inline-flex items-center gap-1">Hỗ trợ khách hàng</NuxtLink></li>
      <li><a href="#" class="hover:underline inline-flex items-center gap-1">An toàn mua bán</a></li>
    </ul>
  </div>

  <!-- Cột 3 -->
  <div>
    <h2 class="text-lg font-semibold mb-4">Về Chúng Tôi</h2>
    <ul class="space-y-1 text-sm">

      <li><NuxtLink href="/introduce" class="hover:underline inline-flex items-center gap-1">Giới Thiệu</NuxtLink></li>
      <li><a href="#" class="hover:underline inline-flex items-center gap-1">Quy chế hoạt động</a></li>
      <li><a href="#" class="hover:underline inline-flex items-center gap-1">Chính sách bảo mật</a></li>
      <li><a href="#" class="hover:underline inline-flex items-center gap-1">Giải quyết tranh chấp</a></li>
      <li><a href="#" class="hover:underline inline-flex items-center gap-1">Tuyển dụng</a></li>
    </ul>
  </div>

  <!-- Cột 4 -->
  <div>
    <h2 class="text-lg font-semibold mb-4">Liên Kết</h2>
    <div class="flex space-x-4 text-xl">
      <a href="https://facebook.com" target="_blank" class="hover:underline inline-flex items-center gap-1">
        <font-awesome-icon :icon="['fab', 'facebook']" />
      </a>
      <a href="https://instagram.com" target="_blank" class="hover:underline inline-flex items-center gap-1"> 
        <font-awesome-icon :icon="['fab', 'instagram']" />
      </a>
      <a href="https://tiktok.com" target="_blank" class="hover:underline inline-flex items-center gap-1">
        <font-awesome-icon :icon="['fab', 'tiktok']" />
      </a>
    </div>

    <h2 class="text-lg font-semibold mb-4 mt-4">Chứng nhận</h2>
    <div class="flex space-x-4 text-sm">
      <a href="https://bocongthuong.gov.vn" target="_blank" class="hover:underline inline-flex items-center gap-1">Đã đăng ký bộ công thương</a>
    </div>
  </div>
</div>

  <hr>
      <!-- Hàng 2: thông tin công ty -->
      <div class="text-xs text-white leading-relaxed">
        <p>
          CÔNG TY TNHH Pasion - Người đại diện theo pháp luật: Nguyễn Trọng Tấn; GPDKKD:
          0312120782 do sở KH & ĐT TP.HCM cấp ngày 11/01/2013;
          GPMXH: 185/GP-BTTTT do Bộ Thông tin và Truyền thông cấp ngày 09/07/2024 -
          Chịu trách nhiệm nội dung: Trần Hoàng Ly. Chính sách sử dụng.
          Địa chỉ: Tầng 18, Toà nhà UOA, Số 6 đường Tân Trào, Phường Tân Phú, Quận 7, Thành phố Hồ Chí Minh, Việt Nam;
          Email: trogiup@chotot.vn - Tổng đài CSKH: 19003003 (1.000đ/phút)
        </p>
      </div>
    </div>
  </footer>
</template>
