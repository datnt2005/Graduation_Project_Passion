<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="px-6 pt-6">
      <h1 class="text-xl font-semibold text-gray-800">Chỉnh sửa đánh giá</h1>
    </div>
    <div class="px-6 pb-4">
      <NuxtLink to="/admin/reviews/list-reviews" class="text-gray-600 hover:underline text-sm">
        Danh sách đánh giá
      </NuxtLink>
      <span class="text-gray-600 text-sm"> / Chỉnh sửa đánh giá</span>
    </div>

    <div class="flex min-h-screen bg-gray-100">
      <main class="flex-1 p-6">
        <div class="max-w-[1200px] mx-auto">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
              <!-- Cột trái -->
              <section class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Nội dung đánh giá</label>
                  <textarea :value="form.content" rows="4" readonly
                    class="w-full bg-gray-100 rounded border border-gray-300 px-3 py-2 text-sm text-gray-600" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Số sao</label>
                  <select :value="form.rating" disabled
                    class="w-full bg-gray-100 rounded border border-gray-300 px-3 py-2 text-sm text-gray-600">
                    <option v-for="i in 5" :key="i" :value="i">{{ i }} sao</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                  <select v-model="form.status"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500">
                    <option value="approved">Đã duyệt</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="rejected">Từ chối</option>
                  </select>
                  <span v-if="errors.status" class="text-xs text-red-500 mt-1">{{ errors.status }}</span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Phản hồi (tuỳ chọn)</label>
                  <textarea v-model="form.reply" rows="3"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:outline-none" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Ảnh đánh giá</label>
                  <div class="grid grid-cols-3 gap-2 mt-3">
                    <div v-for="(img, index) in previewImages" :key="img.id" class="relative">
                      <img :src="img.url" class="w-full h-24 object-cover rounded border" />
                    </div>
                  </div>
                </div>
              </section>

              <!-- Cột phải -->
              <aside class="bg-white rounded border border-gray-300 shadow-sm p-3 text-xs text-gray-700 space-y-3">
                <h2 class="font-semibold text-sm border-b pb-1">Thông tin sản phẩm</h2>
                <div v-if="product" class="flex gap-2 items-center">
                  <img :src="product.image" class="w-16 h-16 object-cover rounded border" />
                  <div>
                    <p class="font-medium">{{ product.name }}</p>
                    <p class="text-gray-500 text-xs">ID: {{ product.id }}</p>
                  </div>
                </div>

                <!-- Nút submit -->
                <div class="pt-4 mt-4 border-t border-gray-200">
                  <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 text-sm"
                    :disabled="loading">
                    {{ loading ? 'Đang lưu...' : 'Cập nhật phản hồi' }}
                  </button>
                </div>
              </aside>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, useRuntimeConfig } from '#app'
import axios from 'axios'
import { useNotification } from '~/composables/useNotification'
definePageMeta({ layout: 'default-admin' })

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const { showNotification } = useNotification()

const id = route.params.id
const form = ref({
  content: '',
  rating: '',
  status: '',
  reply: ''
})

const previewImages = ref([])
const product = ref(null)
const loading = ref(false)
const errors = ref({})

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/admin/reviews/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const data = res.data
    form.value.content = data.content
    form.value.rating = data.rating
    form.value.status = data.status
    form.value.reply = data.reply?.content || ''

    product.value = {
      id: data.product_id,
      name: data.product_name,
      image: data.images?.[0]?.url || ''
    }

    previewImages.value = data.images.map(img => ({
      id: img.id,
      url: img.url
    }))
  } catch (error) {
    console.error('Lỗi load đánh giá:', error)
    showNotification('Không thể tải đánh giá.', 'error')
  }
})

const submit = async () => {
  loading.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('status', form.value.status)
    formData.append('reply', form.value.reply)

    const token = localStorage.getItem('access_token')
    await axios.post(`${apiBase}/admin/reviews/${id}?_method=PUT`, formData, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    })

    showNotification('Cập nhật đánh giá thành công!', 'success')
    router.push('/admin/reviews/list-reviews')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      showNotification('Có lỗi khi cập nhật đánh giá.', 'error')
    }
  } finally {
    loading.value = false
  }
}
</script>
