<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
      <div v-if="loading" class="flex flex-col items-center">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-700">Đang xác minh thanh toán...</p>
      </div>
      <div v-else>
        <div v-if="success" class="flex flex-col items-center">
          <svg class="h-16 w-16 text-green-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h2 class="text-2xl font-bold text-green-600 mb-2">Thanh toán thành công!</h2>
          <p class="text-gray-700 mb-2">Cảm ơn bạn đã mua hàng. Bạn sẽ được chuyển về trang chủ sau {{ countdown }} giây.</p>
          <div class="text-left mt-4 w-full">
            <p><span class="font-semibold">Mã đơn hàng:</span> {{ orderId }}</p>
            <p><span class="font-semibold">Số tiền:</span> {{ formatPrice(amount) }} đ</p>
            <p><span class="font-semibold">Phương thức:</span> MOMO</p>
            <p><span class="font-semibold">Mã giao dịch:</span> {{ transactionId }}</p>
          </div>
          <NuxtLink to="/" class="mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Về trang chủ ngay</NuxtLink>
        </div>
        <div v-else class="flex flex-col items-center">
          <svg class="h-16 w-16 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
          <h2 class="text-2xl font-bold text-red-600 mb-2">Thanh toán thất bại!</h2>
          <p class="text-gray-700 mb-2">{{ message }}</p>
          <NuxtLink to="/checkout" class="mt-6 inline-block bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-700 transition">Thử lại</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { useCart } from '~/composables/useCart'

const route = useRoute()
const router = useRouter()
const { clearCart } = useCart()

const loading = ref(true)
const success = ref(false)
const message = ref('')
const orderId = ref('')
const amount = ref(0)
const transactionId = ref('')
const countdown = ref(3)
let countdownInterval = null

const formatPrice = (price) => {
  if (!price) return '0'
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

onMounted(async () => {
  const momoParams = {}
  Object.keys(route.query).forEach(key => {
    momoParams[key] = route.query[key]
  })

  if (Object.keys(momoParams).length === 0) {
    loading.value = false
    success.value = false
    message.value = 'Không nhận được dữ liệu từ MOMO'
    orderId.value = '-'
    amount.value = 0
    transactionId.value = '-'
    return
  }

  const controller = new AbortController()
  const timeoutId = setTimeout(() => controller.abort(), 5000)

  try {
    const queryString = window.location.search
    const response = await axios.get(`http://localhost:8000/api/payments/momo/return${queryString}`, {
      signal: controller.signal
    })
    clearTimeout(timeoutId)

    if (!response.data) {
      throw new Error('Không nhận được phản hồi từ server')
    }

    console.log('MOMO return data:', response.data)
    loading.value = false

    if (response.data && response.data.message && response.data.message.includes('thành công')) {
      success.value = true
      message.value = response.data.message || 'Thanh toán thành công'
      orderId.value = response.data.order_id || momoParams.orderId || '-'
      amount.value = response.data.amount || Number(momoParams.amount) || 0
      transactionId.value = response.data.transId || momoParams.transId || '-'

      // Save order_id to localStorage if available
      if (response.data.order_id) {
        localStorage.setItem('lastOrderId', response.data.order_id)
      }
      // Clear cart after successful payment
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
      message.value = response.data.message || 'Thanh toán không thành công. Vui lòng thử lại.'
      orderId.value = response.data.order_id || momoParams.orderId || '-'
      amount.value = response.data.amount || Number(momoParams.amount) || 0
      transactionId.value = response.data.transId || momoParams.transId || '-'
    }
  } catch (err) {
    clearTimeout(timeoutId)
    console.error('Error processing MOMO return:', err)
    loading.value = false
    success.value = false
    message.value = err.name === 'AbortError' ? 'Hết thời gian chờ phản hồi từ server' : `Có lỗi xảy ra khi xác minh thanh toán: ${err.message}`
    orderId.value = momoParams.orderId || '-'
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