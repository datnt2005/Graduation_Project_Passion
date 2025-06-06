<template>
 <!-- Left Sidebar -->
    <aside aria-label="Sidebar" class="flex-shrink-0 w-full md:w-[300px] rounded-lg p-4 md:p-6 space-y-6 bg-white md:bg-transparent">
        <!-- Hamburger menu for mobile -->
        <button
          class="md:hidden flex items-center justify-between w-full p-2 bg-[#e2e7f7] rounded-md"
          @click="toggleSidebar"
          aria-label="Toggle sidebar menu"
        >
          <span class="text-[#1a1a1a] font-semibold">Menu</span>
          <font-awesome-icon :icon="['fas', sidebarOpen ? 'times' : 'bars']" />
        </button>
        <nav :class="{ 'hidden': !sidebarOpen }" class="md:block">
          <nav aria-label="Breadcrumb" class="mb-4 text-sm text-[#7f8fa4]">
            <ol class="list-reset flex space-x-1">
              <li><a class="hover:underline" href="#">Trang chủ</a></li>
              <li><span>›</span></li>
              <li class="font-semibold text-[#4a4a4a]">Thông tin tài khoản</li>
            </ol>
          </nav>
          <div class="mb-6">
            <div class="flex items-center space-x-3">
              <img
                alt="Avatar icon"
                class="w-10 h-10 rounded-full"
                src="https://storage.googleapis.com/a1aa/image/88df3d54-9e10-41b0-16b4-768b37ecb5b2.jpg"
              />
              <div class="text-[16px] text-[#7f8fa4]">Tài khoản của</div>
            </div>
            <div class="mt-1 font-semibold text-[#1a1a1a] text-base leading-tight">
              Châu Bùi Văn
            </div>
          </div>
          <ul class="space-y-3 text-sm text-[#4a4a4a]">
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded bg-[#e2e7f7] text-[#1a1a1a] font-semibold transition-colors hover:bg-[#d7e4ff]" href="#">
                <font-awesome-icon icon="user-circle" class="text-[#4a4a4a]" />
                <span>Thông tin tài khoản</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-[#e2e7f7] text-[#7f8fa4] hover:text-black transition-colors" href="#">
                <font-awesome-icon icon="bell" />
                <span>Thông báo của tôi</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-[#e2e7f7] text-[#7f8fa4] hover:text-black transition-colors" href="#">
                <font-awesome-icon icon="file-alt" />
                <span>Quản lý đơn hàng</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-[#e2e7f7] text-[#7f8fa4] hover:text-black transition-colors" href="#">
                <font-awesome-icon icon="map-marker-alt" />
                <span>Sổ địa chỉ</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-[#e2e7f7] text-[#7f8fa4] hover:text-black transition-colors" href="#">
                <font-awesome-icon icon="credit-card" />
                <span>Thông tin thanh toán</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded bg-[#d7e4ff] text-[#1a1a1a] font-semibold transition-colors hover:bg-[#c7defa]" href="#">
                <font-awesome-icon icon="comment-alt" class="text-[#2a6adf]" />
                <span>Đánh giá sản phẩm</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-[#e2e7f7] text-[#7f8fa4] hover:text-black transition-colors" href="#">
                <font-awesome-icon icon="eye" />
                <span>Sản phẩm bạn đã xem</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-[#e2e7f7] text-[#7f8fa4] hover:text-black transition-colors" href="#">
                <font-awesome-icon icon="heart" />
                <span>Sản phẩm yêu thích</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-[#e2e7f7] text-[#7f8fa4] hover:text-black transition-colors" href="#">
                <font-awesome-icon icon="star" />
                <span>Nhận xét của tôi</span>
              </a>
            </li>
            <li>
              <a class="flex items-center space-x-2 px-3 py-2 rounded hover:bg-[#e2e7f7] text-[#7f8fa4] hover:text-black transition-colors" href="#">
                <img
                  alt="Icon of a yellow price tag"
                  class="w-5 h-5"
                  src="https://storage.googleapis.com/a1aa/image/e0af1418-a92a-43bf-ade5-4d49087465f2.jpg"
                />
                <span>Mã giảm giá</span>
              </a>
            </li>
          </ul>
        </nav>
      </aside>
</template>

<script setup>
// No script needed for a static sidebar
import { ref } from 'vue'

// Sidebar toggle
const sidebarOpen = ref(false)
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}
</script>

<style scoped>
/* Add any specific styles if needed */
</style>