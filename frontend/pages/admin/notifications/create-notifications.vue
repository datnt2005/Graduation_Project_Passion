<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Tạo thông báo</h1>
    <div class="px-6 pb-4">
      <nuxt-link to="/admin/notifications/list-notifications" class="text-gray-600 hover:underline text-sm">
        Danh sách thông báo
      </nuxt-link>
      <span class="text-gray-600 text-sm"> / Tạo thông báo</span>
    </div>

    <main class="max-w-3xl mx-auto bg-white p-6 rounded shadow-sm">
      <form @submit.prevent="createNotification">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Tiêu đề</label>
            <input v-model="form.title" type="text" placeholder="Nhập tiêu đề thông báo"
              class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" />
            <span v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Ảnh thông báo</label>
            <input type="file" accept="image/*" @change="handleImageUpload"
              class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" />
            <span v-if="errors.image" class="text-xs text-red-500 mt-1">{{ errors.image }}</span>

            <div v-if="previewImage" class="mt-2">
              <img :src="previewImage" alt="Preview" class="w-32 h-32 object-cover rounded border" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nội dung</label>
            <textarea v-model="form.content" rows="4" placeholder="Nhập nội dung thông báo"
              class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300"></textarea>
            <span v-if="errors.content" class="text-xs text-red-500 mt-1">{{ errors.content }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Vai trò người nhận</label>
            <select v-model="form.to_role" class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300">
              <option disabled value="">-- Chọn vai trò --</option>
              <option value="user">Người dùng</option>
              <option value="seller">Người bán</option>
              <option value="admin">Admin</option>
            </select>
            <span v-if="errors.to_role" class="text-xs text-red-500 mt-1">{{ errors.to_role }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Loại thông báo</label>
            <select v-model="form.type" class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300">
              <option disabled value="">-- Chọn loại --</option>
              <option value="order">Đơn hàng</option>
              <option value="promotion">Khuyến mãi</option>
              <option value="message">Tin nhắn</option>
              <option value="system">Hệ thống</option>
            </select>
            <span v-if="errors.type" class="text-xs text-red-500 mt-1">{{ errors.type }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Liên kết (tuỳ chọn)</label>
            <input v-model="form.link" type="text" placeholder="https://example.com/page"
              class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" />
            <span v-if="errors.link" class="text-xs text-red-500 mt-1">{{ errors.link }}</span>
          </div>

          <div class="flex gap-3">
            <button type="button" @click="submitNotification('draft')"
              class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 disabled:opacity-50"
              :disabled="loading">
              Lưu nháp
            </button>
            <button type="button" @click="submitNotification('sent')"
              class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50"
              :disabled="loading">
              {{ loading ? 'Đang gửi...' : 'Gửi thông báo' }}
            </button>
          </div>
        </div>
      </form>

      <!-- Thông báo Toast -->
      <Teleport to="body">
        <Transition name="fade">
          <div v-if="showNotification"
            class="fixed bottom-5 right-5 bg-white border px-4 py-3 rounded shadow-lg flex items-center space-x-2">
            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
            </svg>
            <p class="text-sm text-gray-800">{{ notificationMessage }}</p>
            <button @click="showNotification = false" class="text-gray-400 hover:text-gray-700 ml-2">&times;</button>
          </div>
        </Transition>
      </Teleport>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useNotification } from '#imports'
import { useRouter, useRuntimeConfig } from '#app'

definePageMeta({
  layout: 'default-admin'
})

const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl || '' // fallback nếu không có API_BASE_URL

const form = ref({
  title: '',
  content: '',
  to_role: '',
  type: '',
  link: ''
})

const imageFile = ref(null)
const previewImage = ref(null)

const errors = ref({})
const loading = ref(false)
const showNotification = ref(false)
const notificationMessage = ref('')

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    previewImage.value = URL.createObjectURL(file)
  }
}

const submitNotification = async (status) => {
  loading.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('content', form.value.content)
    formData.append('to_role', form.value.to_role)
    formData.append('type', form.value.type)
    formData.append('status', status) // << 👈 Gửi draft hoặc sent
    formData.append('link', form.value.link || '')

    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    const token = localStorage.getItem('access_token')

    await axios.post(`${apiBase}/notifications`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`
      }
    })

    notificationMessage.value = status === 'draft'
      ? 'Đã lưu nháp thông báo.'
      : 'Gửi thông báo thành công!'

    showNotification.value = true

    // Reset form
    form.value = { title: '', content: '', to_role: '', type: '', link: '' }
    imageFile.value = null
    previewImage.value = null

    setTimeout(() => {
      showNotification.value = false
      router.push('/admin/notifications/list-notifications')
    }, 1000)
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error('Lỗi không xác định:', err)
    }
  } finally {
    loading.value = false
  }
}


</script>


<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
