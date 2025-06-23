<template>
  <div v-if="show" @click="$emit('close')" class="lg:hidden fixed inset-0 bg-black bg-opacity-40 z-30"></div>

  <aside :class="[
    'fixed z-40 lg:relative bg-[#1D2327] text-white w-64 h-full transition-transform duration-300',
    show ? 'translate-x-0' : '-translate-x-full',
    'lg:translate-x-0'
  ]">
    <!-- Header -->
    <div class="p-4 text-xl font-bold border-b border-gray-800">
      <span class="text-green-400 bg-gray-800 text-sm px-2 py-0.5 rounded">Live</span>
      <span class="ml-2">Passion</span>
    </div>

    <!-- Menu -->
    <nav class="mt-4 text-sm font-medium">
      <ul class="space-y-1">
        <!-- Dashboard -->
        <li>
          <NuxtLink to="/admin/dashboard" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded"
            :class="route.path === '/admin/dashboard' ? 'bg-gray-800 text-green-400 font-bold' : 'text-white'"
            @click="$emit('close')">
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 12l2-2m0 0l7-7 7 7m-9 2v10m4-10h5.586a1 1 0 01.707 1.707l-9.586 9.586a1 1 0 01-1.414 0L3 13.414A1 1 0 013.586 12H9z" />
            </svg>
            Dashboard
          </NuxtLink>
        </li>

        <!-- Người dùng (Dropdown) -->
        <li>
          <button @click="toggleUser"
            class="flex items-center w-full px-4 py-2 text-white hover:bg-gray-800 rounded focus:outline-none"
            :class="userActive ? 'bg-gray-800 text-green-400 font-bold' : ''">
            <font-awesome-icon class="w-4 h-4 mr-3" :icon="['fas', 'users']" />
            <span class="flex-1 text-left">Quản Lí Hồ Sơ</span>
            <font-awesome-icon :icon="['fas', userOpen ? 'angle-down' : 'angle-right']" class="w-3 h-3" />
          </button>
          <transition name="slide-fade">
            <ul v-if="userOpen" class="ml-8 mt-1 space-y-0.5">
              <li>
                <NuxtLink to="/seller/seller_profile" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded"
                  :class="route.path.startsWith('/admin/users') ? 'bg-gray-800 text-green-400 font-bold' : 'text-gray-300'"
                  @click="$emit('close')">
                  <font-awesome-icon class="w-4 h-4 mr-2" :icon="['fas', 'user']" />
                  Cá nhân
                </NuxtLink>
              </li>
              <li>
                <NuxtLink to="/seller/business_seller_profile" class="flex items-center px-4 py-2 hover:bg-gray-800 rounded"
                  :class="route.path.startsWith('/admin/sellers') ? 'bg-gray-800 text-green-400 font-bold' : 'text-gray-300'"
                  @click="$emit('close')">
                  <font-awesome-icon class="w-4 h-4 mr-2" :icon="['fas', 'store']" />
                  Doanh nghiệp
                </NuxtLink>
              </li>
            </ul>
          </transition>
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
              <NuxtLink to="/admin/products/list-product" class="block py-1 hover:text-white rounded"
                :class="route.path.startsWith('/admin/products') ? 'text-green-400 font-bold' : ''"
                @click="$emit('close')">Tất cả sản phẩm</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/admin/products/create-product" class="block py-1 font-semibold text-white rounded"
                :class="route.path.startsWith('/admin/products/create-product') ? 'text-green-400 font-bold' : ''"
                @click="$emit('close')">Thêm sản phẩm</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/admin/attributes/list-attribute" class="block py-1 hover:text-white rounded"
                :class="route.path.startsWith('/admin/attributes') ? 'text-green-400 font-bold' : ''"
                @click="$emit('close')">Thuộc tính</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/admin/categories/list-category" class="block py-1 hover:text-white rounded"
                :class="route.path.startsWith('/admin/categories') ? 'text-green-400 font-bold' : ''"
                @click="$emit('close')">Danh mục</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/admin/tags/list-tag" class="block py-1 hover:text-white rounded"
                :class="route.path.startsWith('/admin/tags') ? 'text-green-400 font-bold' : ''" @click="$emit('close')">
                Thẻ sản phẩm</NuxtLink>
            </li>
            <li>
              <NuxtLink to="#" class="block py-1 hover:text-white rounded" @click="$emit('close')">Đánh giá</NuxtLink>
            </li>
          </ul>
        </li>

        <!-- Đơn hàng -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="/admin/orders/list-order" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3 rounded"
            :class="route.path.startsWith('/admin/orders') ? 'bg-gray-800 text-green-400 font-bold' : 'text-white'"
            @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m13-9l2 9m-5-9v5m-4-5v5" />
            </svg>
            Đơn hàng
          </NuxtLink>
        </li>

        <!-- Chiết khấu -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="/admin/coupons/list-coupon" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3 rounded"
            :class="route.path.startsWith('/admin/coupons') ? 'bg-gray-800 text-green-400 font-bold' : 'text-white'"
            @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
            Chiết khấu
          </NuxtLink>
        </li>

        <!-- Bài viết -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="#" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3 rounded" @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Bài viết
          </NuxtLink>
        </li>

        <!-- Notifications -->
        <!-- Thông báo (Dropdown) -->
        <li class="pt-2 border-t border-gray-800">
          <button @click="toggleNotification"
            class="flex items-center w-full px-4 py-2 hover:bg-gray-800 focus:outline-none rounded"
            :class="notificationActive ? 'bg-gray-800 text-green-400 font-bold' : ''">
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 00-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            Thông báo
            <svg class="w-4 h-4 ml-auto transform transition-transform" :class="{ 'rotate-180': notificationOpen }"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <ul v-show="notificationOpen" class="pl-11 mt-1 space-y-0.5 text-gray-300 text-[13px]">
            <li>
              <NuxtLink to="/seller/notifications/list-notifications" class="block py-1 hover:text-white rounded"
                :class="route.path === '/seller/notifications/list-notifications' ? 'text-green-400 font-bold' : ''"
                @click="$emit('close')">Tất cả</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/seller/notifications/list-from-user" class="block py-1 hover:text-white rounded"
                :class="route.path === '/seller/notifications/list-from-user' ? 'text-green-400 font-bold' : ''"
                @click="$emit('close')">Từ người dùng</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/seller/notifications/list-from-seller" class="block py-1 hover:text-white rounded"
                :class="route.path === '/seller/notifications/list-from-seller' ? 'text-green-400 font-bold' : ''"
                @click="$emit('close')">Từ người bán</NuxtLink>
            </li>
          </ul>
        </li>

        <!-- Cài đặt -->
        <li>
          <NuxtLink to="/admin/settings/list-paymentMethod" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3" @click="$emit('close')">
            <font-awesome-icon :icon="['fas', 'gear']" class="text-gray-400 w-4 h-4" />
            Cài đặt
          </NuxtLink>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
defineProps({ show: Boolean })
defineEmits(['close'])

const route = useRoute()

const userOpen = ref(false)
const toggleUser = () => userOpen.value = !userOpen.value
const userActive = computed(() =>
  route.path.startsWith('/admin/users') || route.path.startsWith('/admin/sellers')
)

const productOpen = ref(false)
const toggleProduct = () => productOpen.value = !productOpen.value
const productActive = computed(() =>
  route.path.startsWith('/admin/products')
  || route.path.startsWith('/admin/attributes')
  || route.path.startsWith('/admin/categories')
  || route.path.startsWith('/admin/tags')
)
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
