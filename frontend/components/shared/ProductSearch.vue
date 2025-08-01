<template>
  <div class="max-w-7xl mx-auto bg-white relative">
    <div class="flex justify-between items-center px-4 py-3 border-b border-[#f0f0f0]">
      <div class="text-[#1BA0E2] font-bold text-sm uppercase">SẢN PHẨM THỊNH HÀNH</div>
    </div>

    <div
      ref="scrollContainer"
      class="grid grid-cols-8 gap-4 overflow-x-auto scrollbar-hide px-4 py-4 relative touch-pan-y"
      style="grid-template-rows: repeat(2, minmax(0, 1fr));"
      @mousedown="startDragging"
      @mousemove="dragging"
      @mouseup="stopDragging"
      @mouseleave="stopDragging"
      @touchstart="startDragging"
      @touchmove="dragging"
      @touchend="stopDragging"
    >
      <div
        v-if="displayProducts.length"
        v-for="(product, index) in displayProducts"
        :key="index"
        class="flex-shrink-0 w-[140px]"
      >
        <NuxtLink :to="`/products/${product.slug}`">
          <div class="relative">
            <img
              :src="getImageUrl(product.image)"
              :alt="product.name"
              class="w-full h-[120px] object-cover rounded"
              width="140"
              height="120"
            />
            <div class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
              TOP
            </div>
          </div>
          <p class="mt-2 text-[#333333] text-sm font-normal leading-tight line-clamp-2">
            {{ product.name }}
          </p>
        </NuxtLink>
      </div>

      <!-- Loading hoặc lỗi fallback -->
      <div v-if="pending" class="col-span-8 text-sm text-gray-500 px-4">Đang tải sản phẩm thịnh hành...</div>
      <div v-if="error" class="col-span-8 text-sm text-red-500 px-4">Lỗi khi tải sản phẩm: {{ error.message }}</div>
      <div v-if="!pending && !error && !displayProducts.length" class="col-span-8 text-sm text-gray-500 px-4">
        Không có sản phẩm thịnh hành.
      </div>
    </div>

    <!-- Nút điều hướng -->
    <button
      v-if="canNavigate"
      @click="scrollLeft"
      aria-label="Previous"
      class="absolute left-2 top-1/2 -translate-y-1/2 bg-white border border-gray-300 rounded-full w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition"
    >
      <i class="fas fa-chevron-left"></i>
    </button>
    <button
      v-if="canNavigate"
      @click="scrollRight"
      aria-label="Next"
      class="absolute right-2 top-1/2 -translate-y-1/2 bg-white border border-gray-300 rounded-full w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition"
    >
      <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Biểu tượng < và > khi có thể kéo -->
    <div
      v-if="canNavigate"
      class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xl font-bold"
      style="pointer-events: none;"
    >
      <
    </div>
    <div
      v-if="canNavigate"
      class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xl font-bold"
      style="pointer-events: none;"
    >
      >
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl
const scrollContainer = ref(null)
const products = ref([])
const pending = ref(true)
const error = ref(null)

// Limit to 32 products
const displayProducts = computed(() => {
  return products.value.slice(0, 32)
})

// Drag state
const isDragging = ref(false)
const startX = ref(0)
const scrollLeftStart = ref(0)

// Fetch data from API
async function fetchTrendingProducts() {
  try {
    pending.value = true
    error.value = null
    const response = await $fetch(`${apiBase}/search/trending-products`, {
      method: 'GET',
    })
    if (response.success) {
      products.value = response.data || []
    } else {
      throw new Error(response.message || 'Lỗi không xác định')
    }
  } catch (err) {
    error.value = err
    products.value = []
  } finally {
    pending.value = false
  }
}

// Logic cuộn
const canNavigate = computed(() => {
  return scrollContainer.value && scrollContainer.value.scrollWidth > scrollContainer.value.clientWidth
})

function scrollLeft() {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({
      left: -200,
      behavior: 'smooth',
    })
  }
}

function scrollRight() {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({
      left: 200,
      behavior: 'smooth',
    })
  }
}

function getImageUrl(path) {
  return `${mediaBase}${path}`
}

// Drag functionality
function startDragging(event) {
  if (scrollContainer.value) {
    isDragging.value = true
    startX.value = event.type.includes('touch') ? event.touches[0].clientX : event.clientX
    scrollLeftStart.value = scrollContainer.value.scrollLeft
    scrollContainer.value.style.scrollBehavior = 'auto' // Tắt smooth scroll khi drag
    scrollContainer.value.style.cursor = 'grabbing' // Thay đổi con trỏ khi kéo
  }
}

function dragging(event) {
  if (isDragging.value && scrollContainer.value) {
    event.preventDefault() // Ngăn chặn hành vi mặc định (như chọn text)
    const currentX = event.type.includes('touch') ? event.touches[0].clientX : event.clientX
    const diffX = startX.value - currentX
    scrollContainer.value.scrollLeft = scrollLeftStart.value + diffX
  }
}

function stopDragging() {
  if (isDragging.value && scrollContainer.value) {
    isDragging.value = false
    scrollContainer.value.style.scrollBehavior = 'smooth' // Khôi phục smooth scroll
    scrollContainer.value.style.cursor = 'grab' // Khôi phục con trỏ ban đầu
  }
}

// Fetch data when component mounts
onMounted(() => {
  fetchTrendingProducts()
})
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.transition {
  transition: all 0.3s ease;
}

/* Ngăn chặn highlight text khi drag */
.touch-pan-y {
  -webkit-user-select: none;
  -ms-user-select: none;
  user-select: none;
  touch-action: pan-y; /* Cho phép cuộn dọc trên touch device, nhưng không ảnh hưởng drag ngang */
  cursor: grab; /* Con trỏ mặc định */
}

/* Thay đổi con trỏ khi hover */
.touch-pan-y:hover {
  cursor: grab;
}

/* Ensure grid layout for two rows */
.grid {
  display: grid;
  grid-template-columns: repeat(8, 140px); /* 8 products per row */
  grid-template-rows: repeat(2, auto); /* Two rows */
  gap: 16px; /* Space between products */
  overflow-x: auto;
  scroll-snap-type: x mandatory; /* Optional: Snap to products when scrolling */
}

/* Ensure products are properly sized */
.grid > div {
  scroll-snap-align: start; /* Optional: Align products when snapping */
}
</style>