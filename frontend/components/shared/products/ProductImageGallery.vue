<template>
  <div class="flex flex-col items-center md:items-start md:w-[480px]">
    <!-- Main image -->
    <div
      class="w-[480px] h-[480px] border border-gray-200 relative overflow-hidden"
      role="region"
      aria-label="Product image gallery"
      ref="mainImageContainer"
      @click="toggleZoom"
      @mousemove="handleMouseMove"
      @mouseleave="handleMouseLeave"
      :class="{ 'cursor-magnify': !isZooming, 'cursor-zoom-out': isZooming }"
      :aria-pressed="isZooming"
      tabindex="0"
      @keydown.enter="toggleZoom"
    >
      <img
        v-if="images.length"
        :src="`${mediaBase}${images[currentIndex].src}`"
        :alt="images[currentIndex].alt"
        class="w-full h-full object-contain transition-transform duration-200"
        :class="{ 'scale-200': isZooming }"
        :style="zoomStyle"
        loading="lazy"
        ref="mainImage"
      />
      <div
        v-else
        class="w-full h-full flex items-center justify-center text-gray-500"
      >
        Không có hình ảnh
      </div>
    </div>
    <!-- Thumbnails -->
    <div
      class="mt-4 flex items-center space-x-2 overflow-x-auto thumbs w-full max-w-[480px]"
      @mouseenter="isGalleryHovered = true; $emit('pause-auto-slide')"
      @mouseleave="isGalleryHovered = false; $emit('start-auto-slide')"
    >
      <button
        @click="$emit('prev-image')"
        :disabled="!images.length || currentIndex === 0"
        class="w-8 h-24 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 flex items-center justify-center"
        aria-label="Previous image"
      >
        <i class="fas fa-chevron-left"></i>
      </button>
      <img
        v-for="(img, index) in images"
        :key="img.src"
        :src="`${mediaBase}${img.src}`"
        :alt="img.alt"
        :class="[
          'w-16 h-24 object-cover rounded-md cursor-pointer border',
          currentIndex === index ? 'border-red-500' : 'border-transparent',
        ]"
        @click="$emit('update:current-index', index)"
        loading="lazy"
        role="button"
        :aria-label="`Select image ${index + 1}`"
      />
      <button
        @click="$emit('next-image')"
        :disabled="!images.length || currentIndex === images.length - 1"
        class="w-8 h-24 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 flex items-center justify-center"
        aria-label="Next image"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Full-screen modal -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 bg-black bg-opacity-80 z-50 flex items-center justify-center"
      role="dialog"
      aria-label="Full-screen image gallery"
      tabindex="-1"
      ref="modal"
      @keydown.left="prevImage"
      @keydown.right="nextImage"
      @keydown.escape="closeModal"
    >
      <div class="relative w-full h-full flex items-center justify-center">
        <img
          :src="`${mediaBase}${images[currentIndex].src}`"
          :alt="images[currentIndex].alt"
          class="max-w-[90%] max-h-[90%] object-contain"
          loading="lazy"
        />
        <!-- Close button -->
        <button
          class="absolute top-4 right-4 text-white text-2xl bg-gray-800 bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75"
          @click="closeModal"
          aria-label="Close full-screen view"
        >
          <i class="fas fa-times"></i>
        </button>
        <!-- Navigation buttons -->
        <button
          v-if="images.length > 1"
          class="absolute left-4 text-white text-2xl bg-gray-800 bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75"
          @click="prevImage"
          :disabled="currentIndex === 0"
          aria-label="Previous image in full-screen"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <button
          v-if="images.length > 1"
          class="absolute right-4 text-white text-2xl bg-gray-800 bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75"
          @click="nextImage"
          :disabled="currentIndex === images.length - 1"
          aria-label="Next image in full-screen"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  images: { type: Array, required: true },
  mediaBase: { type: String, required: true },
  currentIndex: { type: Number, required: true },
});

const emit = defineEmits([
  'update:current-index',
  'next-image',
  'prev-image',
  'start-auto-slide',
  'pause-auto-slide',
]);

const isGalleryHovered = ref(false);
const isZooming = ref(false);
const isModalOpen = ref(false);
const mainImageContainer = ref(null);
const mainImage = ref(null);
const modal = ref(null);
const mousePosition = ref({ x: 0, y: 0 });

// Compute zoom style based on mouse position
const zoomStyle = computed(() => {
  if (!isZooming.value) return {};
  const { x, y } = mousePosition.value;
  return {
    transformOrigin: `${x}% ${y}%`,
  };
});

// Toggle zoom on click
function toggleZoom() {
  isZooming.value = !isZooming.value;
  if (!isZooming.value) {
    mousePosition.value = { x: 50, y: 50 }; // Reset to center
  } else {
    // Open modal on second click while zooming
    openModal();
    isZooming.value = false;
  }
}

// Handle mouse movement for zoom
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

// Reset zoom on mouse leave
function handleMouseLeave() {
  if (isZooming.value) {
    isZooming.value = false;
    mousePosition.value = { x: 50, y: 50 };
  }
}

// Open full-screen modal
function openModal() {
  isModalOpen.value = true;
  // Focus modal for keyboard navigation
  nextTick(() => {
    if (modal.value) modal.value.focus();
  });
}

// Close full-screen modal
function closeModal() {
  isModalOpen.value = false;
}

// Navigate to previous image in modal
function prevImage() {
  if (props.currentIndex > 0) {
    emit('update:current-index', props.currentIndex - 1);
  }
}

// Navigate to next image in modal
function nextImage() {
  if (props.currentIndex < props.images.length - 1) {
    emit('update:current-index', props.currentIndex + 1);
  }
}
</script>

<style scoped>
.thumbs::-webkit-scrollbar {
  display: none;
}

.cursor-magnify {
  cursor: url('/assets/magnify.png'), zoom-in;
}

.cursor-zoom-out {
  cursor: zoom-out;
}

/* Ensure modal is full-screen */
.fixed.inset-0 {
  z-index: 50;
}

/* Smooth transitions for zoom */
.scale-200 {
  transform: scale(2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .w-\[480px\] {
    width: 100%;
    height: 300px;
  }

  .h-\[480px\] {
    height: 300px;
  }

  .w-16 {
    width: 3.5rem;
  }

  .h-24 {
    height: 4.5rem;
  }

  .w-8 {
    width: 2rem;
  }
}
</style>