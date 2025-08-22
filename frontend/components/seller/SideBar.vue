<template>
  <div v-if="show" @click="$emit('close')" class="lg:hidden fixed inset-0 bg-black bg-opacity-40 z-30"></div>

  <aside :class="[
    'fixed z-40 lg:relative bg-[#1D2327] text-white w-64 h-full transition-transform duration-300',
    show ? 'translate-x-0' : '-translate-x-full',
    'lg:translate-x-0',
  ]">
    <!-- Header -->
    <div class="p-4 text-xl font-bold border-b border-gray-800">
      <span class="text-green-400 bg-gray-800 text-sm px-2 py-0.5 rounded">Live</span>
      <NuxtLink to="/seller/dashboard" class="ml-2">Passion</NuxtLink>
    </div>

    <!-- Menu -->
    <nav class="mt-4 text-sm font-medium">
      <ul class="space-y-1">
        <!-- Dashboard -->
        <li>
          <NuxtLink to="/seller/dashboard" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded" :class="route.path === '/seller/dashboard'
              ? 'bg-gray-800 text-green-400 font-bold'
              : 'text-white'
            " @click="$emit('close')">
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 12l2-2m0 0l7-7 7 7m-9 2v10m4-10h5.586a1 1 0 01.707 1.707l-9.586 9.586a1 1 0 01-1.414 0L3 13.414A1 1 0 013.586 12H9z" />
            </svg>
            Thống kê
          </NuxtLink>
        </li>

        <!-- Người dùng (Dropdown) -->
        <li>
          <button @click="toggleUser"
            class="flex items-center w-full px-4 py-2 text-white hover:bg-gray-800 rounded focus:outline-none"
            :class="userActive ? 'bg-gray-800 text-green-400 font-bold' : ''">
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="flex-1 text-left">Quản lý hồ sơ</span>
            <font-awesome-icon :icon="['fas', userOpen ? 'angle-down' : 'angle-right']" class="w-3 h-3" />
          </button>
          <transition name="slide-fade">
            <ul v-if="userOpen" class="ml-8 mt-1 space-y-0.5">
              <li>
                <NuxtLink to="/seller/seller_profile" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded"
                  :class="route.path.startsWith('/seller/seller_profile')
                      ? 'bg-gray-800 text-green-400 font-bold'
                      : 'text-gray-300'
                    " @click="$emit('close')">
                  <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Cá nhân
                </NuxtLink>
              </li>
            </ul>
          </transition>
        </li>

        <!-- người dùng đã mua hàng... -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="/seller/CustomerList"
            class="flex items-center px-4 py-2 gap-3 rounded hover:bg-gray-800 transition" :class="route.path.startsWith('/seller/CustomerList')
                ? 'bg-gray-800 text-green-400 font-bold'
                : 'text-white'
              " @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-4m-4 6v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2h5
           m4-10a4 4 0 110-8 4 4 0 010 8zm6 0a3 3 0 100-6 3 3 0 000 6z" />
            </svg>
            <span>Người dùng</span>
          </NuxtLink>
        </li>

        <!-- Trò chuyện với khách hàng -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="/seller/chat" class="relative flex items-center px-4 py-2 hover:bg-gray-800 gap-3 rounded"
            :class="route.path.startsWith('/seller/chat')
                ? 'bg-gray-800 text-green-400 font-bold'
                : 'text-white'
              " @click="$emit('close')">
            <font-awesome-icon class="w-4 h-4 text-gray-400" :icon="['fas', 'comment']" />
            Trò chuyện với khách hàng
            <span v-if="totalUnread > 0" class="absolute right-4 bg-red-500 w-3 h-3 rounded-full"></span>
          </NuxtLink>
        </li>

        <!-- Sản phẩm (Dropdown) -->
        <li class="pt-2 border-t border-gray-800">
          <button @click="toggleProduct"
            class="flex items-center w-full px-4 py-2 hover:bg-gray-800 focus:outline-none rounded"
            :class="productActive ? 'bg-gray-800 text-green-400 font-bold' : ''">
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v6H3V3zm0 8h18v10H3V11z" />
            </svg>
            Sản phẩm
            <svg class="w-4 h-4 ml-auto transform transition-transform" :class="{ 'rotate-180': productOpen }"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <ul v-show="productOpen" class="pl-11 mt-1 space-y-0.5 text-gray-300 text-[13px]">
            <li>
              <NuxtLink to="/seller/products/list-product" class="block py-1 hover:text-white rounded" :class="route.path.startsWith('/seller/products/list-product')
                  ? 'text-green-400 font-bold'
                  : ''
                " @click="$emit('close')">Tất cả sản phẩm</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/seller/products/create-product" class="block py-1 hover:text-white rounded" :class="route.path.startsWith('/seller/products/create-product')
                  ? 'text-green-400 font-bold'
                  : ''
                " @click="$emit('close')">Thêm sản phẩm</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/seller/inventory/inventory" class="block py-1 hover:text-white rounded" :class="route.path.startsWith('/seller/inventory')
                  ? 'text-green-400 font-bold'
                  : ''
                " @click="$emit('close')">
                Quản lý kho</NuxtLink>
            </li>
          </ul>
        </li>

        <!-- Đơn hàng -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="/seller/orders/list-order" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3 rounded"
            :class="route.path.startsWith('/seller/orders')
                ? 'bg-gray-800 text-green-400 font-bold'
                : 'text-white'
              " @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m13-9l2 9m-5-9v5m-4-5v5" />
            </svg>
            Đơn hàng
          </NuxtLink>
        </li>
        <!-- đổi trả -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="/seller/return/list-return" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3 rounded"
            :class="route.path.startsWith('/seller/return')
                ? 'bg-gray-800 text-green-400 font-bold'
                : 'text-white'
              " @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Đổi trả
          </NuxtLink>
        </li>
        <!-- Thông báo -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="/seller/notifications/list-notifications"
            class="flex items-center w-full px-4 py-2 hover:bg-gray-800 focus:outline-none rounded"
            :class="route.path === '/seller/notifications/list-notifications' ? 'bg-gray-800 text-green-400 font-bold' : 'text-gray-300'">

            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 00-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

            Thông báo
          </NuxtLink>
        </li>

        <!-- Chiết khấu -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="/seller/coupons/list-coupon" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3 rounded"
            :class="route.path.startsWith('/seller/coupons')
                ? 'bg-gray-800 text-green-400 font-bold'
                : 'text-white'
              " @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
            Chiết khấu
          </NuxtLink>
        </li>
        <!-- Đánh giá (Dropdown) -->
        <li class="pt-2 border-t border-gray-800">
          <button @click="toggleReview"
            class="flex items-center w-full px-4 py-2 hover:bg-gray-800 focus:outline-none rounded"
            :class="reviewActive ? 'bg-gray-800 text-green-400 font-bold' : ''">
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3m0 0v3m0-3h3m-3 0H9m6.364-7.636a9 9 0 11-12.728 12.728A9 9 0 0118.364 4.364z" />
            </svg>
            Đánh giá
            <svg class="w-4 h-4 ml-auto transform transition-transform" :class="{ 'rotate-180': reviewOpen }"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <ul v-show="reviewOpen" class="pl-11 mt-1 space-y-0.5 text-gray-300 text-[13px]">
            <li>
              <NuxtLink to="/seller/reviews/list-reviews" class="block py-1 hover:text-white rounded" :class="route.path.startsWith('/seller/reviews') &&
                  !route.path.startsWith('/seller/reports/reviews')
                  ? 'text-green-400 font-bold'
                  : ''
                " @click="$emit('close')">
                Tất cả đánh giá
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/seller/reports/reviews/list-reports" class="block py-1 hover:text-white rounded" :class="route.path.startsWith('/seller/reports/reviews')
                  ? 'text-green-400 font-bold'
                  : ''
                " @click="$emit('close')">
                Đánh giá bị báo cáo
              </NuxtLink>
            </li>
          </ul>
        </li>
        <!-- Quay về trang chủ -->
        <li>
          <NuxtLink to="/" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded" :class="route.path === '/'
              ? 'bg-gray-800 text-green-400 font-bold'
              : 'text-white'
            ">
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Trở về trang chủ
          </NuxtLink>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { watch } from "vue";

import axios from "axios";

defineProps({ show: Boolean });
defineEmits(["close"]);

const route = useRoute();
const notificationOpen = ref(false);
const totalUnread = ref(0);
const seller = ref(null);
const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;

const userOpen = ref(false);
const toggleUser = () => (userOpen.value = !userOpen.value);
const userActive = computed(
  () =>
    route.path.startsWith("/seller/users") ||
    route.path.startsWith("/seller/sellers")
);

const reviewOpen = ref(false);
const toggleReview = () => (reviewOpen.value = !reviewOpen.value);
const reviewActive = computed(
  () =>
    route.path.startsWith("/seller/reviews") ||
    route.path.startsWith("/seller/reports/reviews")
);

const toggleNotification = () =>
  (notificationOpen.value = !notificationOpen.value);
const notificationActive = computed(() =>
  route.path.startsWith("/seller/notifications")
);

const productOpen = ref(false);
const toggleProduct = () => (productOpen.value = !productOpen.value);
const productActive = computed(
  () =>
    route.path.startsWith("/seller/products") ||
    route.path.startsWith("/seller/list-product") ||
    route.path.startsWith("/seller/create-product") ||
    route.path.startsWith("/seller/attributes") ||
    route.path.startsWith("/seller/categories") ||
    route.path.startsWith("/seller/tags")
);

// Cấu hình Axios để gửi token
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("access_token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

const fetchSeller = async () => {
  try {
    const token = localStorage.getItem("access_token");
    if (!token) {
      console.error("Không tìm thấy access_token");
      return false;
    }
    const res = await axios.get(`${API}/sellers/me`); // Thêm /api
    seller.value = res.data.data || res.data.seller || {};
    return true;
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu seller:", error);
    if (error.response?.status === 401) {
      console.warn("Token không hợp lệ hoặc đã hết hạn");
      localStorage.removeItem("access_token");
    }
    return false;
  }
};

const fetchUnread = async () => {
  if (!seller.value?.id) {
    console.warn("Không có seller ID, bỏ qua fetchUnread");
    return;
  }
  try {
    const res = await axios.get(`${API}/chat/sessions`, {
      params: { user_id: seller.value.id, type: "seller" },
    });
    const sessions = res.data.data || [];
    totalUnread.value = sessions.reduce(
      (sum, s) => sum + (s.unread_count || 0),
      0
    );
    console.log("Số tin nhắn chưa đọc:", totalUnread.value); // Debug
  } catch (error) {
    console.error("Lỗi khi lấy tin nhắn chưa đọc:", error);
    if (error.response?.status === 400) {
      console.warn("Tham số không hợp lệ:", error.response.data);
    } else if (error.response?.status === 500) {
      console.error("Lỗi server:", error.response.data);
    }
  }
};

onMounted(async () => {
  const success = await fetchSeller();
  if (success) {
    await fetchUnread();
    const intervalId = setInterval(fetchUnread, 15000); // Poll mỗi 15 giây
    onUnmounted(() => clearInterval(intervalId));
  } else {
    console.warn("Không khởi động polling do lỗi fetchSeller");
  }
});
watch(
  () => route.path,
  (newPath) => {
    if (newPath.startsWith("/seller/chat")) {
      totalUnread.value = 0;
    }
  },
  { immediate: true }
);
</script>

<style>
.slide-fade-enter-active {
  transition: all 0.2s;
}

.slide-fade-leave-active {
  transition: all 0.2s;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
