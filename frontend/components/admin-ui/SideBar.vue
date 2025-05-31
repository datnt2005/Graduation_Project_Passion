<template>

    <!-- <nav class="flex flex-col space-y-3 text-gray-700"> -->

      <!-- Bảng điều khiển -->
      <!-- <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition"> -->
        <!-- <i class="fas fa-home w-5 text-blue-500"></i> -->
        <!-- <span class="font-medium"><font-awesome-icon :icon="['fas', 'house']" /> Bảng điều khiển</span> -->
      <!-- </a> -->
      <!-- Dropdown Sản phẩm -->
     <!-- <div class="relative"> -->
  <div
    v-if="show"
    @click="$emit('close')"
    class="lg:hidden fixed inset-0 bg-black bg-opacity-40 z-30"
  ></div>

  <aside
    :class="[
      'fixed z-40 lg:relative bg-[#1D2327] text-white w-64 h-full transition-transform duration-300',
      show ? 'translate-x-0' : '-translate-x-full',
      'lg:translate-x-0'
    ]"
  >
    <!-- Header -->
    <div class="p-4 text-xl font-bold border-b border-gray-800">
      <span class="text-green-400 bg-gray-800 text-sm px-2 py-0.5 rounded">Live</span>
      <span class="ml-2">Passion</span>
    </div>

    <!-- Menu -->
    <nav class="mt-4 text-sm font-medium">
      <ul class="space-y-1">
        <!-- Item: Dashboard -->
        <li>
          <NuxtLink to="/admin/dashboard" class="flex items-center px-4 py-2 hover:bg-gray-800" @click="$emit('close')">
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                d="M3 12l2-2m0 0l7-7 7 7m-9 2v10m4-10h5.586a1 1 0 01.707 1.707l-9.586 9.586a1 1 0 01-1.414 0L3 13.414A1 1 0 013.586 12H9z"/></svg>
            Dashboard
          </NuxtLink>
        </li>

        <!-- Item: Người dùng -->
        <li>
          <NuxtLink to="/admin/users/list-user" class="flex items-center px-4 py-2 hover:bg-gray-800" @click="$emit('close')">
            <font-awesome-icon class="w-4 h-4 mr-3 text-gray-400" :icon="['fas', 'circle-user']" />
            Người dùng
          </NuxtLink>
        </li>

        <!-- Dropdown: Sản phẩm -->
        <li class="pt-2 border-t border-gray-800">
          <button @click="isOpen = !isOpen"
                  class="flex items-center w-full px-4 py-2 hover:bg-gray-800 focus:outline-none"
          >
            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 3h18v6H3V3zm0 8h18v10H3V11z"/>
            </svg>
            Sản phẩm
            <svg class="w-4 h-4 ml-auto transform transition-transform"
                 :class="{ 'rotate-180': isOpen }"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <ul v-show="isOpen" class="pl-11 mt-1 space-y-0.5 text-gray-300 text-[13px]">
            <li><NuxtLink to="/admin/products/list-products" class="block py-1 hover:text-white" @click="$emit('close')">Tất cả sản phẩm</NuxtLink></li>
            <li><NuxtLink to="#" class="block py-1 font-semibold text-white" @click="$emit('close')">Thêm sản phẩm</NuxtLink></li>
            <li><NuxtLink to="#" class="block py-1 hover:text-white" @click="$emit('close')">Thương hiệu</NuxtLink></li>
            <li><NuxtLink to="#" class="block py-1 hover:text-white" @click="$emit('close')">Danh mục</NuxtLink></li>
            <li><NuxtLink to="#" class="block py-1 hover:text-white" @click="$emit('close')">Tags</NuxtLink></li>
            <li><NuxtLink to="#" class="block py-1 hover:text-white" @click="$emit('close')">Biến thể</NuxtLink></li>
            <li><NuxtLink to="#" class="block py-1 hover:text-white" @click="$emit('close')">Đánh giá</NuxtLink></li>
          </ul>
        </li>

         <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="#" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3" @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m13-9l2 9m-5-9v5m-4-5v5"/>
            </svg>
            Đơn hàng
          </NuxtLink>
        </li>

        <!-- Notifications -->
        <li class="pt-2 border-t border-gray-800">
          <NuxtLink to="#" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3" @click="$emit('close')">
            <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 00-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            Thông báo
          </NuxtLink>
        </li>

        <!-- Settings -->
        <li>
          <NuxtLink to="#" class="flex items-center px-4 py-2 hover:bg-gray-800 gap-3" @click="$emit('close')">
            <font-awesome-icon :icon="['fas', 'gear']" class="text-gray-400 w-4 h-4" />
            Cài đặt
          </NuxtLink>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>

import { ref } from 'vue'
defineProps({ show: Boolean })
defineEmits(['close'])

const isOpen = ref(true) // mặc định mở submenu Products giống WooCommerce
</script>
