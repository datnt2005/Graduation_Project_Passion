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
              class="flex items-center w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900"
            >
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
                  <input
                    v-model="form.title"
                    type="text"
                    placeholder="Nhập tiêu đề"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    @input="validateForm"
                  />
                  <span v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung</label>
                  <TiptapEditor
                    v-model="form.content"
                    class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    @input="validateForm"
                  />
                  <span v-if="errors.content" class="text-red-500 text-xs mt-1">{{ errors.content }}</span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Liên kết chuyển hướng</label>
                  <input
                    v-model="form.link"
                    type="text"
                    placeholder="Nhập đường dẫn (VD: https://...)"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    @input="validateForm"
                  />
                  <span v-if="errors.link" class="text-xs text-red-500 mt-1">{{ errors.link }}</span>
                </div>
              </section>

              <!-- Right Sidebar -->
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
                      @dragover.prevent
                      @drop.prevent="handleDrop"
                      @click="triggerFileInput"
                    >
                      <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="handleImageUpload"
                      />
                      <div class="flex flex-col items-center text-center text-gray-500">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                          />
                        </svg>
                        <p class="text-sm">
                          Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span>
                        </p>
                      </div>
                    </div>

                    <div v-if="previewImage" class="relative mt-2">
                      <img :src="previewImage" alt="Ảnh thông báo" class="w-full h-32 object-cover rounded" />
                      <button
                        type="button"
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
                        @click="removeImage"
                      >
                        ×
                      </button>
                    </div>
                    <span v-if="errors.image" class="text-red-500 text-xs mt-1 block">{{ errors.image }}</span>
                  </div>
                </div>

                <!-- Vai trò người nhận -->
                <div class="border border-gray-200 rounded p-3 bg-gray-50">
                  <h3 class="text-sm font-semibold mb-2 text-gray-700">Vai trò người nhận</h3>
                  <div class="flex flex-col gap-2 pl-1">
                    <label
                      v-for="role in ['user', 'seller']"
                      :key="role"
                      class="inline-flex items-center gap-2"
                    >
                      <input
                        type="checkbox"
                        :value="role"
                        v-model="form.roles"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-300"
                      />
                      {{ roleText(role) }}
                    </label>
                  </div>
                  <span v-if="errors.roles" class="text-red-500 text-xs mt-1">{{ errors.roles[0] }}</span>
                </div>

                <!-- Người nhận cụ thể -->
                <div class="border border-gray-200 rounded p-3 bg-gray-50 max-h-64 overflow-y-auto">
                  <h3 class="text-sm font-semibold mb-2 text-gray-700">Gửi đến người cụ thể</h3>
                  <input
                    v-model="searchUser"
                    type="text"
                    placeholder="Tìm theo tên hoặc email..."
                    class="w-full mb-2 rounded border border-gray-300 px-2 py-1 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                  <div v-if="filteredUsers.length === 0" class="text-xs text-gray-500">
                    {{ form.roles.length === 0 ? 'Vui lòng chọn vai trò trước.' : 'Không tìm thấy người dùng.' }}
                  </div>
                  <div v-else class="flex flex-col gap-1 max-h-48 overflow-y-auto pr-1">
                    <label
                      v-for="user in filteredUsersWithSearch"
                      :key="user.id"
                      class="flex items-center gap-2 text-sm text-gray-700"
                    >
                      <input
                        type="checkbox"
                        :value="user.id"
                        v-model="form.user_ids"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-300"
                      />
                      <span>{{ user.name }} <span class="text-gray-400 text-xs">({{ user.email }})</span></span>
                    </label>
                  </div>
                  <span v-if="errors.user_ids" class="text-red-500 text-xs mt-1 block">{{ errors.user_ids }}</span>
                </div>

                <!-- Kênh gửi -->
                <div class="border border-gray-200 rounded p-3 bg-gray-50">
                  <h3 class="text-sm font-semibold mb-2 text-gray-700">Kênh gửi thông báo</h3>
                  <div class="flex flex-col gap-2 pl-1">
                    <label
                      v-for="ch in ['web', 'email']"
                      :key="ch"
                      class="inline-flex items-center gap-2"
                    >
                      <input
                        type="checkbox"
                        :value="ch"
                        v-model="form.channels"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-300"
                      />
                      {{ channelText(ch) }}
                    </label>
                  </div>
                  <span v-if="errors.channels" class="text-red-500 text-xs mt-1">{{ errors.channels[0] }}</span>
                </div>

                <!-- Loại -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Loại thông báo</label>
                  <select
                    v-model="form.type"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                    @change="validateForm"
                  >
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
                    <button
                      type="submit"
                      class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 text-sm"
                      :disabled="loading || Object.keys(errors).length > 0"
                    >
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
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-[100]"
        >
          <div class="flex-shrink-0">
            <svg
              class="h-6 w-6"
              :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
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
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button
              @click="showNotification = false"
              class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
            >
              <svg
                class="h-5 w-5"
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
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter, useRuntimeConfig, useCookie } from '#app'
import TiptapEditor from '@/components/TiptapEditor.vue'
import { secureFetch } from '@/utils/secureFetch'

definePageMeta({ layout: 'default-admin' })

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl || ''
const mediaBase = config.public.mediaBaseUrl || ''

const id = route.params.id
const loading = ref(false)
const errors = ref({})
const fileInput = ref(null)
const removeOldImage = ref(false)
const imageFile = ref(null)
const previewImage = ref(null)
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
const searchUser = ref('')
const allUsers = ref([])

const form = ref({
  title: '',
  content: '',
  roles: [],
  user_ids: [],
  type: '',
  link: '',
  channels: [],
  status: 'draft'
})

// Hàm hiển thị thông báo
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => {
    showNotification.value = false
  }, 3000)
}

// Xác thực form
const validateForm = () => {
  errors.value = {}
  if (!form.value.title.trim()) {
    errors.value.title = 'Tiêu đề không được để trống'
  } else if (form.value.title.length > 255) {
    errors.value.title = 'Tiêu đề không được vượt quá 255 ký tự'
  }
  if (!form.value.content.trim()) {
    errors.value.content = 'Nội dung không được để trống'
  } else if (form.value.content.length > 5000) {
    errors.value.content = 'Nội dung không được vượt quá 5000 ký tự'
  }
  if (form.value.link && !/^https?:\/\//.test(form.value.link)) {
    errors.value.link = 'Liên kết phải bắt đầu bằng http:// hoặc https://'
  }
  if (!form.value.type) {
    errors.value.type = 'Vui lòng chọn loại thông báo'
  }
  if (form.value.roles.length === 0 && form.value.user_ids.length === 0) {
    errors.value.roles = ['Vui lòng chọn ít nhất một vai trò hoặc người dùng cụ thể']
  }
  if (form.value.channels.length === 0) {
    errors.value.channels = ['Vui lòng chọn ít nhất một kênh gửi']
  }
}

// Tải danh sách người dùng
const fetchUsers = async () => {
  try {
    const data = await secureFetch(`${apiBase}/user-list`, {
      cache: 'no-store'
    }, ['admin'])
    allUsers.value = data.filter(u => u.role !== 'admin')
  } catch (err) {
    console.error('Lỗi khi lấy danh sách người dùng:', err)
    showNotificationMessage('Không thể tải danh sách người dùng', 'error')
  }
}

// Tải dữ liệu thông báo
const initNotificationData = async () => {
  try {
    loading.value = true
    const data = await secureFetch(`${apiBase}/notifications/${id}`, {
      cache: 'no-store'
    }, ['admin'])

    form.value = {
      title: data.title || '',
      content: data.content || '',
      type: data.type || '',
      link: data.link || '',
      roles: Array.isArray(data.to_roles) ? data.to_roles : [],
      channels: Array.isArray(data.channels) ? data.channels : [],
      status: data.status || 'draft',
      user_ids: Array.isArray(data.users) ? data.users.map(u => u.id) : []
    }

    previewImage.value = data.image_url
      ? data.image_url.startsWith('http')
        ? data.image_url
        : `${mediaBase}${data.image_url}`
      : null

    await fetchUsers()
    validateForm()
  } catch (err) {
    console.error('Lỗi khi tải thông báo:', err)
    showNotificationMessage(
      err?.response?.data?.message || 'Không thể tải thông báo',
      'error'
    )
  } finally {
    loading.value = false
  }
}

onMounted(initNotificationData)

// Xử lý ảnh
const triggerFileInput = () => fileInput.value?.click()

const handleImageUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    imageFile.value = file
    previewImage.value = URL.createObjectURL(file)
    removeOldImage.value = true
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

// Lọc người dùng
const filteredUsers = computed(() => {
  if (form.value.roles.length === 0) return []
  return allUsers.value.filter(u => form.value.roles.includes(u.role))
})

const filteredUsersWithSearch = computed(() => {
  const keyword = searchUser.value.toLowerCase().trim()
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

// Cập nhật thông báo
const updateNotification = async () => {
  validateForm()
  if (Object.keys(errors.value).length > 0) {
    showNotificationMessage('Vui lòng sửa các lỗi trong biểu mẫu', 'error')
    return
  }

  loading.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('title', form.value.title.trim())
    formData.append('content', form.value.content.trim())
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

    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) {
      throw new Error('Không tìm thấy token truy cập')
    }

    await secureFetch(`${apiBase}/notifications/${id}?_method=PUT`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`
      },
      body: formData,
      cache: 'no-store'
    }, ['admin'])

    showNotificationMessage('Cập nhật thông báo thành công!', 'success')
    router.push('/admin/notifications/list-notifications')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
      Object.values(errors.value).forEach((errMsgs) => {
        if (Array.isArray(errMsgs)) {
          errMsgs.forEach((msg) => showNotificationMessage(msg, 'error'))
        }
      })
    } else {
      const message = err?.response?.data?.message || 'Lỗi khi cập nhật thông báo.'
      showNotificationMessage(message, 'error')
    }
    console.error('Lỗi khi cập nhật:', err)
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