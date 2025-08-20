<template>
  <div class="max-w-[1300px] mx-auto bg-white relative">
    <div class="flex justify-between items-center px-4 py-3 border-b border-[#f0f0f0]">
      <div class="text-[#1BA0E2] font-bold text-sm uppercase">SẢN PHẨM THỊNH HÀNH</div>
    </div>

    <div
      ref="scrollContainer"
      class="grid grid-rows-2 grid-flow-col-dense gap-4 px-4 py-4 overflow-x-auto scrollbar-hide relative touch-pan-y"
      style="grid-template-rows: repeat(2, minmax(0, 1fr));"
      @mousedown="startDragging"
      @mousemove="dragging"
      @mouseup="stopDragging"
      @mouseleave="stopDragging"
      @touchstart="startDragging"
      @touchmove="dragging"
      @touchend="stopDragging"
      @scroll="updateScrollIndicators"
    >
      <!-- Skeleton loading -->
      <div
        v-if="pending"
        class="contents"
        aria-hidden="true"
      >
        <div
          v-for="i in 16"
          :key="'skel-' + i"
          class="w-[140px] flex-shrink-0"
        >
          <div class="relative">
            <div class="w-full h-[120px] rounded bg-gray-200 animate-pulse"></div>
            <div class="absolute top-2 left-2">
              <div class="h-4 w-10 bg-gray-300 rounded animate-pulse"></div>
            </div>
          </div>
          <div class="mt-2 space-y-1">
            <div class="h-3 bg-gray-200 rounded animate-pulse"></div>
            <div class="h-3 w-2/3 bg-gray-200 rounded animate-pulse"></div>
          </div>
        </div>
      </div>

      <!-- Products -->
      <div
        v-else-if="products.length"
        v-for="(product, index) in products"
        :key="product.id ?? product.slug ?? index"
        class="w-[140px] flex-shrink-0"
      >
        <NuxtLink :to="`/products/${product.slug}`">
          <div class="relative">
            <img
              :src="getImageUrl(product.image)"
              :alt="product.name"
              class="w-full h-[120px] object-cover rounded"
              width="140"
              height="120"
              loading="lazy"
            />
            <div class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase rounded">
              TOP
            </div>
          </div>
          <p class="mt-2 text-[#333333] text-sm font-normal leading-tight line-clamp-2">
            {{ product.name }}
          </p>
        </NuxtLink>
      </div>

      <!-- Empty / Error -->
      <div v-else-if="error" class="text-sm text-red-500 px-4 col-span-2">
        Lỗi khi tải sản phẩm: {{ error.message || 'Không thể tải dữ liệu.' }}
      </div>
      <div v-else class="text-sm text-gray-500 px-4 col-span-2">
        Không có sản phẩm thịnh hành.
      </div>
    </div>

    <!-- Navigation buttons (ẩn khi loading) -->
    <button
      v-if="showNavigationButtons && canScrollLeft"
      @click="scrollLeft"
      class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll left"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M12.707 15.707a1 1 0 01-1.414 0L6.586 11l4.707-4.707a1 1 0 111.414 1.414L9.414 11l3.293 3.293a1 1 0 010 1.414z"
          clip-rule="evenodd"
        />
      </svg>
    </button>

    <button
      v-if="showNavigationButtons"
      @click="scrollRight"
      class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll right"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M7.293 4.293a1 1 0 011.414 0L13.414 9l-4.707 4.707a1 1 0 01-1.414-1.414L10.586 9 7.293 5.707a1 1 0 010-1.414z"
          clip-rule="evenodd"
        />
      </svg>
    </button>

    <!-- Scroll indicators -->
    <div
      v-if="showNavigationButtons && canScrollLeft"
      class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xl font-bold pointer-events-none"
    >
      &lt;
    </div>
    <div
      v-if="showNavigationButtons"
      class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xl font-bold pointer-events-none"
    >
      &gt;
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

const scrollContainer = ref(null)
const products = ref([])
const pending = ref(true)
const error = ref(null)

// Drag state
const isDragging = ref(false)
const startX = ref(0)
const scrollLeftStart = ref(0)

// Scroll indicator states
const canScrollLeft = ref(false)
const canScrollRight = ref(false)

const canNavigate = computed(() => {
  const el = scrollContainer.value
  return !!el && el.scrollWidth > el.clientWidth
})

// Ẩn nút điều hướng khi loading
const showNavigationButtons = computed(() => {
  if (pending.value) return false
  const count = products.value.length
  return count > 16 || canNavigate.value
})

// Update indicators
const updateScrollIndicators = () => {
  const el = scrollContainer.value
  if (!el) return
  const { scrollLeft, scrollWidth, clientWidth } = el
  canScrollLeft.value = scrollLeft > 0
  canScrollRight.value = scrollLeft < scrollWidth - clientWidth - 1
}

// Smooth scroll
function smoothScroll(target, duration) {
  const el = scrollContainer.value
  if (!el) return
  const start = el.scrollLeft
  const change = target - start
  const startTime = performance.now()

  function animateScroll(currentTime) {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)
    const ease = progress * (2 - progress)
    el.scrollLeft = start + change * ease
    if (progress < 1) requestAnimationFrame(animateScroll)
  }
  requestAnimationFrame(animateScroll)
}

function scrollLeft() {
  const el = scrollContainer.value
  if (el) smoothScroll(el.scrollLeft - 200, 400)
}
function scrollRight() {
  const el = scrollContainer.value
  if (el) smoothScroll(el.scrollLeft + 200, 400)
}

function getImageUrl(path) {
  return `${mediaBase}${path}`
}

// Drag handlers
function startDragging(event) {
  const el = scrollContainer.value
  if (!el) return
  isDragging.value = true
  startX.value = event.type.includes('touch') ? event.touches[0].clientX : event.clientX
  scrollLeftStart.value = el.scrollLeft
  el.style.scrollBehavior = 'auto'
  el.style.cursor = 'grabbing'
}
function dragging(event) {
  const el = scrollContainer.value
  if (!isDragging.value || !el) return
  event.preventDefault()
  const currentX = event.type.includes('touch') ? event.touches[0].clientX : event.clientX
  const diffX = startX.value - currentX
  el.scrollLeft = scrollLeftStart.value + diffX
}
function stopDragging() {
  const el = scrollContainer.value
  if (!isDragging.value || !el) return
  isDragging.value = false
  el.style.scrollBehavior = 'smooth'
  el.style.cursor = 'grab'
}

// Fetch data
async function fetchTrendingProducts() {
  try {
    pending.value = true
    error.value = null
    const response = await $fetch(`${apiBase}/search/trending-products`, { method: 'GET' })
    if (response?.success !== false) {
      // chấp nhận cả dạng {success:true,data:[...]} hoặc trả mảng trực tiếp
      products.value = response.data?.products || response.data || response || []
    } else {
      throw new Error(response.message || 'Lỗi không xác định')
    }
  } catch (err) {
    error.value = err
    products.value = []
    // eslint-disable-next-line no-console
    console.error('Lỗi tải sản phẩm:', err)
  } finally {
    pending.value = false
    // đợi render xong rồi tính lại khả năng cuộn
    requestAnimationFrame(updateScrollIndicators)
  }
}

// Recalc when data changes
watch(products, () => {
  updateScrollIndicators()
})

// Init
onMounted(() => {
  fetchTrendingProducts()
  if (scrollContainer.value) {
    scrollContainer.value.addEventListener('scroll', updateScrollIndicators)
  }
})
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.transition { transition: all 0.3s ease; }

.touch-pan-y {
  -webkit-user-select: none;
  -ms-user-select: none;
  user-select: none;
  touch-action: pan-y;
  cursor: grab;
}
.touch-pan-y:hover { cursor: grab; }

/* Ensure smooth scroll container */
.scroll-container { scroll-behavior: smooth; }
</style>
