<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <!-- Breadcrumb -->
    <div class="px-6 pt-6">
      <h1 class="text-xl font-semibold text-gray-800">Chỉnh sửa đánh giá</h1>
    </div>
    <div class="px-6 pb-4">
      <NuxtLink to="/seller/reviews/list-reviews" class="text-gray-600 hover:underline text-sm">
        Danh sách đánh giá
      </NuxtLink>
      <span class="text-gray-600 text-sm"> / Chỉnh sửa đánh giá</span>
    </div>

    <main class="px-6 pb-12">
      <form @submit.prevent="submit" class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
        <!-- Cột nội dung đánh giá -->
        <section class="bg-white p-6 rounded shadow space-y-4 border">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung đánh giá</label>
            <textarea :value="form.content" readonly
              class="w-full bg-gray-100 rounded border border-gray-300 px-3 py-2 text-sm text-gray-600" rows="4" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Số sao</label>
            <select :value="form.rating" disabled
              class="w-full bg-gray-100 rounded border border-gray-300 px-3 py-2 text-sm text-gray-600">
              <option v-for="i in 5" :key="i" :value="i">{{ i }} sao</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phản hồi từ người bán</label>
            <textarea v-model="form.reply" rows="3"
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none placeholder-gray-400"
              placeholder="Nhập nội dung phản hồi nếu cần" />
            <p v-if="errors.reply" class="text-xs text-red-500 mt-1">{{ errors.reply }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ảnh/Video đính kèm</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div v-for="(item, index) in previewImages" :key="item.id" class="relative">
                <img v-if="item.type === 'image'" :src="item.url" class="w-full h-24 object-cover rounded border" />
                <video v-else controls class="w-full h-24 object-cover rounded border">
                  <source :src="item.url" type="video/mp4" />
                  Trình duyệt không hỗ trợ video.
                </video>
              </div>
              <div v-if="previewImages.length === 0" class="text-sm text-gray-500 italic">
                Không có ảnh hoặc video đính kèm.
              </div>
            </div>
          </div>
        </section>

        <!-- Cột thông tin phụ -->
        <aside class="bg-white p-6 rounded shadow border space-y-4 text-sm text-gray-700">
          <h2 class="font-semibold text-base border-b pb-2">Thông tin sản phẩm</h2>
          <div v-if="product" class="flex items-center gap-3">
            <img :src="product.image" class="w-16 h-16 object-cover rounded border" />
            <div>
              <p class="font-medium">{{ product.name }}</p>
              <p class="text-xs text-gray-500">ID: {{ product.id }}</p>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500 italic">Không có thông tin sản phẩm.</div>

          <div class="pt-6 border-t">
            <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm font-medium"
              :disabled="loading">
              {{ loading ? 'Đang lưu...' : 'Cập nhật phản hồi' }}
            </button>
          </div>
        </aside>
      </form>

      <!-- Notification Popup -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <div
            v-if="showNotification"
            class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-[1000]"
          >
            <div class="flex-shrink-0">
              <svg
                class="h-6 w-6"
                :class="notificationType === 'success' ? 'text-green-600' : 'text-red-600'"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  v-if="notificationType === 'success'"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
                <path
                  v-if="notificationType === 'error'"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm text-gray-900">{{ notificationMessage }}</p>
            </div>
            <div class="flex-shrink-0">
              <button
                @click="showNotification = false"
                class="p-1 text-gray-500 hover:text-gray-700 focus:outline-none"
              >
                <svg
                  class="h-4 w-4"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </Transition>
      </Teleport>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, useRuntimeConfig, useCookie } from '#app'
import axios from 'axios'
import { useNotification } from '~/composables/useNotification'

definePageMeta({ layout: 'default-seller' })

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const { showNotification, notificationMessage, notificationType, showMessage } = useNotification()

const id = route.params.id
const form = ref({
  content: '',
  rating: '',
  reply: ''
})
const previewImages = ref([])
const product = ref(null)
const loading = ref(false)
const errors = ref({})

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    console.log('Fetching review with token:', token) // Debug
    const res = await axios.get(`${apiBase}/seller/reviews/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    console.log('Review data:', res.data) // Debug
    const data = res.data
    form.value.content = data.content
    form.value.rating = data.rating
    form.value.reply = data.reply?.content || ''

    product.value = {
      id: data.product_id,
      name: data.product_name,
      image: data.images?.[0]?.url || ''
    }

    previewImages.value = [
      ...data.images.map(img => ({ id: img.id, url: img.url, type: 'image' })),
      ...data.videos.map(vid => ({ url: vid, type: 'video' }))
    ]
  } catch (error) {
    console.error('Lỗi khi tải đánh giá:', error)
    showMessage('Không thể tải đánh giá.', 'error')
  }
})

const submit = async () => {
  loading.value = true
  errors.value = {}

  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    console.log('Submitting reply:', form.value.reply, 'with token:', token) // Debug
    const response = await axios.put(`${apiBase}/seller/reviews/${id}`, {
      reply: form.value.reply
    }, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })

    console.log('Update response:', response.data) // Debug
    showMessage('Cập nhật phản hồi thành công!', 'success')
    router.push('/seller/reviews/list-reviews')
  } catch (err) {
    console.error('Error updating review:', err.response?.data || err.message) // Debug
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
      showMessage('Dữ liệu không hợp lệ, vui lòng kiểm tra lại.', 'error')
    } else {
      showMessage('Có lỗi khi cập nhật phản hồi.', 'error')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Đảm bảo container không ẩn toast */
.bg-gray-100 {
  overflow: visible !important;
}
</style>