<template>
  <div>
    <!-- Thanh trên cùng -->
    <header class="bg-[#1BA0E2] text-white text-sm py-2">
      <div class="container mx-auto flex justify-between items-center px-4">
        <div class="space-x-2">
          <NuxtLink to="/Seller" class="hover:underline inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l3 10h8l3-10h4" />
            </svg>
            Đăng ký bán hàng
          </NuxtLink>
          <a href="#" class="hover:underline inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2h5" />
            </svg>
            Kết nối <font-awesome-icon :icon="['fab', 'facebook']" />
            <font-awesome-icon :icon="['fab', 'instagram']" />
          </a>
        </div>
        <div class="hidden sm:flex items-center space-x-4">
          <a href="#" class="hover:underline inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.437L4 17h5" />
            </svg>
            Thông báo
          </a>

          <template v-if="isLoggedIn">
            <span class="font-medium">Xin chào, <strong>{{ userName }}</strong></span>
            <button @click="logout" class="hover:underline inline-flex items-center gap-1 text-white">
              <i><font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" /></i>
              Đăng xuất
            </button>
          </template>

          <template v-else>
            <NuxtLink to="#" @click.prevent="openLogin" class="hover:underline inline-flex items-center gap-1">
              <i><font-awesome-icon :icon="['fas', 'right-to-bracket']" /></i>
              Đăng nhập
            </NuxtLink>
            <NuxtLink to="#" @click.prevent="openRegister" class="hover:underline inline-flex items-center gap-1">
              <i><font-awesome-icon :icon="['fas', 'plus']" /></i>
              Đăng ký
            </NuxtLink>
          </template>
        </div>

      </div>
    </header>
 <transition name="fade-scale">
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 sm:p-8 relative">

      <!-- Nút đóng -->
      <button @click="closeModal" class="absolute top-3 right-4 text-gray-400 hover:text-red-500 text-2xl">
        &times;
      </button>

      <!-- Tiêu đề + nút chuyển đổi -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-[#1BA0E2]">
          {{ isLogin ? 'Đăng nhập' : 'Đăng ký' }}
        </h2>
        <button @click="isLogin = !isLogin" class="text-sm text-blue-500 hover:underline">
          {{ isLogin ? 'Chưa có tài khoản?' : 'Đã có tài khoản?' }}
        </button>
      </div>

      <!-- FORM 1: ĐĂNG KÝ / ĐĂNG NHẬP -->
      <form v-if="!showOtp && !showVerifyEmailForm" @submit.prevent="submitForm" class="space-y-3">
        <div v-if="!isLogin" class="input-group">
          <i class="fas fa-user input-icon"></i>
          <input v-model="form.name" type="text" placeholder="Họ và tên" class="input-field" />
        </div>

        <div class="input-group">
          <i class="fas fa-envelope input-icon"></i>
          <input v-model="form.email" type="email" placeholder="Email" class="input-field" />
        </div>

        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input v-model="form.password" type="password" placeholder="Mật khẩu" class="input-field" />
        </div>

        <div v-if="!isLogin" class="input-group">
          <i class="fas fa-shield-alt input-icon"></i>
          <input v-model="form.confirmPassword" type="password" placeholder="Xác nhận mật khẩu" class="input-field" />
        </div>

        <div v-if="!isLogin" class="input-group">
          <i class="fas fa-phone input-icon"></i>
          <input v-model="form.phone" type="text" placeholder="Số điện thoại" class="input-field" />
        </div>

        <button type="submit" class="btn-primary w-full" :disabled="isSubmitting">
          <span v-if="isSubmitting">
            <i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...
          </span>
          <span v-else>
            {{ isLogin ? 'Đăng nhập' : 'Đăng ký' }}
          </span>
        </button>
      </form>

      <!-- FORM 2: NHẬP EMAIL GỬI LẠI MÃ -->
      <form v-if="showVerifyEmailForm && !showOtp" @submit.prevent="sendVerificationRequest" class="space-y-3 border-t pt-4 mt-4">
        <p class="text-sm text-gray-600">Bạn cần xác minh email để tiếp tục. Nhập email của bạn:</p>
        <div class="input-group">
          <i class="fas fa-envelope input-icon"></i>
          <input v-model="verifyEmailInput" type="email" placeholder="Email" class="input-field" />
        </div>
        <button type="submit" class="btn-primary w-full">
          Gửi mã xác minh
        </button>
      </form>

      <!-- FORM 3: NHẬP MÃ OTP -->
      <form v-if="showOtp" @submit.prevent="verifyOtp" class="space-y-3">
        <h2 class="text-xl font-semibold text-[#1BA0E2]">Xác minh OTP</h2>
        <p class="text-gray-600 text-sm">Vui lòng kiểm tra email và nhập mã OTP để xác minh tài khoản.</p>

        <div class="input-group">
          <i class="fas fa-key input-icon"></i>
          <input v-model="otp" type="text" placeholder="Nhập mã OTP" class="input-field" />
        </div>

        <button type="submit" class="btn-primary w-full" :disabled="isVerifying">
          <span v-if="isVerifying">
            <i class="fas fa-spinner fa-spin mr-2"></i>Đang xác minh...
          </span>
          <span v-else>Xác minh</span>
        </button>

        <!-- Gửi lại mã OTP có countdown -->
        <p class="text-sm text-gray-500 text-center">
          <span v-if="resendCountdown > 0">Gửi lại mã sau {{ resendCountdown }} giây</span>
          <button v-else @click="resendVerificationEmail" class="text-blue-500 hover:underline text-sm">
            Gửi lại mã OTP
          </button>
        </p>
      </form>

    </div>
  </div>
</transition>



    <!-- Thanh giữa -->
    <div class="bg-white shadow-sm">
      <div class="container mx-auto flex items-center justify-between px-4 py-3">
        <!-- Logo -->
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gray-300 rounded text-center text-xs flex items-center justify-center">
            <NuxtLink to="/">Logo</NuxtLink>
          </div>
        </div>
        <!-- NAVIGATION -->
        <div class="relative group ml-20 hidden md:block">
          <a href="#" class="text-gray-700 hover:text-blue-600 font-semibold">
            Danh mục <font-awesome-icon :icon="['fas', 'bars']" />
          </a>

          <!-- MEGA MENU -->
          <div
            class="absolute left-0 mt-6 w-[1200px] bg-white border border-gray-200 shadow-lg rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-6 grid grid-cols-5 gap-6">

            <!-- Cột 1 -->
            <div>
              <h4 class="font-bold mb-2">Sản phẩm mới</h4>
              <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Giá tốt</a></li>
                <li><a href="#" class="hover:underline">Sale</a></li>
              </ul>
            </div>

            <!-- Cột 2 -->
            <div>
              <h4 class="font-bold mb-2">Danh mục sản phẩm</h4>
              <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Áo phông/ Áo thun</a></li>
                <li><a href="#" class="hover:underline">Áo polo</a></li>
                <li><a href="#" class="hover:underline">Áo sơ mi & Áo kiểu</a></li>
                <li><a href="#" class="hover:underline">Bộ quần áo</a></li>
                <li><a href="#" class="hover:underline">Canifa Active</a></li>
                <li><a href="#" class="hover:underline">Đồ ngủ</a></li>
                <li><a href="#" class="hover:underline">Chống nắng</a></li>
              </ul>
            </div>

            <!-- Cột 3 -->
            <div>
              <h4 class="font-bold mb-2">Phụ kiện</h4>
              <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Chăn</a></li>
                <li><a href="#" class="hover:underline">Khăn mặt</a></li>
                <li><a href="#" class="hover:underline">Khăn tắm</a></li>
                <li><a href="#" class="hover:underline">Khăn quàng cổ</a></li>
              </ul>
              <h4 class="font-bold mt-4 mb-2">Bộ sưu tập</h4>
              <ul class="text-gray-700 space-y-1">
                <li><a href="#" class="hover:underline">Disney</a></li>
                <li><a href="#" class="hover:underline">Doraemon</a></li>
              </ul>
            </div>

            <!-- Cột 4 & 5 (Hình ảnh) -->
            <div class="col-span-2 grid grid-cols-2 gap-4">
              <img src="https://media.canifa.com/mega_menu/item/Nam-1-menu-05Mar.webp" alt="Nữ 1"
                class="rounded-md object-cover w-full h-48">
              <img src="https://media.canifa.com/mega_menu/item/Nu-2-menu-05Mar.webp" alt="Nữ 2"
                class="rounded-md object-cover w-full h-48">
            </div>
          </div>
        </div>



        <!-- Tìm kiếm (desktop) -->
        <div class="flex-1 mx-4 hidden sm:flex justify-center">
          <input type="text" placeholder="Tìm kiếm"
            class="w-full max-w-[500px] px-4 py-2 border border-gray-300 rounded-full focus:outline-none" />
        </div>

        <!-- Menu PC -->
        <div class="hidden sm:flex items-center gap-x-6 text-sm font-medium text-gray-700">
          <!-- Trang chủ -->
          <NuxtLink href="/"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
            <font-awesome-icon :icon="['fas', 'house']" />
            Trang chủ
          </NuxtLink>

          <!-- Tài khoản -->
          <div class="relative group inline-block">
            <div
              class="cursor-pointer hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
              <font-awesome-icon :icon="['fas', 'user']" />
              Tài khoản
            </div>

            <!-- Dropdown nếu có -->
            <ul class="absolute left-0 top-full hidden group-hover:flex flex-col 
                  bg-white border border-gray-200 rounded shadow-lg w-48 
                  opacity-0 group-hover:opacity-100 
                  translate-y-2 group-hover:translate-y-0 
                  transition-all duration-300 ease-in-out z-50 text-sm text-gray-700">
              <li><a href="/users/profile" class="block px-4 py-2 hover:bg-gray-100">Thông tin tài khoản</a></li>
              <li><a href="/users/order" class="block px-4 py-2 hover:bg-gray-100">Đơn hàng của tôi</a></li>
              <li><a href="/support" class="block px-4 py-2 hover:bg-gray-100">Trung tâm hỗ trợ</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Đăng xuất</a></li>
            </ul>
          </div>

          <!-- Giỏ hàng -->
          <NuxtLink href="/cart"
            class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
            <font-awesome-icon :icon="['fas', 'cart-shopping']" />
            Giỏ hàng
          </NuxtLink>
        </div>


        <!-- Icon menu mobile -->
        <div class="sm:hidden">
          <button @click="isMobileMenuOpen = true">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Tìm kiếm (mobile) -->
      <div class="px-4 pb-3 sm:hidden">
        <div class="relative">
          <input type="text" placeholder="Tìm kiếm"
            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-full focus:outline-none" />
          <button
            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#1BA0E2] p-2 rounded-full hover:bg-blue-600">
            <!-- Icon kính lúp SVG nhỏ gọn -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
            </svg>
          </button>
        </div>
      </div>


    </div>

    <!-- Mobile Modal Menu -->
    <div v-if="isMobileMenuOpen"
      class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-start justify-end sm:hidden">
      <div class="w-3/4 bg-white shadow-md h-full p-4 relative">

        <!-- Close button -->
        <button @click="isMobileMenuOpen = false" class="absolute top-4 right-4 text-gray-600 hover:text-black">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Menu Items -->
        <div class="space-y-4 mt-10 text-sm">
          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'house']" /> Trang chủ
          </a>
          <div class="infor relative group inline-block">
            <!-- Nút tài khoản -->
            <a href="#" class="block text-gray-700 hover:text-blue-600 font-semibold">
              <font-awesome-icon :icon="['fas', 'user']" /> Tài khoản
            </a>

            <!-- Dropdown mượt mà -->
            <ul class="absolute left-0 top-full hidden group-hover:flex flex-col 
              bg-white border border-gray-200 rounded shadow-lg w-48 
              opacity-0 group-hover:opacity-100 
              translate-y-2 group-hover:translate-y-0 
              transition-all duration-300 ease-in-out z-50">
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Thông tin tài khoản</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Đơn hàng của tôi</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Trung tâm hỗ trợ</a></li>
              <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Đăng xuất</a></li>
            </ul>
          </div>




          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'cart-shopping']" /> Giỏ hàng
          </a>
          <a href="#" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'bell']" /> Thông báo
          </a>
          <NuxtLink to="/support" class="block text-gray-700 hover:text-blue-600">
            <font-awesome-icon :icon="['fas', 'info']" /> Hỗ trợ
          </NuxtLink>
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon
              :icon="['fas', 'right-to-bracket']" /> Đăng nhập</a>
          <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'plus']" /> Đăng
            ký</a>
        </div>
      </div>
    </div>
  </div>
  <Features />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
const isSubmitting = ref(false)
const isVerifying = ref(false)
const isLoggedIn = ref(false)
const canResend = ref(false)
const verificationPending = ref(false)
const verificationEmail = ref('')
const verifyUserId = ref(null)  
const showVerifyEmailForm = ref(false)
const verifyEmailInput = ref('')
const userName = ref('')

const updateLoginState = () => {
  const token = localStorage.getItem('access_token')
  const user = localStorage.getItem('user')
  if (token && user) {
    isLoggedIn.value = true
    userName.value = JSON.parse(user).name
  } else {
    isLoggedIn.value = false
    userName.value = ''
  }
}


onMounted(() => {
  updateLoginState()

  window.addEventListener('storage', (event) => {
    if (event.key === 'access_token' || event.key === 'user') {
      updateLoginState()
    }
  })
})

const showModal = ref(false)
const isLogin = ref(true)
const showOtp = ref(false)
const otp = ref('')
const tempUserId = ref(null)

const form = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  phone: '',
})

const openLogin = () => {
  isLogin.value = true
  showModal.value = true
  showOtp.value = false
}

const openRegister = () => {
  isLogin.value = false
  showModal.value = true
  showOtp.value = false
}

const closeModal = () => {
  showModal.value = false
  showOtp.value = false
  otp.value = ''
  form.value = {
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    phone: '',
  }
}

const config = useRuntimeConfig()
const api = config.public.apiBaseUrl

// toast
const toast = (icon, title) => {
  let toastInstance = null

  Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    width: '350px',
    height: '50px',
    padding: '10px 20px',
    customClass: {
      popup: 'text-sm rounded-md shadow-md'
    },
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toastEl) => {
      toastInstance = Swal.getTimerLeft()
      toastEl.addEventListener('mouseenter', () => Swal.stopTimer())
      toastEl.addEventListener('mouseleave', () => Swal.resumeTimer())
    }
  })
}

// login register
 const submitForm = async () => {
  isSubmitting.value = true
  try {
    if (isLogin.value) {
      const res = await axios.post(`${api}/login`, {
        email: form.value.email,
        password: form.value.password,
      })

      localStorage.setItem('access_token', res.data.token)
      localStorage.setItem('user', JSON.stringify(res.data.user))
      updateLoginState()

      toast('success', 'Đăng nhập thành công!')
      closeModal()
    } else {
      const res = await axios.post(`${api}/register`, {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.confirmPassword,
        phone: form.value.phone,
      })

      tempUserId.value = res.data.user_id
      showOtp.value = true
      startResendCountdown()
      toast('success', 'Đăng ký thành công. Kiểm tra email để lấy mã OTP.')
    }
  }catch (err) {
    if (
      isLogin.value &&
      err.response?.status === 403 &&
      err.response?.data?.message?.includes('chưa được xác minh')
    ) {
      verificationEmail.value = form.value.email
      verificationPending.value = true

      toast('warning', 'Tài khoản chưa được xác minh, Vui lòng xác minh trước khi đăng nhập');
        showVerifyEmailForm.value = true

    } else {
      const msg = err.response?.data?.errors
        ? Object.values(err.response.data.errors)[0][0]
        : err.response?.data?.message || 'Đã xảy ra lỗi.'
      toast('error', msg)
    }
  } finally {
    isSubmitting.value = false
  }
}

 const verifyOtp = async () => {
  isVerifying.value = true
  try {
    if (!/^\d{6}$/.test(otp.value)) {
      toast('warning', 'Mã OTP phải gồm 6 chữ số.')
      isVerifying.value = false
      return
    }

    await axios.post(`${api}/verify-otp`, {
      email: form.value.email,  
      otp: otp.value,
    })

    toast('success', 'Xác minh thành công! Bạn có thể đăng nhập.')

    form.value = {
      name: '',
      email: '',
      password: '',
      confirmPassword: '',
      phone: '',
    }
    otp.value = ''
    verificationEmail.value = ''
    verificationPending.value = false
    showOtp.value = false
    showVerifyEmailForm.value = false
    isLogin.value = true

  } catch (err) {
    const msg = err.response?.data?.message || 'Mã OTP không hợp lệ hoặc đã hết hạn.'
    toast('error', msg)
  } finally {
    isVerifying.value = false
  }
}


const sendVerificationRequest = async () => {
  try {
    const res = await axios.post(`${api}/resend-otp-by-email`, {
      email: verifyEmailInput.value,
    })

    verifyUserId.value = res.data.user_id
    tempUserId.value = res.data.user_id
    showVerifyEmailForm.value = false
    showOtp.value = true
    startResendCountdown()
    toast('success', 'Mã xác minh đã được gửi. Vui lòng kiểm tra email!')
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi mã xác minh.')
  }
}


// resend
const resendCountdown = ref(0)
let resendTimer = null

const startResendCountdown = () => {
  resendCountdown.value = 60
  resendTimer = setInterval(() => {
    resendCountdown.value--
    if (resendCountdown.value <= 0) {
      clearInterval(resendTimer)
    }
  }, 1000)
}

const resendVerificationEmail = async () => {
  try {
    await axios.post(`${api}/resend-otp-by-email`, {
      email: verificationEmail.value
    })

    toast('success', 'Email xác minh đã được gửi lại!')
    verificationPending.value = false
    startResendCountdown() // ⏱ bắt đầu đếm ngược lại
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi lại email.')
  }
}



const logout = async () => {
  try {
    const token = localStorage.getItem('access_token')

    if (!token) {
      toast('warning', 'Bạn chưa đăng nhập để đăng xuất.')
      return
    }

    await axios.post(`${api}/logout`, {}, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    localStorage.removeItem('access_token')
    localStorage.removeItem('user')
    updateLoginState()

    toast('success', 'Đăng xuất thành công!')
  } catch (err) {
    toast('error', 'Không thể đăng xuất. Vui lòng thử lại.')
    console.error('[Logout Error]', err)
  }
}

const isMobileMenuOpen = ref(false)
import Features from '~/components/shared/Features.vue'
</script>

<style scoped>
.input-style {
  @apply w-full border rounded-xl px-4 py-2 focus:ring-2 focus:ring-[#1BA0E2] outline-none;
}

.btn-style {
  @apply w-full bg-[#1BA0E2] text-white py-2 rounded-xl hover:bg-[#148cc6] transition;
}

.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.3s ease;
}

.fade-scale-enter-from {
  opacity: 0;
  transform: scale(0.95);
}

.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
.input-group {
  @apply relative;
}
.input-icon {
  @apply absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400;
}
.input-field {
  @apply w-full pl-10 pr-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400;
}
.btn-primary {
  @apply bg-[#1BA0E2] text-white py-2 rounded-md font-semibold hover:bg-[#1591cc] transition;
}
</style>