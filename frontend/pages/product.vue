<template>
  <main class="container mx-auto bg-[#F5F5FA] py-6">
    <!-- Store Header -->
    <div class="bg-[#1BA0E2] rounded-t-md px-8 py-5 flex flex-col md:flex-row md:items-center md:justify-between mx-auto shadow">
      <div class="flex items-center gap-4">
        <img src="https://salt.tikicdn.com/ts/upload/30/08/9a/6c6e3b8e6e7e7c7b7e7e7c7b7e7e7c7b.png" alt="Tiki Trading" class="w-16 h-16 rounded-full bg-white border" />
        <div>
          <div class="flex items-center gap-2">
            <span class="text-white font-semibold text-lg">Tiki Trading</span>
            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded ml-1 flex items-center gap-1">
              <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z"/></svg>
              OFFICIAL
            </span>
          </div>
          <div class="flex items-center gap-2 mt-1">
            <span class="text-yellow-400 text-base">★</span>
            <span class="text-white text-sm font-semibold">4.7 / 5</span>
            <span class="text-white text-xs">|</span>
            <span class="text-white text-sm">Người theo dõi: 509.6k+</span>
          </div>
        </div>
      <button class="ml-6 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-semibold px-5 py-2 rounded-lg transition text-sm shadow">
        + Theo Dõi
      </button>

      </div>
      <div class="mt-4 md:mt-0 flex-1 flex justify-end">
        <input
          type="text"
          v-model="input"
          @input="handleSearch"
          placeholder="🔍 Tìm sản phẩm tại cửa hàng"
          class="w-full md:w-[350px] px-4 py-2 rounded border border-gray-200 focus:outline-none text-gray-700 shadow-sm"
        />
      </div>
    </div>
    <!-- Store Nav Tabs -->
    <div class="bg-[#1BA0E2]  px-8 pt-2 pb-0 flex items-center gap-8 text-white font-medium text-base mt-1 rounded-b-md shadow">
      <button
        class="bg-transparent border-none py-3 px-0 focus:outline-none transition"
        :class="tab === 'store' ? 'font-bold underline underline-offset-8 text-white' : 'hover:text-blue-200'"
        @click="tab = 'store'"
      >Cửa Hàng</button>
      <button
        class="bg-transparent border-none py-3 px-0 focus:outline-none transition"
        :class="tab === 'all' ? 'font-bold underline underline-offset-8 text-white' : 'hover:text-blue-200'"
        @click="tab = 'all'"
      >Tất Cả Sản Phẩm</button>
      <button class="bg-transparent border-none py-3 px-0 focus:outline-none hover:text-blue-200 transition">Bộ Sưu Tập</button>
      <button
        class="relative bg-transparent border-none py-3 px-0 focus:outline-none transition"
        :class="tab === 'shock' ? 'font-bold underline underline-offset-8 text-white' : 'hover:text-blue-200'"
        @click="tab = 'shock'"
      >
        Giá Sốc Hôm Nay
        <span v-if="tab === 'shock'" class="absolute left-1/2 -bottom-1 -translate-x-1/2 w-6 h-1 bg-white rounded"></span>
      </button>
      <button class="bg-transparent border-none py-3 px-0 focus:outline-none hover:text-blue-200 transition">Hồ Sơ Cửa Hàng</button>
    </div>
    <!-- Main Content -->
    <div class=" flex gap-4 px-4 mt-6">
      <!-- Sidebar -->
      <Sidebar />
      <!-- Main content -->
      <section class="flex-1 space-y-6">
        <!-- Banner -->
   
        <!-- Products -->
        <Products v-if="tab === 'all'" />
        <ProductsShock v-else-if="tab === 'shock'" />
        <!-- Có thể thêm nội dung khác cho tab 'store' nếu cần -->

        <!-- Pagination -->
        <nav class="flex justify-center items-center gap-2 mt-6 select-none">
          <button class="w-8 h-8 rounded hover:bg-gray-200 transition" aria-label="Previous page">&lt;</button>
          <button class="w-8 h-8 rounded bg-gray-200 font-semibold">1</button>
          <button class="w-8 h-8 rounded hover:bg-gray-200 transition">2</button>
          <button class="w-8 h-8 rounded hover:bg-gray-200 transition">3</button>
          <button class="w-8 h-8 rounded hover:bg-gray-200 transition">4</button>
          <button class="w-8 h-8 rounded hover:bg-gray-200 transition">5</button>
          <button class="w-8 h-8 rounded hover:bg-gray-200 transition" aria-label="Next page">&gt;</button>
        </nav>
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from '~/components/shared/layouts/Sidebar.vue'
import Products from '~/components/shared/Products.vue'
import ProductsShock from '~/components/shared/ProductsShock.vue'
// search 
import { useSearchStore } from '~/stores/search'; 

const input = ref('');
const searchStore = useSearchStore();

const handleSearch = () => {
  searchStore.updateSearch(input.value)
}

const tab = ref('all')
</script>