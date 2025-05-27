<template>
     <!-- Filters -->
<div class="flex flex-wrap gap-4 text-sm mb-4 w-full max-w-5xl">
  <!-- THƯƠNG HIỆU -->
  <div class="relative">
    <div class="flex items-center space-x-1">
      <span class="font-medium">Thương hiệu:</span>
      <button @click="toggleDropdown" class="ml-1 transition-transform" :class="isOpen ? 'rotate-180' : ''">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>

    <!-- Dropdown -->
    <div ref="dropdown" class="absolute z-10 bg-white border rounded shadow-md p-4 mt-2 w-[300px]"
      :class="isOpen ? '' : 'hidden'">
      <div class="grid grid-cols-2 gap-2">
        <button class="bg-gray-100 px-2 py-1 rounded">Philips</button>
        <button class="bg-gray-100 px-2 py-1 rounded">Panasonic</button>
        <button class="bg-gray-100 px-2 py-1 rounded">Sunhouse</button>
        <button class="bg-gray-100 px-2 py-1 rounded">KAFF</button>
        <!-- Thêm nữa nếu muốn -->
      </div>
      <div class="flex justify-between items-center mt-4">
        <button class="text-blue-600 hover:underline">Xoá lọc</button>
        <button class="bg-blue-600 text-white px-4 py-1 rounded">Xem kết quả</button>
      </div>
    </div>
  </div>

  <!-- MÀU SẮC -->
  <div class="relative">
    <div class="flex items-center space-x-1">
      <span class="font-medium">Màu sắc:</span>
      <button @click="toggleColorDropdown" class="ml-1 transition-transform" :class="isColorOpen ? 'rotate-180' : ''">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>

    <!-- Dropdown -->
    <div ref="colorDropdown" class="absolute z-10 bg-white border rounded shadow-md p-4 mt-2 w-[250px]"
      :class="isColorOpen ? '' : 'hidden'">
      <div class="flex flex-wrap gap-2">
        <button class="bg-gray-100 px-2 py-1 rounded">Trắng</button>
        <button class="bg-gray-100 px-2 py-1 rounded">Đen</button>
        <button class="bg-gray-100 px-2 py-1 rounded">Bạc</button>
        <button class="bg-gray-100 px-2 py-1 rounded">Xanh</button>
        <button class="bg-gray-100 px-2 py-1 rounded">Đỏ</button>
      </div>
      <div class="flex justify-between items-center mt-4">
        <button class="text-blue-600 hover:underline">Xoá lọc</button>
        <button class="bg-blue-600 text-white px-4 py-1 rounded">Xem kết quả</button>
      </div>
    </div>
  </div>

  <!-- GIÁ -->
  <div class="relative">
    <div class="flex items-center space-x-1">
      <span class="font-medium">Giá:</span>
      <button @click="togglePriceDropdown" class="ml-1 transition-transform" :class="isPriceOpen ? 'rotate-180' : ''">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>

    <!-- Dropdown -->
    <div ref="priceDropdown" class="absolute z-10 bg-white border rounded shadow-md p-4 mt-2 w-[220px]"
      :class="isPriceOpen ? '' : 'hidden'">
      <div class="flex flex-col gap-2">
        <button class="bg-gray-100 px-2 py-1 rounded">Dưới 1 triệu</button>
        <button class="bg-gray-100 px-2 py-1 rounded">1 - 3 triệu</button>
        <button class="bg-gray-100 px-2 py-1 rounded">3 - 5 triệu</button>
        <button class="bg-gray-100 px-2 py-1 rounded">Trên 5 triệu</button>
      </div>
      <div class="flex justify-between items-center mt-4">
        <button class="text-blue-600 hover:underline">Xoá lọc</button>
        <button class="bg-blue-600 text-white px-4 py-1 rounded">Xem kết quả</button>
      </div>
    </div>
  </div>

</div>
      
</template>

<script setup>
import {ref} from 'vue';
const dropdown = ref(null)
const colorDropdown = ref(null)
const priceDropdown = ref(null)

const isOpen = ref(false)
const isColorOpen = ref(false)
const isPriceOpen = ref(false)

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  isColorOpen.value = false
  isPriceOpen.value = false
}

const toggleColorDropdown = () => {
  isColorOpen.value = !isColorOpen.value
  isOpen.value = false
  isPriceOpen.value = false
}

const togglePriceDropdown = () => {
  isPriceOpen.value = !isPriceOpen.value
  isOpen.value = false
  isColorOpen.value = false
}

</script>