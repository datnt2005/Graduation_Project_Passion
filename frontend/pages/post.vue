<template>
  <main class="bg-[#F5F5FA]">
    <div class="max-w-7xl mx-auto">
      <div class="text-sm text-gray-500 py-2">
        <NuxtLink to="/" class="text-gray-400">Trang chủ</NuxtLink>
        <span class="mx-1">›</span>
        <span class="text-black font-medium">Bài viết</span>
      </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 md:px-8 xl:px-12 mt-2 pb-6 md:pb-12 bg-white ">
      <div class="flex flex-col lg:flex-row gap-6 py-6">
        <!-- Main Content -->
        <main class="flex-1">
          <!-- Search and Filter -->

          <div class="mb-6 flex flex-col sm:flex-row justify-between items-stretch gap-3">
            <!-- Search Box -->
            <div class="w-full sm:w-1/2 relative">
              <input v-model="searchQuery" @input="debouncedSearch" type="text" placeholder="Tìm kiếm bài viết..."
                class="w-full py-2.5 pl-4 pr-12 rounded-lg border border-gray-200 bg-white text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 shadow-sm hover:shadow-md text-sm" />
              <!-- Clear Button -->
              <button v-if="searchQuery" @click="searchQuery = ''; debouncedSearch()"
                class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <!-- Search Icon -->
              <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto sm:items-center">
              <div class="relative w-full sm:w-40">
                <select v-model="selectedCategory" @change="fetchPosts(1); fetchRelated()"
                  class="appearance-none w-full py-2.5 px-3 rounded-lg border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 shadow-sm hover:shadow-md pr-8 text-sm">
                  <option value="">Tất cả danh mục</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
                <!-- Chevron Icon for Select -->
                <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>

              <div class="relative w-full sm:w-40">
                <select v-model="sortOption" @change="fetchPosts(1)"
                  class="appearance-none w-full py-2.5 px-3 rounded-lg border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 shadow-sm hover:shadow-md pr-8 text-sm">
                  <option value="created_at:desc">Mới nhất</option>
                  <option value="views:desc">Lượt xem cao</option>
                  <option value="title:asc">Tiêu đề A-Z</option>
                </select>
                <!-- Chevron Icon for Select -->
                <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>
          <!-- Loading State -->
          <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="n in 6" :key="n" class="bg-white rounded-xl shadow-sm p-4 animate-pulse">
              <div class="w-full h-48 bg-gray-200 rounded-lg mb-4"></div>
              <div class="space-y-3">
                <div class="h-5 bg-gray-200 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 rounded w-full"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
          </div>

          <!-- Post List -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="post in posts" :key="post.id"
              class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
              <NuxtLink :to="`/posts/${post.slug}`">
                <img :src="post.thumbnail_url || '/placeholder.jpg'" :alt="post.title"
                  class="w-full h-48 object-cover rounded-lg mb-4 transition-transform duration-300 hover:scale-[1.02]"
                  @error="onImageError" />
              </NuxtLink>
              <NuxtLink :to="`/posts/${post.slug}`">
                <h3 class="text-lg font-semibold text-gray-800 mb-2 hover:text-blue-500 transition-colors duration-200">
                  {{ post.title }}
                </h3>
              </NuxtLink>
              <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                {{ post.excerpt || post.description || 'Không có tóm tắt' }}
              </p>
              <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
                <span>Người đăng: {{ post.user?.name || '---' }}</span>
                <span>{{ post.category ? post.category.name : '---' }}</span>
              </div>
              <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
                <span>{{ formatDate(post.created_at) }}</span>
                <span>{{ post.views || 0 }} lượt xem</span>
              </div>
            </div>

            <div v-if="!loading && !posts.length" class="text-center text-gray-500 py-10 col-span-full">
              Chưa có bài viết nào.
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="mt-8 flex justify-center items-center gap-2">
            <button :disabled="currentPage === 1" @click="fetchPosts(currentPage - 1)"
              class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-300 transition-all duration-300">
              Trước
            </button>
            <button v-for="page in totalPages" :key="page" @click="fetchPosts(page)" :class="[
              'px-3 py-2 rounded-lg',
              currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300',
              'transition-all duration-300'
            ]">
              {{ page }}
            </button>
            <button :disabled="currentPage === totalPages" @click="fetchPosts(currentPage + 1)"
              class="px-4 py-2 bg-gray-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-300 transition-all duration-300">
              Sau
            </button>
          </div>
        </main>

        <aside class="w-full lg:w-80 flex-shrink-0">
          <div class="mb-6 bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <h3 class="font-bold text-lg text-gray-800 mb-3">Danh mục</h3>
            <ul class="space-y-1">
              <li>
                <button @click="selectCategory('')" :class="[
                  'w-full text-left block py-2 px-4 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300',
                  !selectedCategory ? 'bg-blue-50 text-blue-600 font-semibold' : ''
                ]">
                  Tất cả danh mục
                </button>
              </li>
              <li v-for="category in displayedCategories" :key="category.id">
                <button @click="selectCategory(category.id)" :class="[
                  'w-full text-left flex justify-between items-center py-2 px-4 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300',
                  selectedCategory === category.id ? 'bg-blue-50 text-blue-600 font-semibold' : ''
                ]">
                  <span>{{ category.name }}</span>
                  <span v-if="category.post_count" class="text-sm text-gray-500">{{ category.post_count }}</span>
                </button>
              </li>
              <li v-if="categories.length > displayedCategories.length">
                <NuxtLink to="/categories"
                  class="block py-2 px-4 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300">
                  Xem thêm
                </NuxtLink>
              </li>
            </ul>
          </div>
          <SidebarBlock title="Bài viết mới nhất" :posts="latestPosts" :loading="false" />
        </aside>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { debounce } from 'lodash'
import { useRuntimeConfig } from '#app'
import SidebarBlock from '~/components/posts/SidebarBlock.vue'

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
const latestPosts = ref([])
const relatedPosts = ref([])
const relatedLoading = ref(true)

// Computed property to limit displayed categories to 5
const displayedCategories = computed(() => {
  return categories.value.slice(0, 5)
})

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
    const res = await $fetch(`${apiBase}/post-categories`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    })
    categories.value = res.data.data.map(category => ({
      ...category,
      post_count: category.post_count || 0 // Adjust based on your API response
    })) || []
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

const fetchLatest = async () => {
  try {
    const res = await $fetch(`${apiBase}/posts?limit=5&status=published&sort=created_at:desc`, {
      headers: { 'Accept': 'application/json' }
    })
    latestPosts.value = res.data.data || []
  } catch (e) {
    console.error('Error fetching latest posts:', e)
    latestPosts.value = []
  }
}

const fetchRelated = async () => {
  relatedLoading.value = true
  try {
    let url = `${apiBase}/posts?status=published&limit=5`
    if (selectedCategory.value) url += `&category_id=${selectedCategory.value}`
    const res = await $fetch(url, {
      headers: { 'Accept': 'application/json' }
    })
    relatedPosts.value = res.data.data || []
  } catch (e) {
    console.error('Error fetching related posts:', e)
    relatedPosts.value = []
  } finally {
    relatedLoading.value = false
  }
}

const debouncedSearch = debounce(() => fetchPosts(1), 300)

// Function to handle category selection
const selectCategory = (categoryId) => {
  selectedCategory.value = categoryId
  fetchPosts(1)
  fetchRelated()
  scrollToTop()
}

onMounted(() => {
  fetchCategories()
  fetchPosts()
  fetchLatest()
  fetchRelated()
})
</script>

<style scoped>
/* Line clamp for text truncation */
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

/* Smooth transitions and hover effects */
button,
a {
  transition: all 0.3s ease-in-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Sidebar category styles */
aside .bg-white {
  transition: all 0.3s ease-in-out;
}

aside ul li button,
aside ul li a {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.95rem;
}

aside ul li button:hover,
aside ul li a:hover {
  transform: translateX(4px);
}

aside .font-semibold {
  background-color: #eff6ff;
  /* Tailwind's blue-50 */
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  aside {
    margin-top: 2rem;
  }
}

@media (max-width: 640px) {
  aside .bg-white {
    padding: 1rem;
  }

  aside h3 {
    font-size: 1.125rem;
  }

  aside ul li button,
  aside ul li a {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
  }

  .text-2xl {
    font-size: 1.25rem;
  }

  .h-80 {
    height: 12rem;
  }

  .h-48 {
    height: 10rem;
  }
}

select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

/* Tùy chỉnh dropdown option */
select option {
  padding: 8px 12px;
  background: #fff;
  color: #374151;
  /* gray-700 */
  font-size: 0.875rem;
  /* text-sm */
}

select option:hover {
  background: #eff6ff;
  /* blue-50 */
  color: #2563eb;
  /* blue-600 */
}

/* Custom scrollbar cho select */
select::-webkit-scrollbar {
  width: 6px;
}

select::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

select::-webkit-scrollbar-thumb {
  background: #9ca3af;
  /* gray-400 */
  border-radius: 4px;
}

select::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
  /* gray-500 */
}

/* Hiệu ứng hover và focus */
input:hover,
select:hover {
  border-color: #9ca3af;
  /* gray-400 */
  box-shadow: 0 0 0 3px rgba(209, 213, 219, 0.2);
  /* gray-300 */
}

input:focus,
select:focus {
  border-color: transparent;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
  /* blue-500 */
}

/* Animation khi dropdown mở ra */
select {
  transition: all 0.3s ease;
}

/* Đảm bảo select dropdown trông đẹp trên các trình duyệt */
select:focus+svg {
  transform: translateY(-50%) rotate(180deg);
  transition: transform 0.2s ease;
}
</style>