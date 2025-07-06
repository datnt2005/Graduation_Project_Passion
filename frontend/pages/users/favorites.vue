<template>
  <div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto bg-white rounded shadow p-6">
      <h1 class="text-2xl font-semibold mb-6 text-gray-800">Sản phẩm yêu thích của bạn</h1>

      <div v-if="loading" class="text-center py-8 text-gray-500">Đang tải...</div>

      <div v-else>
        <div v-if="favorites.length === 0" class="text-center text-gray-500 py-8">
          Bạn chưa có sản phẩm yêu thích nào.
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="item in favorites"
            :key="item.id"
            class="border rounded-lg p-4 flex gap-4 items-center bg-gray-50"
          >
            <img
              :src="item.product?.thumbnail || '/no-image.png'"
              alt="Ảnh sản phẩm"
              class="w-24 h-24 object-cover rounded border"
            />
            <div class="flex-1">
              <h2 class="font-semibold text-lg text-gray-800 mb-1">
                {{ item.product?.title || 'Không có tiêu đề' }}
              </h2>
              <p class="text-gray-600 text-sm mb-2 line-clamp-2">
                {{ item.product?.description || 'Không có mô tả' }}
              </p>
              <NuxtLink
                v-if="item.product"
                :to="`/products/${item.product?.id}`"
                class="text-green-600 hover:underline text-sm"
              >Xem chi tiết</NuxtLink>
            </div>
            <button
              v-if="item.product"
              @click="removeFavorite(item.product.id)"
              class="ml-2 px-3 py-1 rounded bg-red-100 text-red-600 hover:bg-red-200 transition"
              :disabled="submitting"
            >
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
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'
import { useToast } from '~/composables/useToast'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const favorites = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const success = ref('')
const { toast } = useToast()

// Lấy danh sách sản phẩm yêu thích
const fetchFavorites = async () => {
  loading.value = true
  error.value = ''
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      error.value = 'Bạn cần đăng nhập để xem danh sách yêu thích.'
      toast('info', 'Vui lòng đăng nhập!')
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

// Xóa sản phẩm khỏi danh sách yêu thích
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
