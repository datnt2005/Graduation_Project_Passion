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
            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
            <select v-model="form.status"
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500">
              <option value="approved">Đã duyệt</option>
              <option value="pending">Chờ duyệt</option>
              <option value="rejected">Từ chối</option>
            </select>
            <p v-if="errors.status" class="text-xs text-red-500 mt-1">{{ errors.status }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phản hồi từ quản trị viên</label>
            <textarea v-model="form.reply" rows="3"
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none placeholder-gray-400"
              placeholder="Nhập nội dung phản hồi nếu cần" />
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

          <div class="pt-6 border-t">
            <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm font-medium"
              :disabled="loading">
              {{ loading ? 'Đang lưu...' : 'Cập nhật phản hồi' }}
            </button>
          </div>
        </aside>
      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, useRuntimeConfig } from '#app'
import axios from 'axios'
import { useNotification } from '~/composables/useNotification'

definePageMeta({ layout: 'default-seller' })

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
    const res = await axios.get(`${apiBase}/seller/reviews/${id}`, {
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

    previewImages.value = [
      ...data.images.map(img => ({ id: img.id, url: img.url, type: 'image' })),
      ...data.videos.map(vid => ({ url: vid, type: 'video' }))
    ]
  } catch (error) {
    console.error('Lỗi khi tải đánh giá:', error)
    showNotification('Không thể tải đánh giá.', 'error')
  }
})

const submit = async () => {
  loading.value = true
  errors.value = {}

  try {
    const token = localStorage.getItem('access_token')
    await axios.put(`${apiBase}/seller/reviews/${id}`, {
      status: form.value.status,
      reply: form.value.reply
    }, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })

    showNotification('Cập nhật đánh giá thành công!', 'success')
    router.push('/seller/reviews/list-reviews')
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
