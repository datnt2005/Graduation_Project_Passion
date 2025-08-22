<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <h1 class="text-xl font-semibold text-gray-800 px-6 pt-6">Cập nhật người dùng</h1>
    <div class="px-6 pb-4">
      <nuxt-link to="/admin/users/list-user" class="text-gray-600 hover:underline text-sm">
        Người dùng
      </nuxt-link>
      <span class="text-gray-600 text-sm"> / Cập nhật người dùng</span>
    </div>

    <div class="flex min-h-screen bg-gray-100">
      <!-- Sidebar -->
      <nav class="w-64 bg-white border-r border-gray-200">
        <ul class="py-2">
          <li>
            <button @click="activeTab = 'overview'" :class="[
              'flex items-center w-full px-4 py-2 text-sm transition-colors',
              activeTab === 'overview' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]">
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
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4">
              <!-- Info Section -->
              <section class="space-y-4">
                <div class="space-y-2">
                  <div>
                    <label for="user-name" class="block text-sm text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                    <input id="user-name" v-model="formData.name" type="text"
                      :disabled="loading"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      placeholder="Nhập họ và tên" />
                    <span v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-email" class="block text-sm text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input id="user-email" v-model="formData.email" type="email"
                      disabled
                      class="w-full rounded border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-500 cursor-not-allowed" />
                    <span v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-old-password" class="block text-sm text-gray-700 mb-1">Mật khẩu cũ (bắt buộc nếu đổi mật khẩu)</label>
                    <input id="user-old-password" v-model="formData.old_password" type="password"
                      :disabled="loading"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      placeholder="Nhập mật khẩu cũ" />
                    <span v-if="errors.old_password" class="text-red-500 text-xs mt-1">{{ errors.old_password }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-password" class="block text-sm text-gray-700 mb-1">Mật khẩu mới (bỏ trống nếu không đổi)</label>
                    <input id="user-password" v-model="formData.password" type="password"
                      :disabled="loading"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      autocomplete="new-password"
                      placeholder="Nhập mật khẩu mới" />
                    <p class="text-xs text-gray-500 mt-1">Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.</p>
                    <span v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</span>
                  </div>
                  <div v-if="formData.password" class="mt-4">
                    <label for="user-password-confirm" class="block text-sm text-gray-700 mb-1">Xác nhận mật khẩu <span class="text-red-500">*</span></label>
                    <input id="user-password-confirm" v-model="formData.passwordConfirm" type="password"
                      :disabled="loading"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      autocomplete="new-password"
                      placeholder="Nhập lại mật khẩu" />
                    <span v-if="errors.passwordConfirm" class="text-red-500 text-xs mt-1">{{ errors.passwordConfirm }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-phone" class="block text-sm text-gray-700 mb-1">Số điện thoại</label>
                    <input id="user-phone" v-model="formData.phone" type="text"
                      :disabled="loading"
                      class="w-full rounded border border-gray-300 bg-white px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                      placeholder="Nhập số điện thoại" />
                    <p class="text-xs text-gray-500 mt-1">Số điện thoại phải là số hợp lệ (bắt đầu bằng +84 hoặc 0).</p>
                    <span v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-role" class="block text-sm text-gray-700 mb-1">Phân quyền <span class="text-red-500">*</span></label>
                    <select id="user-role" v-model="formData.role"
                      :disabled="loading"
                      class="w-full rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                      <option value="">Chọn quyền</option>
                      <option value="admin">Quản trị viên</option>
                      <option value="user">Người dùng</option>
                      <option value="seller">Người bán</option>
                    </select>
                    <span v-if="errors.role" class="text-red-500 text-xs mt-1">{{ errors.role }}</span>
                  </div>
                  <div class="mt-4">
                    <label for="user-status" class="block text-sm text-gray-700 mb-1">Trạng thái</label>
                    <select id="user-status" v-model="formData.status"
                      :disabled="loading"
                      class="w-full rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                      <option value="active">Kích hoạt</option>
                      <option value="inactive">Không kích hoạt</option>
                      <option value="banned">Khóa</option>
                    </select>
                    <span v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</span>
                  </div>
                </div>
              </section>

              <!-- Sidebar Avatar & Button -->
              <aside class="bg-white rounded border border-gray-300 shadow-sm p-3 text-xs text-gray-700 space-y-3 max-w-[320px]">
                <header class="flex items-center justify-between border-b border-gray-300 pb-1">
                  <h2 class="font-semibold">Ảnh đại diện (có thể để trống)</h2>
                </header>
                <div v-if="activeTab === 'overview'" class="space-y-3">
                  <div
                    class="relative flex items-center justify-center w-full max-w-xs p-4 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition"
                    :class="{ 'pointer-events-none opacity-50': loading }"
                    @dragover.prevent
                    @drop.prevent="handleDrop"
                    @click="triggerFileInput">
                    <input ref="fileInput" id="user-avatar" type="file" accept="image/*" class="hidden"
                      :disabled="loading"
                      @change="handleImageUpload" />
                    <div class="flex flex-col items-center text-center text-gray-500">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <p class="text-sm">Kéo ảnh vào đây hoặc <span class="text-blue-500 underline">chọn từ máy</span></p>
                      <p class="text-xs text-gray-400 mt-1">Kích thước tối đa: 2MB, định dạng: JPG, PNG, GIF, SVG, WEBP</p>
                    </div>
                  </div>
                  <span v-if="errors.avatar" class="text-red-500 text-xs mt-1 block">{{ errors.avatar }}</span>
                  <div v-if="imagePreview" class="mt-3">
                    <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded-full border mx-auto" />
                    <button @click="removeImage"
                      class="mt-2 text-red-500 text-xs hover:underline flex items-center mx-auto">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      Xóa ảnh
                    </button>
                  </div>
                  <div v-else-if="formData.avatar_url" class="mt-3">
                    <img :src="formData.avatar_url" alt="Avatar cũ" class="w-32 h-32 object-cover rounded-full border mx-auto" />
                    <button @click="removeImage"
                      class="mt-2 text-red-500 text-xs hover:underline flex items-center mx-auto">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      Xóa ảnh
                    </button>
                  </div>
                </div>
                <div class="pt-4 mt-4 border-t border-gray-200">
                  <button type="submit"
                    class="w-full bg-blue-600 text-white text-sm font-semibold rounded px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed"
                    :disabled="loading">
                    {{ loading ? 'Đang xử lý...' : 'Lưu thay đổi' }}
                  </button>
                </div>
              </aside>
            </div>
          </form>
        </div>
      </main>
    </div>

    <!-- Notification Popup -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <div v-if="showNotification"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6" :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="notificationType === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-if="notificationType === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">{{ notificationMessage }}</p>
          </div>
          <div class="flex-shrink-0">
            <button @click="showNotification = false"
              class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute, useRuntimeConfig } from '#app'
import { secureFetch } from '@/utils/secureFetch'

definePageMeta({ layout: 'default-admin' })

const router = useRouter()
const route = useRoute()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const userId = route.params.id
const activeTab = ref('overview')
const loading = ref(false)
const errors = reactive({})
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
const imagePreview = ref(null)
const fileInput = ref(null)

const formData = reactive({
  name: '',
  email: '',
  old_password: '',
  password: '',
  passwordConfirm: '',
  phone: '',
  role: '',
  status: 'active',
  avatar: null,
  avatar_url: null,
  _method: 'PUT'
})

// Validate form data before submission
const validateForm = () => {
  Object.keys(errors).forEach(key => delete errors[key])

  let isValid = true
  // Define field order for client-side validation errors
  const fieldOrder = ['name', 'email', 'password', 'passwordConfirm', 'old_password', 'phone', 'role', 'status', 'avatar']
  
  if (!formData.name.trim()) {
    errors.name = 'Họ và tên là bắt buộc'
    isValid = false
  } else if (formData.name.length > 255) {
    errors.name = 'Tên không được vượt quá 255 ký tự'
    isValid = false
  }
  
  if (formData.password) {
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/
    if (!passwordRegex.test(formData.password)) {
      errors.password = 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt'
      isValid = false
    }
    if (formData.password !== formData.passwordConfirm) {
      errors.passwordConfirm = 'Mật khẩu xác nhận không khớp'
      isValid = false
    }
    if (!formData.old_password) {
      errors.old_password = 'Vui lòng nhập mật khẩu cũ để đổi mật khẩu'
      isValid = false
    }
  }
  
  if (formData.phone && !/^(\+84|0)(3|5|7|8|9)[0-9]{8}$/.test(formData.phone)) {
    errors.phone = 'Số điện thoại phải là số hợp lệ (bắt đầu bằng +84 hoặc 0)'
    isValid = false
  }
  
  if (!formData.role) {
    errors.role = 'Vui lòng chọn phân quyền'
    isValid = false
  }
  
  if (!['active', 'inactive', 'banned'].includes(formData.status)) {
    errors.status = 'Trạng thái không hợp lệ'
    isValid = false
  }
  
  if (formData.avatar) {
    const maxSize = 2 * 1024 * 1024 // 2MB
    if (formData.avatar.size > maxSize) {
      errors.avatar = 'Ảnh đại diện không được vượt quá 2MB'
      isValid = false
    }
    if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml', 'image/webp'].includes(formData.avatar.type)) {
      errors.avatar = 'Ảnh phải có định dạng JPG, PNG, GIF, SVG hoặc WEBP'
      isValid = false
    }
  }

  if (!isValid) {
    const firstErrorField = fieldOrder.find(field => errors[field])
    const errorMessage = firstErrorField ? errors[firstErrorField] : 'Vui lòng kiểm tra lại các trường thông tin'
    showNotificationMessage(errorMessage, 'error')
  }
  
  return isValid
}

// Fetch user data
const fetchUser = async () => {
  try {
    loading.value = true
    const response = await secureFetch(`${apiBase}/users/${userId}`, {}, ['admin'], true)
    
    console.log('Fetch user response:', response) // Debug response
    
    if (response.success && response.data) {
      Object.assign(formData, {
        name: response.data.name || '',
        email: response.data.email || '',
        phone: response.data.phone || '',
        role: response.data.role || '',
        status: response.data.status || 'active',
        avatar: null,
        avatar_url: response.data.avatar_url || null,
        old_password: '',
        password: '',
        passwordConfirm: ''
      })
    } else {
      showNotificationMessage(response.message || 'Không lấy được thông tin người dùng', 'error')
    }
  } catch (error) {
    console.error('Error fetching user:', error)
    showNotificationMessage(error.message || 'Lỗi kết nối máy chủ khi lấy thông tin người dùng', 'error')
  } finally {
    loading.value = false
  }
}
onMounted(fetchUser)

// Trigger file input click
const triggerFileInput = () => {
  if (!loading.value) {
    fileInput.value.click()
  }
}

// Handle drag-and-drop
const handleDrop = (event) => {
  if (loading.value) return
  const file = event.dataTransfer.files[0]
  if (file) handleImageUpload({ target: { files: [file] } })
}

// Handle image upload
const handleImageUpload = (event) => {
  const file = event.target.files[0] || event.dataTransfer?.files[0]
  if (file) {
    formData.avatar = file
    errors.avatar = ''
    imagePreview.value = URL.createObjectURL(file)
  } else {
    formData.avatar = null
    imagePreview.value = null
    errors.avatar = ''
  }
}

// Remove uploaded or existing image
const removeImage = () => {
  if (loading.value) return
  formData.avatar = null
  formData.avatar_url = null
  imagePreview.value = null
  errors.avatar = ''
  fileInput.value.value = ''
}

// Show notification
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => {
    showNotification.value = false
  }, 4000)
}

// Handle form submission
const handleSubmit = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key])

  // Perform client-side validation
  if (!validateForm()) {
    return
  }

  const payload = new FormData()
  const fieldsToSend = ['name', 'old_password', 'password', 'passwordConfirm', 'phone', 'role', 'status', 'avatar']
  fieldsToSend.forEach(key => {
    if (key === 'avatar' && !formData.avatar) return
    if (key === 'password' && !formData.password) return
    if (key === 'passwordConfirm' && !formData.passwordConfirm) return
    if (key === 'old_password' && !formData.old_password) return
    if (formData[key] !== undefined && formData[key] !== null) {
      payload.append(key, formData[key])
    }
  })
  payload.append('_method', 'PUT')

  // Debug FormData
  for (const pair of payload.entries()) {
    console.log('FormData gửi lên:', pair[0], pair[1])
  }

  try {
    loading.value = true
    const response = await secureFetch(`${apiBase}/users/${userId}`, {
      method: 'POST',
      body: payload
    }, ['admin'], true)

    console.log('API response:', response) // Debug response

    if (response.success) {
      showNotificationMessage('Cập nhật người dùng thành công!', 'success')
      setTimeout(() => {
        router.push('/admin/users/list-user')
      }, 1000)
    } else {
      // Handle validation errors or other failures
      if (response.errors) {
        // Define field order for server-side validation errors
        const fieldOrder = ['name', 'email', 'password', 'passwordConfirm', 'old_password', 'phone', 'role', 'status', 'avatar']
        const firstErrorField = fieldOrder.find(field => response.errors[field])
        const errorMessages = []
        
        // Assign all errors to display under input fields
        Object.entries(response.errors).forEach(([key, messages]) => {
          const message = Array.isArray(messages) ? messages[0] : messages
          errors[key] = message
          errorMessages.push(message)
        })

        // Show only the first error in toast based on field order
        const firstErrorMessage = firstErrorField 
          ? response.errors[firstErrorField][0] 
          : response.message || 'Có lỗi xảy ra khi cập nhật người dùng'
        showNotificationMessage(firstErrorMessage, 'error')
      } else {
        showNotificationMessage(response.message || 'Có lỗi xảy ra khi cập nhật người dùng', 'error')
      }
    }
  } catch (error) {
    console.error('Error updating user:', error)
    console.log('Error details:', {
      message: error.message,
      status: error.status,
      data: error.data,
      errors: error.data?.errors,
    })
    showNotificationMessage(error.message || 'Lỗi kết nối máy chủ khi cập nhật người dùng', 'error')
  } finally {
    loading.value = false
  }
}
</script>