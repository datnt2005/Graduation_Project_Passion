<template>
  <div>
    <!-- Thanh trên cùng -->
<!-- Thanh trên cùng -->
<header class="bg-[#1BA0E2] text-white text-sm py-2">
  <div class="max-w-7xl mx-auto flex justify-between items-center px-4">
    <div class="space-x-2">
      <a href="#" class="hover:underline inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l3 10h8l3-10h4" />
        </svg>
        Đăng ký bán hàng
      </a>
      <a href="#" class="hover:underline inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2h5" />
        </svg>
        Kết nối
      </a>
    </div>
    <div class="hidden sm:flex space-x-4">
      <a href="#" class="hover:underline inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.437L4 17h5" />
        </svg>
        Thông báo
      </a>
      <NuxtLink href="/support" class="hover:underline inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 1.105-1.12 2-2.5 2h-13C4.12 14 3 13.105 3 12s1.12-2 2.5-2h13c1.38 0 2.5.895 2.5 2z" />
        </svg>
        Hỗ trợ  
      </NuxtLink>
      <a href="#" class="hover:underline inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Đăng nhập
      </a>
      <a href="#" class="hover:underline inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Đăng ký
      </a>
    </div>
  </div>
</header>


    <!-- Thanh giữa -->
    <div class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">
        <!-- Logo -->
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gray-300 rounded text-center text-xs flex items-center justify-center">
           <NuxtLink to="/">Logo</NuxtLink>
          </div>
        </div>
      <!-- NAVIGATION -->
        <div class="relative group ml-20 hidden md:block">
        <a href="#" class="text-gray-700 hover:text-blue-600 font-semibold">
            Danh mục
        </a>

        <!-- MEGA MENU -->
        <div class="absolute left-0 mt-6 w-[1200px] bg-white border border-gray-200 shadow-lg rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-6 grid grid-cols-5 gap-6">

            <!-- Cột 1 -->
            <div>
            <h4 class="font-bold mb-2">Sản phẩm mới</h4>
            <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Giá tốt</a></li>
                <li><a href="#" class="hover:underline">Sale</a></li>
            </ul>
            </div>

            <!-- Cột 2 -->
            <div>
            <h4 class="font-bold mb-2">Danh mục sản phẩm</h4>
            <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Áo phông/ Áo thun</a></li>
                <li><a href="#" class="hover:underline">Áo polo</a></li>
                <li><a href="#" class="hover:underline">Áo sơ mi & Áo kiểu</a></li>
                <li><a href="#" class="hover:underline">Bộ quần áo</a></li>
                <li><a href="#" class="hover:underline">Canifa Active</a></li>
                <li><a href="#" class="hover:underline">Đồ ngủ</a></li>
                <li><a href="#" class="hover:underline">Chống nắng</a></li>
            </ul>
            </div>

            <!-- Cột 3 -->
            <div>
            <h4 class="font-bold mb-2">Phụ kiện</h4>
            <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Chăn</a></li>
                <li><a href="#" class="hover:underline">Khăn mặt</a></li>
                <li><a href="#" class="hover:underline">Khăn tắm</a></li>
                <li><a href="#" class="hover:underline">Khăn quàng cổ</a></li>
            </ul>
            <h4 class="font-bold mt-4 mb-2">Bộ sưu tập</h4>
            <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Disney</a></li>
                <li><a href="#" class="hover:underline">Doraemon</a></li>
            </ul>
            </div>

            <!-- Cột 4 & 5 (Hình ảnh) -->
            <div class="col-span-2 grid grid-cols-2 gap-4">
            <img src="https://media.canifa.com/mega_menu/item/Nam-1-menu-05Mar.webp" alt="Nữ 1" class="rounded-md object-cover w-full h-48">
            <img src="https://media.canifa.com/mega_menu/item/Nu-2-menu-05Mar.webp" alt="Nữ 2" class="rounded-md object-cover w-full h-48">
            </div>
        </div>
        </div>



        <!-- Tìm kiếm (desktop) -->
        <div class="flex-1 mx-4 hidden sm:flex justify-center">
          <input
            type="text"
            placeholder="Tìm kiếm"
            class="w-full max-w-[500px] px-4 py-2 border border-gray-300 rounded-full focus:outline-none"
          />
        </div>

        <!-- Menu PC -->
        <div class="hidden sm:flex items-center space-x-6 text-sm">
          <NuxtLink href="/" class="text-gray-700 hover:text-blue-600 font-semibold"><font-awesome-icon :icon="['fas', 'house']" />Trang chủ</NuxtLink>
          <a href="#" class="text-gray-700 hover:text-blue-600 font-semibold"><font-awesome-icon :icon="['fas', 'user']" />Tài khoản</a>
          <a href="#" class="text-gray-700 hover:text-blue-600 font-semibold"><font-awesome-icon :icon="['fas', 'cart-shopping']" />Giỏ hàng</a>
        </div>

        <!-- Icon menu mobile -->
        <div class="sm:hidden">
          <button @click="isMobileMenuOpen = !isMobileMenuOpen">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>

   <!-- Tìm kiếm (mobile) -->
    <div class="px-4 pb-3 sm:hidden">
      <div class="relative">
        <input
          type="text"
          placeholder="Tìm kiếm"
          class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-full focus:outline-none"
        />
        <button
          class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#1BA0E2] p-2 rounded-full hover:bg-blue-600"
        >
          <!-- Icon kính lúp SVG nhỏ gọn -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
          </svg>
        </button>
      </div>
    </div>


    </div> 

    <!-- Menu mobile dropdown -->
    <div v-if="isMobileMenuOpen" class="sm:hidden bg-white shadow-md z-[9999] relative">

      <div class="px-4 py-2 space-y-2 text-sm">
        <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'house']" /> Trang chủ</a>
        <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'user']" />Tài khoản</a>
        <a href="/cart" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'cart-shopping']" />Giỏ hàng</a>
        <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'bell']" />Thông báo</a>
        <NuxtLink href="/support" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'info']" />Hỗ trợ</NuxtLink>
        <a href="#" class="block text-gray-700 hover:text-blue-600">Đăng nhập</a>
        <a href="#" class="block text-gray-700 hover:text-blue-600">Đăng ký</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const isMobileMenuOpen = ref(false)
</script>
