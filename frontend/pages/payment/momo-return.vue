<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-gray-100 mt-20 mb-20 pb-10">
    <div
      class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-lg w-full text-center transform transition-all duration-300 hover:scale-105">
      <!-- Background decorative element -->
      <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-green-500/10 rounded-2xl -z-10"></div>

      <div v-if="loading" class="flex flex-col items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-600 mb-6"></div>
        <p class="text-gray-600 text-lg font-medium animate-pulse">Đang xác minh thanh toán...</p>
      </div>

      <div v-else>
        <div v-if="success" class="flex flex-col items-center py-6 animate-fade-in">
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
                  <span class="font-semibold">MoMo</span>
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
              <div class="mt-4 text-left text-sm space-y-1">
                <div class="flex justify-between">
                  <span>Tổng tiền hàng:</span>
                  <span>{{ formatPrice(order.total_price) }} đ</span>
                </div>
                <div class="flex justify-between" v-if="order.discount_price > 0">
                  <span>Giảm giá sản phẩm:</span>
                  <span class="text-green-600">- {{ formatPrice(order.discount_price) }} đ</span>
                </div>
                <div class="flex justify-between">
                  <span>Phí vận chuyển:</span>
                  <span>{{ formatPrice(order.shipping?.shipping_fee) }} đ</span>
                </div>
                <div class="flex justify-between" v-if="order.shipping && order.shipping.shipping_discount > 0">
                  <span>Giảm giá phí ship:</span>
                  <span class="text-green-600">- {{ formatPrice(order.shipping.shipping_discount) }} đ</span>
                </div>
                <div class="flex justify-between font-bold border-t pt-2 mt-2">
                  <span>Tổng thanh toán:</span>
                  <span class="text-blue-700">{{ formatPrice((parseInt(order.final_price) || 0) + (parseInt(order.shipping?.shipping_fee) || 0)) }} đ</span>
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
                <span class="font-semibold">MoMo</span>
              </div>
            </div>
            <div class="text-right text-lg font-bold text-blue-700 border-t pt-4 mt-4">
              Tổng thanh toán: {{ formatPrice((parseInt(orderDetail?.final_price) || 0) + (parseInt(orderDetail?.shipping?.shipping_fee) || 0)) }} đ
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
          <NuxtLink v-if="orderDetail && orderDetail.length > 0" to="/users/orders" class="inline-block bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Xem Chi Tiết Đơn Hàng
          </NuxtLink>
          <NuxtLink to="/" class="mt-6 inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Về Trang Chủ
          </NuxtLink>
        </div>

        <!-- Thất bại -->
        <div v-else class="flex flex-col items-center py-6">
          <div class="relative mb-6">
            <!-- Nền mờ -->
            <div class="absolute inset-0 bg-red-100 rounded-full blur-md opacity-50 z-0"></div>

            <!-- Icon SVG -->
            <svg class="relative h-20 w-20 text-red-500 animate-pulse z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
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
          <NuxtLink to="/checkout" class="mt-6 inline-block bg-gradient-to-r from-gray-500 to-gray-600 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-md hover:shadow-lg">
            Thử Lại
          </NuxtLink>
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
const { clearAllCart } = useCart()

const loading = ref(true)
const success = ref(false)
const message = ref('')
const orderId = ref('')
const amount = ref(0)
const transactionId = ref('')
const tracking_code = ref('')
const orderDetail = ref({})
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const mediaBaseUrl = config.public.mediaBaseUrl.endsWith('/') ? config.public.mediaBaseUrl : config.public.mediaBaseUrl + '/'
let countdownInterval = null

const formatPrice = (price) => {
  if (!price) return '0'
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
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
      await loadProvinces();
      let firstAddress = null;
      if (Array.isArray(orderDetail.value) && orderDetail.value.length > 0) {
        firstAddress = orderDetail.value[0]?.address;
      } else if (orderDetail.value && orderDetail.value.address) {
        firstAddress = orderDetail.value.address;
      }
      if (firstAddress) {
        if (firstAddress.province_id) await loadDistricts(firstAddress.province_id);
        if (firstAddress.district_id) await loadWards(firstAddress.district_id);
      }
      function enrichAddress(address) {
        if (!address) return;
        if (address.province_id && !address.province_name) {
          const province = provinces.value.find(p => p.ProvinceID == address.province_id);
          address.province_name = province?.ProvinceName || '';
        }
        if (address.district_id && !address.district_name) {
          const district = districts.value.find(d => d.DistrictID == address.district_id);
          address.district_name = district?.DistrictName || '';
        }
        if (address.ward_code && !address.ward_name) {
          const ward = wards.value.find(w => w.WardCode == address.ward_code);
          address.ward_name = ward?.WardName || '';
        }
      }
      if (Array.isArray(orderDetail.value)) {
        orderDetail.value.forEach(order => { if (order.address) enrichAddress(order.address); });
      } else if (orderDetail.value && orderDetail.value.address) {
        enrichAddress(orderDetail.value.address);
      }
      await clearAllCart()

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