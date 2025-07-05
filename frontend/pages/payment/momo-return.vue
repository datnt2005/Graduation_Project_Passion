<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-gray-100">
    <div
      class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-lg w-full text-center transform transition-all duration-300 hover:scale-105">
      <!-- Background decorative element -->
      <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-green-500/10 rounded-2xl -z-10"></div>

      <div v-if="loading" class="flex flex-col items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-600 mb-6"></div>
        <p class="text-gray-600 text-lg font-medium animate-pulse">Đang xác minh thanh toán...</p>
      </div>

      <div v-else>
        <div v-if="success" class="flex flex-col items-center py-6">
          <div class="relative mb-6">
            <div class="relative inline-block">
              <!-- Hiệu ứng nền mờ -->
              <div class="absolute inset-0 bg-green-100 rounded-full blur-md opacity-50 z-0"></div>

              <!-- Icon check -->
              <svg class="h-20 w-20 text-green-500 animate-bounce relative z-10" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>

          </div>
          <h2 class="text-3xl font-extrabold text-green-600 mb-3 animate-fade-in">Thanh Toán Thành Công!</h2>
          <p class="text-gray-600 mb-6 text-lg">Cảm ơn bạn đã mua hàng. Bạn sẽ được chuyển về trang chủ sau {{ countdown
          }} giây.</p>
          <div class="bg-gray-50 rounded-lg p-6 w-full text-left border border-gray-200 shadow-sm">
            <div class="grid grid-cols-2 gap-4">
              <p><span class="font-semibold text-gray-800">Mã vận đơn:</span> {{ tracking_code || 'Đang cập nhật' }}</p>
              <p><span class="font-semibold text-gray-800">Số tiền:</span> {{ formatPrice(amount) }} đ</p>
              <p><span class="font-semibold text-gray-800">Phương thức:</span> MOMO</p>
              <p><span class="font-semibold text-gray-800">Mã giao dịch:</span> {{ transactionId }}</p>
            </div>
          </div>
          <NuxtLink to="/"
            class="mt-6 inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Về Trang Chủ</NuxtLink>
        </div>

        <div v-else class="flex flex-col items-center py-6">
          <div class="relative mb-6 w-20 h-20">
            <div class="absolute inset-0 bg-red-100 rounded-full blur-md opacity-50 z-0"></div>
            <svg class="absolute inset-0 h-20 w-20 text-red-500 animate-pulse z-10" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>

          <h2 class="text-3xl font-extrabold text-red-600 mb-3 animate-fade-in">Thanh Toán Thất Bại!</h2>
          <p class="text-gray-600 mb-6 text-lg">{{ message }}</p>
          <div class="bg-gray-50 rounded-lg p-6 w-full text-left border border-gray-200 shadow-sm">
            <div class="grid grid-cols-2 gap-4">
              <p><span class="font-semibold text-gray-800">Mã vận đơn:</span> {{ tracking_code || 'Đang cập nhật' }}</p>
              <p><span class="font-semibold text-gray-800">Số tiền:</span> {{ formatPrice(amount) }} đ</p>
              <p><span class="font-semibold text-gray-800">Phương thức:</span> MOMO</p>
              <p><span class="font-semibold text-gray-800">Mã giao dịch:</span> {{ transactionId }}</p>
            </div>
          </div>
          <NuxtLink to="/checkout"
            class="mt-6 inline-block bg-gradient-to-r from-gray-500 to-gray-600 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-md hover:shadow-lg">
            Thử Lại</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRuntimeConfig } from '#imports'
import { useCart } from '~/composables/useCart'

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { clearCart } = useCart()

const loading = ref(true)
const success = ref(false)
const message = ref('')
const orderId = ref('')
const amount = ref(0)
const transactionId = ref('')
const countdown = ref(3)
const tracking_code = ref('')
let countdownInterval = null

const formatPrice = (price) => {
  if (!price) return '0'
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

onMounted(async () => {
  const momoParams = { ...route.query }
  console.log('MOMO Redirect Query Parameters:', momoParams)

  if (!momoParams.orderId || !momoParams.resultCode || !momoParams.signature) {
    loading.value = false
    success.value = false
    message.value = 'Thiếu tham số MOMO bắt buộc'
    orderId.value = momoParams.orderId || '-'
    amount.value = Number(momoParams.amount) || 0
    transactionId.value = momoParams.transId || '-'
    console.warn('Missing required MOMO parameters:', momoParams)
    return
  }

  try {
    const queryString = new URLSearchParams(momoParams).toString()
    console.log('Fetch URL:', `${config.public.apiBaseUrl}/payments/momo/return?${queryString}`)
    const res = await fetch(`${config.public.apiBaseUrl}/payments/momo/return?${queryString}`, {
      headers: {
        'Accept': 'application/json'
      }
    })

    if (!res.ok) {
      const errorData = await res.json()
      console.error('Backend Error Response:', errorData)
      throw new Error(errorData.message || `HTTP error! Status: ${res.status}`)
    }

    const data = await res.json()
    console.log('MOMO Return Data:', data)
    loading.value = false

    if (data.message && data.message.includes('thành công') || momoParams.resultCode === '0') {
      success.value = true
      message.value = data.message || 'Thanh toán thành công'
      tracking_code.value = data.tracking_code || 'Đang cập nhật'
      amount.value = data.amount || Number(momoParams.amount) || 0
      transactionId.value = data.transId || momoParams.transId || '-'

      if (data.order_id) {
        localStorage.setItem('lastOrderId', data.order_id)
      }
      await clearCart()

      countdownInterval = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) {
          clearInterval(countdownInterval)
          router.push('/')
        }
      }, 1000)
    } else {
      success.value = false
      message.value = data.message || 'Thanh toán thất bại'
      tracking_code.value = data.tracking_code || 'Đang cập nhật'
      amount.value = data.amount || Number(momoParams.amount) || 0
      transactionId.value = data.transId || momoParams.transId || '-'
    }
  } catch (err) {
    console.error('Fetch error:', err)
    loading.value = false
    success.value = false
    message.value = err.message || 'Có lỗi xảy ra khi xác minh thanh toán'
    tracking_code.value = 'Đang cập nhật'
    amount.value = Number(momoParams.amount) || 0
    transactionId.value = momoParams.transId || '-'
  }
})

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})
</script>

<style scoped>
/* Custom animation keyframes */
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.5s ease-out;
}
</style>