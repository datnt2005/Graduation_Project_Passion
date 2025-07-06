<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <!-- Breadcrumb -->
    <div class="px-6 pt-6">
      <h1 class="text-xl font-semibold text-gray-800">Chỉnh sửa thông báo</h1>
    </div>
    <div class="px-6 pb-4">
      <NuxtLink to="/admin/notifications/list-notifications" class="text-gray-600 hover:underline text-sm">
        Danh sách thông báo
      </NuxtLink>
      <span class="text-gray-600 text-sm"> / Chỉnh sửa thông báo</span>
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
          <form @submit.prevent="updateNotification">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
              <!-- Left Column -->
              <section class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Tiêu đề</label>
                  <input v-model="form.title" type="text" placeholder="Nhập tiêu đề"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                  <span v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung</label>
                  <Editor v-model="form.content" api-key="..." :init="{
                    height: 300,
                    menubar: false,
                    plugins: 'lists link image preview',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignjustify alignleft aligncenter alignright | bullist numlist | removeformat | preview | link image | code',
                    entity_encoding: 'raw', // ✅ Cái này sẽ giữ nguyên ký tự
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

              <!-- Right Sidebar -->
              <aside class="bg-white rounded border border-gray-300 shadow-sm p-3 text-xs text-gray-700 space-y-3">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1">
                  <h2 class="font-semibold text-sm">Thiết lập thông báo</h2>
                </header>

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

                <!-- Vai trò người nhận -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Vai trò người nhận</label>
                  <div class="flex flex-col gap-1 mt-1">
                    <label v-for="role in ['user', 'seller']" :key="role" class="flex items-center gap-2 text-sm">
                      <input type="checkbox" :value="role" v-model="form.roles" />
                      {{ roleText(role) }}
                    </label>
                  </div>
                  <span v-if="errors.roles" class="text-xs text-red-500 mt-1">{{ errors.roles[0] }}</span>
                </div>

                <!-- Người nhận cụ thể -->
                <div class="border border-gray-200 rounded p-3 bg-gray-50 max-h-64 overflow-y-auto">
                  <h3 class="text-sm font-semibold mb-2 text-gray-700">Gửi đến người cụ thể</h3>

                  <!-- Ô tìm kiếm -->
                  <input v-model="searchUser" type="text" placeholder="Tìm theo tên hoặc email..."
                    class="w-full mb-2 rounded border border-gray-300 px-2 py-1 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />

                  <div v-if="filteredUsersWithSearch.length === 0" class="text-xs text-gray-500">
                    {{ form.roles.length === 0 ? 'Vui lòng chọn vai trò trước.' : 'Không tìm thấy người dùng.' }}
                  </div>

                  <div v-else class="flex flex-col gap-1 max-h-48 overflow-y-auto pr-1">
                    <label v-for="user in filteredUsersWithSearch" :key="user.id"
                      class="flex items-center gap-2 text-sm text-gray-700">
                      <input type="checkbox" :value="user.id" v-model="form.user_ids"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-300" />
                      <span>{{ user.name }} <span class="text-gray-400 text-xs">({{ user.email }})</span></span>
                    </label>
                  </div>

                  <span v-if="errors.user_ids" class="text-red-500 text-xs mt-1 block">{{ errors.user_ids }}</span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Kênh gửi</label>
                  <div class="flex flex-col gap-1 mt-1">
                    <label v-for="ch in ['web', 'email']" :key="ch" class="flex items-center gap-2 text-sm">
                      <input type="checkbox" :value="ch" v-model="form.channels" />
                      {{ channelText(ch) }}
                    </label>
                  </div>
                </div>


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

                <div class="pt-4 mt-4 border-t border-gray-200">
                  <div class="flex gap-2">
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 text-sm"
                      :disabled="loading">
                      {{ loading ? 'Đang lưu...' : 'Cập nhật thông báo' }}
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
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter, useRuntimeConfig } from '#app'
import axios from 'axios'
import Editor from '@tinymce/tinymce-vue'
import { useNotification } from '~/composables/useNotification'

definePageMeta({ layout: 'default-admin' })

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const { showNotification } = useNotification()

const id = route.params.id
const loading = ref(false)
const errors = ref({})
const fileInput = ref(null)
const removeOldImage = ref(false)
const imageFile = ref(null)
const previewImage = ref(null)

const searchUser = ref('')
const allUsers = ref([])

const form = ref({
  title: '',
  content: '',
  roles: [],
  user_ids: [], // Array of user IDs (number)
  type: '',
  link: '',
  channels: [],
  status: 'draft'
})

const triggerFileInput = () => fileInput.value?.click()

const handleImageUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    imageFile.value = file
    previewImage.value = URL.createObjectURL(file)
  }
}

const removeImage = () => {
  imageFile.value = null
  previewImage.value = null
  removeOldImage.value = true
}

const handleDrop = (e) => {
  const files = e.dataTransfer.files
  if (files.length) {
    handleImageUpload({ target: { files } })
  }
}

const fetchUsers = async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/user-list`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    allUsers.value = res.data.filter(u => u.role !== 'admin')
  } catch (err) {
    console.error('Lỗi khi lấy danh sách người dùng:', err)
  }
}

const initNotificationData = async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/notifications/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const data = res.data

    form.value = {
      ...form.value,
      title: data.title || '',
      content: data.content || '',
      type: data.type || '',
      link: data.link || '',
      roles: Array.isArray(data.to_roles) ? data.to_roles : [],
      channels: Array.isArray(data.channels) ? data.channels : [],
      status: data.status || 'draft',
      user_ids: [] // tạm thời rỗng, xử lý tiếp bên dưới
    }

    previewImage.value = data.image_url || null

    await fetchUsers()

    const ids = data.users?.map(u => u.id) || []
    form.value.user_ids = ids
  } catch (err) {
    console.error('Lỗi khi tải thông báo:', err)
  }
}

onMounted(initNotificationData)

const filteredUsers = computed(() => {
  if (form.value.roles.length === 0) return []
  return allUsers.value.filter(u => form.value.roles.includes(u.role))
})

const filteredUsersWithSearch = computed(() => {
  if (form.value.roles.length === 0) return []
  const keyword = searchUser.value.toLowerCase()
  return filteredUsers.value.filter(user =>
    user.name.toLowerCase().includes(keyword) ||
    user.email.toLowerCase().includes(keyword)
  )
})

watch(() => form.value.roles, (newRoles) => {
  form.value.user_ids = form.value.user_ids.filter(id => {
    const user = allUsers.value.find(u => u.id === id)
    return user && newRoles.includes(user.role)
  })
})

const updateNotification = async () => {
  loading.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('content', form.value.content)
    formData.append('type', form.value.type)
    formData.append('status', form.value.status)
    formData.append('link', form.value.link || '')
    formData.append('remove_image', removeOldImage.value ? '1' : '0')

    form.value.roles.forEach(role => formData.append('roles[]', role))
    form.value.channels.forEach(ch => formData.append('channels[]', ch))
    form.value.user_ids.forEach(uid => formData.append('user_ids[]', uid))

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

    showNotification('Cập nhật thông báo thành công!', 'success')
    router.push('/admin/notifications/list-notifications')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error('Lỗi khi cập nhật:', err)
      showNotification('Lỗi khi cập nhật thông báo.', 'error')
    }
  } finally {
    loading.value = false
  }
}

// Helper functions
const roleText = (role) => ({
  admin: 'Quản trị viên',
  seller: 'Người bán',
  user: 'Người dùng'
})[role] || role

const channelText = (channel) => ({
  web: 'Web',
  email: 'Email'
})[channel] || channel
</script>

