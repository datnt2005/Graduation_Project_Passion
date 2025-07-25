<template>
  <router-link
    :to="`/products/${product.slug}`"
    class="relative group bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 p-4 block"
    :aria-label="`View details for ${product.name}`"
  >
    <!-- Hình ảnh sản phẩm -->
    <div class="relative">
      <img
        :src="imageSrc"
        :alt="product.name || 'Sản phẩm không xác định'"
        class="mx-auto mb-3 rounded-md border border-gray-200 object-cover w-full h-40 sm:h-48 transition-transform duration-300 group-hover:scale-105"
        loading="lazy"
        @error="handleImageError"
      />
    </div>

    <!-- Thông tin sản phẩm -->
    <div class="text-center">
      <p class="text-sm text-gray-800 font-medium mb-1 line-clamp-2 h-10">
        {{ product.name || 'Sản phẩm không xác định' }}
      </p>
      <div class="flex justify-center items-center gap-2 mb-1">
        <p v-if="formattedPrice" class="text-lg font-semibold text-red-600">
          {{ formattedPrice }}<sup class="text-sm">₫</sup>
        </p>
        <p v-else class="text-sm text-gray-500 italic">
          Giá không khả dụng
        </p>
      </div>
    </div>
  </router-link>
</template>

<script setup>
import { computed } from 'vue';
import { useRuntimeConfig } from '#app';

const config = useRuntimeConfig();
const mediaBase = config?.public?.mediaBaseUrl || '';

const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({
      id: 0,
      name: 'Sản phẩm không xác định',
      slug: 'unknown-product',
      price: '0',
      image: '/default-product.jpg'
    }),
    validator: (product) => {
      if (!product.slug || typeof product.slug !== 'string') {
        console.warn('Invalid product slug:', product);
        return false;
      }
      if (!product.name || typeof product.name !== 'string') {
        console.warn('Invalid product name:', product);
        return false;
      }
      return true;
    }
  }
});

// Compute image source
const imageSrc = computed(() => {
  if (!props.product.image) return `${mediaBase}/default-product.jpg`;
  // Nếu đã là URL tuyệt đối thì trả về luôn
  if (/^https?:\/\//.test(props.product.image)) return props.product.image;
  return `${mediaBase}${props.product.image}`;
});

// Format price for Vietnamese currency
const formattedPrice = computed(() => {
  let price = props.product.price;
  if (!price || price === 'null' || price === null || price === undefined) {
    return null;
  }
  if (typeof price === 'string') {
    price = price.replace(/\./g, '');
  }
  const parsedPrice = parseFloat(price);
  if (isNaN(parsedPrice) || parsedPrice < 0) {
    console.warn('Invalid price format:', props.product.price);
    return null;
  }
  return parsedPrice.toLocaleString('vi-VN', { style: 'decimal' });
});

// Handle image loading error
function handleImageError(event) {
  event.target.src = `${mediaBase}/default-product.jpg`;
}
</script>

<style scoped>
.group {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.group:hover {
  transform: translateY(-4px);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
/* Responsive adjustments */
@media (max-width: 640px) {
  .h-40 {
    height: 10rem;
  }
}
</style>