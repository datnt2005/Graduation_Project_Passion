<template>
  <div class="container mx-auto mt-6 bg-white shadow rounded-md relative overflow-hidden">
    <div class="border-b border-gray-200 px-4 py-3">
      <div class="text-[#1BA0E2] font-bold text-sm uppercase">DANH MỤC NỔI BẬT</div>
    </div>

    <!-- Nút điều hướng trái -->
    <button
      v-if="showNavigationButtons && canScrollLeft"
      @click="scrollLeft"
      class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll left"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0L6.586 11l4.707-4.707a1 1 0 111.414 1.414L9.414 11l3.293 3.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
      </svg>
    </button>

    <!-- Nút điều hướng phải -->
    <button
      v-if="showNavigationButtons"
      @click="scrollRight"
      class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll right"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M7.293 4.293a1 1 0 011.414 0L13.414 9l-4.707 4.707a1 1 0 01-1.414-1.414L10.586 9 7.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
      </svg>
    </button>

    <!-- Chỉ báo cuộn -->
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

    <!-- Danh sách danh mục -->
    <div
      ref="scrollContainer"
      class="overflow-x-auto scrollbar-hide px-4 py-4 touch-pan-y relative"
      @mousedown="startDragging"
      @mousemove="dragging"
      @mouseup="stopDragging"
      @mouseleave="stopDragging"
      @touchstart="startDragging"
      @touchmove="dragging"
      @touchend="stopDragging"
      @scroll="updateScrollIndicators"
    >
      <!-- Skeleton loading layout -->
      <div
        v-if="pending"
        class="min-w-[900px] grid grid-cols-8 grid-rows-2 gap-x-8 gap-y-6"
        aria-hidden="true"
      >
        <div
          v-for="i in 16"
          :key="'skel-' + i"
          class="flex flex-col items-center text-center"
        >
          <div class="bg-gray-100 rounded-full p-4 mb-3 w-20 h-20 flex items-center justify-center animate-pulse">
            <div class="w-16 h-16 rounded-full"></div>
          </div>
          <div class="h-3 w-24 bg-gray-200 rounded animate-pulse"></div>
        </div>
      </div>

      <!-- Grid categories -->
      <div v-else class="min-w-[900px] grid grid-cols-8 grid-rows-2 gap-x-8 gap-y-6">
        <NuxtLink
          v-for="(category, index) in categories"
          :key="category.id || index"
          :to="`/shop/${category.slug}`"
          class="flex flex-col items-center text-center text-[12px] text-gray-800 hover:-translate-y-0.5 transition"
        >
          <div class="bg-gray-100 rounded-full p-4 mb-3 w-20 h-20 flex items-center justify-center">
            <img
              :src="`${mediaBase}${category.image}`"
              :alt="category.name"
              class="w-16 h-16 object-contain"
              width="64"
              height="64"
              loading="lazy"
            />
          </div>
          <p class="text-[13px] text-gray-800 leading-tight font-medium truncate max-w-[100px]">
            {{ category.name }}
          </p>
        </NuxtLink>
      </div>

      <!-- Error / empty -->
      <div v-if="error && !pending" class="text-sm text-red-500 px-4 col-span-2">
        Lỗi khi tải danh mục: {{ error.message || 'Không thể tải dữ liệu.' }}
      </div>
      <div v-if="!pending && !error && !categories.length" class="text-sm text-gray-500 px-4 col-span-2">
        Không có danh mục nổi bật.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

const categories = ref([])
const pending = ref(true)
const error = ref(null)
const scrollContainer = ref(null)

// Drag state
const isDragging = ref(false)
const startX = ref(0)
const scrollLeftStart = ref(0)

// Scroll indicator states
const canScrollLeft = ref(false)
const canScrollRight = ref(false)

// Hiện nút điều hướng: ẩn khi đang loading
const canNavigate = computed(() => {
  const el = scrollContainer.value
  return !!el && el.scrollWidth > el.clientWidth
})
const showNavigationButtons = computed(() => {
  if (pending.value) return false
  const count = categories.value.length
  return count > 16 || canNavigate.value
})

// Compute whether scrolling is possible in each direction
const updateScrollIndicators = () => {
  const el = scrollContainer.value
  if (el) {
    const { scrollLeft, scrollWidth, clientWidth } = el
    canScrollLeft.value = scrollLeft > 0
    canScrollRight.value = scrollLeft < scrollWidth - clientWidth - 1
  }
}

// Smooth slow motion scroll
function smoothScroll(target, duration) {
  const el = scrollContainer.value
  if (!el) return
  const start = el.scrollLeft
  const change = target - start
  const startTime = performance.now()

  function animateScroll(currentTime) {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)
    const ease = progress * (2 - progress) // ease-in-out
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

// Fetch data from API
async function fetchCategories() {
  try {
    pending.value = true
    error.value = null
    const response = await $fetch(`${apiBase}/search/trending-categories`, { method: 'GET' })
    categories.value = response?.data?.categories || response?.data?.data || response?.data || []
  } catch (err) {
    error.value = err
    categories.value = []
    // eslint-disable-next-line no-console
    console.error('Lỗi khi tải danh mục:', err)
  } finally {
    pending.value = false
    // đợi render xong rồi cập nhật chỉ báo cuộn
    requestAnimationFrame(updateScrollIndicators)
  }
}

// Drag functionality
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

// Watch for changes in categories to update scroll indicators
watch(categories, () => {
  updateScrollIndicators()
})

// Init
onMounted(() => {
  fetchCategories()
  if (scrollContainer.value) {
    scrollContainer.value.addEventListener('scroll', updateScrollIndicators)
  }
})
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

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
