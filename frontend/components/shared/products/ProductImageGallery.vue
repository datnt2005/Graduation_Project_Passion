<template>
  <div class="flex flex-col items-center md:items-start w-full max-w-[480px] mx-auto">
    <!-- Main Image -->
    <div
      class="relative w-full h-[300px] sm:h-[400px] md:h-[480px] bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden group transition-all duration-300 hover:shadow-lg"
      role="region"
      aria-label="Product image gallery"
      ref="mainImageContainer"
      @click="openLightbox"
      @mousemove="handleMouseMove"
      @mouseleave="handleMouseLeave"
      :class="{ 'cursor-magnify': !isZooming, 'cursor-zoom-out': isZooming }"
      tabindex="0"
      @keydown.enter="openLightbox"
    >
      <img
        v-if="images.length > 0"
        :src="`${mediaBase}${images[currentImage].src}`"
        :alt="images[currentImage].alt || 'Ảnh sản phẩm'"
        class="w-full h-full object-contain p-6 transition-transform duration-300"
        :class="{ 'scale-200': isZooming, 'group-hover:scale-105': !isZooming }"
        :style="zoomStyle"
        @error="handleImageError(currentImage)"
        loading="lazy"
        ref="mainImage"
      />
      <div
        v-if="images.length > 0"
        class="absolute bottom-3 right-3 bg-gray-900 bg-opacity-60 text-white text-xs px-2 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200"
      >
        Phóng to
      </div>
      <div
        v-else
        class="w-full h-full flex items-center justify-center text-gray-400 text-lg"
      >
        Không có ảnh sản phẩm
      </div>
    </div>

    <!-- Thumbnails -->
    <div
      v-if="images.length > 0"
      class="mt-4 flex items-center gap-2 w-full max-w-[480px]"
      @mouseenter="pauseAutoSlide"
      @mouseleave="startAutoSlide"
    >
      <button
        @click="prevImage"
        :disabled="currentImage === 0"
        class="w-10 h-16 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-100 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        aria-label="Ảnh trước"
        type="button"
      >
        <i class="fas fa-chevron-left"></i>
      </button>

      <div class="flex-1 flex gap-2 overflow-x-auto thumbs scroll-smooth hide-scrollbar">
        <img
          v-for="(img, index) in images"
          :key="img.src"
          :src="`${mediaBase}${img.src}`"
          :alt="img.alt || `Ảnh thu nhỏ ${index + 1}`"
          class="min-w-[64px] w-16 h-16 object-cover rounded-lg cursor-pointer border transition-all duration-200 hover:scale-105 hover:shadow-md"
          :class="{
            'border-blue-500 ring-2 ring-blue-200': index === currentImage,
            'border-gray-200 hover:border-gray-400': index !== currentImage,
          }"
          @click="setCurrentImage(index)"
          @mouseover="setCurrentImage(index)"
          @error="handleImageError(index)"
          loading="lazy"
          role="button"
          :aria-label="`Chọn ảnh ${index + 1}`"
        />
      </div>

      <button
        @click="nextImage"
        :disabled="currentImage === images.length - 1"
        class="w-10 h-16 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-100 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        aria-label="Ảnh tiếp theo"
        type="button"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Lightbox -->
    <div
      v-if="isLightboxOpen && images.length > 0"
      class="fixed inset-0 bg-black bg-opacity-85 z-50 flex items-center justify-center animate-fade-in"
      role="dialog"
      aria-label="Full-screen image gallery"
      tabindex="-1"
      ref="lightbox"
      @click.self="closeLightbox"
      @keydown.left="prevImage"
      @keydown.right="nextImage"
      @keydown.escape="closeLightbox"
    >
      <div class="relative max-w-5xl w-full px-4">
        <button
          class="absolute top-4 right-4 text-white text-2xl bg-gray-900 bg-opacity-60 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-80 transition-all duration-200"
          @click="closeLightbox"
          aria-label="Đóng lightbox"
          type="button"
        >
          <i class="fas fa-times"></i>
        </button>
        <img
          :src="`${mediaBase}${images[currentImage].src}`"
          :alt="images[currentImage].alt || 'Ảnh sản phẩm'"
          class="w-full h-auto max-h-[85vh] object-contain rounded-xl"
          @error="handleImageError(currentImage)"
          loading="lazy"
        />
        <button
          v-if="images.length > 1"
          class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-2xl bg-gray-900 bg-opacity-60 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-80 transition-all duration-200"
          @click="prevImage"
          :disabled="currentImage === 0"
          aria-label="Ảnh trước trong lightbox"
          type="button"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <button
          v-if="images.length > 1"
          class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-2xl bg-gray-900 bg-opacity-60 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-80 transition-all duration-200"
          @click="nextImage"
          :disabled="currentImage === images.length - 1"
          aria-label="Ảnh tiếp theo trong lightbox"
          type="button"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  images: { type: Array, required: true },
  mediaBase: { type: String, required: true },
  currentIndex: { type: Number, default: 0 },
});

const emit = defineEmits([
  'update:current-index',
  'start-auto-slide',
  'pause-auto-slide',
]);

const currentImage = ref(props.currentIndex);
const isLightboxOpen = ref(false);
const isZooming = ref(false);
const mainImageContainer = ref(null);
const mainImage = ref(null);
const lightbox = ref(null);
const mousePosition = ref({ x: 50, y: 50 });
const localImages = ref([...props.images]);

watch(
  () => props.images,
  (newVal) => {
    localImages.value = [...newVal];
    if (currentImage.value >= newVal.length) {
      currentImage.value = 0;
    }
  },
  { immediate: true }
);

watch(
  () => props.currentIndex,
  (newVal) => {
    currentImage.value = newVal;
  }
);

watch(currentImage, (val) => {
  emit('update:current-index', val);
});

const zoomStyle = computed(() => {
  if (!isZooming.value) return {};
  const { x, y } = mousePosition.value;
  return {
    transformOrigin: `${x}% ${y}%`,
  };
});

function setCurrentImage(index) {
  if (localImages.value && localImages.value.length > index) {
    currentImage.value = index;
  }
}

function nextImage() {
  if (!localImages.value || localImages.value.length === 0) return;
  if (currentImage.value < localImages.value.length - 1) {
    currentImage.value++;
  } else {
    currentImage.value = 0;
  }
}

function prevImage() {
  if (!localImages.value || localImages.value.length === 0) return;
  if (currentImage.value > 0) {
    currentImage.value--;
  }
}

function handleMouseMove(event) {
  if (!isZooming.value) return;
  const rect = mainImageContainer.value.getBoundingClientRect();
  const x = ((event.clientX - rect.left) / rect.width) * 100;
  const y = ((event.clientY - rect.top) / rect.height) * 100;
  mousePosition.value = {
    x: Math.max(0, Math.min(100, x)),
    y: Math.max(0, Math.min(100, y)),
  };
}

function handleMouseLeave() {
  if (isZooming.value) {
    isZooming.value = false;
    mousePosition.value = { x: 50, y: 50 };
  }
}

function openLightbox() {
  isLightboxOpen.value = true;
  nextTick(() => {
    if (lightbox.value) lightbox.value.focus();
  });
}

function closeLightbox() {
  isLightboxOpen.value = false;
  isZooming.value = false;
}

function pauseAutoSlide() {
  emit('pause-auto-slide');
}

function startAutoSlide() {
  emit('start-auto-slide');
}

function handleImageError(index) {
  if (localImages.value && localImages.value.length > index) {
    localImages.value[index] = { ...localImages.value[index], src: '/fallback.jpg' };
  }
}

let interval;
onMounted(() => {
  interval = setInterval(nextImage, 20000);
  window.addEventListener('keydown', handleKey);
});

onUnmounted(() => {
  clearInterval(interval);
  window.removeEventListener('keydown', handleKey);
});

function handleKey(e) {
  if (!isLightboxOpen.value) return;
  if (e.key === 'Escape') {
    closeLightbox();
  }
  if (e.key === 'ArrowRight') nextImage();
  if (e.key === 'ArrowLeft') prevImage();
}

defineExpose({ currentImage });
</script>

<style scoped>
.thumbs::-webkit-scrollbar {
  display: none;
}

.thumbs {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.cursor-magnify {
  cursor: url('/assets/magnify.png'), zoom-in;
}

.cursor-zoom-out {
  cursor: zoom-out;
}

@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.animatefade-in {
  animation: fade-in 0.3s ease-in-out;
}

button {
  transition: background-color 0.2s, opacity 0.2s, transform 0.2s;
}

img {
  transition: transform 0.3s, border-color 0.2s;
}

@media (max-width: 768px) {
  .w-full.max-w-\[480px\] {
    max-width: 100%;
  }

  .h-\[480px\] {
    height: 320px;
  }

  .w-16 {
    width: 3.5rem;
  }

  .h-16 {
    height: 3.5rem;
  }

  .w-10 {
    width: 2.5rem;
  }
}

@media (max-width: 480px) {
  .h-\[480px\] {
    height: 280px;
  }

  .p-6 {
    padding: 1rem;
  }
}
</style>