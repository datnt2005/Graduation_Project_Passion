```vue
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
        <!-- Thông tin đơn hàng -->
        <div v-if="orderDetails.length" class="space-y-8">
          <div v-for="order in orderDetails" :key="order.id" class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-gray-700 text-sm">
              <div><span class="font-medium text-gray-500">Mã đơn hàng:</span> <span class="font-semibold">{{ order.id || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">Người nhận:</span> <span class="font-semibold">{{ order.address?.name || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">Email:</span> <span class="font-semibold">{{ order.user?.email || 'N/A' }}</span></div>
              <div><span class="font-medium text-gray-500">SĐT:</span> <span class="font-semibold">{{ order.address?.phone || 'N/A' }}</span></div>
              <div class="sm:col-span-2">
                <span class="font-medium text-gray-500">Địa chỉ giao hàng:</span>
                <div class="mt-1 font-semibold text-gray-800">
                  {{ formatAddress(order.address) }}
                </div>
              </div>
              <div class="sm:col-span-2 text-center mt-2">
                <span class="font-medium text-gray-500">Phương thức thanh toán:</span>
                <span class="font-semibold">{{ getPaymentMethodLabel(order.payment_method || 'N/A') }}</span>
              </div>
            </div>
            <div class="mt-4 text-left text-sm space-y-1">
              <div class="flex justify-between">
                <span>Tổng tiền hàng:</span>
                <span>{{ formatPrice(order.totalPrice || 0) }}</span>
              </div>
              <div v-if="order.discountAmount > 0" class="flex justify-between">
                <span>Giảm giá sản phẩm:</span>
                <span class="text-green-600">- {{ formatPrice(order.discountAmount) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Phí vận chuyển:</span>
                <span>{{ formatPrice(order.shipping?.shippingFee || 0) }}</span>
              </div>
              <div v-if="order.shipping && order.shipping.shippingDiscount > 0" class="flex justify-between">
                <span>Giảm giá phí ship:</span>
                <span class="text-green-600">- {{ formatPrice(order.shipping.shippingDiscount) }}</span>
              </div>
              <div class="flex justify-between font-bold border-t pt-2 mt-2">
                <span>Tổng thanh toán:</span>
                <span class="text-blue-700">
                  {{ formatPrice((parseInt(order.finalPrice || 0) + parseInt(order.shipping?.shippingFee || 0) - parseInt(order.shipping?.shippingDiscount || 0))) }}
                </span>
              </div>
            </div>
            <!-- Danh sách sản phẩm -->
            <div v-if="order.orderItems?.length" class="mt-4">
              <div class="font-medium text-gray-700 mb-2">Sản phẩm đã đặt:</div>
              <div class="max-h-64 overflow-y-auto pr-2">
                <div v-for="item in order.orderItems" :key="item.id || item.product?.id + '-' + (item.productVariant?.id || '')" class="flex items-center gap-4 border-b py-2 last:border-0">
                  <img
                    :src="item.productVariant?.thumbnail ? mediaBaseUrl + item.productVariant.thumbnail : '/images/default-product.jpg'"
                    :alt="item.product?.name || 'Ảnh sản phẩm'"
                    class="w-12 h-12 object-cover rounded-md border"
                    @error="e => e.target.src = '/images/default-product.jpg'"
                  />
                  <div class="flex-1 text-left">
                    <div class="font-semibold text-gray-900">{{ item.product?.name || 'N/A' }}</div>
                    <div v-if="item.productVariant && item.productVariant.attributes" class="text-xs text-gray-500">
                      Phân loại: {{ formatAttributes(item.productVariant.attributes) }}
                    </div>
                    <div class="text-xs text-gray-500">Số lượng: {{ item.quantity || 1 }} × {{ formatPrice(item.price || 0) }}</div>
                  </div>
                  <div class="font-bold text-blue-700">{{ formatPrice(item.total || 0) }}</div>
                </div>
              </div>
            </div>
            <div v-else class="text-gray-500 text-sm italic">Không có thông tin sản phẩm.</div>
          </div>
        </div>
        <div v-else class="bg-gray-50 rounded-xl p-6 w-full border border-gray-200 shadow-sm text-gray-500 text-sm italic">
          Không có thông tin đơn hàng để hiển thị.
        </div>
        <!-- Nút điều hướng -->
        <div class="mt-6 flex gap-4 justify-center">
          <NuxtLink to="/" class="inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Về Trang Chủ
          </NuxtLink>
          <NuxtLink to="/users/orders" class="inline-block bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-md hover:shadow-lg">
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
        <div class="flex gap-4">
          <NuxtLink to="/checkout" class="inline-block bg-gradient-to-r from-gray-500 to-gray-600 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-md hover:shadow-lg">
            Thử Lại
          </NuxtLink>
          <NuxtLink to="/" class="inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg">
            Về Trang Chủ
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRuntimeConfig, navigateTo } from '#app';
import axios from 'axios';
import { useToast } from '~/composables/useToast';
import { useCheckout } from '~/composables/useCheckout';
import { useCart } from '~/composables/useCart';

const route = useRoute();
const config = useRuntimeConfig();
const { toast } = useToast();
const { formatPrice, getPaymentMethodLabel, removeOrderedItems } = useCheckout();
const { fetchCart } = useCart();

const loading = ref(true);
const success = ref(false);
const message = ref('');
const orderDetails = ref([]);
const mediaBaseUrl = config.public.mediaBaseUrl;

const formatAddress = (address) => {
  if (!address) return 'N/A';
  return [address.detail, address.ward_name, address.district_name, address.province_name]
    .filter(Boolean)
    .join(', ');
};

const formatAttributes = (attributes) => {
  if (!attributes || !Array.isArray(attributes)) return 'N/A';
  return attributes.map(attr => `${attr.name}: ${attr.value}`).join(', ');
};

const fetchOrderDetails = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      throw new Error('Vui lòng đăng nhập để xem thông tin đơn hàng.');
    }

    // Lấy orderIds từ query params hoặc localStorage
    let orderIds = [];
    if (route.query.ids) {
      orderIds = route.query.ids.split(',').map(id => id.trim()).filter(id => id);
    } else if (route.query.id) {
      orderIds = [route.query.id];
    } else {
      const storedOrderId = localStorage.getItem('lastOrderId');
      const storedOrderIds = localStorage.getItem('lastOrderIds');
      if (storedOrderId) {
        orderIds = [storedOrderId];
      } else if (storedOrderIds) {
        orderIds = JSON.parse(storedOrderIds || '[]').filter(id => id);
      }
    }

    if (!orderIds.length) {
      throw new Error('Không tìm thấy mã đơn hàng.');
    }

    console.log('Lấy chi tiết đơn hàng cho:', orderIds);

    // Gọi API với retry logic
    const requests = orderIds.map(id => ({
      id,
      promise: axios.get(`${config.public.apiBaseUrl}/user/orders/${id}`, {
        headers: { Authorization: `Bearer ${token}` },
        timeout: 10000,
      }).catch(async (err) => {
        console.warn(`Lỗi khi lấy đơn hàng ${id}:`, err.message);
        if (err.code === 'ECONNABORTED' || (err.response?.status && err.response.status >= 500)) {
          console.log(`Thử lại API cho đơn hàng ${id}`);
          await new Promise(resolve => setTimeout(resolve, 1000));
          return await axios.get(`${config.public.apiBaseUrl}/user/orders/${id}`, {
            headers: { Authorization: `Bearer ${token}` },
            timeout: 10000,
          });
        }
        throw err;
      })
    }));

    const responses = await Promise.allSettled(requests.map(req => req.promise));
    const fulfilledResponses = responses
      .map((res, index) => ({ res, id: requests[index].id }))
      .filter(r => r.res.status === 'fulfilled' && r.res.value.data?.data)
      .map(r => ({
        id: r.id,
        totalPrice: r.res.value.data.data.total_price || 0,
        discountAmount: r.res.value.data.data.discount_price || 0,
        finalPrice: r.res.value.data.data.final_price || 0,
        payment_method: r.res.value.data.data.payment_method || 'N/A',
        orderItems: (r.res.value.data.data.items || []).map(item => ({
          id: item.id,
          product: item.product || {},
          productVariant: item.product_variant || {},
          quantity: item.quantity || 1,
          price: item.price || 0,
          total: item.total || 0,
        })),
        address: r.res.value.data.data.address || {},
        user: r.res.value.data.data.user || {},
        shipping: {
          shippingFee: r.res.value.data.data.shipping?.shipping_fee || 0,
          shippingDiscount: r.res.value.data.data.shipping?.shipping_discount || 0,
        },
      }));

    orderDetails.value = fulfilledResponses;

    console.log('Chi tiết đơn hàng:', JSON.stringify(orderDetails.value, null, 2));

    if (!orderDetails.value.length) {
      if (route.query.error) {
        throw new Error(decodeURIComponent(route.query.error));
      }
      throw new Error('Không tìm thấy thông tin đơn hàng.');
    }

    // Xử lý VNPAY response
    if (route.query.vnp_ResponseCode) {
      if (route.query.vnp_ResponseCode === '00') {
        toast('success', 'Thanh toán VNPAY thành công!');
        success.value = true;
      } else {
        console.warn('Thanh toán VNPAY thất bại:', route.query);
        throw new Error(`Thanh toán VNPAY thất bại: Mã lỗi ${route.query.vnp_ResponseCode}`);
      }
    // Xử lý MOMO response
    } else if (route.query.resultCode) {
      if (route.query.resultCode === '0') {
        toast('success', 'Thanh toán MOMO thành công!');
        success.value = true;
      } else {
        console.warn('Thanh toán MOMO thất bại:', route.query);
        throw new Error(`Thanh toán MOMO thất bại: Mã lỗi ${route.query.resultCode}`);
      }
    } else {
      success.value = true;
    }

  } catch (err) {
    console.error('Lỗi khi lấy chi tiết đơn hàng:', err);
    success.value = false;
    message.value = err.message || 'Có lỗi xảy ra khi xác minh thanh toán.';
    toast('error', message.value);
    if (err.message.includes('đăng nhập')) {
      await navigateTo('/login');
    } else if (err.message.includes('quyền')) {
      await navigateTo('/');
    }
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    // Kiểm tra redirect_url từ localStorage (cho VNPAY/MOMO)
    const redirectUrl = localStorage.getItem('redirect_url');
    if (redirectUrl) {
      console.log('Chuyển hướng đến:', redirectUrl);
      localStorage.removeItem('redirect_url');
      window.location.href = redirectUrl;
      return;
    }

    // Kiểm tra để tránh chuyển hướng lặp
    const storedOrderId = localStorage.getItem('lastOrderId');
    const storedOrderIds = localStorage.getItem('lastOrderIds');
    if (storedOrderId && !route.query.id && !route.query.ids) {
      console.log('Chuyển hướng với lastOrderId:', storedOrderId);
      await navigateTo(`/order-success?id=${storedOrderId}`);
      return;
    } else if (storedOrderIds && !route.query.ids) {
      console.log('Chuyển hướng với lastOrderIds:', storedOrderIds);
      await navigateTo(`/order-success?ids=${JSON.parse(storedOrderIds).join(',')}`);
      return;
    }

    // Lấy danh sách mặt hàng trong giỏ hàng trước khi xóa
    await fetchCart();

    // Lấy chi tiết đơn hàng
    await fetchOrderDetails();

    // Xóa các mặt hàng đã đặt nếu thành công
    if (success.value) {
      await removeOrderedItems();
    }

    // Xóa localStorage sau khi xử lý
    localStorage.removeItem('lastOrderId');
    localStorage.removeItem('lastOrderIds');
    localStorage.removeItem('orderedItems');
    localStorage.removeItem('buy_now');

  } catch (err) {
    console.error('Lỗi trong onMounted:', err);
    toast('error', 'Đã xảy ra lỗi khi xử lý đơn hàng.');
    loading.value = false;
  }
});
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
```