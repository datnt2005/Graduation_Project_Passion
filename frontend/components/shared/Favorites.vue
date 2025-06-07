<template>
  <section class="p-4">
    <h1 class="text-xl font-bold mb-4 text-gray-800">Sản phẩm yêu thích</h1>

    <div v-if="favorites.length === 0" class="text-gray-500">Bạn chưa có sản phẩm yêu thích nào.</div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <div
        v-for="item in favorites"
        :key="item.id"
        class="border border-gray-200 rounded-md p-4"
      >
        <img :src="item.image" :alt="item.name" class="w-full h-48 object-cover rounded-md mb-2" />
        <h2 class="text-lg font-semibold text-gray-900">{{ item.name }}</h2>
        <p class="text-gray-700 text-sm mb-1">{{ item.description }}</p>
        <p class="text-red-600 font-bold">{{ item.price }} ₫</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const favorites = ref([]);

onMounted(() => {
  const stored = localStorage.getItem('favorites');
  favorites.value = stored ? JSON.parse(stored) : [];
});
</script>
