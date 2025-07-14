<template>
  <div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-800 text-center">Sản phẩm yêu thích của bạn</h1>

      <div class="flex justify-end mb-4">
        <div class="relative w-full md:w-1/3">
          <input
            v-model="filter"
            type="text"
            placeholder="Tìm kiếm sản phẩm yêu thích..."
            class="border rounded pl-10 pr-3 py-2 w-full focus:outline-none focus:ring focus:border-blue-300 transition"
          />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-2-2" />
          </svg>
        </div>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6 py-8">
        <div v-for="n in 2" :key="n" class="animate-pulse flex gap-4 items-center bg-gray-50 rounded-xl p-4">
          <div class="w-24 h-24 bg-gray-200 rounded-lg border"></div>
          <div class="flex-1 space-y-3">
            <div class="h-5 bg-gray-200 rounded w-2/3"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
          </div>
        </div>
      </div>

      <div v-else>
        <div v-if="filteredFavorites.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-500">
          <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5a4.5 4.5 0 00-9 0c0 4.5 4.5 7.5 4.5 7.5s4.5-3 4.5-7.5z" />
            <circle cx="12" cy="7.5" r="2" />
          </svg>
          Không tìm thấy sản phẩm yêu thích nào.
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="item in filteredFavorites"
            :key="item.id"
            class="border rounded-2xl p-4 flex gap-4 items-center bg-gray-50 hover:shadow-xl transition-shadow duration-200 group"
          >
            <img
              :src="getImageSrc(item.product?.image)"
              alt="Ảnh sản phẩm"
              class="w-24 h-24 object-cover rounded-xl border bg-white shadow group-hover:scale-105 transition-transform duration-200"
              @error="e => { e.target.src = '/no-image.png' }"
            />

            <div class="flex-1">
              <h2 class="font-semibold text-lg text-gray-800 mb-1 truncate">
                {{ item.product?.name || 'Không có tiêu đề' }}
              </h2>
              <p class="text-gray-600 text-sm mb-2 line-clamp-2" v-html="item.product?.fullDescription || 'Không có mô tả'"></p>
              <div v-if="item.product?.price" class="text-green-600 font-bold mb-1">
                {{ item.product.price.toLocaleString() }}₫
              </div>
              <div v-if="item.product?.shopName" class="text-xs text-gray-400 mb-1">
                <svg class="inline w-4 h-4 mr-1 -mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-2a4 4 0 014-4h10a4 4 0 014 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
                {{ item.product.shopName }}
              </div>
              <NuxtLink
                v-if="item.product"
                :to="`/products/${item.product.slug}`"
                class="text-blue-600 hover:underline text-sm font-medium"
              >
                Xem chi tiết
              </NuxtLink>
            </div>
            <button
              v-if="item.product"
              @click="removeFavorite(item.product.id)"
              class="ml-2 px-3 py-1 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition flex items-center gap-1 shadow-sm"
              :disabled="submitting"
              title="Xóa khỏi yêu thích"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Xóa
            </button>
          </div>
        </div>
      </div>
      <!-- Thông báo -->
      <div v-if="error" class="mt-6 text-red-600 text-center">{{ error }}</div>
      <div v-if="success" class="mt-6 text-green-600 text-center">{{ success }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'
import { useToast } from '~/composables/useToast'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

const favorites = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const success = ref('')
const filter = ref('')
const { toast } = useToast()

const fetchFavorites = async () => {
  loading.value = true
  error.value = ''
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      error.value = 'Bạn cần đăng nhập để xem danh sách yêu thích.'
      toast('info', error.value)
      loading.value = false
      return
    }
    const res = await $fetch(`${apiBase}/favorites`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    favorites.value = res.data || []
  } catch (e) {
    error.value = 'Không thể tải danh sách yêu thích.'
    toast('error', error.value)
  }
  loading.value = false
}

function getImageSrc(image) {
  if (!image) return '/no-image.png'
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
      error.value = 'Bạn cần đăng nhập để xoá khỏi yêu thích.'
      toast('info', error.value)
      submitting.value = false
      return
    }
    await $fetch(`${apiBase}/favorites/toggle`, {
      method: 'POST',
      body: { product_id: productId },
      headers: { Authorization: `Bearer ${token}` }
    })
    favorites.value = favorites.value.filter(item => item.product?.id !== productId)
    success.value = 'Đã xóa sản phẩm khỏi yêu thích.'
    toast('success', success.value)
    setTimeout(() => (success.value = ''), 1500)
  } catch (e) {
    error.value = 'Không thể xóa sản phẩm khỏi yêu thích.'
    toast('error', error.value)
  }
  submitting.value = false
}

const filteredFavorites = computed(() =>
  favorites.value.filter(item =>
    item.product?.name?.toLowerCase().includes(filter.value.trim().toLowerCase())
  )
)

onMounted(fetchFavorites)
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}
</style>
