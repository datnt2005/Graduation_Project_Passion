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
      <font-awesome-icon :icon="['fas', 'bell']" class="text-gray-600 text-lg cursor-pointer" />
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
