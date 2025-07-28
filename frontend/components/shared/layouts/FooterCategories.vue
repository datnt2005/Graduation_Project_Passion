<template>
  <div class="bg-gray-50 py-3">
    <div class="max-w-[1300px] mx-auto px-2">
      <h2 class="text-sm sm:text-base font-bold text-gray-800 mb-2 sm:mb-3">
        Danh mục sản phẩm
      </h2>

      <div
        v-if="!pending && !error && categoryGroups.length"
        class="grid grid-cols-6 gap-x-2 gap-y-1 text-[11px] sm:text-[12px] md:text-sm text-gray-700"
      >
        <div v-for="(categoryGroup, index) in categoryGroups" :key="index">
          <h3 class="font-semibold text-gray-900 mb-1 text-[12px] sm:text-sm">
            {{ categoryGroup.title }}
          </h3>
          <ul class="space-y-[2px]">
            <li v-for="(item, idx) in categoryGroup.items" :key="idx" class="truncate">
              <NuxtLink
                :to="`/shop/${item.slug}`"
                @click="() => trackCategoryClick(item.id)"
                class="hover:underline hover:text-blue-500 transition-all duration-150 relative after:content-['|'] after:mx-1 after:text-gray-300"
                :class="{ 'after:content-none': idx === categoryGroup.items.length - 1 }"
              >
                {{ item.name }}
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>
      <!-- Loading hoặc lỗi fallback -->
      <div v-if="pending" class="text-sm text-gray-500">Đang tải danh mục...</div>
      <div v-if="error" class="text-sm text-red-500">Lỗi khi tải danh mục: {{ error.message }}</div>
      <div v-if="!pending && !error && !categoryGroups.length" class="text-sm text-gray-500">
        Không có danh mục sản phẩm.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const categoryGroups = ref([])
const pending = ref(true)
const error = ref(null)

// Tracking click danh mục
const trackCategoryClick = async (categoryId) => {
  try {
    const token = localStorage.getItem('access_token')
    await $fetch(`${apiBase}/search/track-category-click`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: {
        category_id: categoryId,
      },
    })
    console.log(`Đã track click cho danh mục ID: ${categoryId}`)
  } catch (error) {
    console.error('Lỗi khi tracking danh mục:', error)
  }
}

// Fetch data from API
async function fetchCategories() {
  try {
    pending.value = true
    error.value = null
    const response = await $fetch(`${apiBase}/categories`, {
      method: 'GET',
    })
    if (response.success) {
      const categories = response.data?.data || []
      // Gom nhóm danh mục theo parent_id (nếu có) hoặc giữ nguyên nếu là cấp cao
      const groupedCategories = {}
      categories.forEach((cat) => {
        if (!cat.parent_id) {
          groupedCategories[cat.id] = {
            title: cat.name,
            items: [],
            id: cat.id
          }
        }
      })
      categories.forEach((cat) => {
        if (cat.parent_id) {
          const parent = categories.find((c) => c.id === cat.parent_id)
          if (parent) {
            groupedCategories[parent.id] = groupedCategories[parent.id] || {
              title: parent.name,
              items: [],
              id: parent.id
            }
            groupedCategories[parent.id].items.push({ 
              name: cat.name, 
              slug: cat.slug,
              id: cat.id 
            })
          } else {
            groupedCategories[cat.id] = {
              title: cat.name,
              items: [],
              id: cat.id
            }
          }
        }
      })
      categoryGroups.value = Object.values(groupedCategories)
    } else {
      throw new Error(response.message || 'Lỗi không xác định')
    }
  } catch (err) {
    console.error('Lỗi khi fetch danh mục:', err)
    error.value = err
  } finally {
    pending.value = false
  }
}

// Fetch data when component mounts
onMounted(() => {
  fetchCategories()
})
</script>
