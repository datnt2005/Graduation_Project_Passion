<template>
    <nuxt-link :to="`/products/${product.slug}`" class="block bg-white rounded-md shadow-md hover:shadow-lg transition-shadow duration-200">
        <div class="relative overflow-hidden rounded-t-md">
            <img :src="product.image || '/default-product.jpg'" :alt="product.name" class="w-full h-48 object-cover" />
            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded" v-if="product.discountPercent > 0">
                -{{ product.discountPercent }}%
            </div>
        </div>
        <div class="p-4">
            <h4 class="text-sm font-semibold text-gray-800 line-clamp-2">{{ product.name }}</h4>
            <div class="mt-2 flex items-center ">
                <span class="text-base font-bold text-red-600">{{ formatPrice(product.price) }} đ</span>
            </div>
        </div>
    </nuxt-link>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
        default: () => ({
            id: 0,
            name: 'Unknown Product',
            slug: 'unknown-product',
            price: '0.00',
            image: '/default-product.jpg',
            discountPercent: 0,
            sold: '0'
        })
    }
});

function formatPrice(price) {
    if (!price || price === 'null' || price === null || price === undefined) {
        return '0';
    }
    const parsedPrice = parseFloat(price);
    if (isNaN(parsedPrice)) {
        return '0';
    }
    return parsedPrice.toLocaleString('vi-VN', { style: 'decimal' });
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

img {
    transition: transform 0.2s;
}

a:hover img {
    transform: scale(1.05);
}
</style>