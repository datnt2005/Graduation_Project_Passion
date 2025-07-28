<template>
  <FooterBrands />

  <FooterSearch />

  <FooterCategories />
  <footer class="bg-[#f0f2f5]z text-gray-700 pt-10 pb-6 border-t border-gray-200 text-sm">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Logo và giới thiệu -->
      <div class="text-center mb-10 px-4">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 tracking-wide">
          {{ contactName }}
        </h2>
        <p class="mt-2 text-gray-600 text-sm sm:text-base leading-relaxed">
          Nền tảng thương mại điện tử kết nối đam mê và chất lượng đến từng
          khách hàng.
        </p>
      </div>

      <!-- Nội dung chính -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-10 text-[15px]">
        <!-- Hỗ trợ khách hàng -->
        <div>
          <h2 class="text-base font-semibold mb-4 text-gray-800">
            Hỗ Trợ Khách Hàng
          </h2>
          <ul class="space-y-2">
            <li><NuxtLink to="/faq" class="hover:underline">Câu hỏi thường gặp</NuxtLink></li>
            <li>
              <NuxtLink to="/support" class="hover:underline">Liên hệ khách hàng</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/support" class="hover:underline">Hỗ trợ khách hàng</NuxtLink>
            </li>
            <li><NuxtLink to="/check-policy" class="hover:underline">An toàn mua bán</NuxtLink></li>
          </ul>
        </div>

        <!-- Về chúng tôi -->
        <div>
          <h2 class="text-base font-semibold mb-4 text-gray-800">
            Về Chúng Tôi
          </h2>
          <ul class="space-y-2">
            <li>
              <NuxtLink to="/post" class="hover:underline">Bài viết</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/introduce" class="hover:underline">Giới Thiệu</NuxtLink>
            </li>
          </ul>
          <h3 class="font-semibold mt-6 mb-2">Đối Tác &amp; Bán Hàng</h3>
          <ul class="space-y-1">
            <li><a href="/sell-together-passion" class="hover:underline">Đăng ký bán hàng</a></li>
            <li><a href="/seller/terms-of-service" class="hover:underline">Quy định bán hàng</a></li>
            <li><a href="/support" class="hover:underline">Hỗ trợ vận hành</a></li>
            <li>
              <a href="/return-policy" class="hover:underline">Chính sách đổi trả</a>
            </li>
          </ul>
        </div>

        <!-- Tin tức -->
        <div>
          <h2 class="text-base font-semibold mb-4 text-gray-800">
            Tài Nguyên & Tin Tức
          </h2>
          <ul class="space-y-2">
            <li><a href="#" class="hover:underline">Blog</a></li>
            <li><a href="#" class="hover:underline">Tin khuyến mãi</a></li>
            <li><a href="#" class="hover:underline">Cẩm nang mua sắm</a></li>
            <li><a href="#" class="hover:underline">Tải app Passion</a></li>
          </ul>
        </div>

        <!-- Kết nối -->
        <div>
          <h2 class="text-base font-semibold mb-4 text-gray-800">
            Kết Nối Với Chúng Tôi
          </h2>
          <ul class="space-y-3">
            <li>
              <a href="#" class="hover:underline flex items-center gap-2">
                <i class="fab fa-facebook text-blue-600"></i> Facebook Passion
              </a>
            </li>
            <li>
              <a href="#" class="hover:underline flex items-center gap-2">
                <i class="fab fa-instagram text-pink-500"></i> Instagram
              </a>
            </li>
            <li>
              <a href="mailto:hotro@passion.vn" class="hover:underline flex items-center gap-2">
                <i class="fas fa-envelope text-gray-500"></i>
                <span class="line-clamp-2 break-all text-sm max-w-[180px] sm:max-w-none">
                  {{ contactEmail }}
                </span>
              </a>
            </li>
            <li class="flex items-center gap-2">
              <i class="fas fa-phone text-green-600"></i>
              <span>Hotline: {{ contactHotline }}</span>
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
import FooterCategories from "~/components/shared/layouts/FooterCategories.vue";
import FooterSearch from "~/components/shared/layouts/FooterSearch.vue";
import FooterBrands from "~/components/shared/layouts/FooterBrands.vue";

const { settings } = useSettings();

const contactEmail = computed(() => {
  return (
    settings.value?.contact?.find((s) => s.key === "contact_email")?.value ||
    "Chưa cấu hình"
  );
});

const contactPhone = computed(() => {
  return (
    settings.value?.contact?.find((s) => s.key === "phone_number")?.value ||
    "Chưa cấu hình"
  );
});

const contactName = computed(() => {
  return (
    settings.value?.general?.find((s) => s.key === "site_name")?.value ||
    "Chưa cấu hình"
  );
});

const contactHotline = computed(() => {
  return (
    settings.value?.contact?.find((s) => s.key === "hotline_number")?.value ||
    "Chưa cấu hình"
  );
});
</script>
