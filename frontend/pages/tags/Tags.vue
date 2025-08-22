<template>
  <div class="bg-white p-3 shadow rounded-md relative overflow-hidden">
    <!-- Nút điều hướng -->
    <button
      v-if="!pending && hasOverflow"
      @click="scrollLeft"
      class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll left"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0L6.586 11l4.707-4.707a1 1 0 111.414 1.414L9.414 11l3.293 3.293a1 1 0 010 1.414z" clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Nút điều hướng phải -->
    <button
      v-if="!pending && hasOverflow"
      @click="scrollRight"
      class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll right"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M7.293 4.293a1 1 0 011.414 0L13.414 9l-4.707 4.707a1 1 0 01-1.414-1.414L10.586 9 7.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Danh sách tag cuộn ngang -->
    <div
      ref="scrollContainer"
      class="flex gap-3 overflow-x-auto no-scrollbar scroll-smooth px-6 py-2"
      @scroll="measureOverflow"
    >
      <!-- Skeleton loading -->
      <template v-if="pending">
        <div
          v-for="i in 12"
          :key="'skel-'+i"
          class="flex flex-col items-center justify-center w-[100px] shrink-0 p-2 rounded-lg"
          aria-hidden="true"
        >
          <div class="w-12 h-12 rounded-2xl bg-gray-200 animate-pulse mb-1"></div>
          <div class="w-16 h-3 bg-gray-200 rounded animate-pulse"></div>
        </div>
      </template>

      <!-- Data -->
      <template v-else>
        <NuxtLink
          v-for="(item, index) in tags"
          :key="item.id || index"
          :to="`/tags/${item.slug}`"
          class="flex flex-col items-center justify-center w-[100px] shrink-0 hover:-translate-y-0.5 p-2 rounded-lg transition"
        >
          <img
            :src="getImageUrl(item.image)"
            :alt="item.name"
            class="w-12 h-12 object-contain mb-1 rounded-2xl"
            loading="lazy"
            width="48"
            height="48"
          />
          <p class="text-xs text-center text-gray-700 leading-tight font-medium truncate">
            {{ item.name }}
          </p>
        </NuxtLink>

        <!-- Empty state -->
        <div v-if="!tags.length" class="text-sm text-gray-500">Không có tag nào.</div>
      </template>
    </div>

    <!-- Error -->
    <div v-if="error && !pending" class="px-6 pb-2 text-sm text-red-500">
      Lỗi khi tải tags: {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, onBeforeUnmount, computed, watch } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

const tags = ref([])
const pending = ref(true)
const error = ref(null)
const scrollContainer = ref(null)
const hasOverflow = ref(false)

const errorMessage = computed(() => error.value?.message || 'Không thể tải dữ liệu')

// Helpers
const getImageUrl = (path) => {
  if (!path) return '/default-image.jpg'
  return `${mediaBase}${path}`
}

const measureOverflow = () => {
  const el = scrollContainer.value
  if (!el) return
  hasOverflow.value = el.scrollWidth > el.clientWidth + 2
}

const onResize = () => measureOverflow()

// Scroll actions
const scrollLeft = () => {
  const el = scrollContainer.value
  if (!el) return
  el.scrollBy({ left: -200, behavior: 'smooth' })
}
const scrollRight = () => {
  const el = scrollContainer.value
  if (!el) return
  el.scrollBy({ left: 200, behavior: 'smooth' })
}

// Fetch
const fetchTags = async () => {
  try {
    pending.value = true
    error.value = null
    const res = await fetch(`${apiBase}/tags`)
    const data = await res.json()
    tags.value = data?.data?.tags || data?.data || data || []
  } catch (err) {
    error.value = err
    tags.value = []
  } finally {
    pending.value = false
    await nextTick()
    measureOverflow()
  }
}

watch(tags, async () => {
  await nextTick()
  measureOverflow()
})

onMounted(() => {
  fetchTags()
  window.addEventListener('resize', onResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', onResize)
})
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
