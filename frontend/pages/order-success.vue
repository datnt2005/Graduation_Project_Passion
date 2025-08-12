<template>
  <main class="bg-[#F5F5FA] py-4"> 
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
                    order.address && order.address.detail
                      ? [
                          order.address.detail,
                          order.address.ward_name || 'Phường/Xã không xác định',
                          order.address.district_name || 'Quận/Huyện không xác định',
                          order.address.province_name || 'Tỉnh/TP không xác định'
                        ].join(', ')
                      : 'Không có thông tin địa chỉ'
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
                <span>Tổng giảm giá:</span>
                <span class="text-green-600">- {{ formatPrice(order.discount_price) }} đ</span>
              </div>
              <div class="flex justify-between">
                <span>Phí vận chuyển:</span>
                <span>{{ formatPrice(order.shipping?.shipping_fee || (parseInt(order.final_price) - parseInt(order.total_price)) || 0) }} đ</span>
              </div>
              <div class="flex justify-between" v-if="order.shipping && order.shipping.shipping_discount > 0">
                <span>Giảm giá phí ship:</span>
                <span class="text-green-600">- {{ formatPrice(order.shipping.shipping_discount) }} đ</span>
              </div>
              <div class="flex justify-between font-bold border-t pt-2 mt-2">
                <span>Tổng thanh toán:</span>
                <span class="text-blue-700">
                  {{ formatPrice((parseInt(order.final_price) || 0) - (parseInt(order.shipping?.shipping_discount) || 0)) }} đ
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
                      :src="item.product?.thumbnail ? mediaBaseUrl + item.product.thumbnail : 'products/default.png'"
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
  </main>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useRuntimeConfig } from '#imports'
import { useCart } from '~/composables/useCart'
import { useHead } from '#imports'

useHead({
  title: 'Đặt hàng thành công',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})
const route = useRoute()
const config = useRuntimeConfig()
const { clearOrderedItems } = useCart()

const loading = ref(true)
const success = ref(false)
const message = ref('')
const orderDetails = ref([])
const mediaBaseUrl = config.public.mediaBaseUrl

const provinces = ref([])
const districts = ref([])
const wards = ref([])

const orderIds = route.query.ids ? route.query.ids.split(',') : route.query.id ? [route.query.id] : []

const formatPrice = (price) => {
  if (!price) return '0'
  return parseInt(price, 10).toLocaleString('vi-VN')
}

const parsePrice = (priceStr) => {
  if (!priceStr) return 0
  return parseInt(priceStr.replace(/[^\d]/g, '')) || 0
}

const loadProvinces = async () => {
  try {
    const res = await fetch(`${config.public.apiBaseUrl}/ghn/provinces`)
    const data = await res.json()
    provinces.value = data.data || []
    console.log('Loaded provinces:', provinces.value)
  } catch (error) {
    console.error('Error loading provinces:', error)
  }
}

const loadDistricts = async (province_id) => {
  try {
    const res = await fetch(`${config.public.apiBaseUrl}/ghn/districts`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ province_id }),
    })
    const data = await res.json()
    districts.value = data.data || []
    console.log('Loaded districts for province_id ' + province_id + ':', districts.value)
  } catch (error) {
    console.error('Error loading districts for province_id ' + province_id + ':', error)
  }
}

const loadWards = async (district_id) => {
  try {
    const res = await fetch(`${config.public.apiBaseUrl}/ghn/wards`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ district_id }),
    })
    const data = await res.json()
    wards.value = data.data || []
    console.log('Loaded wards for district_id ' + district_id + ':', wards.value)
  } catch (error) {
    console.error('Error loading wards for district_id ' + district_id + ':', error)
  }
}

function enrichAddress(address) {
  if (!address) return
  if (address.province_id && !address.province_name) {
    const province = provinces.value.find(p => p.ProvinceID == address.province_id)
    address.province_name = province?.ProvinceName || 'Tỉnh/TP không xác định'
  }
  if (address.district_id && !address.district_name) {
    const district = districts.value.find(d => d.DistrictID == address.district_id)
    address.district_name = district?.DistrictName || 'Quận/Huyện không xác định'
  }
  if (address.ward_code && !address.ward_name) {
    const ward = wards.value.find(w => w.WardCode == address.ward_code)
    address.ward_name = ward?.WardName || 'Phường/Xã không xác định'
  }
  console.log('Enriched address:', address)
}

onMounted(async () => {
  console.log('Order IDs:', orderIds)
  if (!orderIds.length) {
    success.value = false
    loading.value = false
    message.value = 'Không tìm thấy thông tin đơn hàng'
    return
  }

  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      message.value = 'Vui lòng đăng nhập để xem thông tin đơn hàng'
      success.value = false
      loading.value = false
      window.dispatchEvent(new CustomEvent('openLoginModal'))
      return
    }

    orderDetails.value = []
    await loadProvinces() // Tải provinces trước

    for (const id of orderIds) {
      const orderRes = await fetch(`${config.public.apiBaseUrl}/user/orders/${id}`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      })

      if (orderRes.status === 403) throw new Error('Bạn không có quyền xem đơn này')
      if (orderRes.status === 404) throw new Error('Không tìm thấy đơn hàng')

      const orderData = await orderRes.json()
      console.log('Order Data for ID ' + id + ':', orderData)
      console.log('Order Data order_items structure:', orderData.order_items)
      console.log('Order Data order_items length:', orderData.order_items?.length)

      if (!orderRes.ok || !orderData) throw new Error(orderData.message || 'Không thể lấy thông tin đơn hàng')

      const shippingFee = orderData.shipping?.shipping_fee || (parseInt(orderData.final_price) - parseInt(orderData.total_price)) || 0
      const enrichedOrder = {
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
      }
      
      console.log('Enriched order order_items:', enrichedOrder.order_items)
      console.log('Enriched order order_items length:', enrichedOrder.order_items?.length)

      // Làm giàu địa chỉ cho từng đơn hàng
      if (enrichedOrder.address) {
        enrichAddress(enrichedOrder.address)
        if (enrichedOrder.address.province_id) {
          await loadDistricts(enrichedOrder.address.province_id)
          if (enrichedOrder.address.district_id) {
            await loadWards(enrichedOrder.address.district_id)
            enrichAddress(enrichedOrder.address) // Cập nhật lại sau khi tải wards
          } else {
            console.warn('No district_id found for order ID:', id)
          }
        } else {
          console.warn('No province_id found for order ID:', id)
        }
      } else {
        console.warn('No address found for order ID:', id)
      }

      orderDetails.value.push(enrichedOrder)
    }

    const isCOD = orderDetails.value.every(order => order.payments?.[0]?.method === 'COD')
    if (isCOD) {
      success.value = true
    } else {
      throw new Error('Chỉ hỗ trợ xác nhận đơn hàng thanh toán COD trên trang này. Đối với thanh toán VNPAY, vui lòng kiểm tra trạng thái tại trang VNPAY Return.')
    }

          // Lấy danh sách sản phẩm đã được thanh toán để xóa khỏi giỏ hàng
      const orderedItems = [];
      console.log('Processing orderDetails.value:', orderDetails.value);
      console.log('orderDetails.value length:', orderDetails.value?.length);
      
      if (Array.isArray(orderDetails.value)) {
        orderDetails.value.forEach((order, orderIndex) => {
          console.log(`Processing order ${orderIndex}:`, order);
          console.log(`Order ${orderIndex} order_items:`, order.order_items);
          console.log(`Order ${orderIndex} order_items length:`, order.order_items?.length);
          
          if (order.order_items && Array.isArray(order.order_items)) {
            order.order_items.forEach((item, itemIndex) => {
              console.log(`Processing item ${itemIndex}:`, item);
              console.log(`Item ${itemIndex} product:`, item.product);
              console.log(`Item ${itemIndex} variant:`, item.variant);
              
              const orderedItem = {
                product_id: item.product?.id,
                product_variant_id: item.variant?.id || null,
                quantity: item.quantity
              };
              
              console.log(`Created orderedItem:`, orderedItem);
              orderedItems.push(orderedItem);
            });
          } else {
            console.log(`Order ${orderIndex} has no order_items or it's not an array`);
          }
        });
      }
      
      console.log('Order success - orderedItems để xóa:', orderedItems);
      console.log('Order success - orderDetails.value:', orderDetails.value);
      
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
    
    loading.value = false
  } catch (err) {
    console.error('Error fetching orders:', err)
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