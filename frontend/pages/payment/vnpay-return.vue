<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-gray-100 mt-20 mb-20 pb-10">
    <div class="relative bg-white rounded-2xl shadow-xl p-8 max-w-2xl w-full text-center transform transition-all duration-300 hover:scale-[1.01]">
      <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-green-500/10 rounded-2xl -z-10"></div>

      <!-- Đang tải -->
      <div v-if="loading" class="flex flex-col items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-600 mb-6"></div>
        <p class="text-gray-600 text-lg font-medium animate-pulse">Đang xác minh thanh toán...</p>
      </div>

      <!-- Thành công -->
      <div v-else-if="success" class="flex flex-col items-center py-6 animate-fade-in">
        <svg width="100" height="100" viewBox="0 0 24 24" fill="green" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 12l2 2 4-4M12 2a10 10 0 100 20 10 10 0 000-20z" stroke="white" stroke-width="2" fill="green"/>
        </svg>
        <h2 class="text-3xl font-extrabold text-green-600 mb-2">Thanh Toán Thành Công!</h2>
        <p class="text-gray-700 mb-6 text-lg">
          Cảm ơn bạn đã mua hàng tại <span class="font-semibold text-primary">Passion</span>.
        </p>
        <!-- THÔNG TIN ĐƠN HÀNG -->
        <div v-if="Array.isArray(orderDetail) && orderDetail.length" class="space-y-8">
          <div v-for="order in orderDetail" :key="order.id" class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
              <div><span class="font-medium text-gray-500">Mã vận đơn:</span> <span class="font-semibold">{{ order.shipping?.tracking_code || 'Đang cập nhật' }}</span></div>
              <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{ order.user?.name || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">Email:</span> <span class="font-semibold">{{ order.user?.email || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">SĐT:</span> <span class="font-semibold">{{ order.address?.phone || 'N/A' }}</span></div>
              <div class="sm:col-span-2">
                <span class="font-medium text-gray-500">Địa chỉ giao hàng:</span>
                <div class="mt-1 font-semibold text-gray-800">
                  {{
                    order.address
                      ? `${order.address.detail || ''}, ${order.address.ward_name || ''}, ${order.address.district_name || ''}, ${order.address.province_name || ''}`.replace(/(, )+/g, ', ').replace(/^, |, $/g, '')
                      : 'Không có'
                  }}
                </div>
              </div>
              <div class="sm:col-span-2 text-center mt-2">
                <span class="font-medium text-gray-500">Phương thức:</span>
                <span class="font-semibold">VNPay</span>
              </div>
            </div>
            <div class="text-right text-lg font-bold text-blue-700 border-t pt-4 mt-4">
              Tổng thanh toán: {{ formatPrice(order.final_price) }} đ
            </div>
            <!-- Danh sách sản phẩm đã đặt (nếu có) -->
            <div v-if="order.order_items?.length" class="mt-4">
              <div class="font-medium text-gray-700 mb-2">Sản phẩm đã đặt:</div>
              <div class="max-h-64 overflow-y-auto pr-2">
                <div v-for="item in order.order_items" :key="item.product?.id + '-' + (item.variant?.id || '')" class="flex items-center gap-4 border-b py-2 last:border-0">
                  <img :src="item.product?.thumbnail ? mediaBaseUrl + item.product.thumbnail : '/images/no-image.png'" :alt="item.product?.name || 'Ảnh sản phẩm'" class="w-12 h-12 object-cover rounded-md border" />
                  <div class="flex-1">
                    <div class="font-semibold text-gray-900">{{ item.product?.name || '-' }}</div>
                    <div v-if="item.variant && item.variant.name" class="text-xs text-gray-500">Phân loại: {{ item.variant.name }}</div>
                    <div class="text-xs text-gray-500">Số lượng: {{ item.quantity }} × {{ formatPrice(item.price) }}</div>
                  </div>
                  <div class="font-bold text-blue-700">{{ formatPrice(item.total) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Nếu chỉ có 1 đơn, vẫn hỗ trợ hiển thị cũ cho backward compatibility -->
        <div v-else-if="orderDetail && orderDetail.user" class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
            <div><span class="font-medium text-gray-500">Mã vận đơn:</span> <span class="font-semibold">{{ tracking_code || 'Đang cập nhật' }}</span></div>
            <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{ orderDetail?.user?.name || 'N/A' }}</span></div>
            <div><span class="font-medium text-gray-500">Email:</span> <span class="font-semibold">{{ orderDetail?.user?.email || 'N/A' }}</span></div>
            <div><span class="font-medium text-gray-500">SĐT:</span> <span class="font-semibold">{{ orderDetail?.address?.phone || 'N/A' }}</span></div>
            <div class="sm:col-span-2">
              <span class="font-medium text-gray-500">Địa chỉ giao hàng:</span>
              <div class="mt-1 font-semibold text-gray-800">
                {{
                  orderDetail.address
                    ? `${orderDetail.address.detail}, ${orderDetail.address.ward_name}, ${orderDetail.address.district_name}, ${orderDetail.address.province_name}`
                    : 'Không có'
                }}
              </div>
            </div>
            <div class="sm:col-span-2 text-center mt-2">
              <span class="font-medium text-gray-500">Phương thức:</span>
              <span class="font-semibold">VNPay</span>
            </div>
          </div>
          <div class="text-right text-lg font-bold text-blue-700 border-t pt-4 mt-4">
            Tổng thanh toán: {{ formatPrice(amount) }} đ
          </div>
          <!-- Danh sách sản phẩm đã đặt (nếu có) -->
          <div v-if="orderDetail?.order_items?.length" class="mt-4">
            <div class="font-medium text-gray-700 mb-2">Sản phẩm đã đặt:</div>
            <div class="max-h-64 overflow-y-auto pr-2">
              <div v-for="item in orderDetail.order_items" :key="item.product?.id + '-' + (item.variant?.id || '')" class="flex items-center gap-4 border-b py-2 last:border-0">
                <img :src="item.product?.thumbnail ? mediaBaseUrl + item.product.thumbnail : '/images/no-image.png'" :alt="item.product?.name || 'Ảnh sản phẩm'" class="w-12 h-12 object-cover rounded-md border" />
                <div class="flex-1">
                  <div class="font-semibold text-gray-900">{{ item.product?.name || '-' }}</div>
                  <div v-if="item.variant && item.variant.name" class="text-xs text-gray-500">Phân loại: {{ item.variant.name }}</div>
                  <div class="text-xs text-gray-500">Số lượng: {{ item.quantity }} × {{ formatPrice(item.price) }}</div>
                </div>
                <div class="font-bold text-blue-700">{{ formatPrice(item.total) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Về trang chủ -->
        <NuxtLink to="/" class="mt-6 inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg">
          Về Trang Chủ
        </NuxtLink>
      </div>

      <!-- Thất bại -->
      <div v-else class="flex flex-col items-center py-6 animate-fade-in">
        <svg class="h-20 w-20 text-red-500 animate-pulse mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <h2 class="text-3xl font-extrabold text-red-600 mb-3">Thanh Toán Thất Bại!</h2>
        <p class="text-gray-600 mb-6 text-lg">{{ message }}</p>

        <NuxtLink to="/checkout" class="mt-6 inline-block bg-gradient-to-r from-gray-500 to-gray-600 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-md hover:shadow-lg">
          Thử Lại
        </NuxtLink>
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
const mediaBaseUrl = config.public.mediaBaseUrl.endsWith('/') ? config.public.mediaBaseUrl : config.public.mediaBaseUrl + '/'
const { clearCart } = useCart()

const loading = ref(true)
const success = ref(false)
const message = ref('')
const orderId = ref('')
const amount = ref(0)
const bankCode = ref('')
const transactionId = ref('')
const countdown = ref(3)
const tracking_code = ref('')
const orderDetail = ref({})
const provinces = ref([])
const districts = ref([])
const wards = ref([])
let countdownInterval = null

const formatPrice = (price) => {
  if (!price) return '0'
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const statusText = (status) => ({
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  shipped: 'Đã gửi hàng',
  delivered: 'Đã giao hàng',
  cancelled: 'Đã huỷ',
  completed: 'Đã thanh toán',
  failed: 'Thất bại',
  refunded: 'Đã hoàn tiền',
  success: 'Thành công',
  paid: 'Đã thanh toán',
  unpaid: 'Chưa thanh toán',
  waiting: 'Đang chờ',
  error: 'Lỗi',
})[status] || (status ? status : 'Không xác định')

const loadProvinces = async () => {
  const res = await fetch(`${config.public.apiBaseUrl}/ghn/provinces`)
  const data = await res.json()
  provinces.value = data.data || []
}

const loadDistricts = async (province_id) => {
  const res = await fetch(`${config.public.apiBaseUrl}/ghn/districts`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ province_id }),
  })
  const data = await res.json()
  districts.value = data.data || []
}

const loadWards = async (district_id) => {
  const res = await fetch(`${config.public.apiBaseUrl}/ghn/wards`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ district_id }),
  })
  const data = await res.json()
  wards.value = data.data || []
}

onMounted(async () => {
  // Collect all query parameters, not just vnp_ ones, to ensure nothing is missed
  const queryParams = { ...route.query }
  console.log('VNPAY Redirect Query Parameters:', queryParams)

  if (!queryParams.vnp_TxnRef || !queryParams.vnp_ResponseCode || !queryParams.vnp_SecureHash) {
    loading.value = false
    success.value = false
    message.value = 'Thiếu tham số VNPAY bắt buộc'
    orderId.value = queryParams.vnp_TxnRef ? queryParams.vnp_TxnRef.split('_')[0] : '-'
    amount.value = Number(queryParams.vnp_Amount) / 100 || 0
    bankCode.value = queryParams.vnp_BankCode || '-'
    transactionId.value = queryParams.vnp_TransactionNo || '-'
    console.warn('Missing required VNPAY parameters:', queryParams)
    return
  }

  try {
    const queryString = new URLSearchParams(queryParams).toString()
    console.log('Fetch URL:', `${config.public.apiBaseUrl}/payments/vnpay/return?${queryString}`)
    const res = await fetch(`${config.public.apiBaseUrl}/payments/vnpay/return?${queryString}`, {
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
    console.log('VNPAY Return Data:', data)
    loading.value = false

    if (data.success || queryParams.vnp_ResponseCode === '00') {
      success.value = true
      message.value = data.message || 'Thanh toán thành công'
      tracking_code.value = data.tracking_code || 'Đang cập nhật'
      amount.value = data.amount || (Number(queryParams.vnp_Amount) / 100) || 0
      bankCode.value = data.bank_code || queryParams.vnp_BankCode || '-'
      transactionId.value = data.transaction_id || queryParams.vnp_TransactionNo || '-'

      if (data.order_ids && Array.isArray(data.order_ids)) {
        orderDetail.value = [];
        for (const id of data.order_ids) {
          const token = localStorage.getItem('access_token');
          if (token) {
            const orderRes = await fetch(`${config.public.apiBaseUrl}/user/orders/${id}`, {
              headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
              },
            });
            if (orderRes.ok) {
              const orderData = await orderRes.json();
              orderDetail.value.push(orderData);
            }
          }
        }
      }
      await clearCart()
    } else {
      success.value = false
      message.value = data.message || 'Thanh toán thất bại'
      tracking_code.value = data.tracking_code || 'Đang cập nhật'
      amount.value = data.amount || (Number(queryParams.vnp_Amount) / 100) || 0
      bankCode.value = data.bank_code || queryParams.vnp_BankCode || '-'
      transactionId.value = data.transaction_id || queryParams.vnp_TransactionNo || '-'
    }
  } catch (err) {
    console.error('Fetch error:', err)
    loading.value = false
    success.value = false
    message.value = err.message || 'Có lỗi xảy ra khi xác minh thanh toán'
    orderId.value = queryParams.vnp_TxnRef ? queryParams.vnp_TxnRef.split('_')[0] : '-'
    amount.value = Number(queryParams.vnp_Amount) / 100 || 0
    bankCode.value = queryParams.vnp_BankCode || '-'
    transactionId.value = queryParams.vnp_TransactionNo || '-'
  }
})

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})
</script>

<style scoped>
@keyframes fadeIn {
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
  animation: fadeIn 0.6s ease-out;
}
</style>