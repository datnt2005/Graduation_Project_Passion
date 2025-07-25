<template>
  <div class="bg-white p-3 shadow rounded-md relative overflow-hidden">
    <!-- Nút điều hướng -->
    <button v-if="canNavigate" @click="scrollLeft"
      class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll left">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
          d="M12.707 15.707a1 1 0 01-1.414 0L6.586 11l4.707-4.707a1 1 0 111.414 1.414L9.414 11l3.293 3.293a1 1 0 010 1.414z"
          clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Nút điều hướng phải -->
    <button v-if="canNavigate" @click="scrollRight"
      class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white z-10 p-1 shadow rounded-full hover:bg-gray-100 transition"
      aria-label="Scroll right">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
          d="M7.293 4.293a1 1 0 011.414 0L13.414 9l-4.707 4.707a1 1 0 01-1.414-1.414L10.586 9 7.293 5.707a1 1 0 010-1.414z"
          clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Danh sách tag cuộn ngang -->
    <div ref="scrollContainer" class="flex gap-3 overflow-x-auto no-scrollbar scroll-smooth px-6 py-2">
      <NuxtLink v-for="(item, index) in tags" :key="item.id || index" :to="`/tags/${item.slug}`"
        class="flex flex-col items-center justify-center w-[100px] shrink-0 hover:-translate-y-0.5 p-2 rounded-lg transition">
        <img :src="`${mediaBase}${item.image}`" :alt="item.name" class="w-12 h-12 object-contain mb-1 rounded-2xl" />
        <p class="text-xs text-center text-gray-700 leading-tight font-medium truncate">
          {{ item.name }}
        </p>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

const tags = ref([])
const scrollContainer = ref(null)

const fetchTags = async () => {
  try {
    const response = await fetch(`${apiBase}/tags`)
    const data = await response.json()
    tags.value = data.data.tags
    console.log(tags.value)
  } catch (err) {
    console.error('Lỗi khi fetch tags:', err)
  }
}

const scrollLeft = () => {
  scrollContainer.value.scrollBy({
    left: -200,
    behavior: 'smooth',
  })
}

const scrollRight = () => {
  scrollContainer.value.scrollBy({
    left: 200,
    behavior: 'smooth',
  })
}

const canNavigate = computed(() => {
  return scrollContainer.value && scrollContainer.value.scrollWidth > scrollContainer.value.clientWidth
});


onMounted(fetchTags)
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
