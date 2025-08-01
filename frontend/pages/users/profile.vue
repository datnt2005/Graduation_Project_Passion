<template>
  <div class="bg-[#f5f7fa] text-[#1a1a1a] font-sans">
    <div class="max-w-7xl mx-auto md:pt-6 md:pb-6 flex flex-col md:flex-row gap-6">
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
                aria-label="Ảnh đại diện"
              />
              <label
                for="avatarInput"
                class="absolute bottom-1.5 right-1.5 bg-white border border-gray-300 rounded-full w-9 h-9 flex items-center justify-center cursor-pointer hover:bg-gray-100 transition"
                title="Đổi ảnh đại diện"
                aria-label="Đổi ảnh đại diện"
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
                aria-required="true"
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
              :disabled="loading || !isFormValid"
              @click="handleSubmit"
              aria-label="Lưu thay đổi thông tin tài khoản"
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
                aria-label="Số điện thoại"
                placeholder="Nhập số điện thoại"
              />
              <span v-else>{{ formData.phone || 'Chưa có số điện thoại' }}</span>
            </div>
            <button
              class="text-sm font-medium border border-[#0b74e5] text-[#0b74e5] px-4 py-1 rounded hover:bg-[#eaf4fe]"
              @click="editingPhone ? handlePhoneSubmit() : editingPhone = true"
              :aria-label="editingPhone ? 'Lưu số điện thoại' : 'Chỉnh sửa số điện thoại'"
            >
              {{ editingPhone ? 'Lưu' : 'Cập nhật' }}
            </button>
          </div>
          <!-- Thông báo lỗi số điện thoại -->
          <transition name="fade">
            <p v-if="editingPhone && phoneError" class="text-red-500 text-sm mt-1">{{ phoneError }}</p>
          </transition>
          <!-- Email -->
          <div class="flex items-center gap-2 text-[#637381] truncate mb-3">
            <font-awesome-icon :icon="['fas', 'envelope']" class="w-5 h-5" />
            <span class="truncate">{{ formData.email || 'Chưa có email' }}</span>
          </div>
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
              :aria-label="showChangePassword ? 'Hủy đổi mật khẩu' : 'Cập nhật mật khẩu'"
            >
              {{ showChangePassword ? 'Hủy' : 'Cập nhật' }}
            </button>
          </div>

          <div v-if="showChangePassword" class="space-y-2">
            <div>
              <input
                type="password"
                class="w-full border px-3 py-2 rounded text-sm"
                v-model="changePasswordForm.old_password"
                placeholder="Mật khẩu cũ"
                autocomplete="off"
                aria-required="true"
                aria-label="Mật khẩu cũ"
              />
            </div>
            <div>
              <input
                type="password"
                class="w-full border px-3 py-2 rounded text-sm"
                v-model="changePasswordForm.password"
                placeholder="Mật khẩu mới"
                autocomplete="off"
                aria-required="true"
                aria-label="Mật khẩu mới"
              />
              <p class="text-xs text-[#7a869a] mt-1">
                Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.
              </p>
            </div>
            <div>
              <input
                type="password"
                class="w-full border px-3 py-2 rounded text-sm"
                v-model="changePasswordForm.confirm_password"
                placeholder="Xác nhận mật khẩu mới"
                autocomplete="off"
                aria-required="true"
                aria-label="Xác nhận mật khẩu mới"
              />
            </div>
            <button
              class="w-full bg-[#0b74e5] text-white rounded px-3 py-2 font-semibold"
              :disabled="loading || !isPasswordFormValid"
              @click="handlePasswordSubmit"
              aria-label="Lưu mật khẩu mới"
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
import { ref, reactive, onMounted, computed } from 'vue'
import { useHead, useRuntimeConfig } from '#app'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useToast } from '~/composables/useToast'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'

useHead({
  title: 'Thông tin tài khoản | Quản lý hồ sơ',
  meta: [
    { name: 'description', content: 'Quản lý thông tin cá nhân, số điện thoại, email và mật khẩu của bạn.' },
    { name: 'robots', content: 'noindex, nofollow' },
    { property: 'og:title', content: 'Thông tin tài khoản - Quản lý hồ sơ' },
    { property: 'og:description', content: 'Cập nhật thông tin cá nhân và bảo mật tài khoản của bạn.' }
  ]
})

const { showSuccess, showError } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const DEFAULT_AVATAR = config.public.mediaBaseUrl + 'avatars/default.jpg'

const loading = ref(true)
const errorMsg = ref('')
const success = ref(false)
const notificationMessage = ref('')
const imagePreview = ref(null)
const fileInput = ref(null)
const userId = ref(null)
const editingPhone = ref(false)
const showChangePassword = ref(false)
const phoneError = ref('')

const formData = reactive({
  name: '',
  email: '',
  phone: '',
  avatar: null,
  avatar_url: null,
  role: ''
})

const changePasswordForm = reactive({
  old_password: '',
  password: '',
  confirm_password: ''
})

const isFormValid = computed(() => {
  if (editingPhone.value && formData.phone) {
    const phoneValid = formData.phone.match(/^\d{10}$/)
    phoneError.value = phoneValid ? '' : 'Số điện thoại phải là 10 chữ số'
    return formData.name.trim() && phoneValid
  }
  return formData.name.trim()
})

const isPasswordFormValid = computed(() => {
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/
  const isValid =
    changePasswordForm.old_password &&
    changePasswordForm.password &&
    changePasswordForm.confirm_password &&
    changePasswordForm.password === changePasswordForm.confirm_password &&
    changePasswordForm.password.length >= 6 &&
    passwordRegex.test(changePasswordForm.password)
  return isValid
})

const fetchUser = async () => {
  const cacheKey = 'user_profile'
  const cache = localStorage.getItem(cacheKey)
  if (cache) {
    const cachedData = JSON.parse(cache)
    userId.value = cachedData.id
    Object.assign(formData, {
      name: cachedData.name || '',
      email: cachedData.email || '',
      phone: cachedData.phone || '',
      avatar_url: cachedData.avatar_url || DEFAULT_AVATAR,
      role: cachedData.role || '',
      avatar: null
    })
    imagePreview.value = formData.avatar_url
    loading.value = false
    return
  }

  loading.value = true
  try {
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Bạn chưa đăng nhập!')
    const res = await axios.get(`${apiBase}/me`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    const data = res.data.data
    if (data) {
      userId.value = data.id
      const UrlAvatar = config.public.mediaBaseUrl
      Object.assign(formData, {
        name: data.name || '',
        email: data.email || '',
        phone: data.phone || '',
        avatar_url: data.avatar?.startsWith('http')
          ? data.avatar
          : `${UrlAvatar}${data.avatar || 'avatars/default.jpg'}`,
        role: data.role || '',
        avatar: null
      })
      imagePreview.value = formData.avatar_url
      localStorage.setItem(cacheKey, JSON.stringify({
        id: data.id,
        name: data.name,
        email: data.email,
        phone: data.phone,
        avatar_url: formData.avatar_url,
        role: data.role
      }))
    }
  } catch (e) {
    showError(e.message || 'Không lấy được thông tin tài khoản!')
  } finally {
    loading.value = false
  }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      showError('Chỉ hỗ trợ định dạng JPG hoặc PNG!')
      return
    }
    if (file.size > 2 * 1024 * 1024) {
      showError('Ảnh không được vượt quá 2MB!')
      return
    }
    formData.avatar = file
    imagePreview.value = URL.createObjectURL(file)
  } else {
    formData.avatar = null
    imagePreview.value = formData.avatar_url || DEFAULT_AVATAR
  }
}

const handleSubmit = async () => {
  if (!isFormValid.value) {
    showError('Vui lòng điền đầy đủ thông tin hợp lệ!')
    return
  }

  const confirm = await Swal.fire({
    title: 'Cập nhật thông tin?',
    text: 'Bạn có chắc chắn muốn lưu thay đổi thông tin cá nhân?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Lưu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  loading.value = true
  try {
    const token = localStorage.getItem('access_token')
    if (!token || !userId.value) throw new Error('Bạn chưa đăng nhập!')

    const payload = new FormData()
    const fieldsToSend = ['name', 'avatar']
    fieldsToSend.forEach((key) => {
      if (key === 'avatar' && !formData.avatar) return
      if (formData[key] === undefined || formData[key] === null) return
      payload.append(key, formData[key])
    })
    payload.append('_method', 'PATCH')

    const res = await axios.post(`${apiBase}/users/${userId.value}`, payload, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    if (res.data.success || res.data.data) {
      showSuccess('Cập nhật thông tin thành công!')
      localStorage.removeItem('user_profile')
      await fetchUser()
    } else {
      showError(res.data.error || 'Cập nhật thất bại!')
    }
  } catch (e) {
    const errorMessage = e.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join(' ')
      : e.response?.data?.error || e.message || 'Lỗi kết nối máy chủ!'
    showError(errorMessage)
  } finally {
    loading.value = false
  }
}

const handlePhoneSubmit = async () => {
  if (!formData.phone.match(/^\d{10}$/)) {
    phoneError.value = 'Số điện thoại phải là 10 chữ số'
    return
  }

  const confirm = await Swal.fire({
    title: 'Cập nhật số điện thoại?',
    text: 'Bạn có chắc chắn muốn lưu số điện thoại mới?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Lưu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  loading.value = true
  try {
    const token = localStorage.getItem('access_token')
    if (!token || !userId.value) throw new Error('Bạn chưa đăng nhập!')

    const payload = new FormData()
    payload.append('phone', formData.phone)
    payload.append('_method', 'PATCH')

    const res = await axios.post(`${apiBase}/users/${userId.value}`, payload, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    if (res.data.success || res.data.data) {
      showSuccess('Cập nhật số điện thoại thành công!')
      localStorage.removeItem('user_profile')
      await fetchUser()
      editingPhone.value = false
      phoneError.value = ''
    } else {
      showError(res.data.error || 'Cập nhật số điện thoại thất bại!')
    }
  } catch (e) {
    const errorMessage = e.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join(' ')
      : e.response?.data?.error || e.message || 'Lỗi kết nối máy chủ!'
    showError(errorMessage)
  } finally {
    loading.value = false
  }
}

const handlePasswordSubmit = async () => {
  if (!isPasswordFormValid.value) {
    showError('Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt, và xác nhận phải khớp!')
    return
  }

  const confirm = await Swal.fire({
    title: 'Đổi mật khẩu?',
    text: 'Bạn có chắc chắn muốn đổi mật khẩu?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Lưu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  loading.value = true
  try {
    const token = localStorage.getItem('access_token')
    if (!token || !userId.value) throw new Error('Bạn chưa đăng nhập!')

    const payload = new FormData()
    payload.append('old_password', changePasswordForm.old_password)
    payload.append('password', changePasswordForm.password)
    payload.append('password_confirmation', changePasswordForm.confirm_password)
    payload.append('_method', 'PATCH')

    const res = await axios.post(`${apiBase}/users/${userId.value}`, payload, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    if (res.data.success || res.data.data) {
      showSuccess('Đổi mật khẩu thành công!')
      showChangePassword.value = false
      changePasswordForm.old_password = ''
      changePasswordForm.password = ''
      changePasswordForm.confirm_password = ''
    } else {
      showError(res.data.error || 'Đổi mật khẩu thất bại!')
    }
  } catch (e) {
    const errorMessage = e.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join(' ')
      : e.response?.data?.error || e.message || 'Lỗi kết nối máy chủ!'
    showError(errorMessage)
  } finally {
    loading.value = false
  }
}

onMounted(fetchUser)
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s }
.fade-enter-from, .fade-leave-to { opacity: 0 }
</style>