<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <!-- Breadcrumb -->
    <div class="px-6 pt-6">
      <h1 class="text-xl font-semibold text-gray-800">Tạo thông báo</h1>
    </div>
    <div class="px-6 pb-4">
      <NuxtLink to="/admin/notifications/list-notifications" class="text-gray-600 hover:underline text-sm">
        Danh sách thông báo
      </NuxtLink>
      <span class="text-gray-600 text-sm"> / Tạo thông báo</span>
    </div>

    <!-- Layout -->
    <div class="flex min-h-screen bg-gray-100">
      <!-- Sidebar -->
      <nav class="w-64 bg-white border-r border-gray-200">
        <ul class="py-2">
          <li>
            <button
              class="flex items-center w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              Tổng quan
            </button>
          </li>
        </ul>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 p-6 bg-gray-100">
        <div class="max-w-[1200px] mx-auto">
          <form @submit.prevent="submitNotification('sent')">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
              <!-- Cột trái -->
              <section class="space-y-4">
                <!-- Tiêu đề -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Tiêu đề</label>
                  <input v-model="form.title" type="text" placeholder="Nhập tiêu đề"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                  <span v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</span>
                </div>

                <!-- Mô tả -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung</label>
                  <Editor v-model="form.content" api-key="rlas5j7eqa6dogiwnt1ld8iilzj3q074o4rw75lsxcygu1zd" :init="{
                    height: 300,
                    menubar: false,
                    plugins: 'lists link image preview',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignjustify alignleft aligncenter alignright | bullist numlist | removeformat | preview | link image | code'
                  }" />
                  <span v-if="errors.content" class="text-red-500 text-xs mt-1">{{ errors.content }}</span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Liên kết chuyển hướng</label>
                  <input v-model="form.link" type="text" placeholder="Nhập đường dẫn (VD: https://...)"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                  <span v-if="errors.link" class="text-xs text-red-500 mt-1">{{ errors.link }}</span>
                </div>
              </section>  
              

              <!-- Cột phải (sidebar) -->
              <aside class="bg-white rounded border border-gray-300 shadow-sm p-3 text-xs text-gray-700 space-y-3">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1">
                  <h2 class="font-semibold text-sm">Thiết lập thông báo</h2>
                </header>
                <!-- Ảnh -->
                <div class="border border-gray-300 rounded-md shadow-sm bg-white">
                  <header class="flex items-center justify-between border-b border-gray-300 pb-1 px-4 py-3">
                    <h2 class="font-semibold text-sm">Ảnh thông báo</h2>
                  </header>
                  <div class="p-4 space-y-3">
                    <div
                      class="relative flex items-center justify-center w-full max-w-xs p-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition"
                      @dragover.prevent @drop.prevent="handleDrop" @click="triggerFileInput">
                      <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
                      <div class="flex flex-col items-center text-center text-gray-500">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm">Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span>
                        </p>
                      </div>
                    </div>

                    <div v-if="previewImage" class="relative mt-2">
                      <img :src="previewImage" alt="Ảnh thông báo" class="w-full h-32 object-cover rounded" />
                      <button type="button"
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
                        @click="removeImage">
                        ×
                      </button>
                    </div>
                    <span v-if="errors.image" class="text-red-500 text-xs mt-1 block">{{ errors.image }}</span>
                  </div>
                </div>
                <!-- Vai trò -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Vai trò người nhận</label>
                  <select v-model="form.to_role"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option disabled value="">-- Chọn vai trò --</option>
                    <option value="user">Người dùng</option>
                    <option value="seller">Người bán</option>
                    <option value="admin">Admin</option>
                  </select>
                  <span v-if="errors.to_role" class="text-xs text-red-500 mt-1">{{ errors.to_role }}</span>
                </div>

                <!-- Loại -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Loại thông báo</label>
                  <select v-model="form.type"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option disabled value="">-- Chọn loại --</option>
                    <option value="order">Đơn hàng</option>
                    <option value="promotion">Khuyến mãi</option>
                    <option value="message">Tin nhắn</option>
                    <option value="system">Hệ thống</option>
                  </select>
                  <span v-if="errors.type" class="text-xs text-red-500 mt-1">{{ errors.type }}</span>
                </div>

                <!-- Nút gửi -->
                <div class="pt-4 mt-4 border-t border-gray-200">
                  <div class="flex gap-2">
                    <button type="button" @click="submitNotification('draft')"
                      class="w-full bg-gray-500 text-white py-2 rounded hover:bg-gray-600 text-sm"
                      :disabled="loading">Lưu nháp</button>
                    <button type="button" @click="submitNotification('sent')"
                      class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 text-sm" :disabled="loading">
                      {{ loading ? 'Đang gửi...' : 'Gửi thông báo' }}
                    </button>
                  </div>
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
import { ref } from 'vue'
import axios from 'axios'
import { useRouter, useRuntimeConfig } from '#app'
import Editor from '@tinymce/tinymce-vue'
import { useNotification } from '~/composables/useNotification'

definePageMeta({ layout: 'default-admin' })

const { showNotification } = useNotification()
const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl || ''

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
const fileInput = ref(null)

const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click()
  }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    previewImage.value = URL.createObjectURL(file)
  }
}

const removeImage = () => {
  imageFile.value = null
  previewImage.value = null
}

const handleDrop = (e) => {
  const files = e.dataTransfer.files
  if (files.length) {
    handleImageUpload({ target: { files } })
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
    formData.append('status', status)
    formData.append('link', form.value.link || '')
    if (imageFile.value) formData.append('image', imageFile.value)

    const token = localStorage.getItem('access_token')

    await axios.post(`${apiBase}/notifications`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`
      }
    })

    showNotification('Tạo thông báo thành công!', 'success') // ✅ THÊM DÒNG NÀY

    form.value = { title: '', content: '', to_role: '', type: '', link: '' }
    imageFile.value = null
    previewImage.value = null

    router.push('/admin/notifications/list-notifications')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error('Lỗi:', err)
      showNotification('Đã xảy ra lỗi, vui lòng thử lại.', 'error') // ✅ THÊM DÒNG NÀY
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
