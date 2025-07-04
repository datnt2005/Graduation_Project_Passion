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

        <!-- Nếu nhiều đơn -->
        <template v-if="Array.isArray(orderDetails) && orderDetails.length > 1">
          <div v-for="(orderDetail, idx) in orderDetails" :key="orderDetail.id || idx" class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
              <div><span class="font-medium text-gray-500">Mã vận đơn:</span> <span class="font-semibold">{{ orderDetail?.shipping?.tracking_code || 'Đang cập nhật' }}</span></div>
              <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{ orderDetail?.user?.name || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">Email:</span> <span class="font-semibold">{{ orderDetail?.user?.email || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">SĐT:</span> <span class="font-semibold">{{ orderDetail?.address?.phone || 'N/A' }}</span></div>
              <div class="sm:col-span-2">
                <span class="font-medium text-gray-500">Địa chỉ giao hàng:</span>
                <div class="mt-1 font-semibold text-gray-800">
                  {{
                    orderDetail?.address
                      ? `${orderDetail.address.detail || ''}, ${orderDetail.address.ward_name || ''}, ${orderDetail.address.district_name || ''}, ${orderDetail.address.province_name || ''}`
                      : 'Không có'
                  }}
                </div>
              </div>
              <div class="sm:col-span-2 text-center mt-2">
                <span class="font-medium text-gray-500">Phương thức:</span>
                <span class="font-semibold">
                  {{ orderDetail?.payments?.[0]?.method === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : orderDetail?.payments?.[0]?.method === 'VNPAY' ? 'VNPay' : orderDetail?.payments?.[0]?.method === 'MOMO' ? 'MoMo' : 'N/A' }}
                </span>
              </div>
            </div>
            <div class="text-right text-lg font-bold text-blue-700 border-t pt-4 mt-4">
              Tổng thanh toán: {{ formatPrice((parseInt(orderDetail?.final_price) || 0) + (parseInt(orderDetail?.shipping?.shipping_fee) || 0)) }} đ
            </div>
            <div v-if="orderDetail?.order_items?.length" class="mt-4">
              <div class="font-medium text-gray-700 mb-2">Sản phẩm đã đặt:</div>
              <div v-for="item in orderDetail.order_items" :key="item.product?.id + '-' + (item.variant?.id || '')" class="flex items-center gap-4 border-b py-2 last:border-0">
                <img :src="item.product?.thumbnail || '/images/no-image.png'" :alt="item.product?.name || 'Ảnh sản phẩm'" class="w-12 h-12 object-cover rounded-md border" />
                <div class="flex-1">
                  <div class="font-semibold text-gray-900">{{ item.product?.name || '-' }}</div>
                  <div v-if="item.variant && item.variant.name" class="text-xs text-gray-500">Phân loại: {{ item.variant.name }}</div>
                  <div class="text-xs text-gray-500">Số lượng: {{ item.quantity }} × {{ formatPrice(item.price) }}</div>
                </div>
                <div class="font-bold text-blue-700">{{ formatPrice(item.total) }}</div>
              </div>
            </div>
          </div>
        </template>

        <!-- Nếu chỉ 1 đơn -->
        <template v-else>
          <div class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
              <div><span class="font-medium text-gray-500">Mã vận đơn:</span> <span class="font-semibold">{{ orderDetails?.shipping?.tracking_code || 'Đang cập nhật' }}</span></div>
              <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{ orderDetails?.user?.name || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">Email:</span> <span class="font-semibold">{{ orderDetails?.user?.email || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">SĐT:</span> <span class="font-semibold">{{ orderDetails?.address?.phone || 'N/A' }}</span></div>
              <div class="sm:col-span-2">
                <span class="font-medium text-gray-500">Địa chỉ giao hàng:</span>
                <div class="mt-1 font-semibold text-gray-800">
                  {{
                    orderDetails?.address
                      ? `${orderDetails.address.detail || ''}, ${orderDetails.address.ward_name || ''}, ${orderDetails.address.district_name || ''}, ${orderDetails.address.province_name || ''}`
                      : 'Không có'
                  }}
                </div>
              </div>
              <div class="sm:col-span-2 text-center mt-2">
                <span class="font-medium text-gray-500">Phương thức:</span>
                <span class="font-semibold">
                  {{ orderDetails?.payments?.[0]?.method === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : orderDetails?.payments?.[0]?.method === 'VNPAY' ? 'VNPay' : orderDetails?.payments?.[0]?.method === 'MOMO' ? 'MoMo' : 'N/A' }}
                </span>
              </div>
            </div>
            <div class="text-right text-lg font-bold text-blue-700 border-t pt-4 mt-4">
              Tổng thanh toán: {{ formatPrice((parseInt(orderDetails?.final_price) || 0) + (parseInt(orderDetails?.shipping?.shipping_fee) || 0)) }} đ
            </div>
            <div v-if="orderDetails?.order_items?.length" class="mt-4">
              <div class="font-medium text-gray-700 mb-2">Sản phẩm đã đặt:</div>
              <div v-for="item in orderDetails.order_items" :key="item.product?.id + '-' + (item.variant?.id || '')" class="flex items-center gap-4 border-b py-2 last:border-0">
                <img :src="item.product?.thumbnail || '/images/no-image.png'" :alt="item.product?.name || 'Ảnh sản phẩm'" class="w-12 h-12 object-cover rounded-md border" />
                <div class="flex-1">
                  <div class="font-semibold text-gray-900">{{ item.product?.name || '-' }}</div>
                  <div v-if="item.variant && item.variant.name" class="text-xs text-gray-500">Phân loại: {{ item.variant.name }}</div>
                  <div class="text-xs text-gray-500">Số lượng: {{ item.quantity }} × {{ formatPrice(item.price) }}</div>
                </div>
                <div class="font-bold text-blue-700">{{ formatPrice(item.total) }}</div>
              </div>
            </div>
          </div>
        </template>

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
const orderDetails = ref([])
const paymentMethod = ref('')

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

const orderIds = route.query.ids ? route.query.ids.split(',') : (route.query.id ? [route.query.id] : []);

onMounted(async () => {
  if (!orderIds.length) {
    success.value = false;
    loading.value = false;
    message.value = 'Không tìm thấy thông tin đơn hàng.';
    return;
  }

  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      message.value = 'Vui lòng đăng nhập để xem thông tin đơn hàng.';
      success.value = false;
      loading.value = false;
      window.dispatchEvent(new CustomEvent('openLoginModal'));
      return;
    }

    // Nếu chỉ 1 đơn, giữ nguyên logic cũ
    if (orderIds.length === 1) {
      const orderId = orderIds[0]
      const orderRes = await fetch(`${config.public.apiBaseUrl}/user/orders/${orderId}`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      })

      if (orderRes.status === 403) {
        throw new Error('Bạn không có quyền xem đơn này')
      }
      if (orderRes.status === 404) {
        throw new Error('Không tìm thấy đơn hàng')
      }

      const orderData = await orderRes.json()
      if (!orderRes.ok || !orderData) {
        throw new Error(orderData.message || 'Không thể lấy thông tin đơn hàng')
      }

      // Map lại dữ liệu cho đúng với UI cũ
      orderDetails.value = {
        ...orderData,
        order_items: orderData.items || [],
        address: orderData.address || {},
        user: orderData.user || {},
        payments: orderData.payments || [],
        final_price: orderData.final_price,
      }
      // Lấy tracking_code và paymentMethod từ API
      tracking_code.value = orderData.shipping?.tracking_code || 'Đang cập nhật'
      paymentMethod.value = orderData.payments?.[0]?.method || 'N/A'

      // Load địa chỉ (nếu cần)
      await loadProvinces()
      await loadDistricts(orderDetails.value.address.province_id)
      await loadWards(orderDetails.value.address.district_id)

      const province = provinces.value.find(p => p.ProvinceID === orderDetails.value.address.province_id)
      const district = districts.value.find(d => d.DistrictID === orderDetails.value.address.district_id)
      const ward = wards.value.find(w => w.WardCode == orderDetails.value.address.ward_code)

      orderDetails.value.address.province_name = province?.ProvinceName || ''
      orderDetails.value.address.district_name = district?.DistrictName || ''
      orderDetails.value.address.ward_name = ward?.WardName || ''

      // Xác minh thanh toán
      const isCOD = orderDetails.value.payments?.[0]?.method === 'COD' || (!orderDetails.value.payments?.[0]?.method && !Object.keys(route.query).some(key => key.startsWith('vnp_')))

      if (isCOD) {
        amount.value = parsePrice(orderDetails.value.final_price) || 0
        bankCode.value = 'Thanh toán khi nhận'
        transactionId.value = 'Không áp dụng'
        success.value = true
      } else {
        throw new Error('Chỉ hỗ trợ xác nhận đơn hàng thanh toán COD trên trang này. Đối với thanh toán VNPAY, vui lòng kiểm tra trạng thái tại trang VNPAY Return.')
      }

      await clearCart()
      loading.value = false
      return
    }

    // Nếu nhiều đơn, fetch từng đơn và push vào orderDetails
    orderDetails.value = [];
    for (const id of orderIds) {
      const orderRes = await fetch(`${config.public.apiBaseUrl}/user/orders/${id}`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });
      if (orderRes.ok) {
        const orderData = await orderRes.json();
        orderDetails.value.push({
          ...orderData,
          order_items: orderData.items || [],
          address: orderData.address || {},
          user: orderData.user || {},
          payments: orderData.payments || [],
          final_price: orderData.final_price,
        });
      }
    }
    success.value = true;
    loading.value = false;
    await clearCart();
  } catch (err) {
    console.error('Error fetching orders:', err);
    success.value = false;
    loading.value = false;
    message.value = err.message || 'Có lỗi xảy ra khi xác minh thanh toán';
  }
});

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