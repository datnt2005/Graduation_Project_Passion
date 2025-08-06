<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Báo cáo đánh giá sản phẩm của bạn</h1>
      </div>

      <!-- Bộ lọc -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 mb-4 rounded">
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Sắp xếp:</label>
          <select v-model="sortOrder" @change="fetchReports(1)" class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8">
            <option value="desc">Mới nhất</option>
            <option value="asc">Cũ nhất</option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Trạng thái:</label>
          <select v-model="filterStatus" @change="fetchReports(1)"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8">
            <option value="">Tất cả</option>
            <option value="pending">Chờ xử lý</option>
            <option value="resolved">Đã ẩn</option>
            <option value="dismissed">Đã bỏ qua</option>
          </select>
        </div>
      </div>

      <!-- Loading indicator -->
      <div v-if="loading" class="text-center py-4">
        <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </div>

      <!-- Bảng dữ liệu -->
      <table v-else class="min-w-full border-collapse border border-gray-300 text-sm">
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
          <tr v-for="(item, index) in reports" :key="item.report_id" :class="{ 'bg-gray-50': index % 2 === 0 }">
            <td class="border px-3 py-2">{{ (currentPage - 1) * perPage + index + 1 }}</td>
            <td class="border px-3 py-2">{{ item.review.product_name }}</td>
            <td class="border px-3 py-2">{{ item.review.user_name }}</td>
            <td class="border px-3 py-2 truncate max-w-[200px]">{{ item.review.content }}</td>
            <td class="border px-3 py-2">{{ reasonLabel(item.reason) }}</td>
            <td class="border px-3 py-2">{{ item.reporter }}</td>
            <td class="border px-3 py-2">
              <span :class="badgeClass(item.status)">{{ statusText(item.status) }}</span>
            </td>
            <td class="border px-3 py-2">{{ formatDate(item.reported_at) }}</td>
            <td class="border px-3 py-2">
              <button @click="viewReport(item.report_id)" class="flex items-center gap-1 text-blue-600 hover:underline">
                <Eye class="w-4 h-4" /> Xem
              </button>
            </td>
          </tr>
          <tr v-if="reports.length === 0">
            <td colspan="9" class="text-center py-4 text-gray-500">Không có báo cáo nào</td>
          </tr>
        </tbody>
      </table>

      <!-- Phân trang -->
      <Pagination v-if="!loading" :currentPage="currentPage" :lastPage="lastPage" @change="fetchReports" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRuntimeConfig, useRouter } from '#app';
import { useToast } from '@/composables/useToast';
import { Eye } from 'lucide-vue-next';
import Pagination from '~/components/Pagination.vue';

const { toast } = useToast();
definePageMeta({ layout: 'default-seller' });

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl || 'http://localhost:8000/api';
const router = useRouter();

const reports = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const total = ref(0);
const sortOrder = ref('desc');
const filterStatus = ref('');

const reasons = [
  { value: 'offensive', label: 'Thô tục' },
  { value: 'image', label: 'Hình ảnh phản cảm' },
  { value: 'duplicate', label: 'Trùng lặp' },
  { value: 'personal', label: 'Thông tin cá nhân' },
  { value: 'ads', label: 'Quảng cáo' },
  { value: 'wrong', label: 'Thông tin sai lệch' },
  { value: 'other', label: 'Khác' },
];

const reasonLabel = code => reasons.find(r => r.value === code)?.label || code;

const formatDate = d => new Date(d).toLocaleString('vi-VN');

const statusText = s =>
  s === 'pending' ? 'Chờ xử lý' : s === 'resolved' ? 'Đã ẩn' : 'Đã bỏ qua';

const badgeClass = s =>
  s === 'pending'
    ? 'px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded'
    : s === 'resolved'
      ? 'px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded'
      : 'px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded';

const fetchReports = async (page = 1) => {
  try {
    loading.value = true;
    currentPage.value = page;

    const queryParams = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.value.toString(),
      sort_order: sortOrder.value,
      ...(filterStatus.value && { status: filterStatus.value }),
    });

    const res = await axios.get(`${apiBase}/seller/reports/reviews?${queryParams.toString()}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` },
    });

    if (!res.data.success || !Array.isArray(res.data.data)) {
      throw new Error(res.data.message || 'Dữ liệu báo cáo không hợp lệ');
    }

    reports.value = res.data.data;
    lastPage.value = res.data.last_page || 1;
    total.value = res.data.total || res.data.data.length;
    currentPage.value = res.data.current_page || page;

    if (!reports.value.length) {
      toast('info', 'Không có báo cáo nào');
    }
  } catch (err) {
    console.error('Lỗi khi tải báo cáo:', err);
    toast('error', 'Lỗi khi tải báo cáo: ' + err.message);
    reports.value = [];
    lastPage.value = 1;
    total.value = 0;
  } finally {
    loading.value = false;
  }
};

const viewReport = id => router.push(`/seller/reports/reviews/view/${id}`);

onMounted(() => fetchReports());
</script>

<style scoped>
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>