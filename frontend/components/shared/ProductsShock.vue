<template>
  <div class="bg-white p-4 rounded shadow-sm">
    <!-- Heading -->
    <h2 class="text-lg font-semibold mb-4 text-red-500">Sản phẩm giá sốc</h2>

    <!-- Filters -->
    <Filters />

    <!-- Products -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
      <div
        v-for="(item, index) in products"
        :key="index"
        class="border rounded-xl overflow-hidden p-2 bg-white text-center shadow transition transform hover:scale-105 hover:-translate-y-1 hover:shadow-lg duration-300"
      >
        <img
          :src="item.image"
          alt="Hình sản phẩm"
          class="w-full h-40 object-cover rounded"
        />
        <p class="text-sm mt-2 font-medium text-gray-700 line-clamp-2">
          {{ item.name }}
        </p>
        <div class="text-red-500 font-semibold mt-1">{{ item.price }}</div>
        <div class="line-through text-gray-400 text-sm">{{ item.discount }}</div>
        <div class="text-yellow-400 text-sm">{{ item.rating }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Filters from '~/components/shared/Filters.vue'

const products = [
  {
    name: 'iPhone 15 Pro Max 256GB - Giá sốc',
    image: 'https://salt.tikicdn.com/cache/750x750/ts/product/2d/7a/8e/6e8e7c7b7e7e7c7b7e7e7c7b7e7e7c7b.jpg.webp',
    price: '$999.000',
    discount: '$1.299.000',
    rating: '★★★★★'
  },
  {
    name: 'Samsung Galaxy S24 Ultra - Giá sốc',
    image: 'https://salt.tikicdn.com/cache/750x750/ts/product/3a/4b/5c/6d7e8f9a0b1c2d3e4f5a6b7c8d9e0f1a.jpg.webp',
    price: '$899.000',
    discount: '$1.199.000',
    rating: '★★★★★'
  },
  {
    name: 'Laptop Gaming ASUS ROG - Giá sốc',
    image: 'https://salt.tikicdn.com/cache/750x750/ts/product/1a/2b/3c/4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a.jpg.webp',
    price: '$1.499.000',
    discount: '$1.999.000',
    rating: '★★★★★'
  },
  {
    name: 'Tai nghe Bluetooth Sony WF-1000XM4 - Giá sốc',
    image: 'https://salt.tikicdn.com/cache/750x750/ts/product/4e/5f/6a/7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e.jpg.webp',
    price: '$199.000',
    discount: '$399.000',
    rating: '★★★★★'
  },
  {
    name: 'Smart TV LG 55 inch 4K - Giá sốc',
    image: 'https://salt.tikicdn.com/cache/750x750/ts/product/5f/6a/7b/8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f.jpg.webp',
    price: '$599.000',
    discount: '$899.000',
    rating: '★★★★★'
  }
]
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>