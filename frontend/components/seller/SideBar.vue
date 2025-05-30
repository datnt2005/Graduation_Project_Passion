<template>
  <aside class="w-64 bg-white h-screen shadow-md p-6 hidden md:block">
    <h2 class="text-2xl font-semibold text-gray-800 mb-8">Admin seller</h2>

    <nav class="flex flex-col space-y-3 text-gray-700">
      <!-- Bảng điều khiển -->
      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-home w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'house']" /> Bảng điều khiển</span>
      </a>

      <!-- Dropdown Sản phẩm -->
     <div class="relative">
  <div
    @click="toggleDropdown"
    class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 cursor-pointer transition"
  >
    <div class="flex items-center gap-4">
      <i class="fas fa-box-open w-5 text-blue-500"></i>
      <span class="font-medium">
        <font-awesome-icon :icon="['fas', 'box']" /> Sản phẩm
      </span>
    </div>
      <font-awesome-icon :icon="['fas', 'chevron-down']" />
    </div>

    <!-- Dropdown Menu -->
    <ul
      v-if="isOpen"
      class="absolute left-0 mt-2 bg-white border border-gray-200 rounded-md shadow-lg w-48 z-50"
    >
      <li>
        <a href="#" class="flex items-start gap-2 px-4 py-2 hover:bg-blue-100 hover:text-blue-600 transition">
          <span class="text-black">•</span>
          Tất cả sản phẩm
        </a>
      </li>
      <li>
        <a href="#" class="flex items-start gap-2 px-4 py-2 hover:bg-blue-100 hover:text-blue-600 transition">
          <span class="text-black">•</span>
          Danh mục
        </a>
      </li>
      <li>
        <a href="#" class="flex items-start gap-2 px-4 py-2 hover:bg-blue-100 hover:text-blue-600 transition">
          <span class="text-black">•</span>
          Thương hiệu
        </a>
      </li>
    </ul>
  </div>


      <!-- Các mục khác -->
      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-shopping-bag w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'box-archive']" /> Đơn hàng</span>
      </a>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-tags w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'tags']" /> Mã giảm giá</span>
      </a>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-comments w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'comments']" /> Bình luận & đánh giá</span>
      </a>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-file-alt w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fab', 'rocketchat']" /> Bài viết</span>
      </a>

      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-bell w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'gear']" /> Cài đặt</span>
      </a>
      <a href="#" class="flex items-center gap-4 px-3 py-2 rounded-md hover:bg-blue-50 hover:text-blue-600 transition">
        <i class="fas fa-bell w-5 text-blue-500"></i>
        <span class="font-medium"><font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" /> Logout</span>
      </a>
    </nav>
  </aside>
</template>

<script setup>
import { ref } from 'vue'

const isOpen = ref(false)

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}
</script>
