<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-gray-100 mt-20 mb-20">
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
        <h2 class="text-3xl font-extrabold text-green-600 mb-2">Đặt Hàng Thành Công!</h2>
        <p class="text-gray-700 mb-6 text-lg">
          Cảm ơn bạn đã mua hàng tại <span class="font-semibold text-primary">Passion</span>.
        </p>

  
      <!-- THÔNG TIN ĐƠN HÀNG -->
    <div class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
      <!-- Dòng 1: Mã đơn + vận đơn -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
        <div><span class="font-medium text-gray-500">Mã đơn hàng:</span> <span class="font-semibold">#{{ orderDetail?.id || 'N/A' }}</span></div>
        <div><span class="font-medium text-gray-500">Mã vận đơn:</span> <span class="font-semibold">{{ tracking_code || 'Đang cập nhật' }}</span></div>

        <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{ orderDetail?.user?.name || 'N/A' }}</span></div>
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

        <div>
          <span class="font-medium text-gray-500">Phương thức:</span>
          <span class="font-semibold">
            {{
              orderDetail?.payments?.[0]?.method === 'COD'
                ? 'Thanh toán khi nhận hàng (COD)'
                : orderDetail?.payments?.[0]?.method === 'VNPAY'
                ? 'VNPay'
                : 'N/A'
            }}
          </span>
        </div>

        <div v-if="bankCode">
          <span class="font-medium text-gray-500">Ngân hàng:</span>
          <span class="font-semibold">{{ bankCode }}</span>
        </div>

        <div v-if="transactionId">
          <span class="font-medium text-gray-500">Mã giao dịch:</span>
          <span class="font-semibold">{{ transactionId }}</span>
        </div>
      </div>

      <!-- Tổng tiền -->
      <div class="text-right text-lg font-bold text-blue-700 border-t pt-4 mt-4">
        Tổng thanh toán: {{ formatPrice(amount || parsePrice(orderDetail?.final_price) || 0) }} đ
      </div>

      <!-- DANH SÁCH SẢN PHẨM -->
      <div>
        <h3 class="text-base font-semibold text-gray-700 mb-2">Sản phẩm đã đặt:</h3>
        <ul class="divide-y divide-gray-100 text-sm">
          <li
            v-for="item in orderDetail?.order_items || []"
            :key="item.id"
            class="py-2 flex justify-between items-center"
          >
            <span class="text-gray-800">{{ item?.product?.name || 'Sản phẩm' }} (x{{ item?.quantity || 0 }})</span>
            <span class="text-gray-600 font-semibold">{{ formatPrice(parsePrice(item?.price) || 0) }} đ</span>
          </li>
        </ul>
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
import { useRoute } from 'vue-router'
import { useRuntimeConfig } from '#imports'
import { useCart } from '~/composables/useCart'

const route = useRoute()
const config = useRuntimeConfig()
const { clearCart } = useCart()

const loading = ref(true)
const success = ref(false)
const message = ref('')
const amount = ref(0)
const bankCode = ref('')
const transactionId = ref('')
const tracking_code = ref('')
const orderDetail = ref({ order_items: [], address: {} })

// Thêm cho địa chỉ
const provinces = ref([])
const districts = ref([])
const wards = ref([])

const formatPrice = (price) => {
  if (!price) return '0'
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const parsePrice = (priceStr) => {
  if (!priceStr) return 0
  return parseInt(priceStr.replace(/[^\d]/g, '')) || 0
}

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
  const orderId = route.query.id || route.query.vnp_TxnRef?.split('_')[0]

  if (!orderId) {
    success.value = false
    loading.value = false
    message.value = 'Không tìm thấy thông tin đơn hàng.'
    return
  }

  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      message.value = 'Vui lòng đăng nhập để xem thông tin đơn hàng.'
      success.value = false
      loading.value = false
      window.dispatchEvent(new CustomEvent('openLoginModal'))
      return
    }

    const orderRes = await fetch(`${config.public.apiBaseUrl}/orders/${orderId}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    const orderData = await orderRes.json()
    if (!orderRes.ok || !orderData.data) {
      throw new Error(orderData.message || 'Không thể lấy thông tin đơn hàng')
    }

    orderDetail.value = orderData.data
    tracking_code.value = orderDetail.value.shipping?.tracking_code || 'Đang cập nhật'

    // Load địa chỉ
    await loadProvinces()
    await loadDistricts(orderDetail.value.address.province_id)
    await loadWards(orderDetail.value.address.district_id)

    const province = provinces.value.find(p => p.ProvinceID === orderDetail.value.address.province_id)
    const district = districts.value.find(d => d.DistrictID === orderDetail.value.address.district_id)
    const ward = wards.value.find(w => w.WardCode == orderDetail.value.address.ward_code)

    orderDetail.value.address.province_name = province?.ProvinceName || ''
    orderDetail.value.address.district_name = district?.DistrictName || ''
    orderDetail.value.address.ward_name = ward?.WardName || ''

    // Xác minh thanh toán
    const isCOD = orderDetail.value.payments?.[0]?.method === 'COD' ||
      (!orderDetail.value.payments?.[0]?.method && !Object.keys(route.query).some(key => key.startsWith('vnp_')))

    if (isCOD) {
      amount.value = parsePrice(orderDetail.value.final_price) || 0
      bankCode.value = 'Thanh toán khi nhận'
      transactionId.value = 'Không áp dụng'
      success.value = true
    } else {
      const queryParams = { ...route.query }
      if (!queryParams.vnp_TxnRef || !queryParams.vnp_ResponseCode || !queryParams.vnp_SecureHash) {
        throw new Error('Thiếu tham số từ VNPAY.')
      }

      const vnpRes = await fetch(
        `${config.public.apiBaseUrl}/payments/vnpay/return?${new URLSearchParams(queryParams).toString()}`,
        {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        }
      )

      const vnpData = await vnpRes.json()

      if (!vnpRes.ok || !vnpData.success) {
        throw new Error(vnpData.message || 'Không nhận được dữ liệu từ VNPAY')
      }

      transactionId.value = vnpData.transaction_id || queryParams.vnp_TransactionNo || '-'
      bankCode.value = vnpData.bank_code || queryParams.vnp_BankCode || '-'
      amount.value = vnpData.amount || (Number(queryParams.vnp_Amount) / 100) || 0
      success.value = true
    }

    await clearCart()
    loading.value = false
  } catch (err) {
    console.error('Error fetching order:', err)
    success.value = false
    loading.value = false
    message.value = err.message || 'Có lỗi xảy ra khi xác minh thanh toán'
  }
})

onUnmounted(() => {
  // Nếu bạn có dùng countdown thì clear tại đây
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