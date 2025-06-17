<template>
  <router-link
    :to="`/products/${product.slug}`"
    class="relative group bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 p-4 block"
    :aria-label="`View details for ${product.name}`"
  >
    <!-- Product image -->
    <ProductImage :image="product.image" :name="product.name" />

    <!-- Product information -->
    <div class="text-center">
      <p class="text-sm text-gray-800 font-medium mb-1 line-clamp-2 h-10">
        {{ product.name || 'Unknown Product' }}
      </p>
      <div class="flex justify-center items-center gap-2 mb-1">
        <p class="text-lg font-semibold text-red-600">
          ₫{{ formatPrice(product.price) }}
        </p>
      </div>
      <!-- Variants (if applicable) -->
      <ProductVariants v-if="product.variants" :variants="product.variants" />
    </div>
  </router-link>
</template>

<script setup>
import { useRuntimeConfig } from '#app';
import ProductImage from './ProductImageGallery.vue';
import ProductVariants from './ProductInfo.vue';

const config = useRuntimeConfig();
const mediaBase = config.public.mediaBaseUrl;

// Define props
const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({
      id: 0,
      name: 'Unknown Product',
      slug: 'unknown-product',
      price: '0',
      image: '/default-product.jpg',
      variants: null
    }),
    validator: (product) => {
      if (!product.price || typeof product.price === 'undefined') {
        console.warn('Invalid product price:', product);
        return false;
      }
      return true;
    }
  }
});

// Format price for Vietnamese currency
function formatPrice(price) {
  return parseFloat(price || 0).toLocaleString('vi-VN', { style: 'decimal' });
}
</script>

<style scoped>
.group {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.group:hover {
  transform: translateY(-4px);
}
</style>