<template>
  <div class=" text-sm text-gray-700 px-6 py-2 flex justify-center rounded-lg shadow-sm">
    <!-- Desktop: hiển thị tất cả -->
    <div class="hidden md:flex flex-wrap gap-4 text-center items-center">
      <span
        v-for="(item, index) in items"
        :key="index"
        class="pl-2 border-l border-gray-300 flex items-center gap-1 whitespace-nowrap"
      >
        {{ item }}
      </span>
    </div>

    <!-- Mobile: chỉ hiển thị từng cái, auto chuyển -->
    <div class="md:hidden text-center min-h-[24px] flex items-center justify-center relative w-full">
      <transition name="fade" mode="out-in">
        <span
          :key="currentIndex"
          class="pl-2 border-l border-gray-300 flex items-center gap-1 absolute left-1/2 -translate-x-1/2"
        >
          {{ items[currentIndex] }}
        </span>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const items = [
  ' Cam kết',
  '✅ 100% hàng thật',
  '🚚 Freeship mọi đơn',
  '💯 Hoàn 200% nếu hàng giả',
  '🔄 30 ngày đổi trả',
  '⚡ Giao hàng nhanh 2h',
  '💸 Giá siêu rẻ',
]

const currentIndex = ref(0)
let intervalId = null

onMounted(() => {
  intervalId = setInterval(() => {
    currentIndex.value = (currentIndex.value + 1) % items.length
  }, 2500) // 2.5s để người dùng đọc kịp
})

onUnmounted(() => {
  clearInterval(intervalId)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
