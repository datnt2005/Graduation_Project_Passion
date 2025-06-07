<script setup>
const emit = defineEmits(['toggle-sidebar'])
</script>

<template>
  <header class="bg-white shadow-md px-4 py-3 flex items-center justify-between">
    <!-- Nút menu chỉ hiển thị ở màn hình nhỏ (mobile) -->
    <button
      @click="$emit('toggle-sidebar')"
      class="lg:hidden text-gray-700 hover:text-gray-900 focus:outline-none"
      aria-label="Toggle Sidebar"
    >
      <font-awesome-icon :icon="['fas', 'bars']" class="text-xl" />
    </button>

    <!-- Logo hoặc tên hệ thống -->
    <div class="text-lg font-semibold text-gray-800 hidden lg:block">
      Trang quản trị
    </div>

    <!-- Thông tin người dùng -->
    <div class="flex items-center gap-3 ml-auto">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
</svg>


      <div class="flex items-center gap-2">
        <img
          src="https://i.pravatar.cc/40"
          alt="Avatar"
          class="w-8 h-8 rounded-full border border-gray-300"
        />
        <span class="text-sm text-gray-700 hidden sm:inline">Xin chào, Người dùng</span>
      </div>
    </div>
  </header>
</template>
