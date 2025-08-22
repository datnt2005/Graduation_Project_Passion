<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Danh sách yêu cầu đổi/trả</h1>
      </div>

      <!-- Bộ lọc -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 mb-4 rounded">
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Sắp xếp:</label>
          <select v-model="sortOrder" @change="fetchRequests(1)"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="desc">Mới nhất</option>
            <option value="asc">Cũ nhất</option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Trạng thái:</label>
          <select v-model="filterStatus" @change="fetchRequests(1)"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả</option>
            <option value="pending">Chờ duyệt</option>
            <option value="approved">Đã duyệt</option>
            <option value="rejected">Đã từ chối</option>
          </select>
        </div>
      </div>

      <!-- Loading indicator -->
      <div v-if="isLoading" class="text-center py-4">
        <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto rounded-xl shadow border">
        <table class="min-w-full w-full bg-white text-sm">
          <thead>
            <tr class="bg-[#f5f6fa] text-gray-600 text-xs font-semibold uppercase border-b border-gray-200">
              <th class="py-3 px-4 text-left">#</th>
              <th class="py-3 px-4 text-left">Sản phẩm</th>
              <th class="py-3 px-4 text-left">Người mua</th>
              <th class="py-3 px-4 text-left">Loại</th>
              <th class="py-3 px-4 text-left">Lý do</th>
              <th class="py-3 px-4 text-center">Trạng thái</th>
              <th class="py-3 px-4 text-center">Ngày tạo</th>
              <th class="py-3 px-4 text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <!-- Danh sách thật -->
            <tr v-for="(item, idx) in filteredRequests" :key="item.id" class="border-b group hover:bg-[#f5f6fa]">
              <td class="py-3 px-4 font-semibold">{{ (pagination.current_page - 1) * perPage + idx + 1 }}</td>
              <td class="py-3 px-4">
                <div class="truncate max-w-[300px]">{{ item.order_item?.product?.name || 'N/A' }}</div>
              </td>
              <td class="py-3 px-4">{{ item.user?.name || 'N/A' }}</td>
              <td class="py-3 px-4">{{ item.type === 'return' ? 'Trả hàng' : 'Đổi hàng' }}</td>
              <td class="py-3 px-4">{{ item.reason || 'N/A' }}</td>
              <td class="py-3 px-4 text-center">
                <span class="inline-block px-3 py-1 text-xs rounded-full font-medium" :class="{
                  'bg-green-100 text-green-700': item.status === 'approved',
                  'bg-yellow-100 text-yellow-700': item.status === 'pending',
                  'bg-red-100 text-red-700': item.status === 'rejected'
                }">
                  {{ getStatusText(item.status) }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">{{ formatDate(item.created_at) }}</td>
              <td class="py-3 px-4 text-center">
                <button @click="openDetail(item)" class="text-blue-600 hover:underline text-sm font-medium">
                  Xem chi tiết
                </button>
              </td>
            </tr>
            <!-- Không có kết quả -->
            <tr v-if="!isLoading && filteredRequests.length === 0">
              <td colspan="8" class="text-center py-6 text-gray-400">Không có yêu cầu nào!</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal chi tiết -->
      <div v-if="detailModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        @click="closeDetail">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 animate-fadeIn overflow-y-auto max-h-[90vh]"
          @click.stop>
          <!-- Tiêu đề -->
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-blue-600">Chi tiết yêu cầu</h2>
            <button @click="closeDetail" class="text-gray-400 hover:text-black text-xl">✕</button>
          </div>

          <!-- THÔNG TIN SẢN PHẨM -->
          <div class="border rounded-lg p-4 mb-4 space-y-1">
            <div class="flex items-center gap-2 text-base font-semibold text-gray-700 mb-2">
              <i class="fas fa-box text-blue-500"></i> Thông tin sản phẩm
            </div>
            <p><strong>Sản phẩm:</strong> {{ currentDetail?.order_item?.product?.name || 'N/A' }}</p>
            <p><strong>Giá:</strong> <span class="text-green-600 font-semibold">{{
              formatPrice(currentDetail?.order_item?.price) }}</span></p>
            <p><strong>Số lượng:</strong> {{ currentDetail?.order_item?.quantity || 'N/A' }}</p>
          </div>

          <!-- THÔNG TIN NGƯỜI MUA -->
          <div class="border rounded-lg p-4 mb-4">
            <div class="flex items-center gap-2 text-base font-semibold text-gray-700 mb-2">
              <i class="fas fa-user text-blue-500"></i> Thông tin người mua
            </div>
            <p><strong>Tên:</strong> {{ currentDetail?.user?.name || 'N/A' }}</p>
            <p><strong>Email:</strong> <a :href="`mailto:${currentDetail?.user?.email}`"
                class="text-blue-600 hover:underline">{{ currentDetail?.user?.email || 'N/A' }}</a></p>
          </div>

          <!-- CHI TIẾT YÊU CẦU -->
          <div class="border rounded-lg p-4 mb-4 space-y-1">
            <div class="flex items-center gap-2 text-base font-semibold text-gray-700 mb-2">
              <i class="fas fa-info-circle text-blue-500"></i> Chi tiết yêu cầu
            </div>
            <p>
              <strong>Loại yêu cầu:</strong>
              <span class="inline-block px-2 py-0.5 rounded-full text-white text-xs font-semibold"
                :class="currentDetail?.type === 'return' ? 'bg-red-500' : 'bg-yellow-500'">
                {{ currentDetail?.type === 'return' ? 'Trả hàng' : 'Đổi hàng' }}
              </span>
            </p>
            <p><strong>Lý do chính:</strong> {{ currentDetail?.reason || 'N/A' }}</p>
            <p><strong>Ghi chú thêm:</strong> {{ currentDetail?.additional_reason || 'N/A' }}</p>
            <p><strong>Ngày tạo:</strong> {{ formatDate(currentDetail?.created_at) }}</p>
            <p><strong>Trạng thái:</strong>
              <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                :class="{
                  'bg-green-100 text-green-700': currentDetail?.status === 'approved',
                  'bg-yellow-100 text-yellow-700': currentDetail?.status === 'pending',
                  'bg-red-100 text-red-700': currentDetail?.status === 'rejected'
                }">
                {{ getStatusText(currentDetail?.status) }}
              </span>
            </p>
          </div>

          <!-- ẢNH MINH CHỨNG -->
          <div class="border rounded-lg p-4 mb-4">
            <div class="flex items-center gap-2 text-base font-semibold text-gray-700 mb-3">
              <i class="fas fa-camera text-blue-500"></i> Ảnh minh chứng
            </div>
            <div v-if="currentDetail?.images && currentDetail.images.length" class="grid grid-cols-2 gap-4">
              <div v-for="img in currentDetail.images" :key="img.id"
                class="rounded-lg border overflow-hidden aspect-square bg-gray-50 flex items-center justify-center">
                <img :src="getImgUrl(img.path)" alt="Evidence image"
                  class="object-contain max-h-48 w-full h-full" />
              </div>
            </div>
            <p v-else class="text-gray-500 italic">Không có ảnh minh chứng.</p>
          </div>

          <!-- NÚT DUYỆT / TỪ CHỐI -->
          <div v-if="currentDetail?.status === 'pending'" class="flex gap-3 mt-6">
            <button @click="handleApprove(currentDetail?.id)" :disabled="loadingApprove || !currentDetail?.id"
              class="flex-1 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm"
              :class="{ 'opacity-60 cursor-not-allowed': loadingApprove || !currentDetail?.id }">
              {{ loadingApprove ? 'Đang duyệt...' : 'Duyệt yêu cầu' }}
            </button>
            <button @click="openRejectModal" :disabled="loadingReject || !currentDetail?.id"
              class="flex-1 py-2 rounded bg-red-500 hover:bg-red-600 text-white font-semibold text-sm"
              :class="{ 'opacity-60 cursor-not-allowed': loadingReject || !currentDetail?.id }">
              {{ loadingReject ? 'Đang xử lý...' : 'Từ chối yêu cầu' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Modal từ chối -->
      <div v-if="rejectModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        @click="closeRejectModal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 animate-fadeIn" @click.stop>
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-red-600">Lý do từ chối</h3>
            <button @click="closeRejectModal" class="text-gray-400 hover:text-black text-xl">✕</button>
          </div>
          <textarea v-model="rejectReason" rows="4" placeholder="Nhập lý do từ chối..."
            class="w-full p-3 border rounded-md text-sm focus:ring-blue-500 focus:outline-none"></textarea>
          <div class="flex justify-end gap-2 mt-4">
            <button @click="closeRejectModal"
              class="px-4 py-2 rounded border text-gray-600 hover:bg-gray-100">Hủy</button>
            <button @click="submitReject" :disabled="loadingReject || !rejectReason.trim() || !currentDetail?.id"
              class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-semibold text-sm"
              :class="{ 'opacity-50 cursor-not-allowed': loadingReject || !rejectReason.trim() || !currentDetail?.id }">
              {{ loadingReject ? 'Đang gửi...' : 'Xác nhận từ chối' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Phân trang -->
      <div class="flex justify-center items-center gap-2 py-4 text-sm text-gray-700">
        <button @click="fetchRequests(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
          class="px-3 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50">
          Trước
        </button>
        <span>Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <button @click="fetchRequests(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="px-3 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50">
          Sau
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { secureFetch } from '@/utils/secureFetch';
import { useNotification } from '~/composables/useNotification';
import { useRuntimeConfig, useRouter } from '#app';

const config = useRuntimeConfig();
const router = useRouter();
const API_BASE_URL = config.public.apiBaseUrl || 'http://localhost:8000/api';
const mediaBase = config.public.mediaBaseUrl || 'http://localhost:8000/storage';
const { showMessage } = useNotification();

const requests = ref([]);
const filterStatus = ref('');
const sortOrder = ref('desc');
const detailModal = ref(false);
const currentDetail = ref(null);
const loadingApprove = ref(false);
const loadingReject = ref(false);
const isLoading = ref(true);
const rejectModal = ref(false);
const rejectReason = ref('');
const perPage = ref(10);
const pagination = ref({
  current_page: 1,
  last_page: 1,
});

// Computed property for filtered requests
const filteredRequests = computed(() => {
  return filterStatus.value
    ? requests.value.filter((item) => item.status === filterStatus.value)
    : requests.value;
});

definePageMeta({
  layout: 'default-seller',
});

const openRejectModal = () => {
  if (!currentDetail.value?.id) {
    if (typeof showMessage === 'function') {
      showMessage('Không thể mở modal từ chối: Yêu cầu không hợp lệ', 'error');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert('Không thể mở modal từ chối: Yêu cầu không hợp lệ');
    }
    return;
  }
  rejectReason.value = '';
  rejectModal.value = true;
};

const closeRejectModal = () => {
  rejectModal.value = false;
  rejectReason.value = '';
};

const submitReject = async () => {
  if (!currentDetail.value?.id) {
    if (typeof showMessage === 'function') {
      showMessage('Không thể từ chối: Yêu cầu không hợp lệ', 'error');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert('Không thể từ chối: Yêu cầu không hợp lệ');
    }
    return;
  }

  try {
    loadingReject.value = true;

    await secureFetch(`${API_BASE_URL}/seller/returns/${currentDetail.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        status: 'rejected',
        admin_note: rejectReason.value.trim(),
      }),
    }, ['seller']);

    await fetchRequests(pagination.value.current_page);
    if (typeof showMessage === 'function') {
      showMessage('Từ chối yêu cầu thành công!', 'success');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert('Từ chối yêu cầu thành công!');
    }
    closeRejectModal();
    closeDetail();
  } catch (error) {
    console.error('Error rejecting request:', error);
    let errorMessage = 'Từ chối yêu cầu thất bại!';
    if (error.response) {
      if (error.response.status === 401) {
        errorMessage = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.';
        router.push('/login');
      } else if (error.response.status === 403) {
        errorMessage = 'Bạn không có quyền từ chối yêu cầu này.';
      } else if (error.response.status === 500) {
        errorMessage = 'Lỗi server. Vui lòng thử lại sau hoặc liên hệ quản trị viên.';
      } else {
        errorMessage = error.response.data?.message || error.message;
      }
    }
    if (typeof showMessage === 'function') {
      showMessage(errorMessage, 'error');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert(errorMessage);
    }
  } finally {
    loadingReject.value = false;
  }
};

const fetchRequests = async (page = 1) => {
  try {
    isLoading.value = true;
    pagination.value.current_page = page;

    const url = new URL(`${API_BASE_URL}/seller/returns`);
    url.searchParams.set('page', page.toString());
    url.searchParams.set('per_page', perPage.value.toString());
    url.searchParams.set('sort_order', sortOrder.value);
    if (filterStatus.value) {
      url.searchParams.set('status', filterStatus.value);
    }

    const res = await secureFetch(url.toString(), {}, ['seller']);

    let items = [];
    if (res && res.data) {
      if (Array.isArray(res.data)) {
        items = res.data;
      } else if (res.data.data && Array.isArray(res.data.data)) {
        items = res.data.data;
        pagination.value.current_page = res.data.current_page || 1;
        pagination.value.last_page = res.data.last_page || 1;
      }
    }

    requests.value = items;
    if (!items.length) {
      if (typeof showMessage === 'function') {
        showMessage('Không có yêu cầu nào', 'info');
      } else {
        console.warn('showMessage is not available, falling back to alert');
        alert('Không có yêu cầu nào');
      }
    }
  } catch (error) {
    console.error('Lỗi khi tải yêu cầu:', error);
    let errorMessage = 'Lỗi khi tải yêu cầu';
    if (error.response) {
      if (error.response.status === 401) {
        errorMessage = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.';
        router.push('/login');
      } else if (error.response.status === 403) {
        errorMessage = 'Bạn không có quyền truy cập yêu cầu này.';
      } else if (error.response.status === 500) {
        errorMessage = 'Lỗi server. Vui lòng thử lại sau hoặc liên hệ quản trị viên.';
      } else {
        errorMessage = error.response.data?.message || error.message;
      }
    } else {
      errorMessage = error.message || 'Không thể kết nối đến server';
    }
    if (typeof showMessage === 'function') {
      showMessage(errorMessage, 'error');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert(errorMessage);
    }
    requests.value = [];
    pagination.value.last_page = 1;
  } finally {
    isLoading.value = false;
  }
};

const openDetail = (item) => {
  if (!item?.id) {
    if (typeof showMessage === 'function') {
      showMessage('Không thể mở chi tiết: Yêu cầu không hợp lệ', 'error');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert('Không thể mở chi tiết: Yêu cầu không hợp lệ');
    }
    return;
  }
  currentDetail.value = item;
  detailModal.value = true;
};

const closeDetail = () => {
  detailModal.value = false;
  currentDetail.value = null;
};

const getStatusText = (status) => {
  switch (status) {
    case 'pending':
      return 'Chờ duyệt';
    case 'approved':
      return 'Đã duyệt';
    case 'rejected':
      return 'Đã từ chối';
    default:
      return 'N/A';
  }
};

const getImgUrl = (path) => {
  return path ? `${mediaBase}${path}` : '';
};

const formatPrice = (price) => {
  if (!price) return 'N/A';
  return new Intl.NumberFormat('vi-VN').format(price) + ' VND';
};

const formatDate = (date) => {
  if (!date || isNaN(new Date(date).getTime())) return 'N/A';
  return new Date(date).toLocaleString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const handleApprove = async (id) => {
  if (!id) {
    if (typeof showMessage === 'function') {
      showMessage('Không thể duyệt: Yêu cầu không hợp lệ', 'error');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert('Không thể duyệt: Yêu cầu không hợp lệ');
    }
    return;
  }

  try {
    loadingApprove.value = true;

    await secureFetch(`${API_BASE_URL}/seller/returns/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ status: 'approved' }),
    }, ['seller']);

    if (typeof showMessage === 'function') {
      showMessage('Duyệt yêu cầu thành công!', 'success');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert('Duyệt yêu cầu thành công!');
    }

    await fetchRequests(pagination.value.current_page);
    closeDetail();
  } catch (error) {
    console.error('Error approving request:', error);
    let errorMessage = 'Duyệt yêu cầu thất bại!';
    if (error.response) {
      if (error.response.status === 401) {
        errorMessage = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.';
        router.push('/login');
      } else if (error.response.status === 403) {
        errorMessage = 'Bạn không có quyền duyệt yêu cầu này.';
      } else if (error.response.status === 500) {
        errorMessage = 'Lỗi server. Vui lòng thử lại sau hoặc liên hệ quản trị viên.';
      } else {
        errorMessage = error.response.data?.message || error.message;
      }
    }
    if (typeof showMessage === 'function') {
      showMessage(errorMessage, 'error');
    } else {
      console.warn('showMessage is not available, falling back to alert');
      alert(errorMessage);
    }
  } finally {
    loadingApprove.value = false;
  }
};

const closeDetailOnEsc = (event) => {
  if (event.key === 'Escape') {
    closeDetail();
  }
};

onMounted(() => {
  fetchRequests();
  window.addEventListener('keydown', closeDetailOnEsc);
});

onUnmounted(() => {
  window.removeEventListener('keydown', closeDetailOnEsc);
});
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.98);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>