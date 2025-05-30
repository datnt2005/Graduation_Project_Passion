<template>
  <main class="bg-[#F5F5FA] py-6">
    <div class="max-w-7xl mx-auto flex gap-4 px-4">
      <!-- Sidebar -->
      <Sidebar />
      <!-- Main content -->
      <section class="flex-1 space-y-6">
        <!-- Banner -->
        <Banner />
        <!-- Features -->
        <Features />

        <!-- Tab buttons -->
        <div class="flex gap-2 mb-4">
          <button
            :class="tab === 'all' ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 border border-blue-500'"
            class="px-4 py-2 rounded font-semibold transition"
            @click="tab = 'all'"
          >
            Tất cả sản phẩm
          </button>
          <button
            :class="tab === 'shock' ? 'bg-red-500 text-white' : 'bg-white text-red-500 border border-red-500'"
            class="px-4 py-2 rounded font-semibold transition"
            @click="tab = 'shock'"
          >
            Giá sốc
          </button>
        </div>

        <!-- Brands -->
        <Brands />
        <!-- Products -->
        <Products v-if="tab === 'all'" />
        <ProductsShock v-else />

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
import Sidebar from '~/components/shared/Sidebar.vue'
import Features from '~/components/shared/Features.vue'
import Products from '~/components/shared/Products.vue'

import ProductsShock from '~/components/shared/ProductsShock.vue'

const tab = ref('all')
</script>