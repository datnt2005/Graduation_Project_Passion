<template>
  <div class="min-h-screen bg-[#f5f7fa] text-[#1a1a1a] font-sans">
    <div class="max-w-[1280px] mx-auto p-4 md:p-6 flex flex-col md:flex-row gap-6">
      
      <!-- Sidebar trái -->
      <SidebarProfile class="w-full md:w-[260px] bg-white rounded-xl shadow-sm border border-[#e0e6ed] p-4" />

      <!-- Nội dung chính -->
      <section class="flex-1 bg-white rounded-2xl shadow-sm border border-[#ececec] p-6 md:p-8 space-y-6">
        <template v-if="loading">
          <div class="animate-pulse space-y-4">
            <div class="h-8 bg-gray-200 rounded w-2/3"></div>
            <div class="h-6 bg-gray-100 rounded w-1/2"></div>
            <div class="h-20 w-20 bg-gray-200 rounded-full"></div>
            <div class="h-12 bg-gray-100 rounded w-full"></div>
          </div>
        </template>
        <template v-else>
          <h2 class="text-2xl font-bold text-[#212b36]">Thông tin tài khoản</h2>
          <div class="flex flex-col md:flex-row items-center gap-6">
            <!-- Avatar -->
            <div class="relative w-24 h-24 group">
              <img
                :src="imagePreview || formData.avatar_url || DEFAULT_AVATAR"
                class="w-full h-full rounded-full object-cover border-4 border-[#e0e6ed] shadow-sm"
                alt="Avatar"
              />
              <label
                for="avatarInput"
                class="absolute bottom-1.5 right-1.5 bg-white border border-gray-300 rounded-full w-9 h-9 flex items-center justify-center cursor-pointer hover:bg-gray-100 transition"
                title="Đổi ảnh đại diện"
              >
                <font-awesome-icon :icon="['fas', 'camera']" class="text-gray-600 text-sm" />
                <input
                  id="avatarInput"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="handleImageUpload"
                  ref="fileInput"
                />
              </label>
            </div>

            <!-- Họ tên -->
            <div class="flex-1 w-full">
              <label for="fullname" class="block text-sm text-[#7a869a] font-medium mb-1">Họ & Tên</label>
              <input
                id="fullname"
                v-model="formData.name"
                class="w-full px-4 py-2 rounded-lg border border-[#e0e6ed] bg-[#f9fbfd] focus:outline-none focus:ring-2 focus:ring-[#0b74e5]"
                type="text"
                placeholder="Nhập họ và tên"
                autocomplete="off"
              />
            </div>
          </div>

          <!-- Thông báo -->
          <div class="min-h-[28px]">
            <transition name="fade">
              <p v-if="errorMsg" class="text-red-500 text-sm">{{ errorMsg }}</p>
            </transition>
            <transition name="fade">
              <p v-if="success" class="text-green-600 text-sm">{{ notificationMessage }}</p>
            </transition>
          </div>

          <!-- Nút lưu -->
          <div class="flex justify-end">
            <button
              class="bg-[#0b74e5] hover:bg-[#1064b3] text-white font-semibold px-6 py-2 rounded-lg transition w-full md:w-auto"
              :disabled="loading"
              @click="handleSubmit"
            >
              <span v-if="loading">Đang lưu...</span>
              <span v-else>Lưu thay đổi</span>
            </button>
          </div>
        </template>
      </section>

      <!-- Sidebar phải -->
      <aside class="w-full md:w-[320px] flex flex-col gap-6">
        <!-- Thông tin cá nhân -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#ececec] p-6">
          <h3 class="text-lg font-semibold mb-4 text-[#212b36]">Thông Tin Cá Nhân</h3>
          <!-- Số điện thoại -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-[#637381]">
              <font-awesome-icon :icon="['fas', 'phone']" class="w-5 h-5" />
              <input
                v-if="editingPhone"
                v-model="formData.phone"
                type="text"
                class="border border-[#0b74e5] rounded px-2 py-1 text-sm w-[120px] focus:outline-none"
              />
              <span v-else>{{ formData.phone || 'Số điện thoại' }}</span>
            </div>
          </div>
          <!-- Email -->
          <div class="flex items-center gap-2 text-[#637381] truncate mb-3">
            <font-awesome-icon :icon="['fas', 'envelope']" class="w-5 h-5" />
            <span class="truncate">{{ formData.email || 'Thêm email' }}</span>
          </div>
          <!-- Vai trò -->
           
        </div>

        <!-- Bảo mật -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#ececec] p-6">
          <h3 class="text-lg font-semibold mb-4 text-[#212b36]">Bảo mật</h3>

          <!-- Đổi mật khẩu -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-[#637381]">
              <font-awesome-icon :icon="['fas', 'lock']" class="w-5 h-5" />
              <span>Đổi mật khẩu</span>
            </div>
            <button
              class="text-sm font-medium border border-[#0b74e5] text-[#0b74e5] px-4 py-1 rounded hover:bg-[#eaf4fe]"
              @click="showChangePassword = !showChangePassword"
              type="button"
            >
              {{ showChangePassword ? 'Huỷ' : 'Cập nhật' }}
            </button>
          </div>

          <div v-if="showChangePassword" class="space-y-2">
            <input
              type="password"
              class="w-full border px-3 py-2 rounded text-sm"
              v-model="changePasswordForm.old_password"
              placeholder="Mật khẩu cũ"
              autocomplete="off"
            />
            <input
              type="password"
              class="w-full border px-3 py-2 rounded text-sm"
              v-model="changePasswordForm.password"
              placeholder="Mật khẩu mới"
              autocomplete="off"
            />
            <input
              type="password"
              class="w-full border px-3 py-2 rounded text-sm"
              v-model="changePasswordForm.confirm_password"
              placeholder="Xác nhận mật khẩu mới"
              autocomplete="off"
            />
            <button
              class="w-full bg-[#0b74e5] text-white rounded px-3 py-2 font-semibold"
              :disabled="loading"
              @click="handleSubmit"
              type="button"
            >
              Lưu mật khẩu
            </button>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>


<script setup>
import { ref, reactive, onMounted } from 'vue';
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue';

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const DEFAULT_AVATAR =  config.public.mediaBaseUrl + 'avatars/default.jpg'

const loading = ref(true)
const errors = reactive({})
const notificationMessage = ref('')
const success = ref(false)
const errorMsg = ref('')
const imagePreview = ref(null)
const fileInput = ref(null)
const userId = ref(null)
const editingPhone = ref(false)
const showChangePassword = ref(false)

const formData = reactive({
  name: '',
  email: '',
  phone: '',
  avatar: null,
  avatar_url: null,
  role: '',
})

const changePasswordForm = reactive({
  old_password: '',
  password: '',
  confirm_password: ''
})
// Lấy userId và info từ API /me
const fetchUser = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('access_token')
    if (!token) return
    const res = await fetch(`${apiBase}/me`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    const data = await res.json()
    const UrlAvatar = config.public.mediaBaseUrl
    if (data && data.data) {
      userId.value = data.data.id
      Object.assign(formData, {
        name: data.data.name || '',
        email: data.data.email || '',
        phone: data.data.phone || '',
        avatar_url: data.data.avatar
          ? `${UrlAvatar}${data.data.avatar}`
          : DEFAULT_AVATAR,
        role: data.data.role || '',
        avatar: null
      })
      imagePreview.value = formData.avatar_url
    }
  } catch (e) {
    errorMsg.value = 'Không lấy được thông tin tài khoản!'
  } finally {
    loading.value = false
  }
}
onMounted(fetchUser)

// Avatar
const triggerFileInput = () => fileInput.value && fileInput.value.click()
const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    formData.avatar = file
    imagePreview.value = URL.createObjectURL(file)
  } else {
    formData.avatar = null
    imagePreview.value = formData.avatar_url
  }
}

// Gửi cập nhật thông tin (bao gồm đổi mật khẩu nếu có)
const handleSubmit = async () => {
  Object.keys(errors).forEach((k) => errors[k] = '')
  errorMsg.value = ''
  success.value = false
  loading.value = true
  try {
    
    const token = localStorage.getItem('access_token')
    if (!token || !userId.value) throw new Error('Bạn chưa đăng nhập!')

    const payload = new FormData()
    // Thông tin cá nhân
    const fieldsToSend = ['name', 'email', 'phone', 'avatar']
    fieldsToSend.forEach((key) => {
      if (key === 'avatar' && !formData.avatar) return
      if (formData[key] === undefined || formData[key] === null) return
      payload.append(key, formData[key])
    })
    // Đổi mật khẩu (nếu cần)
    if (showChangePassword.value) {
      if (
        !changePasswordForm.old_password ||
        !changePasswordForm.password ||
        !changePasswordForm.confirm_password
      ) {
        errorMsg.value = 'Vui lòng nhập đầy đủ các trường đổi mật khẩu!'
        loading.value = false
        return
      }
      if (changePasswordForm.password !== changePasswordForm.confirm_password) {
        errorMsg.value = 'Xác nhận mật khẩu không khớp!'
        loading.value = false
        return
      }
      payload.append('old_password', changePasswordForm.old_password)
      payload.append('password', changePasswordForm.password)
    }
    payload.append('_method', 'PATCH')

    const res = await fetch(`${apiBase}/users/${userId.value}`, {
      method: 'POST',
      body: payload,
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })
    const data = await res.json()
    if ((res.ok && data.data) || data.success) {
      success.value = true
      notificationMessage.value = showChangePassword.value
        ? 'Đổi mật khẩu thành công!'
        : 'Cập nhật thành công!'
      // Reset form đổi mật khẩu nếu có
      if (showChangePassword.value) {
        showChangePassword.value = false
        changePasswordForm.old_password = ''
        changePasswordForm.password = ''
        changePasswordForm.confirm_password = ''
      }
      fetchUser() // reload lại info mới
      setTimeout(() => { success.value = false }, 2000)
    } else if (data.errors) {
      errorMsg.value = Object.values(data.errors).flat().join('\n')
    } else {
      errorMsg.value = data.error || 'Cập nhật thất bại!'
    }
  } catch (e) {
    errorMsg.value = e.message || 'Lỗi kết nối máy chủ!'
  } finally {
    loading.value = false
  }
}
</script>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s }
.fade-enter-from, .fade-leave-to { opacity: 0 }
</style>
