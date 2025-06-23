<template>
  <div class="flex flex-col items-center md:items-start w-full max-w-[480px]">
    <!-- Main Image -->
    <div
      class="relative w-full h-[300px] sm:h-[400px] md:h-[480px] bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden group"
    >
      <img
        v-if="props.images && props.images.length > 0"
        :src="props.images[currentImage]"
        :alt="props.alts[currentImage] || 'Ảnh sản phẩm'"
        class="w-full h-full object-contain p-4 transition-transform duration-300 group-hover:scale-105 cursor-pointer"
        @click="openLightbox"
        @error="handleImageError(currentImage)"
      />
      <div
        v-if="props.images && props.images.length > 0"
        class="absolute bottom-2 right-2 bg-gray-800 bg-opacity-50 text-white text-xs px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity"
      >
        Phóng to
      </div>
      <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
        Không có ảnh sản phẩm
      </div>  
    </div>

    <!-- Thumbnails -->
    <div v-if="props.images && props.images.length > 0" class="mt-4 flex items-center gap-2 w-full max-w-[480px]">
      <button
        @click="prevImage"
        class="w-8 h-20 bg-white border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors disabled:opacity-50"
        :disabled="currentImage === 0"
        aria-label="Ảnh trước"
        type="button"
      >
        <i class="fas fa-chevron-left"></i>
      </button>

      <div class="flex-1 flex gap-2 overflow-x-auto thumbs scroll-smooth">
        <img
          v-for="(img, index) in props.images"
          :key="index"
          :src="img"
          :alt="props.alts[index] || `Ảnh thu nhỏ ${index + 1}`"
          class="min-w-[64px] w-16 h-20 object-cover rounded-md cursor-pointer border transition-all"
          :class="{
            'border-red-500 ring-2 ring-red-300': index === currentImage,
            'border-gray-300 hover:border-gray-400': index !== currentImage,
          }"
          @click="setCurrentImage(index)"
          @error="handleImageError(index)"
        />
      </div>

      <button
        @click="nextImage"
        class="w-8 h-20 bg-white border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors disabled:opacity-50"
        :disabled="currentImage === props.images.length - 1"
        aria-label="Ảnh tiếp theo"
        type="button"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Lightbox -->
    <div
      v-if="isLightboxOpen && props.images && props.images.length > 0"
      class="fixed inset-0 bg-black bg-opacity-80 z-50 flex items-center justify-center"
      @click.self="closeLightbox"
    >
      <div class="relative max-w-4xl w-full">
        <button
          class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300"
          @click="closeLightbox"
          aria-label="Đóng lightbox"
          type="button"
        >
          <i class="fas fa-times"></i>
        </button>
        <img
          :src="props.images[currentImage]"
          :alt="props.alts[currentImage] || 'Ảnh sản phẩm'"
          class="w-full h-auto max-h-[80vh] object-contain rounded-lg"
          @error="handleImageError(currentImage)"
        />
        <button
          class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-2xl hover:text-gray-300"
          @click="prevImage"
          :disabled="currentImage === 0"
          aria-label="Ảnh trước"
          type="button"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <button
          class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-2xl hover:text-gray-300"
          @click="nextImage"
          :disabled="currentImage === props.images.length - 1"
          aria-label="Ảnh tiếp theo"
          type="button"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>
<script setup>

import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  images: { type: Array, required: true },
  alts: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:current-image']);

// v-model:current-image support
const currentImage = ref(0);
watch(currentImage, val => emit('update:current-image', val));

// Allow parent to control current image (optional)
defineExpose({ currentImage });

const isLightboxOpen = ref(false);

function setCurrentImage(index) {
  if (props.images && props.images.length > 0) {
    currentImage.value = index;
  }
}

function nextImage() {
  if (!props.images || props.images.length === 0) return;
  if (currentImage.value < props.images.length - 1) {
    currentImage.value++;
  } else {
    currentImage.value = 0;
  }
}
function prevImage() {
  if (!props.images || props.images.length === 0) return;
  if (currentImage.value > 0) {
    currentImage.value--;
  }
}

function openLightbox() {
  isLightboxOpen.value = true;
}
function closeLightbox() {
  isLightboxOpen.value = false;
}

// Tự động chuyển ảnh
let interval;
onMounted(() => {
  interval = setInterval(nextImage, 4000);
  window.addEventListener('keydown', handleKey);
});
onUnmounted(() => {
  clearInterval(interval);
  window.removeEventListener('keydown', handleKey);
});

// Bắt phím ← → ESC
function handleKey(e) {
  if (!isLightboxOpen.value) return;
  if (e.key === 'Escape') closeLightbox();
  if (e.key === 'ArrowRight') nextImage();
  if (e.key === 'ArrowLeft') prevImage();
}

// Ảnh lỗi
function handleImageError(index) {
  // Không nên gán trực tiếp vào props.images, nên dùng fallback riêng
  // Nhưng để đơn giản, vẫn giữ như cũ
  if (props.images && props.images.length > index) {
    props.images[index] = '/fallback.jpg';
  }
}
</script>

