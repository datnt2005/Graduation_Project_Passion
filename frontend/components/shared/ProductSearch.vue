<template>
  <div class="max-w-[1200px] mx-auto bg-white relative">
    <div
      class="flex justify-between items-center px-4 py-3 border-b border-[#f0f0f0]"
    >
      <h2 class="text-[#1BA0E2] text-sm font-semibold uppercase tracking-wide">
        TÌM KIẾM HÀNG ĐẦU
      </h2>
      <NuxtLink
        to="/search"
        class="text-[#1BA0E2] text-sm hover:underline flex items-center"
      >
        Xem Tất Cả
        <i class="fas fa-chevron-right ml-1 text-xs"></i>
      </NuxtLink>
    </div>

    <div class="flex overflow-x-auto scrollbar-hide space-x-4 px-4 py-4">
      <div
        v-for="(product, index) in products"
        :key="index"
        class="flex-shrink-0 w-[140px]"
      >
        <div class="relative">
          <img
            :src="product.image"
            :alt="product.name"
            class="w-full h-[120px] object-cover rounded"
            width="140"
            height="120"
          />
          <div
            class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase"
          >
            TOP
          </div>
          <div
            class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1"
          >
            Bán {{ product.sales }} / tháng
          </div>
        </div>
        <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
          {{ product.name }}
        </p>
      </div>
    </div>

    <!-- Nút điều hướng -->
    <button
      aria-label="Next"
      class="absolute right-2 top-1/2 -translate-y-1/2 bg-white border border-gray-300 rounded-full w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900"
    >
      <i class="fas fa-chevron-right"></i>
    </button>
  </div>
</template>

<script setup>
const products = ref([
  {
    name: "Áo Babydoll Nữ Tay Bèo",
    image:
      "https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg",
    sales: "105k+",
  },
  {
    name: "Quần Jean Rộng Cạp Cao",
    image:
      "https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg",
    sales: "89k+",
  },
  {
    name: "Áo Thun Form Rộng In Chữ",
    image:
      "https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg",
    sales: "67k+",
  },
  {
    name: "Váy Yếm Denim Dễ Thương",
    image:
      "https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg",
    sales: "42k+",
  },
]);
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
