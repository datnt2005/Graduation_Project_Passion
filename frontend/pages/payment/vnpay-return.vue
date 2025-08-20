<template>
  <main class="bg-[#F5F5FA] py-6">
    <div class="flex items-center justify-center pb-10">
      <div
        class="relative bg-white rounded-2xl shadow-xl p-8 max-w-2xl w-full text-center transform transition-all duration-300 hover:scale-[1.01]">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-green-500/10 rounded-2xl -z-10"></div>

        <!-- Đang tải -->
        <div v-if="loading" class="flex flex-col items-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-600 mb-6"></div>
          <p class="text-gray-600 text-lg font-medium animate-pulse">Đang xác minh thanh toán...</p>
        </div>

        <!-- Thành công -->
        <div v-else-if="success" class="flex flex-col items-center py-6 animate-fade-in">
          <svg width="100" height="100" viewBox="0 0 24 24" fill="green" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 12l2 2 4-4M12 2a10 10 0 100 20 10 10 0 000-20z" stroke="white" stroke-width="2" fill="green" />
          </svg>
          <h2 class="text-3xl font-extrabold text-green-600 mb-2">Thanh Toán Thành Công!</h2>
          <p class="text-gray-700 mb-6 text-lg">
            Cảm ơn bạn đã mua hàng tại <span class="font-semibold text-primary">Passion</span>.
          </p>
          <!-- THÔNG TIN ĐƠN HÀNG -->
          <div v-if="Array.isArray(orderDetail) && orderDetail.length" class="space-y-8">
            <div v-for="order in orderDetail" :key="order.id"
              class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
                <div><span class="font-medium text-gray-500">Mã vận đơn:</span> <span class="font-semibold">{{
                  order.shipping?.tracking_code || 'Đang cập nhật' }}</span></div>
                <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{
                  order.user?.name || 'N/A' }}</span></div>
                <div><span class="font-medium text-gray-500">Email:</span> <span class="font-semibold">{{
                  order.user?.email || 'N/A' }}</span></div>
                <div><span class="font-medium text-gray-500">SĐT:</span> <span class="font-semibold">{{
                  order.address?.phone || 'N/A' }}</span></div>
                <div class="sm:col-span-2">
                  <span class="font-medium text-gray-500">Địa chỉ giao hàng:</span>
                  <div class="mt-1 font-semibold text-gray-800">
                    {{
                      order.address
                        ? [order.address.detail, order.address.ward_name, order.address.district_name,
                        order.address.province_name]
                          .filter(v => v && v !== 'null' && v !== 'undefined')
                          .join(', ')
                        : 'Không có'
                    }}
                  </div>
                </div>
                <div class="sm:col-span-2 text-center mt-2">
                  <span class="font-medium text-gray-500">Phương thức:</span>
                  <span class="font-semibold">VNPay</span>
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
                  <span>{{ formatPrice(order.shipping?.shipping_fee) }}</span>
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

            </div>
          </div>
          <!-- Hỗ trợ hiển thị cũ cho backward compatibility -->
          <div v-else-if="orderDetail && orderDetail.user"
            class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
              <div><span class="font-medium text-gray-500">Mã vận đơn:</span> <span class="font-semibold">{{
                tracking_code || 'Đang cập nhật' }}</span></div>
              <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{
                orderDetail?.user?.name || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">Email:</span> <span class="font-semibold">{{
                orderDetail?.user?.email || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">SĐT:</span> <span class="font-semibold">{{
                orderDetail?.address?.phone || 'N/A' }}</span></div>
              <div class="sm:col-span-2">
                <span class="font-medium text-gray-500">Địa chỉ giao hàng:</span>
                <div class="mt-1 font-semibold text-gray-800">
                  {{
                    orderDetail.address
                      ? [orderDetail.address.detail, orderDetail.address.ward_name, orderDetail.address.district_name,
                      orderDetail.address.province_name]
                        .filter(v => v && v !== 'null' && v !== 'undefined')
                        .join(', ')
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
              Tổng thanh toán: {{ formatPrice(orderDetail?.final_price || 0) }}đ
            </div>

          </div>

          <!-- Về trang chủ -->
          <div class="mt-6 flex gap-4 justify-center">
            <NuxtLink to="/"
              class="inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg">
              Về Trang Chủ
            </NuxtLink>
            <NuxtLink v-if="orderDetail && orderDetail.length > 0" to="/users/orders"
              class="inline-block bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-md hover:shadow-lg">
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

          <NuxtLink to="/checkout"
            class="mt-6 inline-block bg-gradient-to-r from-gray-500 to-gray-600 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-md hover:shadow-lg">
            Thử Lại
          </NuxtLink>
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
  title: 'Thanh Toán VNPay',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const mediaBaseUrl = config.public.mediaBaseUrl.endsWith('/') ? config.public.mediaBaseUrl : config.public.mediaBaseUrl + '/'
const { clearOrderedItems } = useCart()
const REDIRECT_URL = 'https://passionfpt.shop/'

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
    return '/images/default-product.jpg'; // Đảm bảo tệp này tồn tại trong public/images/
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

onMounted(async () => {
  console.log('mediaBaseUrl:', config.public.mediaBaseUrl); // Debug mediaBaseUrl
  const queryParams = { ...route.query }
  console.log('VNPAY Redirect Query Parameters:', queryParams)

  // Ngăn reload spam: nếu giao dịch này đã xử lý thì bỏ qua và tự chuyển sau 10s
  const txnRef = queryParams.vnp_TxnRef || ''
  const transNo = queryParams.vnp_TransactionNo || ''
  const responseCode = queryParams.vnp_ResponseCode || ''
  processedKey.value = `payment_processed_vnpay_${[txnRef, transNo, responseCode].filter(Boolean).join('_')}`
  if (processedKey.value && sessionStorage.getItem(processedKey.value)) {
    loading.value = false
    success.value = (responseCode === '00')
    message.value = success.value ? 'Thanh toán đã được xử lý.' : 'Thanh toán thất bại'
    startRedirectCountdown()
    return
  }

  if (!queryParams.vnp_TxnRef || !queryParams.vnp_ResponseCode || !queryParams.vnp_SecureHash) {
    loading.value = false
    success.value = false
    message.value = 'Thiếu tham số VNPAY bắt buộc'
    orderId.value = queryParams.vnp_TxnRef ? queryParams.vnp_TxnRef.split('_')[0] : '-'
    amount.value = Number(queryParams.vnp_Amount) / 100 || 0
    bankCode.value = queryParams.vnp_BankCode || '-'
    transactionId.value = queryParams.vnp_TransactionNo || '-'
    console.warn('Missing required VNPAY parameters:', queryParams)
    startRedirectCountdown()
    return
  }

  try {
    const queryString = new URLSearchParams(queryParams).toString()
    console.log('Fetch URL:', `${config.public.apiBaseUrl}/payments/vnpay/return?${queryString}`)
    const res = await fetch(`${config.public.apiBaseUrl}/payments/vnpay/return?${queryString}`)
    const data = await res.json()

    if (data.success) {
      loading.value = false
      success.value = true
      message.value = data.message || 'Thanh toán thành công'
      orderId.value = data.order_id || '-'
      amount.value = Number(data.amount) / 100 || 0
      bankCode.value = data.bank_code || '-'
      transactionId.value = data.transaction_id || '-'
      tracking_code.value = data.tracking_code || '-'

      // Xử lý order_detail từ backend
      console.log('VNPay backend response data:', data);
      console.log('VNPay order_detail structure:', data.order_detail);

      if (data.order_detail) {
        if (Array.isArray(data.order_detail)) {
          console.log('VNPay order_detail is array, length:', data.order_detail.length);
          for (const order of data.order_detail) {
            console.log('VNPay order structure:', order);
            console.log('VNPay order order_items:', order.order_items);
            for (const item of order.order_items) {
              if (!item.variant?.thumbnail && !item.product?.thumbnail) {
                item.product.thumbnail = await fetchProductDetails(item.product.id);
              }
            }
          }
          orderDetail.value = data.order_detail
        } else {
          console.log('VNPay order_detail is single object');
          console.log('VNPay order_detail order_items:', data.order_detail.order_items);
          for (const item of data.order_detail.order_items) {
            if (!item.variant?.thumbnail && !item.product?.thumbnail) {
              item.product.thumbnail = await fetchProductDetails(item.product.id);
            }
          }
          orderDetail.value = [data.order_detail]
        }
      } else {
        console.log('VNPay no order_detail in response');
        orderDetail.value = []
      }

      console.log('Order Detail:', JSON.stringify(orderDetail.value, null, 2)); // Debug orderDetail

      if (orderDetail.value && orderDetail.value.length) {
        const order = orderDetail.value[0]
        if (order) {
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

          console.log('VNPay return - orderedItems để xóa:', orderedItems);
          console.log('VNPay return - orderDetail.value:', orderDetail.value);

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
        } else {
          loading.value = false
          success.value = false
          message.value = 'Không tìm thấy thông tin đơn hàng'
          orderId.value = '-'
          amount.value = 0
          bankCode.value = '-'
          transactionId.value = '-'
          tracking_code.value = '-'
          orderDetail.value = []
        }
      } else {
        loading.value = false
        success.value = false
        message.value = 'Không tìm thấy thông tin đơn hàng'
        orderId.value = '-'
        amount.value = 0
        bankCode.value = '-'
        transactionId.value = '-'
        tracking_code.value = '-'
        orderDetail.value = []
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
      if (Array.isArray(orderDetail.value)) {
        orderDetail.value.forEach(order => { if (order.address) enrichAddress(order.address); });
      } else if (orderDetail.value && orderDetail.value.address) {
        enrichAddress(orderDetail.value.address);
      }
      if (processedKey.value) sessionStorage.setItem(processedKey.value, '1')
      startRedirectCountdown()
    } else {
      loading.value = false
      success.value = false
      message.value = data.message || 'Thanh toán thất bại'
      orderId.value = queryParams.vnp_TxnRef ? queryParams.vnp_TxnRef.split('_')[0] : '-'
      amount.value = Number(queryParams.vnp_Amount) / 100 || 0
      bankCode.value = queryParams.vnp_BankCode || '-'
      transactionId.value = queryParams.vnp_TransactionNo || '-'
      tracking_code.value = '-'
      orderDetail.value = []
      if (processedKey.value) sessionStorage.setItem(processedKey.value, '1')
      startRedirectCountdown()
    }
  } catch (error) {
    loading.value = false
    success.value = false
    message.value = 'Lỗi kết nối API VNPAY'
    console.error('VNPAY Return Error:', error)
    orderId.value = '-'
    amount.value = 0
    bankCode.value = '-'
    transactionId.value = '-'
    tracking_code.value = '-'
    orderDetail.value = []
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