<template>
  <div class="relative group bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 p-4">
    <!-- Hình ảnh sản phẩm -->
    <div class="relative">
      <img 
        :src="props.product.image" 
        :alt="props.product.name" 
        class="mx-auto mb-3 rounded-md border border-gray-200 object-cover w-full h-40 sm:h-48 transition-transform duration-300 group-hover:scale-105" 
        loading="lazy"
      />
      <!-- Badge giảm giá (nếu có) -->
      <span v-if="props.product.discount" class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
        Giảm {{ props.product.discount }}%
      </span>
    </div>

    <!-- Thông tin sản phẩm -->
    <div class="text-center">
      <p class="text-sm text-gray-800 font-medium mb-1 line-clamp-2 h-10">
        {{ props.product.name }}
      </p>
      <p class="text-lg font-semibold text-red-600 mb-1">
        {{ props.product.price }} ₫
      </p>
      <!-- Đánh giá (hiển thị khi hover) -->
      <div class="flex justify-center items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <i v-for="star in 5" :key="star" :class="star <= props.product.rating ? 'fas fa-star text-yellow-400' : 'far fa-star text-gray-300'"></i>
        <span class="text-xs text-gray-600">({{ props.product.rating }}/5)</span>
      </div>
    </div>
  </div>
</template>

<script>
import { defineProps } from 'vue';

export default {
  setup(props) {
    // Props được định nghĩa bằng defineProps nhưng không cần logic bổ sung
    // Chúng ta chỉ cần trả về để sử dụng trong template
    return {
      props,
    };
  },
  props: {
    product: {
      type: Object,
      required: true,
      default: () => ({
        image: '',
        name: '',
        price: '',
        rating: 0,
        discount: 0,
      }),
    },
  },
};
</script>