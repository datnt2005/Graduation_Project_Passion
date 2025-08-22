<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Báo cáo sản phẩm</h1>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 mb-4 rounded">
        <div class="flex items-center gap-2">
          <button @click="filterStatus = ''; applyFilters()" :class="[
            'text-blue-600 hover:underline',
            filterStatus === '' ? 'font-semibold' : ''
          ]">
            Tất cả
          </button>
          <span>({{ totalReports }})</span>
          <button @click="filterStatus = 'pending'; applyFilters()" :class="[
            'text-blue-600 hover:underline',
            filterStatus === 'pending' ? 'font-semibold' : ''
          ]">
            Chờ xử lý
          </button>
          <span>({{ pendingReports }})</span>
          <button @click="filterStatus = 'resolved'; applyFilters()" :class="[
            'text-blue-600 hover:underline',
            filterStatus === 'resolved' ? 'font-semibold' : ''
          ]">
            Đã ẩn
          </button>
          <span>({{ resolvedReports }})</span>
          <button @click="filterStatus = 'dismissed'; applyFilters()" :class="[
            'text-blue-600 hover:underline',
            filterStatus === 'dismissed' ? 'font-semibold' : ''
          ]">
            Đã bỏ qua
          </button>
          <span>({{ dismissedReports }})</span>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
          <!-- Sort by Date -->
          <select v-model="sortOrder" @change="applyFilters"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="desc">Mới nhất</option>
            <option value="asc">Cũ nhất</option>
          </select>
          <!-- Seller Filter -->
          <select v-model="filterSeller" @change="applyFilters"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả cửa hàng</option>
            <option v-for="seller in sellers" :key="seller" :value="seller">
              {{ seller }}
            </option>
          </select>
          <!-- Reason Filter -->
          <select v-model="filterReason" @change="applyFilters"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả lý do</option>
            <option v-for="reason in reasons" :key="reason.value" :value="reason.value">
              {{ reason.label }}
            </option>
          </select>
          <!-- Search -->
          <div class="relative">
            <input v-model="searchQuery" @input="applyFilters" type="text" placeholder="Tìm kiếm sản phẩm..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64" />
            <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Action Bar -->
      <div
        class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-t border-gray-300">
        <select v-model="selectedAction"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Hành động hàng loạt</option>
          <option value="resolved">Chấp nhận</option>
          <option value="dismissed">Từ chối</option>
          <option value="delete">Xóa</option>
        </select>
        <button @click="applyBulkAction" :disabled="!selectedAction || selectedReports.length === 0 || loading" :class="[
          'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
          (!selectedAction || selectedReports.length === 0 || loading)
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-blue-600 text-white hover:bg-blue-700'
        ]">
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <div class="ml-auto text-sm text-gray-600">
          {{ selectedReports.length }} báo cáo được chọn / {{ filteredReports.length }} báo cáo
        </div>
      </div>

      <!-- Data Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border px-3 py-2 w-10">
              <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
            </th>
            <th class="border px-3 py-2">Sản phẩm</th>
            <th class="border px-3 py-2">Lý do</th>
            <th class="border px-3 py-2">Người báo cáo</th>
            <th class="border px-3 py-2">Cửa hàng</th>
            <th class="border px-3 py-2">Trạng thái</th>
            <th class="border px-3 py-2">Ngày</th>
            <th class="border px-3 py-2">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedReports" :key="item.report_id"
            :class="{ 'bg-gray-50': index % 2 === 0 }">
            <td class="border px-3 py-2">
              <input type="checkbox" v-model="selectedReports" :value="item.report_id" />
            </td>
            <td class="border px-3 py-2 font-semibold text-blue-700 hover:underline cursor-pointer"
              @click="viewProduct(item.product.id)">
              {{ truncateText(item.product.name, 30) }}
            </td>
            <td class="border px-3 py-2">{{ reasonLabel(item.reason) }}</td>
            <td class="border px-3 py-2">{{ item.reporter }}</td>
            <td class="border px-3 py-2">{{ item.product.seller_name || '–' }}</td>
            <td class="border px-3 py-2"><span :class="badgeClass(item.status)">{{ statusText(item.status) }}</span>
            </td>
            <td class="border px-3 py-2">{{ formatDate(item.reported_at) }}</td>
            <td class="border px-3 py-2 relative">
              <button @click.stop="toggleDropdown($event, item.report_id)" class="p-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <Pagination :currentPage="currentPage" :lastPage="lastPage" @change="fetchReports" />

      <!-- Dropdown Menu -->
      <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95">
          <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
            <div class="absolute bg-white rounded shadow-lg ring-1 ring-black w-40 z-50" :style="dropdownPosition"
              @click.stop>
              <div class="py-1">
                <button @click="openReportDetails(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <Eye class="w-4 h-4 mr-2" /> Xem chi tiết
                </button>
                <button @click="updateStatus(activeDropdown, 'resolved'); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                  <Check class="w-4 h-4 mr-2" /> Chấp nhận
                </button>
                <button @click="updateStatus(activeDropdown, 'dismissed'); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                  <X class="w-4 h-4 mr-2" /> Từ chối
                </button>
                <button @click="deleteReport(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                  <X class="w-4 h-4 mr-2" /> Xóa
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Report Details Popup -->
      <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
          enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100" leave-to-class="opacity-0">
          <div v-if="showReportDetails"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Chi tiết báo cáo sản phẩm</h2>
                <button @click="closeReportDetails" class="text-gray-500 hover:text-gray-700">
                  <X class="w-6 h-6" />
                </button>
              </div>
              <div v-if="selectedReport" class="space-y-4">
                <div>
                  <h3 class="text-sm font-medium text-gray-600">Sản phẩm</h3>
                  <p class="text-gray-800 font-semibold text-blue-700 hover:underline cursor-pointer"
                    @click="viewProduct(selectedReport.product.id)">
                    {{ selectedReport.product.name }}
                  </p>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-gray-600">Cửa hàng</h3>
                  <p class="text-gray-800">{{ selectedReport.product.seller?.store_name || '–' }}</p>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-gray-600">Lý do báo cáo</h3>
                  <p class="text-gray-800">{{ reasonLabel(selectedReport.reason) }}</p>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-gray-600">Người báo cáo</h3>
                  <p class="text-gray-800">{{ selectedReport.reporter?.name || '–' }}</p>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-gray-600">Trạng thái</h3>
                  <span :class="badgeClass(selectedReport.status)">{{ statusText(selectedReport.status) }}</span>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-gray-600">Ngày báo cáo</h3>
                  <p class="text-gray-800">{{ formatDate(selectedReport.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { Eye, Check, X } from 'lucide-vue-next';
import { secureFetch } from '@/utils/secureFetch';
import { useNotification } from '~/composables/useNotification';
import Pagination from '~/components/Pagination.vue';
import Swal from 'sweetalert2';

// Initialize notification and router
const {showMessage}  = useNotification();
const router = useRouter();

// Define page metadata
definePageMeta({ layout: 'default-admin' });

// Runtime configuration
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

// Reactive state
const reports = ref([]);
const allReports = ref([]);
const selectedReports = ref([]);
const selectAll = ref(false);
const sortOrder = ref('desc');
const filterStatus = ref('');
const filterSeller = ref('');
const filterReason = ref('');
const searchQuery = ref('');
const sellers = ref([]);
const reasons = ref([
  { value: 'not_matching_description', label: 'Sản phẩm không đúng với mô tả' },
  { value: 'fraud_signs', label: 'Sản phẩm có dấu hiệu lừa đảo' },
  { value: 'counterfeit', label: 'Hàng giả, hàng nhái' },
  { value: 'unknown_origin', label: 'Sản phẩm không rõ nguồn gốc, xuất xứ' },
  { value: 'unclear_images', label: 'Hình ảnh sản phẩm không rõ ràng' },
  { value: 'offensive_content', label: 'Sản phẩm có hình ảnh hoặc nội dung phản cảm' },
  { value: 'mismatched_name_image', label: 'Tên sản phẩm không phù hợp với hình ảnh' },
  { value: 'prohibited_product', label: 'Sản phẩm bị cấm buôn bán (như động vật hoang dã, sản phẩm 18+,...)' },
  { value: 'other', label: 'Lý do khác' },
]);
const currentPage = ref(1);
const perPage = 10;
const lastPage = ref(1);
const totalReports = ref(0);
const pendingReports = ref(0);
const resolvedReports = ref(0);
const dismissedReports = ref(0);
const activeDropdown = ref(null);
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' });
const showReportDetails = ref(false);
const selectedReport = ref(null);
const loading = ref(false);
const selectedAction = ref('');

// Hàm xóa một báo cáo riêng lẻ
const deleteReport = async (id) => {
  const confirm = await Swal.fire({
    title: 'Xác nhận xóa báo cáo?',
    text: 'Hành động này không thể hoàn tác!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy',
    reverseButtons: true,
  });

  if (!confirm.isConfirmed) return;

  try {
    const response = await secureFetch(`${apiBase}/admin/reports/products/${id}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
    }, ['admin']);

    const isSuccess = response?.success || [200, 201, 204, 202].includes(response?.status);

    if (!isSuccess) {
      const errorMessage = response?.message || 'Lỗi không xác định từ server';
      throw new Error(errorMessage);
    }

    allReports.value = allReports.value.filter(r => r.report_id !== id);
    await applyFilters();

    if (showReportDetails.value && selectedReport.value?.report_id === id) {
      closeReportDetails();
    }

    showMessage('Đã xóa báo cáo thành công', 'success');
  } catch (err) {
    console.error('Error in deleteReport:', err);
    showMessage(`Không thể xóa báo cáo: ${err.message}`, 'error');
  }
};

// Hàm xử lý hành động xóa hàng loạt
const applyBulkDelete = async () => {
  if (selectedReports.value.length === 0) {
    showMessage('Vui lòng chọn ít nhất một báo cáo', 'error');
    return;
  }

  const confirm = await Swal.fire({
    title: `Xác nhận xóa ${selectedReports.value.length} báo cáo?`,
    text: 'Hành động này không thể hoàn tác!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy',
    reverseButtons: true,
  });

  if (!confirm.isConfirmed) return;

  try {
    loading.value = true;
    const results = await Promise.all(
      selectedReports.value.map(id =>
        secureFetch(`${apiBase}/admin/reports/products/${id}`, {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
        }, ['admin']).then(response => ({
          id,
          success: response?.success || [200, 201, 204].includes(response?.status),
          message: response?.message || 'Lỗi không xác định',
        }))
      )
    );

    const failed = results.filter(r => !r.success);
    if (failed.length > 0) {
      const errorMessages = failed.map(r => `Báo cáo ID ${r.id}: ${r.message}`).join('; ');
      showMessage(`Có lỗi khi xóa một số báo cáo: ${errorMessages}`, 'error');
      return;
    }

    allReports.value = allReports.value.filter(r => !selectedReports.value.includes(r.report_id));
    await applyFilters();

    if (showReportDetails.value && selectedReports.value.includes(selectedReport.value?.report_id)) {
      closeReportDetails();
    }

    showMessage(`Đã xóa ${selectedReports.value.length} báo cáo thành công`, 'success');
    selectedReports.value = [];
    selectAll.value = false;
    selectedAction.value = '';
  } catch (err) {
    console.error('Error in applyBulkDelete:', err);
    showMessage(`Không thể xóa báo cáo: ${err.message}`, 'error');
  } finally {
    loading.value = false;
  }
};

const reasonLabel = (code) => {
  const reason = reasons.value.find(r => r.value === code);
  return reason ? reason.label : code || 'Lý do không xác định';
};

const formatDate = (d) => {
  if (!d) return '–';
  return new Date(d).toLocaleString('vi-VN', {
    dateStyle: 'short',
    timeStyle: 'short',
  });
};

const statusText = (s) => {
  return s === 'pending' ? 'Chờ xử lý' : s === 'resolved' ? 'Chấp nhận' : 'Từ chối';
};

const badgeClass = (s) => {
  return s === 'pending'
    ? 'px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded'
    : s === 'resolved'
      ? 'px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded'
      : 'px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded';
};

const truncateText = (text, maxLength) => {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text;
};


const fetchReports = async (page = 1) => {
  try {
    loading.value = true;
    currentPage.value = page;
    const queryParams = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.toString(),
      ...(searchQuery.value && { search: searchQuery.value }),
      ...(filterSeller.value && { seller_name: filterSeller.value }),
      ...(filterReason.value && { reason: filterReason.value }),
      ...(filterStatus.value && { status: filterStatus.value }),
    });
    const response = await secureFetch(`${apiBase}/admin/reports/products?${queryParams}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    }, ['admin']);

    console.log('API Response:', response); // Debug log

    if (!response.success || !Array.isArray(response.data)) {
      throw new Error(`Expected response.data to be an array, got ${typeof response.data}`);
    }

    allReports.value = response.data;
    lastPage.value = response.last_page || 1;
    totalReports.value = response.total || response.data.length;
    pendingReports.value = response.data.filter(r => r.status === 'pending').length;
    resolvedReports.value = response.data.filter(r => r.status === 'resolved').length;
    dismissedReports.value = response.data.filter(r => r.status === 'dismissed').length;

    sellers.value = [...new Set(
      response.data
        .map(report => report?.product?.seller_name)
        .filter(name => name && typeof name === 'string')
    )];

    const customReasons = [...new Set(
      response.data
        .map(report => report?.reason)
        .filter(reason => reason && !reasons.value.some(r => r.value === reason))
    )].map(reason => ({
      value: reason,
      label: reason
    }));
    reasons.value = [
      ...reasons.value.filter(r => !customReasons.some(cr => cr.value === r.value)), // Avoid duplicates
      ...customReasons
    ];

    await nextTick();
    applyFilters();
  } catch (err) {
    console.error('Error in fetchReports:', err);
    allReports.value = [];
    lastPage.value = 1;
    totalReports.value = 0;
    pendingReports.value = 0;
    resolvedReports.value = 0;
    dismissedReports.value = 0;
    showMessage(`Lỗi khi tải báo cáo sản phẩm: ${err.message || 'Lỗi hệ thống'}`, 'error');
  } finally {
    loading.value = false;
  }
};

const updateStatus = async (id, status) => {
  if (!['resolved', 'dismissed'].includes(status)) {
    showMessage('Trạng thái không hợp lệ. Chỉ chấp nhận "resolved" hoặc "dismissed".', 'error');
    return;
  }

  const label = status === 'resolved' ? 'chấp nhận' : 'từ chối';

  const confirm = await Swal.fire({
    title: `Xác nhận ${label} báo cáo?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy',
    reverseButtons: true,
  });

  if (!confirm.isConfirmed) return;

  try {
    const data = await secureFetch(`${apiBase}/admin/reports/${id}/status`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status }),
    }, ['admin']);

    const isSuccess =
      data?.success ||
      [200, 201, 204, 202].includes(data?.status) ||
      (data?.message?.toLowerCase().includes('thành công'));

    if (!isSuccess) {
      const errorMessage =
        data?.message ||
        data?.errors?.status?.[0] ||
        'Lỗi không xác định từ server';
      throw new Error(errorMessage);
    }

    const reportIndex = allReports.value.findIndex(r => r.report_id === id);
    if (reportIndex !== -1) {
      allReports.value = [
        ...allReports.value.slice(0, reportIndex),
        { ...allReports.value[reportIndex], status },
        ...allReports.value.slice(reportIndex + 1),
      ];
    } else {
      console.warn(`Không tìm thấy báo cáo có ID ${id} trong allReports`);
    }

    pendingReports.value = allReports.value.filter(r => r.status === 'pending').length;
    resolvedReports.value = allReports.value.filter(r => r.status === 'resolved').length;
    dismissedReports.value = allReports.value.filter(r => r.status === 'dismissed').length;

    await nextTick();
    applyFilters();

    if (showReportDetails.value && selectedReport.value?.report_id === id) {
      selectedReport.value = { ...selectedReport.value, status };
    }

    showMessage(`Đã ${label} báo cáo thành công`, 'success');

    if (showReportDetails.value && selectedReport.value?.report_id === id) {
      closeReportDetails();
    }
  } catch (err) {
    console.error('Error in updateStatus:', err);
    showMessage(`Không thể ${label} báo cáo: ${err.message}`, 'error');
  }
};

const applyBulkAction = async () => {
  if (!selectedAction.value || selectedReports.value.length === 0) {
    showMessage('Vui lòng chọn hành động và ít nhất một báo cáo', 'error');
    return;
  }

  if (!['resolved', 'dismissed', 'delete'].includes(selectedAction.value)) {
    showMessage('Hành động không hợp lệ.', 'error');
    return;
  }

  if (selectedAction.value === 'delete') {
    await applyBulkDelete();
    return;
  }

  const label = selectedAction.value === 'resolved' ? 'chấp nhận' : 'từ chối';
  const confirm = await Swal.fire({
    title: `Xác nhận ${label} ${selectedReports.value.length} báo cáo?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy',
    reverseButtons: true,
  });

  if (!confirm.isConfirmed) return;

  try {
    loading.value = true;
    const apiStatus = selectedAction.value;
    const data = await Promise.all(
      selectedReports.value.map(id =>
        secureFetch(`${apiBase}/admin/reports/${id}/status`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: apiStatus }),
        }, ['admin']).then(async data => {
          return {
            status: data?.success || [200, 201, 204, 202].includes(data?.status) || (data?.message?.toLowerCase().includes('thành công')) ? 'success' : 'error',
            data: data,
            id
          };
        })
      )
    );

    const failedResponses = data.filter(
      res => !(res.status === 'success')
    );
    if (failedResponses.length > 0) {
      const errorMessages = failedResponses
        .map(res => res.data?.message || res.data?.errors?.status?.[0] || `Lỗi không xác định cho báo cáo ID ${res.id}`)
        .join('; ');
      console.error('Bulk Action Errors:', failedResponses);
      showMessage(`Có lỗi khi ${label} một số báo cáo: ${errorMessages}`, 'error');
      return;
    }

    allReports.value = allReports.value.map(report =>
      selectedReports.value.includes(report.report_id)
        ? { ...report, status: apiStatus }
        : report
    );

    pendingReports.value = allReports.value.filter(r => r.status === 'pending').length;
    resolvedReports.value = allReports.value.filter(r => r.status === 'resolved').length;
    dismissedReports.value = allReports.value.filter(r => r.status === 'dismissed').length;

    await nextTick();
    applyFilters();

    showMessage(`Đã ${label} ${selectedReports.value.length} báo cáo thành công`, 'success');

    selectedReports.value = [];
    selectAll.value = false;
    selectedAction.value = '';

    if (showReportDetails.value && selectedReports.value.includes(selectedReport.value?.report_id)) {
      closeReportDetails();
    }
  } catch (err) {
    console.error('Error in applyBulkAction:', err);
    showMessage(`Không thể ${label}: ${err.message}`, 'error');
  } finally {
    loading.value = false;
  }
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedReports.value = paginatedReports.value.map(r => r.report_id);
  } else {
    selectedReports.value = [];
  }
};

const viewProduct = (id) => {
  if (!id) {
    showMessage('ID sản phẩm không hợp lệ', 'error');
    return;
  }
  router.push(`/admin/products/edit-product/${id}`);
};

const toggleDropdown = (e, id) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null;
  } else {
    activeDropdown.value = id;
    nextTick(() => {
      const button = e.target.closest('button');
      if (button) {
        const rect = button.getBoundingClientRect();
        dropdownPosition.value = {
          top: `${rect.bottom + window.scrollY + 8}px`,
          left: `${rect.right + window.scrollX - 192}px`,
          width: '192px',
        };
      }
    });
  }
};

const closeDropdown = (event) => {
  if (!event || !event.target.closest('.relative') && !event.target.closest('.absolute')) {
    activeDropdown.value = null;
  }
};

const openReportDetails = async (id) => {
  try {
    const response = await secureFetch(`${apiBase}/admin/reports/products/${id}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    }, ['admin']);

    if (!response.success || !response.data) {
      throw new Error('Invalid report details data');
    }

    selectedReport.value = response.data;
    showReportDetails.value = true;
  } catch (err) {
    console.error('Error in openReportDetails:', err);
    showMessage(`Lỗi khi tải chi tiết báo cáo: ${err.message || 'Lỗi hệ thống'}`, 'error');
  }
};

const closeReportDetails = () => {
  showReportDetails.value = false;
  selectedReport.value = null;
};

// Computed Properties
const filteredReports = computed(() => reports.value);

const paginatedReports = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return filteredReports.value.slice(start, end);
});

const applyFilters = () => {
  let filtered = [...allReports.value];
  if (filterStatus.value) {
    filtered = filtered.filter(r => r.status === filterStatus.value);
  }
  if (filterSeller.value) {
    filtered = filtered.filter(r => r.product?.seller_name === filterSeller.value);
  }
  if (filterReason.value) {
    filtered = filtered.filter(r => r.reason === filterReason.value);
  }
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(r =>
      r.product?.name?.toLowerCase().includes(query) ||
      r.product?.description?.toLowerCase().includes(query) ||
      r.reporter?.toLowerCase().includes(query)
    );
  }
  filtered.sort((a, b) => {
    const da = new Date(a.reported_at);
    const db = new Date(b.reported_at);
    return sortOrder.value === 'asc' ? da - db : db - da;
  });
  reports.value = [...filtered];
};

// Lifecycle Hooks
onMounted(() => {
  fetchReports();
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
</script>
<style scoped>
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

button {
  position: relative;
  overflow: hidden;
}

button::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  pointer-events: none;
  background-image: radial-gradient(circle, #000 10%, transparent 10.01%);
  background-repeat: no-repeat;
  background-position: 50%;
  transform: scale(10, 10);
  opacity: 0;
  transition: transform 0.5s, opacity 1s;
}

button:active::after {
  transform: scale(0, 0);
  opacity: 0.2;
  transition: 0s;
}
</style>