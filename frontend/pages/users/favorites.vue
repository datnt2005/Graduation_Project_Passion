<template>
  <div class="bg-[#f5f7fa] text-[#1a1a1a] font-sans">
    <div class="max-w-7xl mx-auto md:pt-6 md:pb-6 flex flex-col md:flex-row gap-6">
      <SidebarProfile class="w-full md:w-[260px] bg-white rounded-xl shadow-sm border border-[#e0e6ed] p-4" />

      <main class="flex-1 p-0 md:p-4">
        <div>
          <h2 class="text-2xl sm:text-3xl text-center font-extrabold text-gray-900 mb-4">Sản phẩm yêu thích</h2>

          <section class="rounded-2xl shadow-sm border border-[#ececec] p-6 md:p-8 bg-white">
            <!-- Search -->
            <div class="flex justify-end mb-6">
              <div class="relative w-full md:w-1/3">
                <input v-model="filter" type="text" placeholder="Tìm kiếm sản phẩm yêu thích..."
                  class="w-full px-4 py-2 pl-10 rounded-lg border border-[#e0e6ed] bg-[#f9fbfd] focus:outline-none focus:ring-2 focus:ring-[#0b74e5] transition" />
                <font-awesome-icon :icon="['fas', 'magnifying-glass']"
                  class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[#637381]" />
              </div>
            </div>

            <!-- Empty -->
            <div v-if="!loading && paginatedFavorites.length === 0"
              class="flex flex-col items-center justify-center py-16 text-[#637381]">
              <font-awesome-icon :icon="['fas', 'heart-circle-xmark']" class="w-16 h-16 mb-4 text-[#e0e6ed]" />
              <p>Không tìm thấy sản phẩm yêu thích nào.</p>
            </div>

            <!-- List -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="item in paginatedFavorites" :key="item.id"
                class="border border-[#e0e6ed] rounded-2xl p-4 flex gap-4 items-center bg-[#f9fbfd] hover:shadow-xl transition-shadow duration-200 group relative">
                <img :src="getImageSrc(item.product?.image)" alt="Ảnh sản phẩm"
                  class="w-24 h-24 object-cover rounded-xl border bg-white shadow-sm group-hover:scale-105 transition-transform duration-200"
                  @error="e => { e.target.src = DEFAULT_IMAGE }" />
                <div class="flex-1">
                  <h2 class="font-semibold text-lg text-[#212b36] mb-1 truncate max-w-[220px]">
                    {{ item.product?.name || 'Không có tiêu đề' }}
                  </h2>
                  <p class="text-[#637381] text-sm mb-2 line-clamp-2"
                    v-html="item.product?.fullDescription || 'Không có mô tả'"></p>
                  <div v-if="item.product?.price" class="text-green-600 font-bold mb-1">
                    {{ item.product.price.toLocaleString() }}₫
                  </div>
                  <div v-if="item.product?.shopName" class="text-xs text-[#7a869a] mb-1 flex items-center">
                    <font-awesome-icon :icon="['fas', 'store']" class="w-4 h-4 mr-1" />
                    {{ item.product.shopName }}
                  </div>
                  <NuxtLink v-if="item.product" :to="`/products/${item.product.slug}`"
                    class="text-[#0b74e5] hover:underline text-sm font-medium">
                    Xem chi tiết
                  </NuxtLink>
                </div>
                <button class="absolute top-2 right-2 text-red-500 hover:text-red-600 transition" :disabled="submitting"
                  @click="removeFavorite(item.product.id)">
                  <font-awesome-icon :icon="['fas', 'heart']" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex justify-center mt-6 space-x-2 items-center text-sm font-medium">
              <!-- Nút Trước -->
              <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
                class="px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Trước
              </button>

              <!-- Các nút số trang -->
              <template v-for="page in totalPages" :key="page">
                <button @click="currentPage = page" class="px-3 py-1.5 rounded-md transition duration-200 border"
                  :class="currentPage === page
                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'">
                  {{ page }}
                </button>
              </template>

              <!-- Nút Sau -->
              <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Sau
              </button>
            </div>
          </section>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useHead, useRuntimeConfig } from '#app'
import { useToast } from '~/composables/useToast'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'

useHead({
  title: 'Sản phẩm yêu thích | Quản lý danh sách yêu thích',
  meta: [
    { name: 'description', content: 'Xem và quản lý danh sách sản phẩm yêu thích của bạn.' },
    { name: 'robots', content: 'noindex, nofollow' }
  ]
})

const { showSuccess, showError } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl
const DEFAULT_IMAGE = `${mediaBase}no-image.png`

const favorites = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const success = ref('')
const filter = ref('')
const currentPage = ref(1)
const itemsPerPage = 9

const fetchFavorites = async () => {
  loading.value = true
  error.value = ''
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      error.value = 'Bạn cần đăng nhập để xem danh sách yêu thích.'
      return
    }
    const res = await $fetch(`${apiBase}/favorites`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    favorites.value = res.data || []
  } catch (e) {
    error.value = 'Không thể tải danh sách yêu thích.'
    showError(error.value)
  } finally {
    loading.value = false
  }
}

const getImageSrc = (image) => {
  if (!image) return DEFAULT_IMAGE
  if (image.startsWith('http')) return image
  return `${mediaBase.replace(/\/$/, '')}/${image.replace(/^\//, '')}`
}

const removeFavorite = async (productId) => {
  error.value = ''
  success.value = ''
  submitting.value = true
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      error.value = 'Bạn cần đăng nhập để thao tác.'
      return
    }
    await $fetch(`${apiBase}/favorites/toggle`, {
      method: 'POST',
      body: { product_id: productId },
      headers: { Authorization: `Bearer ${token}` }
    })
    favorites.value = favorites.value.filter(item => item.product?.id !== productId)
    success.value = 'Đã xóa khỏi yêu thích.'
    showSuccess(success.value)
    setTimeout(() => (success.value = ''), 1500)
  } catch (e) {
    error.value = 'Không thể xóa sản phẩm khỏi yêu thích.'
    showError(error.value)
  } finally {
    submitting.value = false
  }
}

const filteredFavorites = computed(() =>
  favorites.value.filter(item =>
    item.product?.name?.toLowerCase().includes(filter.value.trim().toLowerCase())
  )
)

const totalPages = computed(() => Math.ceil(filteredFavorites.value.length / itemsPerPage))
const paginatedFavorites = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredFavorites.value.slice(start, start + itemsPerPage)
})

onMounted(fetchFavorites)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}
</style>
