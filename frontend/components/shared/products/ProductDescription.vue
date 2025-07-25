<template>
  <section class="mb-8 text-gray-700 text-sm bg-white p-4 rounded-2">
    <h2 class="text-xl font-semibold mb-3">Mô tả chi tiết</h2>

    <div
      class="text-base text-gray-700 leading-relaxed relative transition-all duration-300"
      :class="!isExpanded ? 'max-h-[460px] overflow-hidden fade-mask' : ''"
      v-html="displayedHtml"
    ></div>

    <button
      v-if="shouldShowToggle"
      class="mt-3 text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50" 
      type="button"
      @click="isExpanded = !isExpanded"
      :aria-expanded="isExpanded"
    >
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
const MAX_LENGTH = 1000; 

const shouldShowToggle = computed(() => props.fullDescription.length > MAX_LENGTH);

// Không cắt chuỗi – hiển thị nguyên HTML nhưng kiểm soát chiều cao bằng CSS
const displayedHtml = computed(() => props.fullDescription);
</script>
<style scoped>
.fade-mask::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100px;
  background: linear-gradient(to bottom, rgba(255, 255, 255, 0), white 80%);
  pointer-events: none;
}
</style>
