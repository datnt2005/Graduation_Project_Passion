<template>
  <section class="p-4">
    <h1 class="text-xl font-bold mb-4 text-gray-800">Sản phẩm yêu thích</h1>

    <div v-if="favorites.length === 0" class="text-gray-500">
      Bạn chưa có sản phẩm yêu thích nào.
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <div v-for="item in favorites" :key="item.id" class="border border-gray-200 rounded-md p-4">
        <img
          :src="getImage(item.product)"
          :alt="item.product.name"
          class="w-full h-48 object-cover rounded-md mb-2"
        />
        <h2 class="text-lg font-semibold text-gray-900">{{ item.product.name }}</h2>
        <p class="text-gray-700 text-sm mb-1 line-clamp-2">{{ item.product.description }}</p>
        <p class="text-red-600 font-bold">
          {{ formatPrice(item.product.sale_price || item.product.price) }} ₫
        </p>
      </div>
    </div>
  </section>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from '~/composables/useToast';

const favorites = ref([]);
const { toast } = useToast();
const mediaBase = useRuntimeConfig().public.mediaBase; // hoặc URL cố định ảnh

const getImage = (product) => {
  return product.image ? `${mediaBase}${product.image}` : `${mediaBase}/default.jpg`;
};

const formatPrice = (price) => {
  const parsed = parseFloat(price);
  return isNaN(parsed) ? '0' : parsed.toLocaleString('vi-VN');
};

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token');
    const res = await $fetch('/favorite/list', {
      headers: {
        Authorization: `Bearer ${token}`,
      }
    });
    favorites.value = res;
  } catch (err) {
    toast('error', 'Không thể tải danh sách yêu thích');
    console.error(err);
  }
});
</script>
