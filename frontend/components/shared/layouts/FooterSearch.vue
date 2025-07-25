<template>
  <div class="bg-white py-3">
    <div class="max-w-[1300px] mx-auto px-4">
      <h2 class="text-sm font-semibold text-gray-800 mb-1">Từ khóa phổ biến</h2>
      <div
        v-if="!pending && !error && keywords.length"
        class="flex flex-wrap text-xs text-gray-600 gap-x-2 gap-y-1"
      >
        <NuxtLink
          v-for="(term, index) in keywords"
          :key="index"
          :to="`/shop/search?search=${term}`"
          class="text-gray-700 hover:text-blue-600 transition-colors duration-200 hover:underline relative after:content-['|'] after:ml-1 after:text-gray-400"
          :class="{ 'after:content-none': index === keywords.length - 1 }"
        >
          {{ term }}
        </NuxtLink>
      </div>
      <!-- Loading hoặc lỗi fallback -->
      <div v-if="pending" class="text-sm text-gray-500">Đang tải từ khóa...</div>
      <div v-if="error" class="text-sm text-red-500">Lỗi khi tải từ khóa: {{ error.message }}</div>
      <div v-if="!pending && !error && !keywords.length" class="text-sm text-gray-500">
        Không có từ khóa phổ biến.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const keywords = ref([])
const pending = ref(true)
const error = ref(null)

// Default keywords (fallback)
const defaultKeywords = [
  'áo thun nam',
  'váy nữ đẹp',
  'iphone 15 pro max',
  'tai nghe bluetooth',
  'nồi chiên không dầu',
  'giày sneaker',
  'nước hoa nam',
  'kem chống nắng',
  'bàn phím cơ',
  'máy hút bụi',
  'đồng hồ nữ',
  'sách tâm lý học',
]

// Fetch data from API
async function fetchKeywords() {
  try {
    pending.value = true
    error.value = null
    const response = await $fetch(`${apiBase}/search/suggestions`, {
      method: 'GET',
    })
    if (response.success) {
      keywords.value = response.data.top_keywords || []
    } else {
      throw new Error(response.message || 'Lỗi không xác định')
    }
  } catch (err) {
    console.error('Lỗi khi fetch từ khóa:', err)
    keywords.value = defaultKeywords // Fallback to default keywords
    error.value = err
  } finally {
    pending.value = false
  }
}

// Fetch data when component mounts
onMounted(() => {
  fetchKeywords()
})
</script>

<style scoped>
/* Không cần style bổ sung vì giao diện đã ổn */
</style>