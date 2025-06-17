<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý đơn hàng</h1>
        <!-- Nếu muốn thêm nút tạo mới, thêm ở đây -->
      </div>

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
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Mã đơn</th>
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
            <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700">#{{ order.id }}</td>
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
                      @click="showOrderDetails(order); activeDropdown = null"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      Xem chi tiết
                    </button>
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

    <!-- Các modal chi tiết, cập nhật trạng thái giữ nguyên -->
    <!-- Modal xem chi tiết đơn hàng -->
    <div v-if="selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6 relative">
        <button @click="selectedOrder = null" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <h2 class="text-lg font-semibold mb-4">Chi tiết đơn hàng #{{ selectedOrder.id }}</h2>
        <div>
          <div><b>Khách hàng:</b> {{ selectedOrder.user.name }} ({{ selectedOrder.user.email }})</div>
          <div><b>Trạng thái:</b> {{ getStatusText(selectedOrder.status) }}</div>
          <div><b>Tổng tiền:</b> {{ selectedOrder.final_price }}</div>
          <div><b>Ngày tạo:</b> {{ selectedOrder.created_at }}</div>
          <div class="mt-2">
            <b>Sản phẩm:</b>
            <ul class="list-disc ml-6">
              <li v-for="item in selectedOrder.order_items" :key="item.id">
                {{ item.product.name }} x {{ item.quantity }} ({{ item.price }})
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal cập nhật trạng thái -->
    <div v-if="showUpdateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
        <button @click="closeUpdateModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <h2 class="text-lg font-semibold mb-4">Cập nhật trạng thái đơn hàng</h2>
        <div class="mb-4">
          <div><b>Đơn hàng #{{ orderToUpdate?.id }}</b></div>
          <div>Trạng thái hiện tại: <span :class="getStatusClass(orderToUpdate?.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">{{ getStatusText(orderToUpdate?.status) }}</span></div>
        </div>
        <div class="mb-4">
          <label class="block mb-1">Chọn trạng thái mới:</label>
          <select v-model="newStatus" class="w-full border rounded px-3 py-2">
            <option v-for="status in availableStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-2">
          <button @click="closeUpdateModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Hủy</button>
          <button @click="confirmUpdateStatus" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" :disabled="loading">Cập nhật</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import axios from 'axios';

definePageMeta({
    layout: 'default-admin'
});

// State
const orders = ref([]);
const selectedOrder = ref(null);
const showUpdateModal = ref(false);
const newStatus = ref('');
const orderToUpdate = ref(null);
const loading = ref(false);
const currentPage = ref(1);
const perPage = ref(10);
const totalItems = ref(0);
const totalPages = ref(1);
const activeDropdown = ref(null);

const filters = ref({
    status: '',
    from_date: '',
    to_date: '',
    order_id: '',
});

const paymentMethods = ref([]);
const paymentLoading = ref(false);

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

// Methods
const fetchOrders = async () => {
    try {
        loading.value = true;
        const params = {
            ...filters.value,
            page: currentPage.value,
            per_page: perPage.value
        };
        
        const response = await axios.get('http://localhost:8000/api/orders', { params });
        
        // Format the orders data if needed
        orders.value = response.data.data.map(order => ({
            ...order,
            order_items: order.order_items.map(item => ({
                ...item,
                product: {
                    ...item.product,
                    thumbnail: item.product.thumbnail || 'https://via.placeholder.com/150?text=No+Image'
                }
            }))
        }));

        totalItems.value = response.data.meta.total;
        totalPages.value = response.data.meta.last_page;
    } catch (error) {
        console.error('Error fetching orders:', error);
        // Handle error (show notification, etc.)
    } finally {
        loading.value = false;
    }
};

const fetchPaymentMethods = async () => {
  paymentLoading.value = true;
  try {
    const response = await axios.get('http://localhost:8000/api/payment-methods');
    paymentMethods.value = response.data.data.filter(m => m.status === 'active');
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
    newStatus.value = order.status;
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

const confirmUpdateStatus = async () => {
    if (!orderToUpdate.value || !newStatus.value) {
        showNotification('Vui lòng chọn trạng thái mới', false);
        return;
    }

    try {
        loading.value = true;
        const response = await axios.put(`http://localhost:8000/api/orders/${orderToUpdate.value.id}`, {
            status: newStatus.value
        });

        if (response.data.success) {
            showNotification(response.data.status_message || 'Cập nhật trạng thái thành công', true);
            await fetchOrders();
            showUpdateModal.value = false;
            orderToUpdate.value = null;
            newStatus.value = '';
        } else {
            showNotification(response.data.message || 'Có lỗi xảy ra khi cập nhật trạng thái', false);
        }
    } catch (error) {
        console.error('Error updating order status:', error);
        const errorMessage = error.response?.data?.message 
            || error.response?.data?.error 
            || error.message 
            || 'Có lỗi xảy ra khi cập nhật trạng thái';
        showNotification(errorMessage, false);
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
        await axios.delete(`http://localhost:8000/api/orders/${orderId}`);
        showNotification('Đã xóa đơn hàng thành công', true);
        await fetchOrders();
    } catch (error) {
        console.error('Error deleting order:', error);
        showNotification(
            error.response?.data?.message || 'Có lỗi xảy ra khi xóa đơn hàng',
            false
        );
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
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns);
});

// Add new method to handle modal close
const closeUpdateModal = () => {
    showUpdateModal.value = false;
    orderToUpdate.value = null;
    newStatus.value = '';
};
</script>

<style scoped>
.object-cover {
    object-fit: cover;
}
</style>