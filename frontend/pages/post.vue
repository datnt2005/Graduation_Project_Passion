<template>
  <main class="flex-1 p-4 md:p-8 bg-gray-50 max-w-7xl mx-auto">
    <!-- Search and Filter -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
      <div class="w-full md:w-1/2 relative">
        <input
          v-model="searchQuery"
          @input="debouncedSearch"
          type="text"
          placeholder="Tìm kiếm bài viết..."
          class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <svg
          class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <div class="flex items-center gap-4">
        <select
          v-model="sortOption"
          @change="fetchPosts(1)"
          class="p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="created_at:desc">Mới nhất</option>
          <option value="views:desc">Lượt xem cao nhất</option>
          <option value="title:asc">Tiêu đề A-Z</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-10 text-gray-500">Đang tải...</div>

    <!-- Featured Post -->
    <div v-if="posts.length && posts[0]" class="flex flex-col md:flex-row items-start border-b border-gray-200 pb-8 mb-8 md:gap-x-8 bg-white rounded-lg shadow-md p-6">
      <div class="w-full md:w-1/2 md:pr-8 mb-6 md:mb-0 flex-shrink-0">
        <NuxtLink :to="`/posts/${posts[0].slug}`">
          <img
            :src="posts[0].thumbnail_url || '/placeholder.jpg'"
            alt="Featured Post"
            class="w-full h-64 rounded-lg shadow-md object-cover transition-transform duration-300 hover:scale-105"
            @error="onImageError"
          />
        </NuxtLink>
      </div>
      <div class="w-full md:w-1/2 md:ml-8">
        <NuxtLink :to="`/posts/${posts[0].slug}`">
          <h2 class="text-2xl font-bold text-gray-800 mb-3 hover:text-blue-600 transition">
            {{ posts[0].title }}
          </h2>
        </NuxtLink>
        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
          {{ posts[0].excerpt || posts[0].description || 'Không có tóm tắt' }}
        </p>
        <div class="flex justify-between items-center text-xs text-gray-500 mb-4">
          <span>Người đăng: {{ posts[0].user?.name || '---' }}</span>
          <span>{{ posts[0].category ? posts[0].category.name : '' }}</span> <!-- Sửa lỗi category -->
        </div>
        <div class="flex justify-between items-center text-xs text-gray-500 mb-4">
          <span>{{ formatDate(posts[0].created_at) }}</span>
          <span>{{ posts[0].views || 0 }} lượt xem</span> <!-- Đảm bảo hiển thị views -->
        </div>
        <ul v-if="posts[0].tags && posts[0].tags.length" class="flex flex-wrap gap-2">
          <li v-for="tag in posts[0].tags" :key="tag" class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">{{ tag }}</li>
        </ul>
      </div>
    </div>

    <!-- List Posts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-12">
      <div
        v-for="post in posts.slice(1)"
        :key="post.id"
        class="flex items-start pb-6 border-b border-gray-200 bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow"
      >
        <NuxtLink :to="`/posts/${post.slug}`">
          <img
            :src="post.thumbnail_url || '/placeholder.jpg'"
            alt="Article Image"
            class="w-32 h-20 object-cover rounded-md flex-shrink-0 mr-4 transition-transform duration-300 hover:scale-105"
            @error="onImageError"
          />
        </NuxtLink>
        <div class="flex-1">
          <NuxtLink :to="`/posts/${post.slug}`">
            <h3 class="text-lg font-semibold text-gray-800 mb-1 hover:text-blue-600 transition">
              {{ post.title }}
            </h3>
          </NuxtLink>
          <p class="text-gray-600 text-sm line-clamp-2 mb-2">
            {{ post.excerpt || post.description || 'Không có tóm tắt' }}
          </p>
          <div class="flex justify-between items-center text-xs text-gray-500 mb-1">
            <span>Người đăng: {{ post.user?.name || '---' }}</span>
            <span>{{ post.category ? post.category.name : '' }}</span> <!-- Sửa lỗi category -->
          </div>
          <div class="flex justify-between items-center text-xs text-gray-500">
            <span>{{ formatDate(post.created_at) }}</span>
            <span>{{ post.views || 0 }} lượt xem</span> <!-- Đảm bảo hiển thị views -->
          </div>
        </div>
      </div>
      <div v-if="!loading && !posts.length" class="text-center text-gray-500 py-10 col-span-full">Chưa có bài viết nào.</div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="mt-8 flex justify-center items-center gap-2">
      <button
        :disabled="currentPage === 1"
        @click="fetchPosts(currentPage - 1)"
        class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Trước
      </button>
      <button
        v-for="page in totalPages"
        :key="page"
        @click="fetchPosts(page)"
        :class="['px-3 py-2 rounded-lg', { 'bg-blue-600 text-white': currentPage === page, 'bg-gray-200': currentPage !== page }]"
      >
        {{ page }}
      </button>
      <button
        :disabled="currentPage === totalPages"
        @click="fetchPosts(currentPage + 1)"
        class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Sau
      </button>
    </div>

    <!-- Back to Top Button -->
    <button
      v-if="posts.length"
      @click="scrollToTop"
      class="fixed bottom-4 right-4 p-6 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg transition-colors duration-300"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7 6m0 0l7-6" />
      </svg>
    </button>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { debounce } from 'lodash'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const posts = ref([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const searchQuery = ref('')
const selectedCategory = ref('')
const sortOption = ref('created_at:desc')
const categories = ref([])

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('vi-VN', { year: 'numeric', month: 'long', day: 'numeric' })
}

const onImageError = (event) => {
  event.target.src = '/placeholder.jpg'
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const fetchCategories = async () => {
  try {
    const res = await $fetch(`${apiBase}/categories`, {
      headers: { 'Accept': 'application/json' }
    })
    categories.value = res.data || []
  } catch (e) {
    console.error('Error fetching categories:', e)
  }
}

const fetchPosts = async (page = 1) => {
  loading.value = true
  try {
    const queryParams = new URLSearchParams({
      status: 'published',
      page,
      ...(searchQuery.value && { search: searchQuery.value }),
      ...(selectedCategory.value && { category_id: selectedCategory.value }),
      ...(sortOption.value && { sort: sortOption.value })
    })
    const res = await $fetch(`${apiBase}/posts?${queryParams.toString()}`, {
      headers: { 'Accept': 'application/json' }
    })
    posts.value = res.data.data || []
    currentPage.value = res.data.current_page || 1
    totalPages.value = res.data.last_page || 1
  } catch (e) {
    posts.value = []
    console.error('Error fetching posts:', e)
  } finally {
    loading.value = false
  }
}

const debouncedSearch = debounce(() => fetchPosts(1), 300)

onMounted(() => {
  fetchCategories()
  fetchPosts()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>