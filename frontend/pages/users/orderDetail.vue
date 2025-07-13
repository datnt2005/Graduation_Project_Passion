<template>
  <div class="flex min-h-screen bg-[#f5f7fa] font-sans text-[#1a1a1a] justify-center py-8">
    <div class="flex bg-white rounded-lg shadow-xl max-w-6xl w-full overflow-hidden">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-8 overflow-y-auto">
        <div class="min-h-full">
          <!-- Header -->
          <div class="flex items-center mb-6">
            <NuxtLink to="/orders" class="text-blue-600 hover:text-blue-800 mr-2"
              aria-label="Quay lại danh sách đơn hàng">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
            </NuxtLink>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Chi tiết Đơn hàng #{{ order?.id || orderId }}</h2>
          </div>

          <!-- Loading -->
          <div v-if="isLoading" class="flex justify-center py-10">
            <div class="animate-spin w-8 h-8 border-t-2 border-blue-500 rounded-full"
              aria-label="Đang tải thông tin đơn hàng"></div>
          </div>

          <!-- Order Content -->
          <div v-else-if="order">
            <!-- Order Progress -->
            <div class="flex items-center justify-center gap-4 mb-8">
              <div class="flex flex-col items-center">
                <i class="fas fa-clipboard-list text-2xl"
                  :class="order.status === 'pending' ? 'text-blue-600' : 'text-gray-400'" aria-hidden="true"></i>
                <span class="text-xs mt-1"
                  :class="order.status === 'pending' ? 'text-blue-600 font-semibold' : 'text-gray-400'">Chờ xử lý</span>
              </div>
              <div class="h-1 w-8 bg-gray-300 rounded"></div>
              <div class="flex flex-col items-center">
                <i class="fas fa-cogs text-2xl"
                  :class="['processing', 'shipped', 'delivered', 'failed'].includes(order.status) ? 'text-blue-600' : 'text-gray-400'"
                  aria-hidden="true"></i>
                <span class="text-xs mt-1"
                  :class="['processing', 'shipped', 'delivered', 'failed'].includes(order.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đã xử lý</span>
              </div>
              <div class="h-1 w-8 bg-gray-300 rounded"></div>
              <div class="flex flex-col items-center">
                <i class="fas fa-shipping-fast text-2xl"
                  :class="['shipped', 'delivered', 'failed'].includes(order.status) ? 'text-blue-600' : 'text-gray-400'"
                  aria-hidden="true"></i>
                <span class="text-xs mt-1"
                  :class="['shipped', 'delivered', 'failed'].includes(order.status) ? 'text-blue-600 font-semibold' : 'text-gray-400'">Đang giao</span>
              </div>
              <div class="h-1 w-8 bg-gray-300 rounded"></div>
              <div class="flex flex-col items-center">
                <i v-if="order.status === 'failed'" class="fas fa-times-circle text-2xl text-red-600" aria-hidden="true"></i>
                <i v-else class="fas fa-check-circle text-2xl"
                  :class="order.status === 'delivered' ? 'text-green-600' : 'text-gray-400'" aria-hidden="true"></i>
                <span class="text-xs mt-1"
                  :class="order.status === 'delivered' ? 'text-green-600 font-semibold' : order.status === 'failed' ? 'text-red-600 font-semibold' : 'text-gray-400'">
                  {{ order.status === 'failed' ? 'Giao thất bại' : 'Đã giao' }}
                </span>
              </div>
            </div>

            <!-- Order Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              <!-- Order Details -->
              <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Thông tin Đơn hàng</h3>
                <div class="space-y-2 text-sm text-gray-700">
                  <p><strong>Mã vận đơn:</strong> {{ order.shipping?.tracking_code || '-' }}</p>
                  <p><strong>Ngày đặt hàng:</strong> {{ formatDate(order.created_at) }}</p>
                  <p><strong>Trạng thái:</strong>
                    <span :class="statusClass(order.status)"
                      class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap">
                      {{ statusText(order.status) }}
                    </span>
                  </p>
                  <p><strong>Trạng thái vận chuyển:</strong>
                    <span :class="statusClass(order.shipping?.status)"
                      class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap">
                      {{ statusText(order.shipping?.status) || 'Chưa có' }}
                    </span>
                  </p>
                  <p><strong>Tổng tiền:</strong> <span class="text-lg font-bold text-blue-600">{{
                    formatPrice(order.final_price * 1000) }}</span></p>
                </div>
              </div>

              <!-- Shipping Address -->
              <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Địa chỉ Giao hàng</h3>
                <div class="space-y-2 text-sm text-gray-700">
                  <p><strong>Người nhận:</strong> {{ order.user?.name || '-' }}</p>
                  <p><strong>Số điện thoại:</strong> {{ order.address?.phone || '-' }}</p>
                  <p><strong>Địa chỉ:</strong> {{ order.address?.detail || '-' }}, {{ order.address?.ward_name || '-'
                    }}, {{ order.address?.district_name || '-' }}, {{ order.address?.province_name || '-' }}</p>
                </div>
              </div>

              <!-- Payment Information -->
              <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Phương thức Thanh toán</h3>
                <div class="space-y-2 text-sm text-gray-700">
                  <p><strong>Phương thức:</strong> {{ order.payments?.[0]?.method || '-' }}</p>
                  <p><strong>Trạng thái thanh toán:</strong> {{ statusText(order.payments?.[0]?.status) || '-' }}</p>
                  <p><strong>Mã giao dịch:</strong> {{ order.payments?.[0]?.transaction_id || '-' }}</p>
                </div>
              </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
              <h3 class="text-xl font-bold text-gray-800 p-6 border-b border-gray-200 bg-gray-50">Sản phẩm trong đơn
                hàng</h3>
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm
                    </th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng
                    </th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn giá
                    </th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thành tiền
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in order.order_items" :key="item.product?.id + '-' + (item.variant?.id || '')">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                      <img :src="getProductImage(item.product?.thumbnail)" :alt="item.product?.name || 'Ảnh sản phẩm'"
                        class="w-10 h-10 rounded mr-3 object-cover border" loading="lazy">
                      <div>
                        <span>{{ item.product?.name || '-' }}</span>
                        <p v-if="item.variant" class="text-xs text-gray-500">Phân loại: {{ item.variant.name }}</p>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.quantity || 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatPrice(item.price * 1000) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatPrice(item.total * 1000) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="bg-gray-50">
                    <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-700">Tạm tính:</td>
                    <td class="px-6 py-3 text-left text-sm font-bold text-gray-900">{{ formatPrice(order.subtotal *
                      1000) }}</td>
                  </tr>
                  <tr class="bg-gray-50">
                    <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-700">Phí vận chuyển:</td>
                    <td class="px-6 py-3 text-left text-sm font-bold text-gray-900">{{
                      formatPrice(order.shipping?.shipping_fee * 1000 || 0) }}</td>
                  </tr>
                  <tr class="bg-gray-100">
                    <td colspan="3" class="px-6 py-3 text-right text-lg font-extrabold text-gray-900">Tổng cộng:</td>
                    <td class="px-6 py-3 text-left text-lg font-extrabold text-blue-600">{{
                      formatPrice(order.final_price * 1000) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Refund Information -->
<div v-if="['failed', 'cancelled', 'refunded', 'returned'].includes(order.status)"
  class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
  <h3 class="text-xl font-bold text-gray-800 p-6 border-b border-gray-200 bg-gray-50">Xử lý hoàn tiền</h3>
  <div class="p-6 text-sm text-gray-700">
    <p class="mb-4"><strong>Lý do hiện tại:</strong> {{ order.note || 'Chưa có ghi chú' }}</p>
    <!-- Thông báo lỗi nếu không có dữ liệu hoàn tiền -->
    <div v-if="order.status === 'refunded' && !order.refund"
      class="mt-4 bg-yellow-50 p-4 rounded-md border border-yellow-200 text-yellow-700">
      <p><strong>Cảnh báo:</strong> Đơn hàng ở trạng thái đã hoàn tiền, nhưng không tìm thấy thông tin yêu cầu hoàn tiền. Vui lòng liên hệ hỗ trợ!</p>
    </div>
    <!-- Hiển thị thông tin hoàn tiền nếu có -->
    <div v-if="hasRefund || order.refund"
      class="mt-4 bg-gray-50 p-4 rounded-md border border-gray-200">
      <h4 class="text-lg font-semibold text-gray-800 mb-3">Thông tin yêu cầu hoàn tiền</h4>
      <div class="space-y-3">
        <p><strong>Mã hoàn tiền:</strong> {{ order.refund?.id || '-' }}</p>
        <p><strong>Số tiền hoàn:</strong> {{ formatPrice((order.refund?.amount || order.final_price) * 1000) }}</p>
        <p><strong>Trạng thái:</strong>
          <span :class="refundStatusClass(order.refund?.status || 'approved')"
            class="px-3 py-1 text-xs rounded-full font-medium whitespace-nowrap">
            {{ refundStatusText(order.refund?.status || 'approved') }}
          </span>
        </p>
        <p><strong>Lý do:</strong> {{ order.refund?.reason || order.note || 'Không có lý do' }}</p>
        <p><strong>Thời gian tạo:</strong> {{ formatDate(order.refund?.created_at || order.updated_at) || '-' }}</p>
      </div>
    </div>
    <!-- Hiển thị form nếu chưa có yêu cầu và trạng thái cho phép -->
    <div v-if="!hasRefund && !order.refund && ['failed', 'cancelled', 'returned'].includes(order.status)"
      class="mt-4 bg-white p-4 rounded-md border border-gray-200">
      <h4 class="text-lg font-semibold text-gray-800 mb-3">Gửi yêu cầu hoàn tiền</h4>
      <div class="space-y-4">
        <div>
          <label class="block mb-1 font-medium text-gray-700" for="refund-amount">Số tiền hoàn (VND):</label>
          <input v-model.number="refundAmount" id="refund-amount" type="number" min="0" :max="maxRefundAmount"
            class="w-full border rounded px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Nhập số tiền hoàn" aria-label="Nhập số tiền hoàn" aria-required="true">
          <p v-if="refundAmount > maxRefundAmount" class="text-red-500 text-xs mt-1">
            Số tiền hoàn không được vượt quá {{ formatPrice(maxRefundAmount) }}!
          </p>
          <p v-else class="text-green-500 text-xs mt-1">
            Số tiền hoàn đã được tự động điền: {{ formatPrice(refundAmount) }}
          </p>
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700" for="refund-reason">Lý do hoàn tiền:</label>
          <textarea v-model="refundReason" id="refund-reason"
            class="w-full border rounded px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Nhập lý do hoàn tiền" rows="4" aria-label="Nhập lý do hoàn tiền" aria-required="true"></textarea>
        </div>
        <button @click="resetRefundAmount"
          class="px-4 py-2 bg-gray-性を6px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer;">Đặt lại số tiền</button>
        <button @click="requestRefund(order)"
          class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:bg-gray-400 disabled:cursor-not-allowed"
          :disabled="!refundAmount || !refundReason || refundAmount <= 0 || refundAmount > maxRefundAmount"
          aria-label="Gửi yêu cầu hoàn tiền">
          Gửi yêu cầu hoàn tiền
        </button>
      </div>
    </div>
  </div>
</div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
              <button v-if="order.can_delete" @click="confirmCancel(order.id)"
                class="px-6 py-3 bg-red-500 text-white rounded-md shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out"
                aria-label="Hủy đơn hàng">
                Hủy đơn hàng
              </button>
              <button v-if="order.status === 'delivered'" @click="printOrder(order.id)"
                class="px-6 py-3 bg-gray-600 text-white rounded-md shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out"
                aria-label="In hóa đơn">
                In hóa đơn
              </button>
              <button v-if="order.status === 'delivered'" @click="downloadPDF(order.id)"
                class="px-6 py-3 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                aria-label="Tải hóa đơn PDF">
                Tải PDF
              </button>
            </div>
          </div>

          <!-- Error or No Data -->
          <div v-else class="text-center text-gray-600 mt-10">
            Không tìm thấy thông tin đơn hàng.
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter, useHead } from '#app';
import axios from 'axios';
import Swal from 'sweetalert2';
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue';

// State
const route = useRoute();
const router = useRouter();
const orderId = computed(() => route.params.id);
const order = ref(null);
const isLoading = ref(true);
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBaseUrl = config.public.mediaBaseUrl.endsWith('/') ? config.public.mediaBaseUrl : config.public.mediaBaseUrl + '/';
const refundAmount = ref(0);
const refundReason = ref('');
const hasRefund = ref(false);

// Utility Functions
const formatPrice = (price) => {
  const number = typeof price === 'string' ? parseFloat(price) : price;
  if (isNaN(number)) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(number);
};

const formatDate = (date) => {
  if (!date) return '-';
  try {
    const parsedDate = new Date(date);
    if (isNaN(parsedDate)) return '-';
    return parsedDate.toLocaleString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch {
    return '-';
  }
};

const toast = (icon, title) => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    width: '350px',
    padding: '10px 20px',
    customClass: { popup: 'text-sm rounded-md shadow-md' },
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toastEl) => {
      toastEl.addEventListener('mouseenter', () => Swal.stopTimer());
      toastEl.addEventListener('mouseleave', () => Swal.resumeTimer());
    }
  });
};

const getProductImage = (thumbnail) => {
  if (!thumbnail) return '/images/no-image.png';
  if (thumbnail.startsWith('http://') || thumbnail.startsWith('https://')) return `${thumbnail}?w=100&q=80`;
  return `${mediaBaseUrl}${thumbnail}?w=100&q=80`;
};

// Status Mappings
const statusText = (status) => ({
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  shipping: 'Đang giao hàng',
  shipped: 'Đã gửi hàng',
  delivered: 'Đã giao hàng',
  cancelled: 'Đã huỷ',
  completed: 'Đã thanh toán',
  failed: 'Giao thất bại',
  refunded: 'Đã hoàn tiền',
  returned: 'Đã trả hàng',
  success: 'Thành công',
  paid: 'Đã thanh toán',
  unpaid: 'Chưa thanh toán',
  waiting: 'Đang chờ',
  error: 'Lỗi',
})[status] || (status ? status : 'Không xác định');

const statusClass = (status) => ({
  pending: 'bg-yellow-100 text-yellow-700',
  processing: 'bg-indigo-100 text-indigo-700',
  shipped: 'bg-blue-100 text-blue-700',
  delivered: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
  refunded: 'bg-orange-100 text-orange-700',
  returned: 'bg-orange-100 text-orange-700',
  failed: 'bg-red-100 text-red-700'
})[status] || 'bg-gray-100 text-gray-700';

const refundStatusText = (status) => ({
  pending: 'Chờ xử lý',
  approved: 'Đã duyệt',
  rejected: 'Đã từ chối'
})[status] || (status ? status : 'Đã duyệt');

const refundStatusClass = (status) => ({
  pending: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800'
})[status] || 'bg-green-100 text-green-800';

// Computed
const maxRefundAmount = computed(() => {
  if (!order.value) return 0;
  const finalPrice = Number(order.value.final_price || 0) * 1000;
  const shippingFee = Number(order.value.shipping?.shipping_fee || 0) * 1000;
  console.log('Calculating maxRefundAmount:', { finalPrice, shippingFee });
  return Math.max(finalPrice - shippingFee, 0);
});

// Reset refund amount to default
const resetRefundAmount = () => {
  refundAmount.value = maxRefundAmount.value;
  toast('info', `Số tiền hoàn đã được đặt lại: ${formatPrice(refundAmount.value)}`);
};

// API Calls
const fetchOrder = async () => {
  isLoading.value = true;
  try {
    let token = null;
    if (process.client) {
      token = localStorage.getItem('access_token') || useCookie('access_token')?.value;
    }
    if (!token) throw new Error('Chưa đăng nhập');

    const { data } = await axios.get(`${apiBase}/orders/${orderId.value}`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    if (!data.success) throw new Error(data.message || 'Lỗi khi tải thông tin đơn hàng');

    order.value = {
      ...data.data,
      refund: data.data.refund || null,
      subtotal: data.data.order_items.reduce((sum, item) => sum + Number(item.total || 0), 0)
    };

    // Kiểm tra và gán maxRefundAmount
    const finalPrice = Number(order.value.final_price || 0) * 1000;
    const shippingFee = Number(order.value.shipping?.shipping_fee || 0) * 1000;
    if (isNaN(finalPrice) || isNaN(shippingFee)) {
      console.error('Invalid final_price or shipping_fee:', { finalPrice, shippingFee });
      toast('error', 'Dữ liệu đơn hàng không hợp lệ, không thể tính số tiền hoàn!');
      refundAmount.value = 0;
    } else {
      // Gán maxRefundAmount và kiểm tra trạng thái hợp lệ
      if (!order.value.refund && ['failed', 'cancelled', 'returned'].includes(order.value.status)) {
        refundAmount.value = Math.max(finalPrice - shippingFee, 0);
        if (refundAmount.value > 0) {
          toast('info', `Số tiền hoàn đã được tự động điền: ${formatPrice(refundAmount.value)}`);
        } else {
          toast('error', 'Số tiền hoàn không hợp lệ!');
        }
      }
    }

    // Fetch refund data if status is refunded and no refund data
    if (data.data.status === 'refunded' && !order.value.refund) {
      const refundRes = await axios.get(`${apiBase}/refunds?order_id=${orderId.value}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      if (refundRes.data.success && Array.isArray(refundRes.data.data) && refundRes.data.data.length > 0) {
        order.value.refund = refundRes.data.data[0];
      }
    }

    hasRefund.value = !!order.value.refund || data.data.status === 'refunded';

    // Fetch address names
    if (data.data.address) {
      const [provinceRes, districtRes, wardRes] = await Promise.all([
        axios.get(`${apiBase}/ghn/provinces`),
        axios.post(`${apiBase}/ghn/districts`, { province_id: data.data.address.province_id }),
        axios.post(`${apiBase}/ghn/wards`, { district_id: data.data.address.district_id })
      ]);

      const provinces = Array.isArray(provinceRes.data.data) ? provinceRes.data.data : [];
      const districts = Array.isArray(districtRes.data.data) ? districtRes.data.data : [];
      const wards = Array.isArray(wardRes.data.data) ? wardRes.data.data : [];

      order.value.address = {
        ...order.value.address,
        province_name: provinces.find(p => p.ProvinceID == data.data.address.province_id)?.ProvinceName || '-',
        district_name: districts.find(d => d.DistrictID == data.data.address.district_id)?.DistrictName || '-',
        ward_name: wards.find(w => w.WardCode == data.data.address.ward_code)?.WardName || '-'
      };
    }
  } catch (err) {
    console.error('Error fetching order:', err.message, err.response?.status, err.response?.data);
    toast('error', err.response?.data?.message || 'Không thể tải thông tin đơn hàng!');
    if (err.response?.status === 401 || err.response?.status === 403) {
      if (process.client) {
        localStorage.removeItem('access_token');
      }
      router.push('/login');
    }
    order.value = null;
    hasRefund.value = false;
    refundAmount.value = 0; // Đặt lại refundAmount nếu có lỗi
  } finally {
    isLoading.value = false;
  }
};

const requestRefund = async (order) => {
  if (hasRefund.value || order.refund) {
    toast('error', 'Đơn hàng này đã có yêu cầu hoàn tiền!');
    return;
  }

  if (!refundReason.value) {
    toast('error', 'Vui lòng nhập lý do hoàn tiền!');
    return;
  }
  if (!refundAmount.value || refundAmount.value <= 0) {
    toast('error', 'Vui lòng nhập số tiền hoàn hợp lệ!');
    return;
  }
  if (refundAmount.value > maxRefundAmount.value) {
    toast('error', `Số tiền hoàn không được vượt quá ${formatPrice(maxRefundAmount.value)}!`);
    return;
  }

  const result = await Swal.fire({
    title: 'Xác nhận yêu cầu hoàn tiền',
    text: `Bạn có chắc chắn muốn yêu cầu hoàn ${formatPrice(refundAmount.value)} cho đơn hàng ${order.shipping?.tracking_code || order.id}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Gửi yêu cầu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#f97316',
    cancelButtonColor: '#6b7280'
  });

  if (!result.isConfirmed) return;

  try {
    const token = process.client ? localStorage.getItem('access_token') || useCookie('access_token')?.value : null;
    if (!token) throw new Error('Chưa đăng nhập');

    const response = await axios.post(`${apiBase}/orders/${order.id}/refund`, {
      reason: refundReason.value,
      amount: Number(refundAmount.value) / 1000,
      status: 'pending'
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });

    if (response.data.success) {
      toast('success', response.data.message || ' czasu yêu cầu hoàn tiền đã được gửi!');
      refundAmount.value = 0;
      refundReason.value = '';
      await fetchOrder();
    } else {
      throw new Error(response.data.message || 'Lỗi khi gửi yêu cầu hoàn tiền');
    }
  } catch (error) {
    console.error('Lỗi khi gửi yêu cầu hoàn tiền:', error.message, error.response?.status, error.response?.data);
    let message = error.response?.data?.message || error.message;
    if (message.includes('Đơn hàng này đã có yêu cầu hoàn tiền')) {
      message = 'Đơn hàng này đã có yêu cầu hoàn tiền đang chờ xử lý!';
      await fetchOrder();
    } else if (message.includes('Số tiền hoàn không được vượt quá')) {
      message = `Số tiền hoàn không được vượt quá ${formatPrice(maxRefundAmount.value)}!`;
    } else if (error.response?.status === 404) {
      message = 'Không tìm thấy đơn hàng hoặc dịch vụ hoàn tiền!';
    } else if (error.response?.status === 403) {
      message = 'Bạn không có quyền yêu cầu hoàn tiền. Vui lòng đăng nhập lại!';
      if (process.client) {
        localStorage.removeItem('access_token');
      }
      router.push('/login');
    }
    toast('error', message);
  }
};

const confirmCancel = (orderId) => {
  Swal.fire({
    icon: 'error',
    title: 'Xác nhận hủy',
    text: 'Bạn có chắc chắn muốn hủy đơn hàng này?',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#e53e3e',
    cancelButtonColor: '#fff',
    customClass: {
      popup: 'rounded-md shadow-sm',
      title: 'text-base font-semibold',
      htmlContainer: 'text-sm',
      confirmButton: 'text-sm px-4 py-2 bg-red-600 text-white',
      cancelButton: 'text-sm px-4 py-2 bg-white text-gray-700 border border-gray-300'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      cancelOrder(orderId);
    }
  });
};

const cancelOrder = async (id) => {
  try {
    const token = process.client ? localStorage.getItem('access_token') || useCookie('access_token')?.value : null;
    if (!token) throw new Error('Chưa đăng nhập');

    await axios.post(`${apiBase}/orders/${id}/cancel`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    });
    toast('success', 'Đơn hàng đã được hủy!');
    await fetchOrder();
  } catch (err) {
    console.error('Error cancelling order:', err.message, err.response?.status, err.response?.data);
    toast('error', err.response?.data?.message || 'Không thể hủy đơn hàng!');
  }
};

const printOrder = (orderId) => {
  if (process.client) {
    window.open(`/users/print-order/${orderId}`, '_blank');
  }
};

const downloadPDF = async (orderId) => {
  try {
    const token = process.client ? localStorage.getItem('access_token') || useCookie('access_token')?.value : null;
    if (!token) throw new Error('Chưa đăng nhập');

    const res = await axios.get(`${apiBase}/orders/${orderId}`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    const data = res.data.data;
    const { default: html2pdf } = await import('html2pdf.js');
    const content = `
      <div style="font-family:sans-serif; font-size:13px;">
        <h2 style="text-align:center">HÓA ĐƠN MUA HÀNG - #ORD${String(data.id).padStart(3, '0')}</h2>
        <p><strong>Khách hàng:</strong> ${data.user.name}</p>
        <p><strong>Email:</strong> ${data.user.email}</p>
        <p><strong>Địa chỉ:</strong> ${data.address.detail}, ${data.address.ward_name}, ${data.address.district_name}, ${data.address.province_name}</p>
        <p><strong>SĐT:</strong> ${data.address.phone}</p>
        <hr />
        <table style="width:100%; border-collapse: collapse;" border="1">
          <thead><tr>
            <th style="padding:4px;">Sản phẩm</th>
            <th style="padding:4px;">SL</th>
            <th style="padding:4px;">Đơn giá</th>
            <th style="padding:4px;">Thành tiền</th>
          </tr></thead>
          <tbody>
            ${data.order_items.map(item => `
              <tr>
                <td style="padding:4px;">${item.product.name}${item.variant ? ` (${item.variant.name})` : ''}</td>
                <td style="padding:4px; text-align:center;">${item.quantity}</td>
                <td style="padding:4px; text-align:right;">${formatPrice(item.price * 1000)}</td>
                <td style="padding:4px; text-align:right;">${formatPrice(item.total * 1000)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
        <p style="text-align:right; font-weight:bold; margin-top:10px;">Tổng cộng: ${formatPrice(data.final_price * 1000)}</p>
      </div>
    `;
    const opt = {
      margin: 0.5,
      filename: `HoaDon_${data.user.name.replace(/\s+/g, '_')}_ORD${String(data.id).padStart(3, '0')}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().from(content).set(opt).save();
  } catch (err) {
    console.error('Error downloading PDF:', err.message, err.response?.status, err.response?.data);
    toast('error', err.response?.data?.message || 'Không thể tải hóa đơn PDF!');
  }
};

// Lifecycle
onMounted(async () => {
  useHead({
    title: `Chi tiết Đơn hàng #${orderId.value} | Quản lý đơn hàng`,
    meta: [
      { name: 'description', content: `Xem chi tiết đơn hàng #${orderId.value}, yêu cầu hoàn tiền và tải hóa đơn PDF.` },
      { name: 'robots', content: 'noindex, nofollow' },
      { property: 'og:title', content: `Chi tiết Đơn hàng #${orderId.value}` },
      { property: 'og:description', content: `Xem chi tiết đơn hàng #${orderId.value}, yêu cầu hoàn tiền và tải hóa đơn PDF.` },
      { property: 'og:type', content: 'website' }
    ]
  });
  await fetchOrder();
});

// Watch hasRefund for debugging
watch(hasRefund, (newValue) => {
  console.log('hasRefund changed:', newValue);
});

// Watch refundAmount for debugging
watch(refundAmount, (newValue) => {
  console.log('refundAmount changed:', newValue);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.2s ease;
  transform-origin: top right;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>