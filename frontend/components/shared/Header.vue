<template>
  <div>
    <!-- Thanh trên cùng -->
  <header class="bg-[#1BA0E2] text-white text-sm py-2">
    <div class="container mx-auto flex justify-between items-center px-4">
      <div class="space-x-2">
        <NuxtLink to="/Seller" class="hover:underline inline-flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 10h4l3 10h8l3-10h4" /></svg>
          Đăng ký bán hàng
        </NuxtLink>
        <a href="#" class="hover:underline inline-flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2h5" /></svg>
          Kết nối <font-awesome-icon :icon="['fab', 'facebook']" />
          <font-awesome-icon :icon="['fab', 'instagram']" />
        </a>
      </div>
      <div class="hidden sm:flex space-x-4">
        <a href="#" class="hover:underline inline-flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.437L4 17h5" /></svg>
          Thông báo
        </a>
        <NuxtLink to="#" @click.prevent="openLogin" class="hover:underline inline-flex items-center gap-1">
          <i><font-awesome-icon :icon="['fas', 'right-to-bracket']" /></i>
          Đăng nhập
        </NuxtLink>
        <NuxtLink to="#" @click.prevent="openRegister" class="hover:underline inline-flex items-center gap-1">
          <i><font-awesome-icon :icon="['fas', 'plus']" /></i>
          Đăng ký
        </NuxtLink>
      </div>
    </div>
  </header>

  <!-- MODAL -->
  <transition name="fade-scale">
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative">
        <button class="absolute top-3 right-4 text-gray-400 hover:text-red-500 text-2xl" @click="closeModal">
          &times;
        </button>

        <!-- Form đăng ký / đăng nhập -->
        <div v-if="!showOtp" class="space-y-5">
          <div class="flex justify-between items-center mb-2">
            <h2 class="text-2xl font-bold text-[#1BA0E2]">{{ isLogin ? 'Đăng nhập' : 'Đăng ký' }}</h2>
            <button @click="isLogin = !isLogin" class="text-sm text-blue-500 hover:underline">
              {{ isLogin ? 'Chưa có tài khoản?' : 'Đã có tài khoản?' }}
            </button>
          </div>
          <form @submit.prevent="submitForm" class="space-y-3">
            <input v-if="!isLogin" v-model="form.name" type="text" placeholder="Họ và tên"
              class="input-style" />
            <input v-model="form.email" type="email" placeholder="Email" class="input-style" />
            <input v-model="form.password" type="password" placeholder="Mật khẩu" class="input-style" />
            <input v-if="!isLogin" v-model="form.confirmPassword" type="password" placeholder="Xác nhận mật khẩu"
              class="input-style" />
            <input v-if="!isLogin" v-model="form.phone" type="text" placeholder="Số điện thoại"
              class="input-style" />
            <button type="submit" class="btn-style">
              {{ isLogin ? 'Đăng nhập' : 'Đăng ký' }}
            </button>
          </form>
        </div>

        <!-- Form nhập OTP -->
        <div v-else class="space-y-5">
          <h2 class="text-xl font-semibold text-[#1BA0E2]">Xác minh OTP</h2>
          <p class="text-gray-600 text-sm">Vui lòng kiểm tra email và nhập mã OTP để xác minh tài khoản.</p>
          <form @submit.prevent="verifyOtp" class="space-y-3">
            <input v-model="otp" type="text" placeholder="Nhập mã OTP" class="input-style" />
            <button type="submit" class="btn-style">Xác minh</button>
          </form>
        </div>
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
        <div class="absolute left-0 mt-6 w-[1200px] bg-white border border-gray-200 shadow-lg rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-6 grid grid-cols-5 gap-6">

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
            <img src="https://media.canifa.com/mega_menu/item/Nam-1-menu-05Mar.webp" alt="Nữ 1" class="rounded-md object-cover w-full h-48">
            <img src="https://media.canifa.com/mega_menu/item/Nu-2-menu-05Mar.webp" alt="Nữ 2" class="rounded-md object-cover w-full h-48">
            </div>
        </div>
        </div>



        <!-- Tìm kiếm (desktop) -->
        <div class="flex-1 mx-4 hidden sm:flex justify-center">
          <input
            type="text"
            placeholder="Tìm kiếm"
            class="w-full max-w-[500px] px-4 py-2 border border-gray-300 rounded-full focus:outline-none"
          />
        </div>

        <!-- Menu PC -->
        <div class="hidden sm:flex items-center gap-x-6 text-sm font-medium text-gray-700">
      <!-- Trang chủ -->
      <NuxtLink
        href="/"
        class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1"
      >
        <font-awesome-icon :icon="['fas', 'house']" />
        Trang chủ
      </NuxtLink>

      <!-- Tài khoản -->
        <div class="relative group inline-block">
          <div class="cursor-pointer hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1">
            <font-awesome-icon :icon="['fas', 'user']" />
            Tài khoản
          </div>

          <!-- Dropdown nếu có -->
          <ul
            class="absolute left-0 top-full hidden group-hover:flex flex-col 
                  bg-white border border-gray-200 rounded shadow-lg w-48 
                  opacity-0 group-hover:opacity-100 
                  translate-y-2 group-hover:translate-y-0 
                  transition-all duration-300 ease-in-out z-50 text-sm text-gray-700"
          >
            <li><a href="/users/profile" class="block px-4 py-2 hover:bg-gray-100">Thông tin tài khoản</a></li>
            <li><a href="/users/order" class="block px-4 py-2 hover:bg-gray-100">Đơn hàng của tôi</a></li>
            <li><a href="/support" class="block px-4 py-2 hover:bg-gray-100">Trung tâm hỗ trợ</a></li>
            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Đăng xuất</a></li>
          </ul>
        </div>

        <!-- Giỏ hàng -->
        <NuxtLink
          href="/cart"
          class="hover:text-blue-600 transition-colors duration-200 tracking-wide flex items-center gap-1"
        >
          <font-awesome-icon :icon="['fas', 'cart-shopping']" />
          Giỏ hàng
        </NuxtLink>
      </div>

      
        <!-- Icon menu mobile -->
        <div class="sm:hidden">
          <button @click="isMobileMenuOpen = true">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>

   <!-- Tìm kiếm (mobile) -->
    <div class="px-4 pb-3 sm:hidden">
      <div class="relative">
        <input
          type="text"
          placeholder="Tìm kiếm"
          class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-full focus:outline-none"
        />
        <button
          class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-[#1BA0E2] p-2 rounded-full hover:bg-blue-600"
        >
          <!-- Icon kính lúp SVG nhỏ gọn -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
          </svg>
        </button>
      </div>
    </div>


    </div> 

    <!-- Mobile Modal Menu -->
  <div v-if="isMobileMenuOpen" class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-start justify-end sm:hidden">
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
      <ul
        class="absolute left-0 top-full hidden group-hover:flex flex-col 
              bg-white border border-gray-200 rounded shadow-lg w-48 
              opacity-0 group-hover:opacity-100 
              translate-y-2 group-hover:translate-y-0 
              transition-all duration-300 ease-in-out z-50"
      >
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
        <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'right-to-bracket']" /> Đăng nhập</a>
        <a href="#" class="block text-gray-700 hover:text-blue-600"><font-awesome-icon :icon="['fas', 'plus']" /> Đăng ký</a>
      </div>
    </div>
  </div>
  </div>
        <Features />
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

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
const submitForm = async () => {
  try {
    if (isLogin.value) {
      const res = await axios.post(`${api}/login`, {
        email: form.value.email,
        password: form.value.password,
      })
      Swal.fire('Thành công!', 'Đăng nhập thành công!', 'success')
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
      Swal.fire('Thành công!', 'Đăng ký thành công. Vui lòng kiểm tra email để lấy mã OTP.', 'success')
    }
  } catch (err) {
   console.error('Lỗi chi tiết:', err.response?.data)
  const errors = err.response?.data?.errors
  const message = err.response?.data?.message || 'Đăng ký thất bại.'

  if (errors) {
    const firstError = Object.values(errors)[0][0]
    Swal.fire('Lỗi!', firstError, 'error')
  } else {
    Swal.fire('Lỗi!', message, 'error')
  }
  }
}

const verifyOtp = async () => {
  try {
    await axios.post(`${api}/verify-otp`, {
      user_id: tempUserId.value,
      otp: otp.value,
    })
    Swal.fire('Xác minh thành công!', 'Bạn có thể đăng nhập.', 'success')
    closeModal()
  } catch (err) {
    const msg = err.response?.data?.message || 'Mã OTP không hợp lệ hoặc đã hết hạn.'
    Swal.fire('Lỗi!', msg, 'error')
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
.fade-scale-enter-active, .fade-scale-leave-active {
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
</style>