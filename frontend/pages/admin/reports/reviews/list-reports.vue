<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý báo cáo đánh giá</h1>
      </div>

      <!-- Bộ lọc -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 mb-4 rounded">
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Sắp xếp:</label>
          <select v-model="sortOrder" @change="applyFilters"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="desc">Mới nhất</option>
            <option value="asc">Cũ nhất</option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Trạng thái:</label>
          <select v-model="filterStatus" @change="applyFilters"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả</option>
            <option value="pending">Chờ xử lý</option>
            <option value="resolved">Đã ẩn</option>
            <option value="dismissed">Đã bỏ qua</option>
          </select>
        </div>
      </div>

      <!-- Bảng dữ liệu -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border px-3 py-2">#</th>
            <th class="border px-3 py-2">Sản phẩm</th>
            <th class="border px-3 py-2">Người đánh giá</th>
            <th class="border px-3 py-2">Nội dung</th>
            <th class="border px-3 py-2">Lý do</th>
            <th class="border px-3 py-2">Người báo cáo</th>
            <th class="border px-3 py-2">Trạng thái</th>
            <th class="border px-3 py-2">Ngày</th>
            <th class="border px-3 py-2">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedReports" :key="item.report_id" :class="{ 'bg-gray-50': index % 2 === 0 }">
            <td class="border px-3 py-2">{{ (currentPage - 1) * perPage + index + 1 }}</td>
            <td class="border px-3 py-2">{{ item.review.product_name }}</td>
            <td class="border px-3 py-2">{{ item.review.user_name }}</td>
            <td class="border px-3 py-2 truncate max-w-[200px]">{{ item.review.content }}</td>
            <td class="border px-3 py-2">{{ reasonLabel(item.reason) }}</td>
            <td class="border px-3 py-2">{{ item.reporter }}</td>
            <td class="border px-3 py-2"><span :class="badgeClass(item.status)">{{ statusText(item.status) }}</span></td>
            <td class="border px-3 py-2">{{ formatDate(item.reported_at) }}</td>
            <td class="border px-3 py-2 relative">
              <button @click.stop="toggleDropdown($event, item.report_id)" class="p-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z" />
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="paginatedReports.length === 0">
            <td colspan="9" class="text-center py-4 text-gray-500">Không có báo cáo nào</td>
          </tr>
        </tbody>
      </table>

      <!-- Phân trang -->
      <Pagination :currentPage="currentPage" :lastPage="lastPage" @change="fetchReports" />

      <!-- Dropdown Menu -->
      <Teleport to="body">
        <Transition enter-active-class="transition duration-100 ease-out"
          enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-75 ease-in"
          leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
          <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
            <div class="absolute bg-white rounded shadow-lg ring-1 ring-black w-40 z-50" :style="dropdownPosition"
              @click.stop>
              <div class="py-1">
                <button @click="viewReport(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <Eye class="w-4 h-4 mr-2" /> Xem
                </button>
                <button @click="updateStatus(activeDropdown, 'resolved'); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                  <Check class="w-4 h-4 mr-2" /> Ẩn
                </button>
                <button @click="updateStatus(activeDropdown, 'dismissed'); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                  <X class="w-4 h-4 mr-2" /> Bỏ qua
                </button>
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
import axios from 'axios';
import Swal from 'sweetalert2';
import { useRuntimeConfig } from '#app';
import { useRouter } from 'vue-router';
import { Eye, Check, X } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import Pagination from '~/components/Pagination.vue';

const { toast } = useToast();
const router = useRouter();

definePageMeta({ layout: 'default-admin' });

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const reports = ref([]);
const allReports = ref([]);
const sortOrder = ref('desc');
const filterStatus = ref('');
const currentPage = ref(1);
const perPage = 10;
const lastPage = ref(1);
const totalReports = ref(0);
const activeDropdown = ref(null);
const dropdownPosition = ref({ top: '0px', left: '0px' });
const loading = ref(false);

const reasons = ref([
  { value: 'offensive', label: 'Thô tục' },
  { value: 'image', label: 'Hình ảnh phản cảm' },
  { value: 'duplicate', label: 'Trùng lặp' },
  { value: 'personal', label: 'Thông tin cá nhân' },
  { value: 'ads', label: 'Quảng cáo' },
  { value: 'wrong', label: 'Thông tin sai lệch' },
  { value: 'other', label: 'Khác' },
]);

const reasonLabel = (code) => reasons.value.find(r => r.value === code)?.label || code;

const formatDate = (d) => {
  if (!d) return '–';
  return new Date(d).toLocaleString('vi-VN', {
    dateStyle: 'short',
    timeStyle: 'short',
  });
};

const statusText = (s) => {
  return s === 'pending' ? 'Chờ xử lý' : s === 'resolved' ? 'Đã ẩn' : 'Đã bỏ qua';
};

const badgeClass = (s) => {
  return s === 'pending'
    ? 'px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded'
    : s === 'resolved'
      ? 'px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded'
      : 'px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded';
};

const fetchReports = async (page = 1) => {
  try {
    loading.value = true;
    currentPage.value = page;
    const token = localStorage.getItem('access_token');
    if (!token) {
      toast('error', 'Vui lòng đăng nhập để tải báo cáo');
      return;
    }

    const queryParams = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.toString(),
      ...(filterStatus.value && { status: filterStatus.value }),
      ...(sortOrder.value && { sort_order: sortOrder.value }),
    });

    const res = await axios.get(`${apiBase}/admin/reports/reviews?${queryParams}`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    console.log('API response:', res.data); // Debug response

    if (!res.data.success || !Array.isArray(res.data.data)) {
      throw new Error(res.data.message || 'Dữ liệu báo cáo không hợp lệ');
    }

    allReports.value = res.data.data;
    lastPage.value = res.data.last_page || 1;
    totalReports.value = res.data.total || res.data.data.length;

    await nextTick();
    applyFilters();
  } catch (err) {
    console.error('Lỗi chi tiết:', err.response?.data || err.message);
    const errorMessage =
      err.response?.status === 404
        ? 'Không tìm thấy endpoint. Vui lòng kiểm tra cấu hình server.'
        : err.response?.status === 403
          ? 'Bạn không có quyền truy cập. Vui lòng kiểm tra vai trò admin.'
          : err.response?.data?.message || 'Lỗi khi tải báo cáo';
    toast('error', errorMessage);
    allReports.value = [];
    reports.value = [];
    lastPage.value = 1;
    totalReports.value = 0;
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  let filtered = [...allReports.value];
  if (filterStatus.value) {
    filtered = filtered.filter(r => r.status === filterStatus.value);
  }
  filtered.sort((a, b) => {
    const da = new Date(a.reported_at);
    const db = new Date(b.reported_at);
    return sortOrder.value === 'asc' ? da - db : db - da;
  });
  reports.value = filtered;
};

const paginatedReports = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return reports.value.slice(start, end);
});

const updateStatus = async (id, status) => {
  if (!['resolved', 'dismissed'].includes(status)) {
    toast('error', 'Trạng thái không hợp lệ. Chỉ chấp nhận "resolved" hoặc "dismissed".');
    return;
  }

  const label = status === 'resolved' ? 'ẩn đánh giá' : 'bỏ qua báo cáo';
  const confirm = await Swal.fire({
    title: `Xác nhận ${label}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy',
    reverseButtons: true,
  });

  if (!confirm.isConfirmed) return;

  try {
    loading.value = true;
    const res = await axios.put(
      `${apiBase}/admin/reports/reviews/${id}/status`,
      { status },
      {
        headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` },
      }
    );

    if (!res.data.success) {
      throw new Error(res.data.message || 'Không thể cập nhật trạng thái');
    }

    toast('success', `Đã ${label} thành công`);
    await fetchReports(currentPage.value);
  } catch (err) {
    console.error('Lỗi chi tiết:', err.response?.data || err.message);
    toast('error', err.response?.data?.message || `Không thể ${label}`);
  } finally {
    loading.value = false;
  }
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
          left: `${rect.right + window.scrollX - 160}px`,
          width: '160px',
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

const viewReport = (id) => {
  if (!id) {
    toast('error', 'ID báo cáo không hợp lệ');
    return;
  }
  router.push(`/admin/reports/reviews/view/${id}`);
};

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