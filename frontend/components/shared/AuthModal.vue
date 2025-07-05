<template>
  <transition name="fade-slide">
    <div v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
      <div
        class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl flex relative overflow-hidden border border-gray-100">
        <div
          class="hidden md:flex w-1/2 flex-col items-center justify-center bg-gradient-to-br from-[#1BA0E2] to-[#1591cc] text-white p-8">
          <h2 class="text-3xl font-bold mb-4">Chào mừng bạn!</h2>
          <p class="text-sm text-center opacity-80">Tham gia ngay để trải nghiệm những tính năng tuyệt vời.</p>
          <img src="/images/img-form-removebg-preview.png" alt="Welcome Image"
            class="mt-6 w-3/4 max-w-[300px] object-contain" />
        </div>

        <div class="w-full md:w-1/2 p-6 sm:p-8 relative">
          <button @click="closeModal"
            class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl transition-transform duration-300 hover:scale-125">
            <i class="fas fa-times"></i>
          </button>

          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-[#1BA0E2] font-inter tracking-tight">
              {{ isLogin ? 'Đăng nhập' : 'Đăng ký' }}
            </h2>
            <button @click="isLogin = !isLogin"
              class="text-sm text-[#1BA0E2] hover:text-[#1591cc] transition-colors duration-200 font-medium">
              {{ isLogin ? 'Chưa có tài khoản?' : 'Đã có tài khoản?' }}
            </button>
          </div>

          <form v-if="!showOtp && !showVerifyEmailForm && !isForgotMode && !isResetMode" @submit.prevent="submitForm"
            class="space-y-4">
            <div v-if="!isLogin" class="relative">
              <i
                class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
              <input v-model="form.name" type="text" placeholder="Họ và tên"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <div class="relative">
              <i
                class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
              <input v-model="form.email" type="email" placeholder="Email"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <div class="relative">
              <i
                class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
              <input v-model="form.password" type="password" placeholder="Mật khẩu"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <div v-if="!isLogin" class="relative">
              <i
                class="fas fa-shield-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
              <input v-model="form.confirmPassword" type="password" placeholder="Xác nhận mật khẩu"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <div v-if="!isLogin" class="relative">
              <i
                class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
              <input v-model="form.phone" type="text" placeholder="Số điện thoại"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <div v-if="isLogin" class="flex justify-end">
              <button type="button" @click="isForgotMode = true"
                class="text-sm text-[#1BA0E2] hover:text-[#1591cc] font-inter font-medium transition-colors duration-200 mb-1">
                Quên mật khẩu?
              </button>
            </div>

            <button type="submit"
              class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02] focus:ring-2 focus:ring-[#1BA0E2] focus:ring-opacity-50 font-inter disabled:opacity-50"
              :disabled="isSubmitting">
              <span v-if="isSubmitting">
                <svg class="animate-spin h-5 w-5 mr-2 inline-block" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Đang xử lý...
              </span>
              <span v-else>{{ isLogin ? 'Đăng nhập' : 'Đăng ký' }}</span>
            </button>

            <button @click="loginWithGoogle" type="button"
              class="flex justify-center w-full items-center gap-2 px-5 py-3 rounded-xl border border-gray-300 bg-white text-gray-800 font-semibold text-base shadow-sm hover:shadow-md hover:border-gray-500 transition-all duration-150 active:scale-95">
              <svg class="w-6 h-6" viewBox="0 0 48 48">
                <g>
                  <path fill="#4285F4"
                    d="M24 9.5c3.8 0 7.2 1.34 9.81 3.55l7.27-7.27C36.66 2.05 30.71 0 24 0 14.8 0 6.4 4.96 1.44 12.44l8.58 6.67C12.21 13.45 17.61 9.5 24 9.5z" />
                  <path fill="#34A853"
                    d="M46.15 24.53c0-1.64-.16-3.22-.46-4.74H24v9h12.5c-.54 2.91-2.19 5.38-4.63 7.04l7.16 5.57C43.9 36.97 46.15 31.18 46.15 24.53z" />
                  <path fill="#FBBC05"
                    d="M9.42 28.9a14.2 14.2 0 0 1-.77-4.4c0-1.52.28-2.99.77-4.4l-8.58-6.67A24 24 0 0 0 0 24c0 3.81.93 7.41 2.58 10.56l8.84-6.66z" />
                  <path fill="#EA4335"
                    d="M24 48c6.71 0 12.33-2.2 16.44-5.98l-7.16-5.57c-2.01 1.35-4.62 2.16-9.28 2.16-6.39 0-11.79-3.95-13.98-9.61l-8.84 6.66C6.4 43.04 14.8 48 24 48z" />
                  <path fill="none" d="M0 0h48v48H0z" />
                </g>
              </svg>
              Đăng nhập bằng Google
            </button>
          </form>

          <!-- FORM 2: XÁC MINH EMAIL -->
          <form v-if="showVerifyEmailForm && !showOtp" @submit.prevent="sendVerificationRequest"
            class="space-y-4 border-t pt-5 mt-6">
            <p class="text-sm text-gray-600">Bạn cần xác minh email để tiếp tục. Nhập email của bạn:</p>
            <div class="relative">
              <i
                class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
              <input v-model="verifyEmailInput" type="email" placeholder="Email"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <button type="submit"
              class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02] focus:ring-2 focus:ring-[#1BA0E2] focus:ring-opacity-50 font-inter disabled:opacity-50"
              :disabled="isSubmitting">
              <span v-if="isSubmitting">
                <svg class="animate-spin h-5 w-5 mr-2 inline-block" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Đang gửi...
              </span>
              <span v-else>Gửi mã xác minh</span>
            </button>
            <button type="button"
              class="w-full text-sm text-gray-600 hover:text-[#1BA0E2] transition-colors duration-200 font-inter"
              @click="cancelOtp">
              Quay lại đăng nhập
            </button>
          </form>

          <!-- FORM 3: NHẬP OTP -->
          <form v-if="showOtp" @submit.prevent="verifyOtp" class="space-y-5 border-t pt-5 mt-6">
            <h2 class="text-xl font-bold text-[#1BA0E2] font-inter">Xác minh OTP</h2>
            <p class="text-sm text-gray-600">Vui lòng kiểm tra email và nhập mã OTP để xác minh tài khoản.</p>

            <div class="relative">
              <i
                class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-all duration-300 peer-focus:text-[#1BA0E2] peer-focus:scale-110"></i>
              <input v-model="otp" type="text" placeholder="Nhập mã OTP"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <button type="submit"
              class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02] focus:ring-2 focus:ring-[#1BA0E2] focus:ring-opacity-50 font-inter disabled:opacity-50"
              :disabled="isVerifying">
              <span v-if="isVerifying">
                <svg class="animate-spin h-5 w-5 mr-2 inline-block" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Đang xác minh...
              </span>
              <span v-else>Xác minh</span>
            </button>

            <button type="button" @click="resendVerificationEmail"
              class="w-full border border-[#1BA0E2] text-[#1BA0E2] py-3 rounded-xl font-semibold hover:bg-[#1BA0E2] hover:text-white transition-all duration-300 font-inter disabled:opacity-50"
              :disabled="resendCountdown > 0">
              <template v-if="resendCountdown > 0">Gửi lại mã sau {{ resendCountdown }} giây</template>
              <template v-else>Gửi lại mã OTP</template>
            </button>

            <button type="button"
              class="w-full text-sm text-gray-600 hover:text-[#1BA0E2] transition-colors duration-200 font-inter"
              @click="cancelOtp">
              Quay lại đăng nhập
            </button>
          </form>

          <!-- FORM 4: QUÊN MẬT KHẨU -->
          <form v-if="isForgotMode" @submit.prevent="sendForgotEmail" class="space-y-5 border-t pt-5 mt-6">
            <h2 class="text-xl font-bold text-[#1BA0E2] font-inter">Quên mật khẩu</h2>
            <p class="text-sm text-gray-600">Nhập email để nhận hướng dẫn đặt lại mật khẩu.</p>

            <div class="relative">
              <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input v-model="forgotEmail" type="email" placeholder="Nhập email"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <button type="submit"
              class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02]"
              :disabled="isSending">
              <span v-if="isSending"><i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...</span>
              <span v-else>Gửi hướng dẫn</span>
            </button>

            <button type="button"
              class="w-full text-sm text-gray-600 hover:text-[#1BA0E2] transition-colors duration-200 font-inter"
              @click="isForgotMode = false">
              Quay lại đăng nhập
            </button>
          </form>

          <!-- FORM 5: ĐẶT LẠI MẬT KHẨU -->
          <form v-if="isResetMode" @submit.prevent="submitResetPassword" class="space-y-5 border-t pt-5 mt-6">
            <h2 class="text-xl font-bold text-[#1BA0E2] font-inter">Đặt lại mật khẩu</h2>
            <p class="text-sm text-gray-600">Vui lòng nhập mã OTP và mật khẩu mới.</p>

            <div class="relative">
              <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input v-model="resetForm.otp" type="text" placeholder="Mã OTP" maxlength="6"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <div class="relative">
              <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input v-model="resetForm.password" type="password" placeholder="Mật khẩu mới"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <div class="relative">
              <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input v-model="resetForm.password_confirmation" type="password" placeholder="Xác nhận mật khẩu mới"
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1BA0E2] focus:bg-white transition-all duration-300 font-inter text-sm peer" />
            </div>

            <button type="submit"
              class="w-full bg-gradient-to-r from-[#1BA0E2] to-[#1591cc] text-white py-3 rounded-xl font-semibold hover:from-[#1591cc] hover:to-[#127aa3] transition-all duration-300 hover:scale-[1.02]"
              :disabled="isResetting">
              <span v-if="isResetting"><i class="fas fa-spinner fa-spin mr-2"></i>Đang đặt lại...</span>
              <span v-else>Đặt lại mật khẩu</span>
            </button>

            <button type="button"
              class="w-full text-sm text-gray-600 hover:text-[#1BA0E2] transition-colors duration-200 font-inter"
              @click="() => { isResetMode = false; isLogin = true; }">
              Quay lại trang đăng nhập
            </button>
          </form>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from '~/composables/useToast'
import { useRuntimeConfig } from '#imports'

const props = defineProps({
  show: { type: Boolean, default: false },
  initialMode: { type: String, default: 'login' },
})

const emit = defineEmits(['close', 'login-success'])

const { toast } = useToast()
const config = useRuntimeConfig()
const api = config.public.apiBaseUrl

const showModal = ref(props.show)
const isLogin = ref(props.initialMode === 'login')
const showOtp = ref(false)
const otp = ref('')
const tempUserId = ref(null)
const verifyUserId = ref(null)
const verificationEmail = ref('')
const verifyEmailInput = ref('')
const verificationPending = ref(false)
const isSubmitting = ref(false)
const isVerifying = ref(false)
const resendCountdown = ref(0)
const isForgotMode = ref(props.initialMode === 'forgot')
const isResetMode = ref(props.initialMode === 'reset')
const isSending = ref(false)
const isResetting = ref(false)
const showVerifyEmailForm = ref(props.initialMode === 'verify')
let resendTimer = null

const form = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  phone: '',
})

const forgotEmail = ref('')
const resetForm = ref({
  email: '',
  otp: '',
  password: '',
  password_confirmation: '',
})

const loginWithGoogle = () => {
  const width = 500
  const height = 600
  const left = window.screen.width / 2 - width / 2
  const top = window.screen.height / 2 - height / 2

  const googleAuthUrl = 'http://localhost:8000/api/auth/google/redirect'
  const expectedOrigin = 'http://localhost:8000'
  const popup = window.open(
    googleAuthUrl,
    'Google Login',
    `width=${width},height=${height},top=${top},left=${left}`
  )

  const messageHandler = async (event) => {
    if (event.origin !== expectedOrigin) {
      console.warn('Invalid origin:', event.origin)
      return
    }

    if (event.data?.token) {
      localStorage.setItem('access_token', event.data.token)

      try {
        const res = await fetch('http://localhost:8000/api/me', {
          headers: { Authorization: `Bearer ${event.data.token}` },
        })

        const data = await res.json()

        if (res.ok && data.data) {
          emit('login-success', data.data)
          toast('success', 'Đăng nhập Google thành công!')
          setTimeout(() => {
            window.location.reload()
          }, 1500)
          showModal.value = false
        } else {
          throw new Error(data.message || 'Không lấy được thông tin tài khoản!')
        }
      } catch (error) {
        console.error('Login verification failed:', error)
        toast('error', 'Xác thực đăng nhập thất bại.')
        localStorage.removeItem('access_token')
      } finally {
        popup?.close()
        window.removeEventListener('message', messageHandler)
      }
    } else if (event.data?.error) {
      toast('error', event.data.error)
      popup?.close()
      window.removeEventListener('message', messageHandler)
    }
  }

  window.addEventListener('message', messageHandler, { once: true })
}

const cancelOtp = () => {
  showOtp.value = false
  showVerifyEmailForm.value = false
  otp.value = ''
  verifyEmailInput.value = ''
  form.value = { name: '', email: '', password: '', confirmPassword: '', phone: '' }
}

const closeModal = () => {
  showModal.value = false
  showOtp.value = false
  otp.value = ''
  verifyEmailInput.value = ''
  form.value = { name: '', email: '', password: '', confirmPassword: '', phone: '' }
  isForgotMode.value = false
  isResetMode.value = false
  emit('close')
}

const submitForm = async () => {
  isSubmitting.value = true
  try {
    if (isLogin.value) {
      const res = await axios.post(`${api}/login`, {
        email: form.value.email,
        password: form.value.password,
      })
      localStorage.setItem('access_token', res.data.token)
      const userRes = await axios.get(`${api}/me`, {
        headers: { Authorization: `Bearer ${res.data.token}` },
      })
      emit('login-success', userRes.data.data)
      toast('success', 'Đăng nhập thành công!')
      setTimeout(() => {
        window.location.reload()
      }, 1500)
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
  } catch (err) {
    if (
      isLogin.value &&
      err.response?.status === 403 &&
      err.response?.data?.message?.includes('chưa được xác minh')
    ) {
      verificationEmail.value = form.value.email
      verificationPending.value = true
      toast('warning', 'Tài khoản chưa được xác minh, vui lòng xác minh trước khi đăng nhập')
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
    if (!/^[0-9]{6}$/.test(otp.value)) {
      toast('warning', 'Mã OTP phải gồm 6 chữ số.')
      return
    }
    await axios.post(`${api}/verify-otp`, {
      email: form.value.email,
      otp: otp.value,
    })
    toast('success', 'Xác minh thành công! Bạn có thể đăng nhập.')
    showOtp.value = false
    isLogin.value = true
  } catch (err) {
    toast('error', err.response?.data?.message || 'Mã OTP không hợp lệ hoặc đã hết hạn.')
  } finally {
    isVerifying.value = false
  }
}

const sendVerificationRequest = async () => {
  isSubmitting.value = true
  try {
    const res = await axios.post(`${api}/resend-otp-by-email`, {
      email: verifyEmailInput.value,
    })
    verificationEmail.value = verifyEmailInput.value
    verifyUserId.value = res.data.user_id
    tempUserId.value = res.data.user_id
    showOtp.value = true
    showVerifyEmailForm.value = false
    startResendCountdown()
    toast('success', 'Mã xác minh đã được gửi. Vui lòng kiểm tra email!')
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi mã xác minh.')
  } finally {
    isSubmitting.value = false
  }
}

const startResendCountdown = () => {
  resendCountdown.value = 60
  clearInterval(resendTimer)
  resendTimer = setInterval(() => {
    resendCountdown.value--
    if (resendCountdown.value <= 0) clearInterval(resendTimer)
  }, 1000)
}

const resendVerificationEmail = async () => {
  if (!verificationEmail.value) {
    toast('warning', 'Không tìm thấy email xác minh trước đó.')
    return
  }
  try {
    await axios.post(`${api}/resend-otp-by-email`, {
      email: verificationEmail.value,
    })
    toast('success', 'Email xác minh đã được gửi lại!')
    startResendCountdown()
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi lại email.')
  }
}

const sendForgotEmail = async () => {
  isSending.value = true
  try {
    const res = await axios.post(`${api}/send-forgot-password`, { email: forgotEmail.value })
    toast('success', 'Email đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra hộp thư đến của bạn.')
    resetForm.value.email = forgotEmail.value
    showOtp.value = false
    showVerifyEmailForm.value = false
    isLogin.value = false
    isResetMode.value = true
    isForgotMode.value = false
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể gửi email đặt lại mật khẩu.')
  } finally {
    isSending.value = false
  }
}

const submitResetPassword = async () => {
  isResetting.value = true
  try {
    await axios.post(`${api}/reset-password`, resetForm.value)
    toast('success', 'Mật khẩu đã được đặt lại thành công!')
    showResetPassword.value = false
    isResetMode.value = false
    isLogin.value = true
  } catch (err) {
    toast('error', err.response?.data?.message || 'Không thể đặt lại mật khẩu.')
  } finally {
    isResetting.value = false
  }
}

watch(() => props.show, (newVal) => {
  showModal.value = newVal
})

watch(() => props.initialMode, (newMode) => {
  isLogin.value = newMode === 'login'
  isForgotMode.value = newMode === 'forgot'
  isResetMode.value = newMode === 'reset'
  showVerifyEmailForm.value = newMode === 'verify'
})
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(50px) scale(0.95);
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.relative {
  position: relative;
}
</style>