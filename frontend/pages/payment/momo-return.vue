<template>
  <main class="bg-[#F5F5FA] py-6">
  <div class="flex items-center justify-center pb-10">
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
          Cảm ơn bạn đã thanh toán thành công qua <span class="font-semibold text-pink-600">MoMo</span>.
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
                      ? [order.address.detail, order.address.ward_name, order.address.district_name, order.address.province_name]
                          .filter(v => v && v !== 'null' && v !== 'undefined')
                          .join(', ')
                      : 'Không có'
                  }}
                </div>
              </div>
              <div class="sm:col-span-2 text-center mt-2">
                <span class="font-medium text-gray-500">Phương thức:</span>
                <span class="font-semibold text-pink-600">MoMo</span>
              </div>
            </div>
            <div class="mt-4 text-left text-sm space-y-1">
              <div class="flex justify-between">
                <span>Tổng tiền hàng:</span>
                <span>{{ formatPrice(order.total_price) }}đ</span>
              </div>
              <div class="flex justify-between" v-if="order.discount_price > 0">
                <span>Giảm giá sản phẩm:</span>
                <span class="text-green-600">- {{ formatPrice(order.discount_price) }}đ</span>
              </div>
              <div class="flex justify-between">
                <span>Phí vận chuyển:</span>
                <span>{{ formatPrice(order.shipping?.shipping_fee) }}đ</span>
              </div>
              <div class="flex justify-between" v-if="order.shipping && order.shipping.shipping_discount > 0">
                <span>Giảm giá phí ship:</span>
                <span class="text-green-600">- {{ formatPrice(order.shipping.shipping_discount) }}đ</span>
              </div>
              <div class="flex justify-between font-bold border-t pt-2 mt-2">
                <span>Tổng thanh toán:</span>
                <span class="text-blue-700">{{ formatPrice(order.final_price || 0) }}đ</span>
              </div>
            </div>
            <!-- Danh sách sản phẩm đã đặt (nếu có) -->
            <div v-if="order.order_items?.length" class="mt-4">
              <div class="font-medium text-gray-700 mb-2">Sản phẩm đã đặt:</div>
              <div class="max-h-64 overflow-y-auto pr-2">
                <div v-for="item in order.order_items" :key="item.product?.id + '-' + (item.variant?.id || '')" class="flex items-center gap-4 border-b py-2 last:border-0">
                  <div class="flex gap-2 items-center">
                    <img
                      :src="getProductImage(item.variant?.thumbnail || item.product?.thumbnail)"
                      :alt="item.product?.name || 'Ảnh sản phẩm'"
                      class="w-12 h-12 object-cover rounded-md border"
                      @error="e => e.target.src = '/images/default-product.jpg'"
                    />
                  </div>
                  <div class="flex-1">
                    <div class="font-semibold text-gray-900">{{ item.product?.name || '-' }}</div>
                    <div v-if="item.variant && item.variant.name" class="text-xs text-gray-500">Phân loại: {{ item.variant.name }}</div>
                    <div class="text-xs text-gray-500">Số lượng: {{ item.quantity }} × {{ formatPrice(item.price) }}đ</div>
                  </div>
                  <div class="font-bold text-blue-700">{{ formatPrice(item.total) }}đ</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Hỗ trợ hiển thị cũ cho backward compatibility -->
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
                    ? [orderDetail.address.detail, orderDetail.address.ward_name, orderDetail.address.district_name, orderDetail.address.province_name]
                        .filter(v => v && v !== 'null' && v !== 'undefined')
                        .join(', ')
                    : 'Không có'
                }}
              </div>
            </div>
            <div class="sm:col-span-2 text-center mt-2">
              <span class="font-medium text-gray-500">Phương thức:</span>
              <span class="font-semibold text-pink-600">MoMo</span>
            </div>
          </div>
          <div class="text-right text-lg font-bold text-blue-700 border-t pt-4 mt-4">
            Tổng thanh toán: {{ formatPrice(orderDetail?.final_price || 0) }}
          </div>
        </div>

        <!-- Về trang chủ -->
        <div class="mt-6 flex gap-4 justify-center">
          <NuxtLink to="/" class="inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Về Trang Chủ
          </NuxtLink>
          <NuxtLink v-if="orderDetail && orderDetail.length > 0" to="/users/orders" class="inline-block bg-gradient-to-r from-pink-600 to-pink-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-pink-700 hover:to-pink-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Xem Chi Tiết Đơn Hàng
          </NuxtLink>
        </div>
        <p class="mt-4 text-sm text-gray-500">Trang sẽ tự chuyển về trang chủ sau <span class="font-semibold">{{ countdown }}</span>s.</p>
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
        <p class="mt-4 text-sm text-gray-500">Trang sẽ tự chuyển về trang chủ sau <span class="font-semibold">{{ countdown }}</span>s.</p>
      </div>
    </div>
  </div>
  </main>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRuntimeConfig } from '#imports'
import { useCart } from '~/composables/useCart'
import { useHead } from '#imports'

useHead({
  title: 'Thanh Toán MoMo',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const mediaBaseUrl = config.public.mediaBaseUrl.endsWith('/') ? config.public.mediaBaseUrl : config.public.mediaBaseUrl + '/'
const { clearOrderedItems } = useCart()

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

const processedKey = ref('')

const startRedirectCountdown = () => {
  try { clearInterval(countdownInterval) } catch {}
  countdown.value = 10
  countdownInterval = setInterval(() => {
    countdown.value -= 1
    if (countdown.value <= 0) {
      clearInterval(countdownInterval)
      router.push('/')
    }
  }, 1000)
}

const formatPrice = (price) => {
  if (!price) return '0'
  // Chuyển về số nguyên và format
  const intPrice = Math.floor(parseFloat(price))
  return intPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
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

const getProductImage = (thumbnail) => {
  if (!thumbnail) {
    console.warn('Thumbnail is missing, using default image');
    return '/images/default-product.jpg';
  }
  if (thumbnail.startsWith('http://') || thumbnail.startsWith('https://')) {
    const url = `${thumbnail}?w=100&q=80`;
    console.log('Generated image URL (absolute):', url);
    return url;
  }
  const url = `${mediaBaseUrl}${thumbnail}?w=100&q=80`;
  console.log('Generated image URL (relative):', url);
  return url;
};

const fetchProductDetails = async (productId) => {
  try {
    const response = await fetch(`${config.public.apiBaseUrl}/products/${productId}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` }
    });
    const data = await response.json();
    return data.data?.thumbnail || '/images/default-product.jpg';
  } catch (error) {
    console.error('Error fetching product details:', error);
    return '/images/default-product.jpg';
  }
};

// Hàm parse order IDs từ MoMo response
const parseOrderIds = () => {
  console.log('Route query params:', route.query);
  
  // Thử lấy từ query params trước
  let ids = route.query.ids ? route.query.ids.split(',') : (route.query.id ? [route.query.id] : []);
  console.log('IDs from query params:', ids);
  
  // Nếu không có, thử parse từ extraData của MoMo
  if (!ids.length && route.query.extraData) {
    try {
      console.log('ExtraData from MoMo:', route.query.extraData);
      
      // Decode base64 trước
      const decodedData = atob(route.query.extraData);
      console.log('Decoded extraData:', decodedData);
      
      // Sau đó parse JSON
      const extraData = JSON.parse(decodedData);
      console.log('Parsed extraData:', extraData);
      
      if (extraData.order_ids && Array.isArray(extraData.order_ids)) {
        ids = extraData.order_ids.map(id => id.toString());
        console.log('Order IDs from extraData:', ids);
      }
    } catch (error) {
      console.error('Error parsing extraData:', error);
      console.error('ExtraData value:', route.query.extraData);
    }
  }
  
  return ids;
};

onMounted(async () => {
  console.log('mediaBaseUrl:', config.public.mediaBaseUrl);
  const queryParams = { ...route.query }
  console.log('MoMo Redirect Query Parameters:', queryParams)

  // Parse order IDs từ MoMo
  const orderIds = parseOrderIds();
  console.log('Final Order IDs:', orderIds);

  if (!orderIds.length) {
    loading.value = false;
    success.value = false;
    message.value = 'Không tìm thấy thông tin đơn hàng. Vui lòng kiểm tra lại hoặc liên hệ hỗ trợ.';
    console.error('No order IDs found in URL params or extraData');
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

    // Kiểm tra resultCode từ MoMo
    const resultCode = route.query.resultCode;
    console.log('MoMo resultCode:', resultCode);

    // Ngăn reload spam (đã xử lý giao dịch này rồi thì bỏ qua và chuyển hướng sau 10s)
    const keyParts = ['momo', resultCode || '', (orderIds && orderIds.length ? orderIds.join('_') : ''), route.query.transId || route.query.orderId || route.query.requestId || '']
    processedKey.value = `payment_processed_${keyParts.filter(Boolean).join('_')}`
    if (processedKey.value && sessionStorage.getItem(processedKey.value)) {
      console.log('Momo return already processed, skipping API calls.')
      loading.value = false
      success.value = (resultCode === '0')
      message.value = success.value ? 'Thanh toán đã được xử lý.' : (route.query.message || 'Thanh toán thất bại')
      startRedirectCountdown()
      return
    }
    
    if (resultCode === '0') {
      // Thanh toán thành công
      success.value = true;
      message.value = 'Thanh toán thành công';
      console.log('Payment successful');
      
      // Fetch order details
      orderDetail.value = [];
      for (const id of orderIds) {
        console.log(`Fetching order details for ID: ${id}`);
        const orderRes = await fetch(`${config.public.apiBaseUrl}/user/orders/${id}`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        });

        if (orderRes.status === 403) {
          throw new Error('Bạn không có quyền xem đơn này');
        }
        if (orderRes.status === 404) {
          throw new Error(`Không tìm thấy đơn hàng với mã: ${id}`);
        }

        const orderData = await orderRes.json();
        console.log('Order Data:', orderData);
        if (!orderRes.ok || !orderData) {
          throw new Error(orderData.message || 'Không thể lấy thông tin đơn hàng');
        }

        // Fetch product images if needed
        if (orderData.order_items) {
          for (const item of orderData.order_items) {
            if (!item.variant?.thumbnail && !item.product?.thumbnail) {
              item.product.thumbnail = await fetchProductDetails(item.product.id);
            }
          }
        }

        const shippingFee = orderData.shipping?.shipping_fee || orderData.final_price - orderData.total_price || 0;
        orderDetail.value.push({
          ...orderData,
          order_items: orderData.order_items || [],
          address: orderData.address || {},
          user: orderData.user || {},
          payments: orderData.payments || [],
          final_price: orderData.final_price,
          shipping: {
            ...orderData.shipping,
            shipping_fee: shippingFee,
          },
        });
      }

      // Load address data
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
      if (Array.isArray(orderDetail.value)) {
        orderDetail.value.forEach(order => { if (order.address) enrichAddress(order.address); });
      } else if (orderDetail.value && orderDetail.value.address) {
        enrichAddress(orderDetail.value.address);
      }

      // Lấy danh sách sản phẩm đã được thanh toán để xóa khỏi giỏ hàng
      const orderedItems = [];
      if (Array.isArray(orderDetail.value)) {
        orderDetail.value.forEach(order => {
          if (order.order_items && Array.isArray(order.order_items)) {
            order.order_items.forEach(item => {
              orderedItems.push({
                product_id: item.product?.id,
                product_variant_id: item.variant?.id || null,
                quantity: item.quantity
              });
            });
          }
        });
      }
      
      console.log('Momo return - orderedItems để xóa:', orderedItems);
      console.log('Momo return - orderDetail.value:', orderDetail.value);
      
      // Chỉ xóa những sản phẩm đã được thanh toán
      if (orderedItems.length > 0) {
        console.log('Bắt đầu xóa items khỏi giỏ hàng...');
        try {
          await clearOrderedItems(orderedItems);
          console.log('Hoàn thành xóa items khỏi giỏ hàng');
        } catch (error) {
          console.error('Lỗi khi xóa items khỏi giỏ hàng:', error);
        }
      } else {
        console.log('Không có items nào để xóa');
      }
      
      loading.value = false;
      if (processedKey.value) sessionStorage.setItem(processedKey.value, '1')
      startRedirectCountdown()
    } else {
      // Thanh toán thất bại
      success.value = false;
      message.value = route.query.message || 'Thanh toán thất bại';
      console.log('Payment failed:', route.query.message);
      loading.value = false;
      if (processedKey.value) sessionStorage.setItem(processedKey.value, '1')
      startRedirectCountdown()
    }
  } catch (error) {
    console.error('Error processing MoMo payment:', error);
    success.value = false;
    loading.value = false;
    message.value = error.message || 'Có lỗi xảy ra khi xác minh thanh toán';
    if (processedKey.value) sessionStorage.setItem(processedKey.value, '1')
    startRedirectCountdown()
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