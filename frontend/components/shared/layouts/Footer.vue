<template>
  <footer class="bg-white text-gray-700 pt-10 pb-6 border-t border-gray-200">
    <!-- Thương hiệu -->
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-gray-800 tracking-widest">
          PASSION
        </h2>
        <p class="text-sm text-gray-600 mt-2">
          Nền tảng thương mại điện tử kết nối đam mê và chất lượng đến từng
          khách hàng.
        </p>
      </div>
      <!-- Footer main -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 text-sm">
        <!-- Hỗ trợ khách hàng -->
        <div>
          <h2 class="text-lg font-semibold mb-4">Hỗ Trợ Khách Hàng</h2>
          <ul class="space-y-2">
            <li>
              <a href="#" class="hover:underline flex items-center gap-1">
                <span>Trung tâm trợ giúp</span>
              </a>
            </li>
            <li>
              <NuxtLink
                to="/contact"
                class="hover:underline flex items-center gap-1"
              >
                <span>Liên hệ khách hàng</span>
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/support"
                class="hover:underline flex items-center gap-1"
              >
                <span>Hỗ trợ khách hàng</span>
              </NuxtLink>
            </li>
            <li>
              <a href="#" class="hover:underline flex items-center gap-1">
                <span>An toàn mua bán</span>
              </a>
            </li>
          </ul>
        </div>
        <!-- Về chúng tôi -->
        <div>
          <h2 class="text-lg font-semibold mb-4">Về Chúng Tôi</h2>
          <ul class="space-y-2">
            <li>
              <NuxtLink
                to="/post"
                class="hover:underline flex items-center gap-1"
              >
                <span>Bài viết</span>
              </NuxtLink>
            </li>
            <li>
              <NuxtLink
                to="/introduce"
                class="hover:underline flex items-center gap-1"
              >
                <span>Giới Thiệu</span>
              </NuxtLink>
            </li>
          </ul>
          <h3 class="font-semibold text-base mt-6 mb-2">
            Đối Tác &amp; Bán Hàng
          </h3>
          <ul class="space-y-1">
            <li><a href="#" class="hover:underline">Đăng ký bán hàng</a></li>
            <li><a href="#" class="hover:underline">Quy định bán hàng</a></li>
            <li><a href="#" class="hover:underline">Hỗ trợ vận hành</a></li>
            <li>
              <a href="#" class="hover:underline">Chính sách thanh toán</a>
            </li>
          </ul>
        </div>
        <!-- Tài nguyên & tin tức -->
        <div>
          <h2 class="text-lg font-semibold mb-4">Tài Nguyên & Tin Tức</h2>
          <ul class="space-y-2">
            <li><a href="#" class="hover:underline">Blog</a></li>
            <li><a href="#" class="hover:underline">Tin khuyến mãi</a></li>
            <li><a href="#" class="hover:underline">Cẩm nang mua sắm</a></li>
            <li><a href="#" class="hover:underline">Tải app Passion</a></li>
          </ul>
        </div>
        <!-- Kết nối với chúng tôi -->
        <div>
          <h2 class="text-lg font-semibold mb-4">Kết Nối Với Chúng Tôi</h2>
          <ul class="space-y-2">
            <li>
              <a href="#" class="hover:underline flex items-center">
                <i class="fab fa-facebook mr-2 text-blue-600"></i> Facebook
                Passion
              </a>
            </li>
            <li>
              <a href="#" class="hover:underline flex items-center">
                <i class="fab fa-instagram mr-2 text-pink-500"></i> Instagram
              </a>
            </li>
            <li>
              <a
                href="mailto:hotro@passion.vn"
                class="hover:underline flex items-center"
              >
                <i class="fas fa-envelope mr-2 text-gray-500"></i>
                {{ contactEmail }}
              </a>
            </li>
            <li>
              <span class="flex items-center">
                <i class="fas fa-phone mr-2 text-green-600"></i> Hotline: 1900
                1234
              </span>
            </li>
          </ul>
        </div>
      </div>
      <!-- Copyright -->
      <div class="mt-10 border-t pt-4 text-center text-xs text-gray-500">
        © 2025 Passion. All rights reserved.
      </div>
    </div>
  </footer>
</template>

<script setup>
import { useSettings } from "~/composables/useSettings";

const { settings } = useSettings();

const contactEmail = computed(() => {
  return (
    settings.value?.contact?.find((s) => s.key === "contact_email")?.value ||
    "Chưa cấu hình"
  );
});
</script>
