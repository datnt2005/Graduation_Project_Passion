<template>
  <section class="mb-8">
    <h2 class="text-xl font-semibold mb-3">Mô tả chi tiết</h2>
    
    <div class="text-base text-gray-700 mb-4 leading-relaxed"
         v-html="displayedHtml"
         :class="{ 'line-clamp-2 overflow-hidden': !isExpanded }">
    </div>

    <button
      v-if="shouldShowToggle"
      class="text-sm text-gray-700 border border-gray-300 rounded-md px-4 py-1.5 hover:bg-gray-100 transition-colors duration-150"
      type="button"
      @click="isExpanded = !isExpanded"
      :aria-expanded="isExpanded">
      {{ isExpanded ? 'Thu gọn' : 'Xem thêm' }}
    </button>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  fullDescription: { type: String, required: true }
});

const isExpanded = ref(false);

// Ngưỡng độ dài (số ký tự) để bắt đầu rút gọn
const MAX_LENGTH = 1000;

// Tự động phát hiện nếu mô tả dài thì mới cho "Xem thêm"
const shouldShowToggle = computed(() => props.fullDescription.length > MAX_LENGTH);

// Nội dung HTML hiển thị
const displayedHtml = computed(() => {
  if (isExpanded.value || !shouldShowToggle.value) return props.fullDescription;
  return props.fullDescription.slice(0, MAX_LENGTH) + '...';
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
