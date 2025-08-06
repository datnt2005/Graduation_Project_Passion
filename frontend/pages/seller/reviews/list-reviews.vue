<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý đánh giá</h1>
      </div>

      <!-- Bộ lọc nâng cao -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2 flex-wrap">
          <button v-for="n in [null, 5, 4, 3, 2, 1]" :key="'star-' + n"
            @click="filterRating = n; fetchReviews(1)" :class="[
              'px-3 py-1 rounded border',
              filterRating === n ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300'
            ]">
            {{ n ? `${n} sao` : 'Tất cả' }} ({{ countByRating[n ?? 'all'] || 0 }})
          </button>
        </div>

        <button @click="filterHasMedia = !filterHasMedia; fetchReviews(1)" :class="[
          'px-3 py-1 rounded border',
          filterHasMedia ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300'
        ]">
          Có ảnh / video ({{ countWithMedia }})
        </button>

        <div class="flex items-center gap-2 flex-wrap">
          <button v-for="status in ['all', 'approved', 'pending', 'rejected']" :key="'status-' + status"
            @click="filterStatus = status; fetchReviews(1)" :class="[
              'px-3 py-1 rounded border',
              filterStatus === status ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300'
            ]">
            {{ statusText(status) }} ({{ countByStatus[status] || 0 }})
          </button>
        </div>

        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Sắp xếp:</label>
          <select v-model="sortOrder" @change="fetchReviews(1)"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="desc">Mới nhất</option>
            <option value="asc">Cũ nhất</option>
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

      <!-- Bảng đánh giá -->
      <table v-else class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border px-3 py-2 font-semibold text-left">ID</th>
            <th class="border px-3 py-2 font-semibold text-left">Sản phẩm</th>
            <th class="border px-3 py-2 font-semibold text-left">Ảnh SP</th>
            <th class="border px-3 py-2 font-semibold text-left">Ảnh đánh giá</th>
            <th class="border px-3 py-2 font-semibold text-left">Thích</th>
            <th class="border px-3 py-2 font-semibold text-left">Nội dung</th>
            <th class="border px-3 py-2 font-semibold text-left">Sao</th>
            <th class="border px-3 py-2 font-semibold text-left">Phản hồi</th>
            <th class="border px-3 py-2 font-semibold text-left">Trạng thái</th>
            <th class="border px-3 py-2 font-semibold text-left">Ngày</th>
            <th class="border px-3 py-2 font-semibold text-left">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="review in reviews" :key="review.id" :class="{ 'bg-gray-50': review.id % 2 === 0 }"
            class="border-b border-gray-300">
            <td class="border px-3 py-2">#{{ review.id }}</td>
            <td class="border px-3 py-2">{{ review.product_name }}</td>
            <td class="border px-3 py-2">
              <img v-if="review.product_image" :src="review.product_image"
                class="w-12 h-12 object-cover rounded" />
            </td>
            <td class="border px-3 py-2">
              <div class="flex gap-1">
                <img v-for="img in review.images" :key="img" :src="img"
                  class="w-10 h-10 object-cover rounded" />
              </div>
            </td>
            <td class="border px-3 py-2">{{ review.likes_count }}</td>
            <td class="border px-3 py-2 truncate max-w-[200px]">{{ review.content }}</td>
            <td class="border px-3 py-2">{{ review.rating }} ★</td>
            <td class="border px-3 py-2 text-sm italic text-gray-600">
              <template v-if="review.reply">
                {{ review.reply.content }}
              </template>
              <template v-else>
                <span class="text-gray-400">Chưa phản hồi</span>
              </template>
            </td>
            <td class="border px-3 py-2">
              <span :class="statusClass(review.status)" class="px-2 py-1 text-xs font-semibold rounded">
                {{ statusText(review.status) }}
              </span>
            </td>
            <td class="border px-3 py-2">{{ formatDate(review.created_at) }}</td>
            <td class="border px-3 py-2 relative">
              <button @click.stop="toggleDropdown($event, review.id)"
                class="p-1 text-gray-600 hover:text-black focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                  fill="currentColor">
                  <path
                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="reviews.length === 0">
            <td colspan="11" class="text-center py-4 text-gray-500">Không có đánh giá nào</td>
          </tr>
        </tbody>
      </table>

      <!-- Phân trang -->
      <Pagination v-if="!loading" :currentPage="currentPage" :lastPage="lastPage" @change="fetchReviews" />

      <!-- Dropdown -->
      <Teleport to="body">
        <Transition enter-active-class="transition duration-100 ease-out"
          enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-75 ease-in"
          leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
          <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
            <div class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right w-40"
              :style="dropdownPosition" @click.stop>
              <div class="py-1">
                <button @click="viewReview(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <Eye class="w-4 h-4 mr-2" /> Xem
                </button>
                <button @click="editReview(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50">
                  <Pencil class="w-4 h-4 mr-2" /> Sửa
                </button>
                <button @click="reportReview(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                  </svg> Báo cáo
                </button>
                <button @click="confirmDelete(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                  <Trash2 class="w-4 h-4 mr-2" /> Xóa
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Dialog xác nhận xóa -->
      <Teleport to="body">
        <Transition v-if="showConfirmDelete" enter-active-class="transition ease-out duration-200"
          enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-100" leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95">
          <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-96">
              <h3 class="text-lg font-semibold">Xác nhận xóa</h3>
              <p class="mt-2 text-sm text-gray-600">Bạn có chắc muốn xóa đánh giá này?</p>
              <div class="mt-4 flex justify-end gap-2">
                <button @click="showConfirmDelete = false" class="px-4 py-2 text-gray-600">Hủy</button>
                <button @click="deleteReview(selectedReviewId); showConfirmDelete = false" class="px-4 py-2 bg-red-600 text-white rounded">Xóa</button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Report Dialog -->
      <ReportDialog 
        v-if="showReportDialog" 
        :target-id="selectedReviewId" 
        type="review" 
        @close="showReportDialog = false" 
        @submitted="handleReportSubmitted" 
      />

      <!-- Notification Popup -->
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
            v-if="showNotification"
            class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-[1000]"
          >
            <div class="flex-shrink-0">
              <svg
                class="h-6 w-6"
                :class="notificationType === 'success' ? 'text-green-600' : 'text-red-600'"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  v-if="notificationType === 'success'"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
                <path
                  v-if="notificationType === 'error'"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm text-gray-900">{{ notificationMessage }}</p>
            </div>
            <div class="flex-shrink-0">
              <button
                @click="showNotification = false"
                class="p-1 text-gray-500 hover:text-gray-700 focus:outline-none"
              >
                <svg
                  class="h-4 w-4"
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
    </div>
  </div>
</template>

<script setup>
import { Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRuntimeConfig } from '#app';
import { useNotification } from '~/composables/useNotification';
import { secureAxios } from '@/utils/secureAxios';
import Pagination from '~/components/Pagination.vue';
import ReportDialog from '~/components/shared/ReportDialog.vue';

definePageMeta({ layout: 'default-seller' });

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl || 'http://localhost:8000/api';
const router = useRouter();
const { showNotification, notificationMessage, notificationType, setNotification } = useNotification();

const reviews = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const total = ref(0);
const sortOrder = ref('desc');
const filterRating = ref(null);
const filterStatus = ref('all');
const filterHasMedia = ref(false);
const countByRating = ref({ all: 0 });
const countByStatus = ref({ all: 0 });
const countWithMedia = ref(0);
const activeDropdown = ref(null);
const dropdownPosition = ref({ top: '0px', left: '0px' });
const showReportDialog = ref(false);
const showConfirmDelete = ref(false);
const selectedReviewId = ref(null);

// Fetch reviews
const fetchReviews = async (page = 1) => {
  try {
    loading.value = true;
    currentPage.value = page;

    const queryParams = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.value.toString(),
      sort_order: sortOrder.value,
      ...(filterRating.value !== null && { rating: filterRating.value }),
      ...(filterStatus.value !== 'all' && { status: filterStatus.value }),
      ...(filterHasMedia.value && { has_media: true }),
    });

    const response = await secureAxios(`${apiBase}/seller/reviews?${queryParams.toString()}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('access_token')}`,
      },
      cache: 'no-store',
    }, ['seller']);

    if (!response.data.success || !Array.isArray(response.data.data)) {
      throw new Error(response.data.message || 'Dữ liệu đánh giá không hợp lệ');
    }

    reviews.value = response.data.data;
    lastPage.value = response.data.last_page || 1;
    total.value = response.data.total || response.data.data.length;
    currentPage.value = response.data.current_page || page;
    countByRating.value = response.data.count_by_rating || { all: 0 };
    countByStatus.value = response.data.count_by_status || { all: 0 };
    countWithMedia.value = response.data.count_with_media || 0;

    if (!reviews.value.length) {
      setNotification('Không có đánh giá nào', 'info');
    }
  } catch (e) {
    console.error('Lỗi khi tải đánh giá:', e);
    setNotification('Lỗi khi tải đánh giá: ' + e.message, 'error');
    reviews.value = [];
    lastPage.value = 1;
    total.value = 0;
  } finally {
    loading.value = false;
  }
};

// Toggle dropdown
const toggleDropdown = (event, id) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null;
    return;
  }

  const rect = event.currentTarget.getBoundingClientRect();
  dropdownPosition.value = {
    top: `${rect.bottom + window.scrollY}px`,
    left: `${rect.left + window.scrollX - 160}px`,
  };
  activeDropdown.value = id;
};

const closeDropdown = () => {
  activeDropdown.value = null;
};

// Navigation
const viewReview = (id) => {
  router.push(`/seller/reviews/view/${id}`);
};

const editReview = (id) => {
  router.push(`/seller/reviews/edit-reviews/${id}`);
};

// Report review
const reportReview = (id) => {
  selectedReviewId.value = id;
  showReportDialog.value = true;
};

const handleReportSubmitted = () => {
  showReportDialog.value = false;
  setNotification('Báo cáo đã được gửi thành công', 'success');
};

// Delete review
const confirmDelete = (id) => {
  selectedReviewId.value = id;
  showConfirmDelete.value = true;
};

const deleteReview = async (id) => {
  try {
    await secureAxios(`${apiBase}/seller/reviews/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` },
    }, ['seller']);

    reviews.value = reviews.value.filter(r => r.id !== id);
    setNotification('Đã xóa đánh giá thành công', 'success');
    await fetchReviews(currentPage.value);
  } catch (e) {
    console.error('Lỗi khi xóa đánh giá:', e);
    setNotification('Lỗi khi xóa đánh giá: ' + e.message, 'error');
  }
};

// Format
const formatDate = (dateStr) => {
  const d = new Date(dateStr);
  return d.toLocaleString('vi-VN');
};

const statusText = (status) => {
  switch (status) {
    case 'approved': return 'Đã duyệt';
    case 'pending': return 'Chờ duyệt';
    case 'rejected': return 'Từ chối';
    case 'all': return 'Tất cả';
    default: return 'Không rõ';
  }
};

const statusClass = (status) => {
  switch (status) {
    case 'approved': return 'bg-green-100 text-green-800';
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

// Mounted
onMounted(() => {
  fetchReviews();
  document.addEventListener('click', closeDropdown);
});

// Unmounted
onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
</script>

<style scoped>
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
  transition: transform .5s, opacity 1s;
}
button:active::after {
  transform: scale(0, 0);
  opacity: .2;
  transition: 0s;
}

.bg-gray-100 {
  overflow: visible !important;
}
</style>