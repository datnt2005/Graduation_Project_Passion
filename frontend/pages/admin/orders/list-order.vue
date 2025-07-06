<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý đơn hàng</h1>
      </div>

      <!-- Nút chuyển đổi -->
      <div class="flex gap-2 mb-4 px-4 pt-4">
        <button
          @click="showPayoutList = false"
          :class="['px-4 py-2 rounded', !showPayoutList ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']"
        >Đơn hàng</button>
        <button
          @click="showPayoutList = true"
          :class="['px-4 py-2 rounded', showPayoutList ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']"
        >Thanh toán đã cập nhật</button>
      </div>

      <div v-if="!showPayoutList">
        <!-- Filter Bar -->
        <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
          <div class="flex items-center gap-2">
            <span class="font-bold">Tất cả</span>
            <span>({{ totalItems }})</span>
          </div>
          <div class="flex gap-2">
            <select v-model="filters.status" class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Tất cả trạng thái</option>
              <option value="pending">Chờ xử lý</option>
              <option value="processing">Đang xử lý</option>
              <option value="shipped">Đang giao</option>
              <option value="delivered">Đã giao</option>
              <option value="cancelled">Đã hủy</option>
            </select>
            <!-- Thêm filter phương thức thanh toán -->
            <select v-model="filters.payment_method" class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Tất cả phương thức</option>
              <option v-for="method in paymentMethods" :key="method.id" :value="method.name">
                {{ method.name }}
              </option>
            </select>
            <input type="date" v-model="filters.from_date" class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Từ ngày">
            <input type="date" v-model="filters.to_date" class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Đến ngày">
            <input type="text" v-model="filters.order_id" placeholder="Mã đơn hàng" class="rounded-md border border-gray-300 py-1.5 px-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="ml-auto flex gap-2">
            <button @click="resetFilters" class="px-4 py-2 border rounded-md bg-white hover:bg-gray-50">Đặt lại</button>
            <button @click="fetchOrders" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Tìm kiếm</button>
          </div>
        </div>

        <!-- Table -->
        <table class="min-w-full border-collapse border border-gray-300 text-sm">
          <thead class="bg-white border-b border-gray-300">
            <tr>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Mã vận đơn</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Khách hàng</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tổng tiền</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Phương thức thanh toán</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày tạo</th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in orders" :key="order.id" :class="{'bg-gray-50': order.id % 2 === 0}" class="border-b border-gray-300">
              <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700">{{ order.shipping?.tracking_code || 'Chưa có' }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                {{ order.user.name }}<br>
                <span class="text-xs">{{ order.user.email }}</span>
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">{{ order.final_price }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                {{ getPaymentMethodText(order.payments[0]?.method) }}
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                <span :class="getStatusClass(order.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ getStatusText(order.status) }}
                </span>
              </td>
              <td class="border border-gray-300 px-3 py-2 text-left">{{ order.created_at }}</td>
              <td class="border border-gray-300 px-3 py-2 text-left">
                <div class="relative inline-block text-left">
                  <button @click.stop="toggleDropdown(order.id)" class="inline-flex items-center text-gray-600 hover:text-gray-800 focus:outline-none">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                  </button>
                  <!-- Dropdown menu -->
                  <div
                    v-if="activeDropdown === order.id"
                    class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                  >
                    <div class="py-1">
                      <button
                        @click="updateOrderStatus(order)"
                        class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50"
                      >
                        Đổi trạng thái
                      </button>
                      <button
                        @click="deleteOrder(order.id); activeDropdown = null"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                      >
                        Xóa
                      </button>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination giữ nguyên, có thể style lại cho đồng bộ -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Hiển thị {{ (currentPage - 1) * perPage + 1 }} đến {{ Math.min(currentPage * perPage, totalItems) }} 
              trong tổng số {{ totalItems }} đơn hàng
            </div>
            <div class="flex space-x-2">
              <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                class="px-3 py-1 border rounded-md disabled:opacity-50">Trước</button>
              <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                class="px-3 py-1 border rounded-md disabled:opacity-50">Sau</button>
            </div>
          </div>
        </div>
      </div>

      <div v-else>
        <!-- Bảng payout đã cập nhật -->
        <div class="bg-white p-6 rounded shadow w-full overflow-x-auto">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <span>💸</span> Danh sách thanh toán đã cập nhật
          </h2>
          <div class="flex flex-wrap gap-3 mb-4">
            <input v-model="payoutTrackingKeyword" type="text" placeholder="Tìm theo mã vận đơn (tracking_code)" class="border p-2 rounded flex-1 min-w-[180px] placeholder-gray-400">
            <select v-model="payoutSortOption" class="border p-2 rounded min-w-[160px]">
              <option value="transferred_desc">Mới nhất (ngày chuyển khoản)</option>
              <option value="created_desc">Gần đây nhất (ngày tạo)</option>
              <option value="created_asc">Cũ nhất</option>
            </select>
          </div>
          <div v-if="payoutLoading" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
          <div v-else-if="payoutError" class="text-center text-red-500 py-10">{{ payoutError }}</div>
          <div v-else-if="!payoutTrackingFilteredData.length" class="text-center text-gray-400 py-10">Không có payout nào</div>
          <div v-else class="mt-4">
            <table class="w-full table-auto divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">MÃ PAYOUT</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">SỐ TIỀN</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">NGÀY YÊU CẦU</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">NGÀY DUYỆT</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">TRẠNG THÁI</th>
                  <th class="px-4 py-3 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">GHI CHÚ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in payoutTrackingPaginatedData" :key="item.id" class="hover:bg-blue-50 transition">
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-blue-700">
                    {{ getTrackingCode(item.order_id) }}
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.amount) }} đ</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.created_at) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.transferred_at) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <span :class="payoutStatusClass(item.status)">{{ payoutStatusLabel(item.status) }}</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.note }}</td>
                </tr>
              </tbody>
            </table>
            <div v-if="payoutTrackingTotalPages > 1" class="flex justify-center mt-4">
              <button @click="payoutTrackingPage--" :disabled="payoutTrackingPage === 1" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&lt;</button>
              <button v-for="p in payoutTrackingTotalPages" :key="p" @click="payoutTrackingPage = p" :class="['px-3 py-1 mx-1 rounded border', payoutTrackingPage === p ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{ p }}</button>
              <button @click="payoutTrackingPage++" :disabled="payoutTrackingPage === payoutTrackingTotalPages" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&gt;</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification Popup giống list-coupon.vue -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="notification.show"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50"
        >
          <div class="flex-shrink-0">
            <svg
              class="h-6 w-6 text-green-400"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notification.message }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button
              @click="closeNotification"
              class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
            >
              <svg
                class="h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Modal cập nhật trạng thái payout -->
    <div v-if="showUpdateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
        <button @click="closeUpdateModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <h2 class="text-lg font-semibold mb-4">Cập nhật trạng thái payout</h2>
        <div class="mb-4">
          <div><b>Đơn hàng - Mã vận đơn:</b> {{ orderToUpdate?.shipping?.tracking_code || 'Chưa có' }}</div>
          <div><b>Số tiền payout:</b> {{ formatPrice(orderToUpdate?.payout_amount || orderToUpdate?.amount) }}</div>
          <div><b>Trạng thái hiện tại:</b> <span class="font-semibold">{{ payoutStatusText(orderToUpdate?.payout_status) }}</span></div>
        </div>
        <div class="mb-4">
          <label class="block mb-1">Chọn trạng thái payout mới:</label>
          <select v-model="newPayoutStatus" class="w-full border rounded px-3 py-2">
            <option value="pending">Chờ xử lý</option>
            <option value="completed">Đã chuyển khoản</option>
            <option value="failed">Thất bại</option>
          </select>
        </div>
        <div class="flex justify-end gap-2">
          <button @click="closeUpdateModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Hủy</button>
          <button @click="confirmUpdatePayoutStatus" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" :disabled="loading">Cập nhật</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted, watch } from 'vue';
import { useRuntimeConfig } from '#app';

definePageMeta({
    layout: 'default-admin'
});

// State
const orders = ref([]);
const selectedOrder = ref(null);
const showUpdateModal = ref(false);
const newPayoutStatus = ref('');
const orderToUpdate = ref(null);
const loading = ref(false);
const currentPage = ref(1);
const perPage = ref(10);
const totalItems = ref(0);
const totalPages = ref(1);
const activeDropdown = ref(null);
const showPayoutList = ref(false);
const payoutLoading = ref(false);
const payoutError = ref('');
const payoutData = ref([]);
const payoutFilteredData = ref([]);
const payoutPage = ref(1);
const payoutPageSize = ref(10);
const payoutTotalPages = computed(() => Math.ceil(payoutFilteredData.value.length / payoutPageSize.value));
const payoutPaginatedData = computed(() => {
  const start = (payoutPage.value - 1) * payoutPageSize.value;
  return payoutFilteredData.value.slice(start, start + payoutPageSize.value);
});

const filters = ref({
    status: '',
    from_date: '',
    to_date: '',
    order_id: '',
});

const paymentMethods = ref([]);
const paymentLoading = ref(false);

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

// Status mapping
const statusMap = {
    pending: { text: 'Chờ xử lý', class: 'bg-yellow-100 text-yellow-800' },
    processing: { text: 'Đang xử lý', class: 'bg-blue-100 text-blue-800' },
    shipped: { text: 'Đang giao', class: 'bg-purple-100 text-purple-800' },
    delivered: { text: 'Đã giao', class: 'bg-green-100 text-green-800' },
    cancelled: { text: 'Đã hủy', class: 'bg-red-100 text-red-800' }
};

const paymentMethodMap = {
  cod: 'Thanh toán khi nhận hàng',
  banking: 'Chuyển khoản',
  momo: 'Ví MoMo'
  // Thêm các phương thức khác nếu có
};

// Add new state for notification
const notification = ref({
    show: false,
    message: '',
    success: true,
    timeout: null
});

const payoutTrackingKeyword = ref('');
const payoutTrackingPage = ref(1);
const payoutTrackingPageSize = ref(10);
const payoutSortOption = ref('transferred_desc');
const payoutTrackingFilteredData = computed(() => {
  let arr = payoutFilteredData.value;
  // Lọc theo tracking_code
  if (payoutTrackingKeyword.value) {
    const kw = payoutTrackingKeyword.value.toLowerCase();
    arr = arr.filter(item => {
      const code = getTrackingCode(item.order_id).toLowerCase();
      return code.includes(kw);
    });
  }
  // Sắp xếp theo lựa chọn
  if (payoutSortOption.value === 'transferred_desc') {
    arr = [...arr].sort((a, b) => new Date(b.transferred_at || 0) - new Date(a.transferred_at || 0));
  } else if (payoutSortOption.value === 'created_desc') {
    arr = [...arr].sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
  } else if (payoutSortOption.value === 'created_asc') {
    arr = [...arr].sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));
  }
  return arr;
});
const payoutTrackingTotalPages = computed(() => Math.ceil(payoutTrackingFilteredData.value.length / payoutTrackingPageSize.value));
const payoutTrackingPaginatedData = computed(() => {
  const start = (payoutTrackingPage.value - 1) * payoutTrackingPageSize.value;
  return payoutTrackingFilteredData.value.slice(start, start + payoutTrackingPageSize.value);
});
watch([payoutTrackingKeyword, payoutSortOption], () => { payoutTrackingPage.value = 1; });

// Methods
const fetchOrders = async () => {
    try {
        loading.value = true;
        const params = {
            ...filters.value,
            page: currentPage.value,
            per_page: perPage.value
        };
        const url = `${apiBase}/orders?` + new URLSearchParams(params).toString();
        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('access_token')}`
            }
        });
        const data = await response.json();
        orders.value = data.data || [];
        totalItems.value = data.meta?.total || 0;
        totalPages.value = data.meta?.last_page || 1;
    } catch (error) {
        console.error('Error fetching orders:', error);
    } finally {
        loading.value = false;
    }
};

const fetchPaymentMethods = async () => {
  paymentLoading.value = true;
  try {
    const response = await fetch(`${apiBase}/payment-methods`);
    const data = await response.json();
    paymentMethods.value = data.data.filter(m => m.status === 'active');
  } catch (e) {
    paymentMethods.value = [];
  } finally {
    paymentLoading.value = false;
  }
};

const resetFilters = () => {
    filters.value = {
        status: '',
        from_date: '',
        to_date: '',
        order_id: ''
    };
    currentPage.value = 1;
    fetchOrders();
};

const showOrderDetails = (order) => {
    selectedOrder.value = order;
};

const updateOrderStatus = (order) => {
    orderToUpdate.value = order;
    newPayoutStatus.value = order.payout_status;
    showUpdateModal.value = true;
    activeDropdown.value = null;
};

const getAvailableStatuses = (currentStatus) => {
    const transitions = {
        pending: ['processing', 'cancelled'],
        processing: ['shipped', 'cancelled'],
        shipped: ['delivered', 'cancelled'],
        delivered: [],
        cancelled: []
    };
    return transitions[currentStatus] || [];
};

const confirmUpdatePayoutStatus = async () => {
  if (!orderToUpdate.value) return;
  // Chỉ cho phép cập nhật payout nếu đơn hàng đã giao
  if (orderToUpdate.value.status !== 'delivered') {
    showNotification('Chỉ có thể cập nhật payout cho đơn hàng đã giao thành công!', false);
    return;
  }
  if (!newPayoutStatus.value) {
    showNotification('Vui lòng chọn trạng thái payout mới', false);
    return;
  }
  if (!orderToUpdate.value.payout_id) {
    showNotification('Không tìm thấy payout_id cho đơn hàng này!', false);
    return;
  }
  try {
    loading.value = true;
    const token = localStorage.getItem('access_token');
    const body = { status: newPayoutStatus.value };
    if (newPayoutStatus.value === 'completed') {
      body.transferred_at = new Date().toISOString();
    }
    const response = await fetch(`${apiBase}/payouts/${orderToUpdate.value.payout_id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(body)
    });
    if (response.ok) {
      showNotification('Cập nhật trạng thái payout thành công', true);
      await fetchOrders();
      showUpdateModal.value = false;
      orderToUpdate.value = null;
      newPayoutStatus.value = '';
    } else {
      const data = await response.json();
      showNotification(data.message || 'Có lỗi xảy ra khi cập nhật trạng thái payout', false);
    }
  } catch (error) {
    console.error('Error updating payout status:', error);
    showNotification('Có lỗi xảy ra khi cập nhật trạng thái payout', false);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
    fetchOrders();
};

const getStatusText = (status) => {
    return statusMap[status]?.text || status;
};

const getStatusClass = (status) => {
    return statusMap[status]?.class || 'bg-gray-100 text-gray-800';
};

const handleImageError = (e) => {
    e.target.src = 'https://via.placeholder.com/150?text=No+Image';
};

const getPaymentMethodText = (method) => {
  return paymentMethodMap[method] || method || 'Không xác định';
};

const showNotification = (message, success = true) => {
    if (notification.value.timeout) {
        clearTimeout(notification.value.timeout);
    }
    notification.value = {
        show: true,
        message,
        success,
        timeout: setTimeout(() => {
            notification.value.show = false;
        }, 5000)
    };
};

const closeNotification = () => {
    notification.value.show = false;
    if (notification.value.timeout) {
        clearTimeout(notification.value.timeout);
    }
};

const deleteOrder = async (orderId) => {
    if (!confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) return;
    try {
        loading.value = true;
        const token = localStorage.getItem('access_token');
        const response = await fetch(`${apiBase}/orders/${orderId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        if (response.ok) {
            showNotification('Đã xóa đơn hàng thành công', true);
            await fetchOrders();
        } else {
            const data = await response.json();
            showNotification(data.message || 'Có lỗi xảy ra khi xóa đơn hàng', false);
        }
    } catch (error) {
        console.error('Error deleting order:', error);
        showNotification('Có lỗi xảy ra khi xóa đơn hàng', false);
    } finally {
        loading.value = false;
    }
};

const toggleDropdown = (orderId) => {
    if (activeDropdown.value === orderId) {
        activeDropdown.value = null;
    } else {
        activeDropdown.value = orderId;
    }
};

const closeDropdowns = (event) => {
    if (!event.target.closest('.relative')) {
        activeDropdown.value = null;
    }
};

// Computed
const availableStatuses = computed(() => {
    if (!orderToUpdate.value) return [];
    return getAvailableStatuses(orderToUpdate.value.status).map(status => ({
        value: status,
        label: getStatusText(status)
    }));
});

// Lifecycle
onMounted(() => {
    fetchOrders();
    fetchPaymentMethods(); // <-- gọi khi mount
    document.addEventListener('click', closeDropdowns);
    fetchPayoutData();
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns);
});

// Add new method to handle modal close
const closeUpdateModal = () => {
    showUpdateModal.value = false;
    orderToUpdate.value = null;
    newPayoutStatus.value = '';
};

const payoutStatusText = (status) => {
    const statusText = {
        pending: 'Chờ xử lý',
        completed: 'Đã chuyển khoản',
        failed: 'Thất bại'
    };
    return statusText[status] || status;
};

function formatPrice(price) {
  if (!price) return '0 đ';
  if (typeof price === 'string' && price.includes('đ')) return price;
  return Number(price).toLocaleString('vi-VN') + ' đ';
}

async function fetchPayoutData() {
  payoutLoading.value = true;
  payoutError.value = '';
  try {
    let token = null;
    if (process.client) {
      token = localStorage.getItem('access_token');
    }
    const res = await fetch(`${apiBase}/payout/list-approved`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    });
    if (!res.ok) throw new Error('Không lấy được dữ liệu payout')
    const resData = await res.json()
    payoutData.value = Array.isArray(resData) ? resData : (resData.data || [])
    payoutFilteredData.value = payoutData.value.filter(item => item.status === 'completed');
  } catch (e) {
    payoutError.value = 'Không thể tải dữ liệu payout!';
    payoutData.value = [];
    payoutFilteredData.value = [];
  } finally {
    payoutLoading.value = false;
  }
}

function getTrackingCode(orderId) {
  const order = orders.value.find(o => o.id === orderId);
  return order && order.shipping && order.shipping.tracking_code ? order.shipping.tracking_code : '-';
}

function formatNumber(number) {
  if (!number) return '0 đ';
  if (typeof number === 'string' && number.includes('đ')) return number;
  return Number(number).toLocaleString('vi-VN') + ' đ';
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN');
}

function payoutStatusClass(status) {
  const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800'
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
}

function payoutStatusLabel(status) {
  const statusLabels = {
    pending: 'Chờ xử lý',
    completed: 'Đã chuyển khoản',
    failed: 'Thất bại'
  };
  return statusLabels[status] || status;
}
</script>

<style scoped>
.object-cover {
    object-fit: cover;
}
</style>