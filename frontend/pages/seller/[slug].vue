<template>
  <div class="container bg-white p-4 shadow w-full  mx-auto mt-4" v-if="seller">
     <div>
      <h1>Chào mừng đến gian hàng</h1>
    </div>
    <!-- Header: Thông tin shop -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center text-2xl">📘</div>
        <div>
          <h2 class="font-semibold text-lg">{{ seller.store_name }}</h2>
          <div class="flex items-center text-sm text-gray-500 space-x-2">
            <span class="text-yellow-500">★ 4.8</span>
            <span>|</span>
            <span>👥 13k người theo dõi</span>
          </div>
        </div>
      </div>
      <div class="flex space-x-2">
        <button class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm">Chat</button>
        <button class="border px-3 py-1 rounded hover:bg-gray-100 transition text-sm"><font-awesome-icon
            :icon="['fas', 'plus']" /> Theo dõi</button>
      </div>
    </div>

    <!-- Menu điều hướng + tìm kiếm -->
    <div class="mt-6 border-t pt-4 flex flex-col lg:flex-row justify-between gap-4">
      <nav class="flex flex-wrap gap-4 text-sm font-medium text-gray-700">
        <a href="#" class="hover:text-blue-600">Cửa hàng</a>
        <a href="#" class="hover:text-blue-600">Tất cả sản phẩm</a>
        <a href="#" class="hover:text-blue-600">Bộ sưu tập</a>
        <a href="#" class="hover:text-blue-600">Giá sốc hôm nay</a>
        <a href="#" class="hover:text-blue-600">Hồ sơ cửa hàng</a>
      </nav>
      <div class="w-full lg:w-auto">
        <div class="flex border rounded overflow-hidden max-w-full">
          <input type="text" placeholder="Tìm kiếm sản phẩm tại cửa hàng"
            class="flex-1 px-3 py-2 text-sm outline-none" />
          <button class="bg-gray-100 px-4 text-sm hover:bg-gray-200 transition">Tìm</button>
        </div>
      </div>
    </div>

    <!-- Nội dung chính: Sidebar + danh sách sp -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-6">
      <!-- Nút chỉ hiển thị ở mobile -->
      <div class="md:hidden mb-4">
        <button @click="isSidebarOpen = true"
          class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-md text-sm text-blue-600 bg-white shadow hover:bg-blue-50 transition">
          ☰ <span>Danh mục</span>
        </button>
      </div>

      <!-- Lớp nền mờ khi sidebar mở (mobile) -->
      <div v-if="isSidebarOpen && !screenIsMdUp" @click="isSidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-40 z-30"></div>

      <!-- Sidebar danh mục -->
      <transition name="slide-in">
        <aside v-show="isSidebarOpen || screenIsMdUp"
          class="bg-white p-5 shadow-md rounded-lg col-span-1 h-fit z-40 relative md:static fixed top-0 left-0 w-64 h-full md:h-fit md:w-auto md:rounded-lg md:p-4 transition-transform">
          <!-- Nút đóng ở mobile -->
          <button v-if="!screenIsMdUp" @click="isSidebarOpen = false"
            class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-xl">
            ✕
          </button>

          <h3 class="font-semibold text-base mb-4 text-gray-800 border-b pb-2">📂 Tất cả danh mục</h3>
          <ul class="space-y-2 text-gray-700 text-sm">
            <li v-for="item in categories" :key="item.id" >
              <a href="#" class="block px-3 py-2 rounded hover:bg-blue-50 hover:text-blue-600 transition">{{ item.icon }} {{ item.name }}  </a>
            </li>
          </ul>
        </aside>
      </transition> 


      <!-- Danh sách sản phẩm -->
      <section class="col-span-1 md:col-span-4">
        <!-- Bộ lọc -->
        <div class="bg-white p-3 shadow rounded mb-4 flex flex-wrap justify-between items-center text-sm">
          <h3 class="font-semibold mb-2 md:mb-0">Tất cả sản phẩm:</h3>
          <div class="flex flex-wrap gap-3 font-medium text-gray-600">
            <button class="hover:text-blue-600">Phổ biến</button>
            <button class="hover:text-blue-600">Bán chạy</button>
            <button class="hover:text-blue-600">Hàng mới</button>
            <button class="hover:text-blue-600">Giá thấp - cao</button>
            <button class="hover:text-blue-600">Giá cao - thấp</button>
          </div>
        </div>

        <!-- Grid sản phẩm -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
          <!-- Card sản phẩm -->
          <div class="bg-white rounded shadow p-3 text-left">
            <img
              src="https://salt.tikicdn.com/cache/750x750/ts/product/a9/83/7c/6664136e604227071213793e7a2091d9.jpg.webp"
              alt="book" class="w-full h-35 object-cover mb-2 rounded-md">
            <p class="text-sm leading-snug font-medium mb-1">PHẠM XUÂN ẨN - Tên Người Như Cuộc Đời</p>
            <p class="text-gray-500 text-xs mb-1">Tác giả</p>
            <div class="flex items-center text-xs text-gray-600 mb-1">
              <span class="text-yellow-500 mr-1">★</span>
              <span class="mr-2">4.8</span>
              <span class="text-gray-400">| Đã bán 1.2k</span>
            </div>
            <p class="font-bold text-red-500 text-sm">120.000₫</p>
            <p class="text-xs text-gray-400 mt-1">Giao từ 3 - 5 ngày</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios';

const route = useRoute()
// const seller = route.params.seller
const seller = ref(null);

onMounted(async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/sellers/store/${route.params.slug}`);
    seller.value = res.data.seller;
  } catch (e) {
    console.log(e)
  }
});

const categories = [
  { icon: '📚', name: 'Sách chuyện' },
  { icon: '🔥', name: 'Sách passion' },
  { icon: '🎭', name: 'Giải trí' },
  { icon: '👗', name: 'Thời trang' },
  { icon: '🧒', name: 'Trẻ em' },
  { icon: '👩‍👧', name: 'Mẹ & Bé' },
]


const isSidebarOpen = ref(false)
const screenIsMdUp = ref(false)

onMounted(() => {
  const updateScreenSize = () => {
    screenIsMdUp.value = window.innerWidth >= 768
    if (screenIsMdUp.value) {
      isSidebarOpen.value = true
    }
  }

  updateScreenSize()
  window.addEventListener('resize', updateScreenSize)
})
</script>
