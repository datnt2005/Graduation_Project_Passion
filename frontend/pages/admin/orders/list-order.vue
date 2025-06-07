<template>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Notification Popup -->
        <div v-if="notification.show" 
             :class="[
                'fixed top-4 right-4 p-4 rounded-md shadow-lg z-50',
                notification.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
             ]">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg v-if="notification.success" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ notification.message }}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="closeNotification" class="inline-flex text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Bộ lọc đơn hàng</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                    <select v-model="filters.status" class="w-full border rounded-md p-2">
                        <option value="">Tất cả</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="shipped">Đang giao</option>
                        <option value="delivered">Đã giao</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Từ ngày</label>
                    <input type="date" v-model="filters.from_date" class="w-full border rounded-md p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Đến ngày</label>
                    <input type="date" v-model="filters.to_date" class="w-full border rounded-md p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mã đơn hàng</label>
                    <input type="text" v-model="filters.order_id" placeholder="Nhập mã đơn hàng" class="w-full border rounded-md p-2">
                </div>
            </div>
            <div class="mt-4 flex justify-end space-x-3">
                <button @click="resetFilters" class="px-4 py-2 border rounded-md hover:bg-gray-50">
                    Đặt lại
                </button>
                <button @click="fetchOrders" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Tìm kiếm
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mã đơn
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Khách hàng
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tổng tiền
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Trạng thái
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày tạo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thao tác
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ order.id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ order.user.name }}<br>
                                <span class="text-xs">{{ order.user.email }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ order.final_price }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(order.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ getStatusText(order.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ order.created_at }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium relative">
                                <div class="relative inline-block text-left">
                                    <div>
                                        <button @click="toggleDropdown(order.id)" type="button" class="inline-flex items-center text-gray-600 hover:text-gray-800">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div v-if="activeDropdown === order.id"
                                         class="fixed transform -translate-x-full mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 divide-y divide-gray-100">
                                        <div class="py-1" role="menu" aria-orientation="vertical">
                                            <!-- Chi tiết -->
                                            <button @click="showOrderDetails(order)"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition ease-in-out duration-150">
                                                <svg class="mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                                Chi tiết
                                            </button>

                                            <!-- Cập nhật -->
                                            <button v-if="order.status !== 'delivered' && order.status !== 'cancelled'"
                                                    @click="updateOrderStatus(order)"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition ease-in-out duration-150">
                                                <svg class="mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                </svg>
                                                Cập nhật
                                            </button>

                                            <!-- Xóa -->
                                            <button v-if="order.status === 'cancelled' || order.status === 'pending'"
                                                    @click="deleteOrder(order.id)"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-gray-100 transition ease-in-out duration-150">
                                                <svg class="mr-3 h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                Xóa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Hiển thị {{ (currentPage - 1) * perPage + 1 }} đến {{ Math.min(currentPage * perPage, totalItems) }} 
                        trong tổng số {{ totalItems }} đơn hàng
                    </div>
                    <div class="flex space-x-2">
                        <button @click="changePage(currentPage - 1)" 
                                :disabled="currentPage === 1"
                                class="px-3 py-1 border rounded-md disabled:opacity-50">
                            Trước
                        </button>
                        <button @click="changePage(currentPage + 1)" 
                                :disabled="currentPage === totalPages"
                                class="px-3 py-1 border rounded-md disabled:opacity-50">
                            Sau
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <div v-if="selectedOrder" 
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white"
                 style="max-height: 90vh; overflow-y: auto;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Chi tiết đơn hàng #{{ selectedOrder.id }}</h3>
                    <button @click="selectedOrder = null" 
                            class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <h4 class="font-medium mb-2">Thông tin khách hàng</h4>
                        <p>{{ selectedOrder.user.name }}</p>
                        <p>{{ selectedOrder.user.email }}</p>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Địa chỉ giao hàng</h4>
                        <p>{{ selectedOrder.address.address }}</p>
                        <p>{{ selectedOrder.address.phone }}</p>
                    </div>
                </div>

                <div class="mb-4">
                    <h4 class="font-medium mb-2">Sản phẩm</h4>
                    <div class="border rounded-md">
                        <div v-for="item in selectedOrder.order_items" :key="item.id" class="p-3 border-b last:border-b-0">
                            <div class="flex items-center">
                                <img 
                                    :src="item.product.thumbnail || 'https://via.placeholder.com/150'" 
                                    :alt="item.product.name" 
                                    class="w-16 h-16 object-cover rounded"
                                    @error="handleImageError"
                                >
                                <div class="ml-4 flex-grow">
                                    <p class="font-medium">{{ item.product.name }}</p>
                                    <p v-if="item.variant && item.variant.name" class="text-sm text-gray-500">
                                        Phiên bản: {{ item.variant.name }}
                                    </p>
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-sm text-gray-600">
                                            Số lượng: {{ item.quantity }} x {{ item.price }}
                                        </p>
                                        <p class="font-medium text-gray-900">{{ item.total }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="flex justify-between mb-2">
                        <span>Tổng tiền hàng:</span>
                        <span>{{ selectedOrder.total_price }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Giảm giá:</span>
                        <span>{{ selectedOrder.discount_price }}</span>
                    </div>
                    <div class="flex justify-between font-medium">
                        <span>Tổng thanh toán:</span>
                        <span>{{ selectedOrder.final_price }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Status Modal -->
        <div v-if="showUpdateModal" 
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
                 style="max-height: 90vh; overflow-y: auto;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Cập nhật trạng thái</h3>
                    <button @click="closeUpdateModal" 
                            class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Trạng thái hiện tại: 
                        <span :class="getStatusClass(orderToUpdate?.status)" class="px-2 py-1 rounded-full text-xs">
                            {{ getStatusText(orderToUpdate?.status) }}
                        </span>
                    </label>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái mới</label>
                    <select v-model="newStatus" 
                            class="w-full border rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option v-for="status in availableStatuses" 
                                :key="status.value" 
                                :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button @click="closeUpdateModal" 
                            class="px-4 py-2 border rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        Hủy
                    </button>
                    <button @click="confirmUpdateStatus" 
                            :disabled="loading"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ loading ? 'Đang xử lý...' : 'Cập nhật' }}
                    </button>
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

// Status mapping
const statusMap = {
    pending: { text: 'Chờ xử lý', class: 'bg-yellow-100 text-yellow-800' },
    processing: { text: 'Đang xử lý', class: 'bg-blue-100 text-blue-800' },
    shipped: { text: 'Đang giao', class: 'bg-purple-100 text-purple-800' },
    delivered: { text: 'Đã giao', class: 'bg-green-100 text-green-800' },
    cancelled: { text: 'Đã hủy', class: 'bg-red-100 text-red-800' }
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