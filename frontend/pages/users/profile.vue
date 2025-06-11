<template>
  <div class="bg-[#f6f7fb] min-h-screen py-6 md:py-10 font-sans">
    <div class="max-w-5xl mx-auto flex flex-col md:flex-row gap-8">
      <!-- Thông tin cá nhân -->
      <section class="flex-1 bg-white rounded-2xl shadow-sm border border-[#ececec] p-4 md:p-8">
        <template v-if="loading">
          <div class="animate-pulse space-y-6">
            <div class="h-8 w-3/4 bg-gray-200 rounded mb-4"></div>
            <div class="h-6 w-1/2 bg-gray-100 rounded"></div>
            <div class="h-20 w-20 md:h-24 md:w-24 bg-gray-200 rounded-full my-6"></div>
            <div class="h-12 w-full bg-gray-100 rounded"></div>
          </div>
        </template>
        <template v-else>
          <h2 class="text-2xl font-semibold text-[#212b36] mb-7">Thông tin tài khoản</h2>
          <div class="flex flex-col md:flex-row items-center gap-6 mb-8">
            <!-- Avatar -->
            <div class="relative w-20 h-20 md:w-24 md:h-24 group mb-4 md:mb-0">
              <img
                :src="imagePreview || formData.avatar_url || DEFAULT_AVATAR"
                class="w-full h-full object-cover rounded-full border-4 border-[#e0e6ed] transition"
                alt="Avatar"
                loading="lazy"
              />
              <label
                for="avatarInput"
                class="absolute bottom-2 right-2 bg-[#0b74e5] text-white rounded-full w-8 h-8 md:w-9 md:h-9 flex justify-center items-center border-2 border-white cursor-pointer hover:bg-[#1064b3] transition"
                title="Đổi ảnh đại diện"
              >
                <font-awesome-icon :icon="['fas', 'camera']" />
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
            <div class="flex-1 min-w-0 w-full">
              <label class="text-sm text-[#7a869a] font-medium block mb-1" for="fullname">Họ & Tên</label>
              <input
                id="fullname"
                v-model="formData.name"
                class="border border-[#e0e6ed] rounded-lg px-4 py-2 w-full text-base focus:outline-none focus:ring-2 focus:ring-[#0b74e5] bg-[#f7fafd]"
                type="text"
                placeholder="Nhập họ và tên"
                autocomplete="off"
              />
            </div>
          </div>
          <!-- Thông báo lỗi/thành công -->
          <div class="mt-3 min-h-[28px] h-7">
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
              class="w-full md:w-auto bg-[#0b74e5] text-white rounded-lg px-7 py-2 font-semibold text-base hover:bg-[#1064b3] transition"
              :disabled="loading"
              @click="handleSubmit"
            >
              <span v-if="loading">Đang lưu...</span>
              <span v-else>Lưu thay đổi</span>
            </button>
          </div>
        </template>
      </section>

      <!-- Sidebar thông tin phụ -->
      <aside class="md:w-[340px] w-full flex flex-col gap-5">
        <!-- Thông tin liên hệ -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#ececec] p-6 space-y-4">
          <h3 class="text-lg font-semibold text-[#212b36] mb-3">Thông Tin Cá Nhân</h3>
          <!-- Số điện thoại (editable inline) -->
          <div class="flex items-center justify-between">
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
          <div class="flex items-center gap-2 text-[#637381] truncate max-w-[200px]">
            <font-awesome-icon :icon="['fas', 'envelope']" class="w-5 h-5" />
            <span class="truncate">{{ formData.email || 'Thêm email' }}</span>
          </div>
          <!-- Vai trò -->
          <div class="flex items-center gap-2 text-[#637381]">
            <font-awesome-icon :icon="['fas', 'user-tag']" class="w-5 h-5" />
            <span>Vai trò: {{ formData.role || 'Người dùng' }}</span>
          </div>
        </div>
        <!-- Đổi mật khẩu -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#ececec] p-6 space-y-4">
          <h3 class="text-lg font-semibold text-[#212b36] mb-3">Bảo mật</h3>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-[#637381]">
              <font-awesome-icon :icon="['fas', 'lock']" class="w-5 h-5" />
              <span>Đổi mật khẩu</span>
            </div>
            <button
              class="border border-[#0b74e5] text-[#0b74e5] rounded px-4 py-1 text-sm font-medium hover:bg-[#eaf4fe] transition"
              @click="showChangePassword = !showChangePassword"
              type="button"
            >
              {{ showChangePassword ? 'Huỷ' : 'Cập nhật' }}
            </button>
          </div>
          <div v-if="showChangePassword" class="mt-3 space-y-2">
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
              class="w-full bg-[#0b74e5] text-white rounded px-3 py-2 mt-2 font-semibold"
              :disabled="loading"
              @click="handleSubmit"
              type="button"
            >Lưu mật khẩu</button>
          </div>
          <!-- Mã pin và xoá tài khoản: chỉ là nút minh hoạ -->
          <div class="flex items-center justify-between mt-4">
            <div class="flex items-center gap-2 text-[#637381]">
              <font-awesome-icon :icon="['fas', 'key']" class="w-5 h-5" />
              <span>Thiết lập mã PIN</span>
            </div>
            <button class="border border-[#0b74e5] text-[#0b74e5] rounded px-4 py-1 text-sm font-medium hover:bg-[#eaf4fe] transition">
              Thiết lập
            </button>
          </div>
          <div class="flex items-center justify-between mt-2">
            <div class="flex items-center gap-2 text-[#f44336]">
              <font-awesome-icon :icon="['fas', 'trash']" class="w-5 h-5" />
              <span>Yêu cầu xóa tài khoản</span>
            </div>
            <button class="border border-[#f44336] text-[#f44336] rounded px-4 py-1 text-sm font-medium hover:bg-[#fdeaea] transition">
              Yêu cầu
            </button>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const DEFAULT_AVATAR = 'https://www.pngmart.com/files/22/User-Avatar-Profile-PNG.png'

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
    const UrlAvatar = 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/'
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
