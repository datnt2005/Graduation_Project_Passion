<template>
  <div class="bg-white py-3">
    <!-- giảm py-6 thành py-3 -->
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-sm font-semibold text-gray-800 mb-1">Từ khóa phổ biến</h2>
      <!-- giảm mb-2 thành mb-1 -->
      <div class="flex flex-wrap text-xs text-gray-600 gap-x-2 gap-y-1">
        <NuxtLink
          v-for="(term, index) in keywords"
          :key="index"
          :to="`/search?q=${term}`"
          class="text-gray-700 hover:text-blue-600 transition-colors duration-200 hover:underline relative after:content-['|'] after:ml-1 after:text-gray-400"
          :class="{ 'after:content-none': index === keywords.length - 1 }"
        >
          {{ term }}
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
const keywords = [
  "áo thun nam",
  "váy nữ đẹp",
  "iphone 15 pro max",
  "tai nghe bluetooth",
  "nồi chiên không dầu",
  "giày sneaker",
  "nước hoa nam",
  "kem chống nắng",
  "bàn phím cơ",
  "máy hút bụi",
  "đồng hồ nữ",
  "sách tâm lý học",
];
</script>
