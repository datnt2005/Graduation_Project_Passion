<template>
  <div class="bg-gray-100 rounded p-3 text-xs text-gray-700">
    <div class="flex items-center gap-3 mb-2">
      <img :src="review.avatar" :alt="'Avatar of ' + review.user" class="w-10 h-10 rounded-full" height="40" width="40" loading="lazy" />
      <div>
        <p class="font-semibold">{{ review.user }}</p>
        <p>Đã viết 9 đánh giá</p>
      </div>
      <div class="ml-auto flex items-center gap-1 text-yellow-400">
        <i v-for="i in review.rating" :key="i" class="fas fa-star"></i>
      </div>
    </div>
    <p class="mb-1 font-semibold">Hài lòng</p>
    <p v-if="review.purchased" class="mb-1 text-green-600 font-semibold">Đã mua hàng</p>
    <p class="mb-1">{{ review.comment }}</p>
    <div class="flex items-center gap-4 text-gray-500 text-xs">
      <span><i class="fas fa-thumbs-up"></i> {{ review.likes }} Đã nhận</span>
      <span><i class="fas fa-heart"></i> {{ review.thanks }} Lượt cảm ơn</span>
    </div>
  </div>
</template>

<script setup>
defineProps(['review'])
</script>