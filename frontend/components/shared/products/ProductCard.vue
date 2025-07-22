<template>
  <div
    class="relative overflow-hidden p-2 bg-white rounded shadow transition transform hover:scale-[1.03] hover:-translate-y-1 hover:shadow-lg duration-300 text-left"
  >
    <nuxt-link
      :to="`/products/${item.slug}`"
      class="block group"
      @click="trackClick"
    >
      <!-- Discount badge -->
      <div
        v-if="item.percent && item.percent > 0"
        class="absolute top-0 right-0 bg-red-100 text-red-500 text-xs font-semibold px-2 py-1 rounded z-10"
      >
        -{{ item.percent }}%
      </div>

      <!-- Hình ảnh sản phẩm -->
      <img
        :src="item.image"
        :alt="item.name"
        class="w-full h-40 object-cover rounded group-hover:brightness-95 transition duration-300"
        loading="lazy"
      />

      <!-- Tên sản phẩm -->
      <p
        class="text-sm mt-2 font-medium text-gray-700 line-clamp-2"
        :title="item.name"
      >
        {{ item.name }}
      </p>

      <!-- Giá -->
      <div class="text-red-500 font-semibold mt-1">
        {{ formatPrice(item.price) }}<sup>₫</sup>
      </div>

      <!-- Giá gạch ngang nếu có giảm -->
      <div v-if="item.discount" class="line-through text-gray-400 text-sm">
        {{ formatPrice(item.discount) }}<sup>₫</sup>
      </div>

      <!-- Đánh giá & đã bán -->
      <div class="flex items-center text-[12px] text-gray-400 space-x-2 mt-1">
        <div class="text-yellow-400">{{ item.rating }}</div>
        <div>| {{ item.sold.toLocaleString() }} đã bán</div>
      </div>
    </nuxt-link>
  </div>
</template>

<script setup>
import { useRuntimeConfig } from "#imports";
import { defineProps } from "vue";

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

async function trackClick() {
  try {
    const token = localStorage.getItem("access_token");
    await $fetch(`${apiBase}/search/track-click`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: {
        product_id: props.item.id,
      },
    });
    console.log(`Tracked click for product ID: ${props.item.id}`);
  } catch (error) {
    console.error("Error tracking product click:", error);
  }
}

const formatPrice = (price) => {
  return price ? price.toLocaleString("vi-VN") : "0";
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
