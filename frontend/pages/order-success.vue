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
        <h2 class="text-3xl font-extrabold text-green-600 mb-2">Đặt Hàng Thành Công!</h2>
        <p class="text-gray-700 mb-6 text-lg">
          Cảm ơn bạn đã mua hàng tại <span class="font-semibold text-primary">Passion</span>.
        </p>
        <!-- THÔNG TIN ĐƠN HÀNG -->
        <div v-if="Array.isArray(orderDetails) && orderDetails.length" class="space-y-8">
          <div v-for="order in orderDetails" :key="order.id" class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
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
                      ? [order.address.detail, order.address.ward_name, order.address.district_name, order.address.province_name].filter(Boolean).join(', ')
                      : 'Không có'
                  }}
                </div>
              </div>
              <div class="sm:col-span-2 text-center mt-2">
                <span class="font-medium text-gray-500">Phương thức:</span>
                <span class="font-semibold">{{ order.payments?.[0]?.method === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : order.payments?.[0]?.method === 'VNPAY' ? 'VNPay' : order.payments?.[0]?.method === 'MOMO' ? 'MoMo' : 'N/A' }}</span>
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
                <span class="text-blue-700">
                  {{ formatPrice((parseInt(order.final_price) || 0) + (parseInt(order.shipping?.shipping_fee) || 0)) }} đ
                </span>
              </div>
            </div>
            <!-- Danh sách sản phẩm đã đặt (nếu có) -->
            <div v-if="order.order_items?.length" class="mt-4">
              <div class="font-medium text-gray-700 mb-2">Sản phẩm đã đặt:</div>
              <div class="max-h-64 overflow-y-auto pr-2">
                <div v-for="item in order.order_items" :key="item.product?.id + '-' + (item.variant?.id || '')" class="flex items-center gap-4 border-b py-2 last:border-0">
                  <div class="flex gap-2 items-center">
                    <img
                      :src="item.variant?.thumbnail ? mediaBaseUrl + item.variant.thumbnail : '/images/default-product.jpg'"
                      :alt="item.product?.name || 'Ảnh sản phẩm'"
                      class="w-12 h-12 object-cover rounded-md border"
                      @error="e => e.target.src = '/images/default-product.jpg'"
                    />
                  </div>
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
        <div v-else-if="orderDetails && orderDetails.user" class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
            <div><span class="font-medium text-gray-500">Mã vận đơn:</span> <span class="font-semibold">{{ tracking_code || 'Đang cập nhật' }}</span></div>
            <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{ orderDetails?.user?.name || 'N/A' }}</span></div>
            <div><span class="font-medium text-gray-500">Email:</span> <span class="font-semibold">{{ orderDetails?.user?.email || 'N/A' }}</span></div>
            <div><span class="font-medium text-gray-500">SĐT:</span> <span class="font-semibold">{{ orderDetails?.address?.phone || 'N/A' }}</span></div>
            <div class="sm:col-span-2">
              <span class="font-medium text-gray-500">Địa chỉ giao hàng:</span>
              <div class="mt-1 font-semibold text-gray-800">
                {{
                  orderDetails.address
                    ? [orderDetails.address.detail, orderDetails.address.ward_name, orderDetails.address.district_name, orderDetails.address.province_name].filter(Boolean).join(', ')
                    : 'Không có'
                }}
              </div>
            </div>
            <div class="sm:col-span-2 text-center mt-2">
              <span class="font-medium text-gray-500">Phương thức:</span>
              <span class="font-semibold">{{ orderDetails.payments?.[0]?.method === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : orderDetails.payments?.[0]?.method === 'VNPAY' ? 'VNPay' : orderDetails.payments?.[0]?.method === 'MOMO' ? 'MoMo' : 'N/A' }}</span>
            </div>
          </div>
          <div class="text-right text-lg font-bold text-blue-700 border-t pt-4 mt-4">
            Tổng thanh toán: {{ formatPrice((parseInt(orderDetails?.final_price) || 0) + (parseInt(orderDetails?.shipping?.shipping_fee) || 0)) }} đ
          </div>
          <!-- Danh sách sản phẩm đã đặt (nếu có) -->
          <div v-if="orderDetails?.order_items?.length" class="mt-4">
            <div class="font-medium text-gray-700 mb-2">Sản phẩm đã đặt:</div>
            <div class="max-h-64 overflow-y-auto pr-2">
              <div v-for="item in orderDetails.order_items" :key="item.product?.id + '-' + (item.variant?.id || '')" class="flex items-center gap-4 border-b py-2 last:border-0">
                <img
                  :src="item.variant?.thumbnail ? mediaBaseUrl + item.variant.thumbnail : '/images/default-product.jpg'"
                  :alt="item.product?.name || 'Ảnh sản phẩm'"
                  class="w-12 h-12 object-cover rounded-md border"
                  @error="e => e.target.src = '/images/default-product.jpg'"
                />
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
        <div class="mt-6 flex gap-4 justify-center">
          <NuxtLink to="/" class="inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Về Trang Chủ
          </NuxtLink>
          <NuxtLink v-if="orderDetails && orderDetails.length > 0" to="/users/orders" class="inline-block bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Xem Chi Tiết Đơn Hàng
          </NuxtLink>
        </div>
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
const { clearAllCart } = useCart()

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
  if (!price) return '0';
  // Chuyển sang số nguyên, không hiển thị phần thập phân
  return parseInt(price, 10).toLocaleString('vi-VN');
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

    // Load danh sách địa chỉ trước
    await loadProvinces();
    // Tạm thời lấy province_id, district_id, ward_code từ đơn đầu tiên (nếu có)
    let firstAddress = null;
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
      firstAddress = orderDetails.value?.address;
    } else {
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
      firstAddress = orderDetails.value.length > 0 ? orderDetails.value[0]?.address : null;
    }
    if (firstAddress) {
      if (firstAddress.province_id) await loadDistricts(firstAddress.province_id);
      if (firstAddress.district_id) await loadWards(firstAddress.district_id);
    }

    // Hàm enrich address
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

    // enrich cho từng đơn
    if (orderIds.length === 1) {
      if (orderDetails.value?.address) enrichAddress(orderDetails.value.address);
    } else {
      orderDetails.value.forEach(order => {
        if (order.address) enrichAddress(order.address);
      });
    }

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

    await clearAllCart()
    loading.value = false
    return
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