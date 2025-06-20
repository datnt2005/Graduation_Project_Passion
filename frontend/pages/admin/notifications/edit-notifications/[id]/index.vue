<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Chỉnh sửa thông báo</h1>
    <div class="px-6 pb-4">
      <nuxt-link to="/admin/notifications/list-notifications" class="text-gray-600 hover:underline text-sm">
        Danh sách thông báo
      </nuxt-link>
      <span class="text-gray-600 text-sm"> / Chỉnh sửa thông báo</span>
    </div>

    <main class="max-w-3xl mx-auto bg-white p-6 rounded shadow-sm">
      <form @submit.prevent="updateNotification">
        <div class="space-y-4">
          <!-- Các trường giống như create -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Tiêu đề</label>
            <input v-model="form.title" type="text" class="w-full border px-3 py-2 rounded" />
            <span v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Ảnh thông báo</label>
            <input type="file" accept="image/*" @change="handleImageUpload" class="w-full border px-3 py-2 rounded" />
            <span v-if="errors.image" class="text-xs text-red-500 mt-1">{{ errors.image }}</span>

            <div v-if="previewImage" class="mt-2">
              <img :src="previewImage" alt="Preview" class="w-32 h-32 object-cover rounded border" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nội dung</label>
            <textarea v-model="form.content" rows="4" class="w-full border px-3 py-2 rounded"></textarea>
            <span v-if="errors.content" class="text-xs text-red-500 mt-1">{{ errors.content }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Vai trò người nhận</label>
            <select v-model="form.to_role" class="w-full border px-3 py-2 rounded">
              <option disabled value="">-- Chọn vai trò --</option>
              <option value="user">Người dùng</option>
              <option value="seller">Người bán</option>
              <option value="admin">Admin</option>
            </select>
            <span v-if="errors.to_role" class="text-xs text-red-500 mt-1">{{ errors.to_role }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Loại thông báo</label>
            <select v-model="form.type" class="w-full border px-3 py-2 rounded">
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
            <input v-model="form.link" type="text" class="w-full border px-3 py-2 rounded" />
            <span v-if="errors.link" class="text-xs text-red-500 mt-1">{{ errors.link }}</span>
          </div>

          <button type="submit"
            class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50"
            :disabled="loading">
            {{ loading ? 'Đang lưu...' : 'Cập nhật thông báo' }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, useRuntimeConfig } from '#app'
import axios from 'axios'

definePageMeta({
  layout: 'default-admin'
})

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl || ''
const id = route.params.id

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

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/notifications/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const data = res.data  // ✅ CHỈ LÀ `data`, không phải `data.data`
    form.value = {
      title: data.title,
      content: data.content,
      to_role: data.to_role,
      type: data.type,
      link: data.link || ''
    }

    if (data.image_url) {
      previewImage.value = data.image_url
    }
  } catch (error) {
    console.error('Không thể load thông báo:', error)
  }
})


const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    previewImage.value = URL.createObjectURL(file)
  }
}

const updateNotification = async () => {
  loading.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('content', form.value.content)
    formData.append('to_role', form.value.to_role)
    formData.append('type', form.value.type)
    formData.append('link', form.value.link || '')

    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    const token = localStorage.getItem('access_token')
    await axios.post(`${apiBase}/notifications/${id}?_method=PUT`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        Authorization: `Bearer ${token}`
      }
    })

    router.push('/admin/notifications/list-notifications')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error('Lỗi cập nhật:', err)
    }
  } finally {
    loading.value = false
  }
}
</script>
