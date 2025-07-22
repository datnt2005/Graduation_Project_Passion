<template>
  <div class="bg-white border-t border-gray-200 py-3 mt-w-6xl">
    <div class="max-w-[1300px] mx-auto px-4">
      <h2 class="text-sm font-semibold text-gray-800 mb-2">Thương hiệu nổi bật</h2>
      <div
        v-if="!pending && !error && brands.length"
        class="flex flex-wrap text-xs text-center gap-x-2 gap-y-1"
      >
        <NuxtLink
          v-for="(brand, index) in brands"
          :key="brand.id || index"
          :to="`/seller/${brand.slug}`"
          class="text-gray-700 hover:text-blue-600 transition-colors duration-200 hover:underline relative after:content-['|'] after:ml-1 after:text-gray-300 whitespace-nowrap"
          :class="{ 'after:content-none': index === brands.length - 1 }"
        >
          {{ brand.name }}
        </NuxtLink>
      </div>
      <!-- Loading hoặc lỗi fallback -->
      <div v-if="pending" class="text-sm text-gray-500">Đang tải thương hiệu...</div>
      <div v-if="error" class="text-sm text-red-500">Lỗi khi tải thương hiệu: {{ error.message }}</div>
      <div v-if="!pending && !error && !brands.length" class="text-sm text-gray-500">
        Không có thương hiệu nổi bật.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const brands = ref([])
const pending = ref(true)
const error = ref(null)

// Fetch data from API
async function fetchBrands() {
  try {
    pending.value = true
    error.value = null
    const response = await $fetch(`${apiBase}/brands`, {
      method: 'GET',
    })
    if (response.success) {
      brands.value = response.data || []
    } else {
      throw new Error(response.message || 'Lỗi không xác định')
    }
  } catch (err) {
    console.error('Lỗi khi fetch thương hiệu:', err)
    error.value = err
  } finally {
    pending.value = false
  }
}

// Fetch data when component mounts
onMounted(() => {
  fetchBrands()
})
</script>