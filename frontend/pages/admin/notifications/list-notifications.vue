
<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý thông báo</h1>
        <NuxtLink
          to="/admin/notifications/create-notifications"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tạo thông báo
        </NuxtLink>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <button
            @click="setFilter('all')"
            :class="['text-blue-600 hover:underline', filterStatus === '' && filterDraft === '' ? 'font-semibold' : '']"
          >
            Tất cả
          </button>
          <span>({{ totalNotifications }})</span>
          <button
            @click="setFilter('sent')"
            :class="['text-blue-600 hover:underline', filterStatus === 'sent' && filterDraft === '' ? 'font-semibold' : '']"
          >
            Đã gửi
          </button>
          <span>({{ sentNotifications }})</span>
          <button
            @click="setFilter('draft')"
            :class="['text-blue-600 hover:underline', filterDraft === 'draft' ? 'font-semibold' : '']"
          >
            Nháp
          </button>
          <span>({{ draftNotifications }})</span>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
          <select
            v-model="sortBy"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="newest">Mới nhất</option>
            <option value="oldest">Cũ nhất</option>
          </select>
          <select
            v-model="filterType"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Tất cả loại</option>
            <option value="order">Đơn hàng</option>
            <option value="promotion">Khuyến mãi</option>
            <option value="message">Tin nhắn</option>
            <option value="system">Hệ thống</option>
          </select>
          <select
            v-model="filterRole"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Tất cả vai trò</option>
            <option value="user">Người dùng</option>
            <option value="seller">Người bán</option>
            <option value="admin">Quản trị viên</option>
          </select>
          <select
            v-model="filterChannel"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Tất cả kênh</option>
            <option value="web">Web</option>
            <option value="email">Email</option>
          </select>
          <select
            v-model="filterReadStatus"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Tất cả trạng thái đọc</option>
            <option value="read">Đã đọc</option>
            <option value="unread">Chưa đọc</option>
          </select>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Tìm kiếm thông báo..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64"
            />
            <svg
              class="absolute left-2.5 top-2 h-4 w-4 text-gray-400"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- Action Bar -->
      <div
        class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-t border-gray-300 relative z-20"
      >
        <div class="relative z-30">
          <select
            v-model="selectedAction"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-48"
            title="Chọn hành động hàng loạt"
            @change="logSelectedAction"
          >
            <option value="" disabled>Hành động hàng loạt</option>
            <option value="send">Gửi các thông báo đã chọn</option>
            <option value="delete">Xóa các thông báo đã chọn</option>
          </select>
        </div>
        <button
          @click="applyBulkAction"
          :disabled="!selectedAction || selectedIds.length === 0 || loading"
          :class="[
            'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150 z-30',
            !selectedAction || selectedIds.length === 0 || loading
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-blue-600 text-white hover:bg-blue-700'
          ]"
          title="Áp dụng hành động cho các thông báo đã chọn"
        >
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <button
          @click="sendAllNotifications"
          :disabled="loading"
          class="bg-green-100 text-green-700 px-4 py-2 rounded hover:bg-green-200 text-sm flex items-center gap-2 z-30"
          title="Gửi tất cả thông báo chưa gửi"
        >
          <Send class="w-4 h-4" />
          Gửi tất cả thông báo chưa gửi
        </button>
        <button
          @click="deleteAll"
          :disabled="loading"
          class="bg-red-100 text-red-700 px-4 py-2 rounded hover:bg-red-200 text-sm flex items-center gap-2 z-30"
          title="Xóa tất cả thông báo"
        >
          <Trash2 class="w-4 h-4" />
          Xóa tất cả
        </button>
        <div class="ml-auto text-sm text-gray-600 z-30">
          {{ selectedIds.length }} thông báo được chọn / {{ filteredNotifications.length }} thông báo
        </div>
      </div>

      <!-- Notification Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm mt-4 bg-white">
        <thead>
          <tr>
            <th class="border px-3 py-2 text-left font-semibold">
              <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected" />
            </th>
            <th class="border px-3 py-2 text-left font-semibold">ID</th>
            <th class="border px-3 py-2 text-left font-semibold">Tiêu đề</th>
            <th class="border px-3 py-2 text-left font-semibold">Ảnh</th>
            <th class="border px-3 py-2 text-left font-semibold">Loại</th>
            <th class="border px-3 py-2 text-left font-semibold">Vai trò người nhận</th>
            <th class="border px-3 py-2 text-left font-semibold">Kênh gửi</th>
            <th class="border px-3 py-2 text-left font-semibold">Trạng thái</th>
            <th class="border px-3 py-2 text-left font-semibold">Hiển thị</th>
            <th class="border px-3 py-2 text-left font-semibold">Trạng thái đọc</th>
            <th class="border px-3 py-2 text-left font-semibold">Ngày gửi</th>
            <th class="border px-3 py-2 text-left font-semibold">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in paginatedNotifications" :key="item.id" class="border-b">
            <td class="px-3 py-2">
              <input type="checkbox" :value="item.id" v-model="selectedIds" />
            </td>
            <td class="px-3 py-2">#{{ item.id }}</td>
            <td class="px-3 py-2 font-semibold text-blue-700 hover:underline cursor-pointer" @click="viewNotification(item.id)">
              {{ truncateText(item.title, 30) }}
            </td>
            <td class="px-3 py-2">
              <img
                v-if="item.image_url"
                :src="item.image_url.startsWith('http') ? item.image_url : `${mediaBase}${item.image_url}`"
                alt="Ảnh"
                class="w-14 h-14 object-cover rounded"
              />
              <span v-else class="text-gray-400 italic">Không có</span>
            </td>
            <td class="px-3 py-2">{{ typeLabel(item.type) }}</td>
            <td class="px-3 py-2">
              <span v-if="item.to_roles && item.to_roles.length">
                <span
                  v-for="(role, index) in item.to_roles"
                  :key="index"
                  class="inline-block bg-blue-100 text-blue-800 rounded-full px-2 py-1 text-xs mr-1"
                >
                  {{ roleLabel(role) }}
                </span>
              </span>
              <span v-else class="text-gray-400 italic">Không có</span>
            </td>
            <td class="px-3 py-2">
              <span v-if="item.channels && Array.isArray(item.channels)">
                <span
                  v-for="(channel, index) in item.channels"
                  :key="index"
                  class="inline-block bg-gray-200 text-gray-700 rounded-full px-2 py-1 text-xs mr-1"
                >
                  {{ channelLabel(channel) }}
                </span>
              </span>
              <span v-else class="text-gray-400 italic">Không có</span>
            </td>
            <td class="px-3 py-2">
              <span
                :class="item.status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
                class="px-2 py-1 text-xs font-semibold rounded"
              >
                {{ item.status === 'draft' ? 'Lưu nháp' : 'Đã gửi' }}
              </span>
            </td>
            <td class="px-3 py-2">
              <span :class="item.is_hidden ? 'text-red-500' : 'text-green-600'">
                {{ item.is_hidden ? 'Đã ẩn' : 'Đang hiển thị' }}
              </span>
            </td>
            <td class="px-3 py-2">
              <span v-if="item.read_status && item.read_status.length">
                <span
                  v-for="(status, index) in item.read_status"
                  :key="index"
                  class="inline-block px-2 py-1 text-xs font-semibold rounded mr-1"
                  :class="status.is_read ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                >
                  {{ status.is_read ? `Đã đọc` : `Chưa đọc` }}
                </span>
              </span>
              <span v-else class="text-gray-400 italic">Không có</span>
            </td>
            <td class="px-3 py-2">{{ formatDate(item.created_at) }}</td>
            <td class="px-3 py-2 relative">
              <button
                @click="toggleDropdown(item.id, $event)"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-800 focus:outline-none"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                  />
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="paginatedNotifications.length === 0">
            <td colspan="12" class="text-center py-4 text-gray-500">Không có thông báo nào</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <Pagination :currentPage="currentPage" :lastPage="lastPage" @change="fetchNotifications" />

      <!-- Dropdown -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition duration-100 ease-out"
          enter-from-class="transform scale-95 opacity-0"
          enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-75 ease-in"
          leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0"
        >
          <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
            <div
              class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right w-40"
              :style="dropdownPosition"
            >
              <div class="py-1">
                <button
                  @click="viewNotification(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <Eye class="w-4 h-4 mr-2" /> Xem
                </button>
                <button
                  @click="editNotification(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50"
                >
                  <Pencil class="w-4 h-4 mr-2" /> Sửa
                </button>
                <button
                  @click="confirmDelete(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  <Trash2 class="w-4 h-4 mr-2" /> Xóa
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

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

      <!-- Confirmation Dialog -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="showConfirmDialog" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
              <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfirmDialog"></div>
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
              <div
                class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
              >
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div
                      class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10"
                    >
                      <svg
                        class="h-6 w-6 text-blue-600"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                      <h3 class="text-lg font-medium leading-6 text-gray-900">{{ confirmDialogTitle }}</h3>
                      <div class="mt-2">
                        <p class="text-sm text-gray-500">{{ confirmDialogMessage }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button
                    type="button"
                    class="w-full inline-flex justify-center rounded-md px-4 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto"
                    @click="handleConfirmAction"
                  >
                    Xác nhận
                  </button>
                  <button
                    type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto"
                    @click="closeConfirmDialog"
                  >
                    Hủy
                  </button>
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter, useRuntimeConfig } from '#app';
import { Eye } from 'lucide-vue-next';
import { secureFetch } from '@/utils/secureFetch';
import Pagination from '~/components/Pagination.vue';
import Swal from 'sweetalert2';

definePageMeta({ layout: 'default-seller' });

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl || 'http://localhost:8000/api';
const mediaBase = config.public.mediaBaseUrl || '';
const router = useRouter();

const notifications = ref([]);
const activeDropdown = ref(null);
const showMenuDropdown = ref(false);
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' });
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const sortOrder = ref('desc');
const filterType = ref('');
const searchQuery = ref('');
const totalNotifications = ref(0);
const userId = ref(null);
const loading = ref(false);
const selectedNotificationIds = ref([]);
const selectAll = ref(false);
const showModal = ref(false);
const currentNotification = ref(null);

// Debounce for search
let debounceTimeout = null;
const debounceFetchNotifications = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchNotifications(1);
  }, 300);
};

// Quản lý timeout cho toast
let notificationTimeout = null;

// Hiển thị thông báo toast
const showNotificationMessage = (message, type = 'success') => {
  clearTimeout(notificationTimeout);
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  notificationTimeout = setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

// Toggle menu dropdown
const toggleMenuDropdown = () => {
  showMenuDropdown.value = !showMenuDropdown.value;
};

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedNotificationIds.value = paginatedNotifications.value.map(item => item.id);
  } else {
    selectedNotificationIds.value = [];
  }
};

// Toggle dropdown
const toggleDropdown = (id, event) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null;
    return;
  }
  activeDropdown.value = id;
  nextTick(() => {
    const button = event.target.closest('button');
    if (button) {
      const rect = button.getBoundingClientRect();
      dropdownPosition.value = {
        top: `${rect.bottom + window.scrollY + 8}px`,
        left: `${rect.right + window.scrollX - 192}px`,
        width: '192px',
      };
    }
  });
};

const closeDropdown = (event = null) => {
  if (event && (!event.target.closest('.relative') && !event.target.closest('.absolute'))) {
    activeDropdown.value = null;
    showMenuDropdown.value = false;
  } else if (!event) {
    activeDropdown.value = null;
    showMenuDropdown.value = false;
  }
};

const typeLabel = (type) => ({
  order: 'Đơn hàng',
  promotion: 'Khuyến mãi',
  message: 'Tin nhắn',
  system: 'Hệ thống',
})[type] || 'Không xác định';

const formatDate = (dateStr) => {
  if (!dateStr) return '–';
  const d = new Date(dateStr);
  return d.toLocaleDateString('vi-VN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'Asia/Ho_Chi_Minh',
  });
};

const truncateText = (text, maxLength) => {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text;
};

// Lấy user_id từ token hoặc API
const fetchUserId = async () => {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Không tìm thấy token');
    const response = await secureFetch(`${apiBase}/me`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      cache: 'no-store',
    }, ['seller']);
    userId.value = response.data?.id;
    console.log('User ID:', userId.value);
  } catch (error) {
    console.error('Lỗi khi lấy user_id:', error);
    showNotificationMessage('Không thể xác định người dùng. Vui lòng đăng nhập lại.', 'error');
    router.push('/login');
  }
};

// Lấy trạng thái recipient cho một thông báo
const fetchRecipientStatus = async (notificationId) => {
  try {
    const response = await secureFetch(`${apiBase}/notifications/${notificationId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('access_token')}`,
      },
      cache: 'no-store',
    }, ['seller']);
    
    console.log(`Response for notification ${notificationId}:`, response); // Debug

    // Kiểm tra nếu response.data không tồn tại hoặc không có recipients
    if (!response.data || !response.data.recipients) {
      console.warn(`No recipients found for notification ${notificationId}`);
      return { is_read: false, is_hidden: false };
    }

    const recipient = response.data.recipients.find(r => r.user_id === userId.value);
    if (!recipient) {
      console.warn(`No recipient found for user ${userId.value} in notification ${notificationId}`);
      return { is_read: false, is_hidden: false };
    }

    return recipient;
  } catch (error) {
    console.error(`Error fetching recipient status for ${notificationId}:`, error);
    return { is_read: false, is_hidden: false }; // Trả về giá trị mặc định để tránh crash
  }
};

// Lấy danh sách thông báo
const fetchNotifications = async (page = 1) => {
  if (!userId.value) await fetchUserId();
  if (!userId.value) return;

  try {
    loading.value = true;
    currentPage.value = page;
    const queryParams = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.value.toString(),
      to_roles: 'seller',
      user_id: userId.value,
      ...(filterType.value && { type: filterType.value }),
      ...(searchQuery.value && { search: searchQuery.value }),
      sort_order: sortOrder.value,
    });
    const endpoint = `${apiBase}/notifications?${queryParams.toString()}`;
    const data = await secureFetch(endpoint, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('access_token')}`,
      },
      cache: 'no-store',
    }, ['seller']);

    console.log('Notifications API response:', data); // Debug

    if (!data.success || !Array.isArray(data.data)) {
      throw new Error(data.message || 'Dữ liệu thông báo không hợp lệ');
    }

    // Gắn trạng thái is_read từ recipients
    notifications.value = await Promise.all(data.data.map(async (item) => {
      const recipient = await fetchRecipientStatus(item.id);
      if (recipient.is_hidden) {
        console.log(`Notification ${item.id} is hidden for user ${userId.value}`);
        return null; // Bỏ qua thông báo đã ẩn
      }
      return {
        ...item,
        is_read: recipient ? recipient.is_read : false,
        is_hidden: recipient ? recipient.is_hidden : false,
        content: item.content || '',
      };
    }));

    // Lọc bỏ các giá trị null (thông báo đã ẩn)
    notifications.value = notifications.value.filter(item => item !== null);
    console.log('Processed notifications:', notifications.value); // Debug

    lastPage.value = data.last_page || 1;
    totalNotifications.value = data.total || data.data.length;
    currentPage.value = data.current_page || page;

    if (!notifications.value.length) {
      showNotificationMessage('Không có thông báo nào', 'info');
    }
  } catch (error) {
    console.error('Lỗi khi lấy thông báo:', error);
    showNotificationMessage(`Có lỗi khi tải thông báo: ${error.message}`, 'error');
    notifications.value = [];
    lastPage.value = 1;
    totalNotifications.value = 0;
  } finally {
    loading.value = false;
  }
};

// Xử lý click vào thông báo
const handleNotificationClick = async (item, action = 'item') => {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Bạn chưa đăng nhập!');

    if (!item.is_read) {
      await secureFetch(`${apiBase}/notifications/${item.id}/read`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      }, ['seller']);
      notifications.value = notifications.value.map(n =>
        n.id === item.id ? { ...n, is_read: true } : n
      );
    }

    if (action === 'detail') {
      currentNotification.value = item;
      showModal.value = true;
    } else if (item.link) {
      window.open(item.link, '_blank');
    } else {
      currentNotification.value = item;
      showModal.value = true;
    }
  } catch (error) {
    console.error('Lỗi khi xử lý thông báo:', error);
    showNotificationMessage(error.message || 'Không thể xử lý thông báo', 'error');
  }
};

// Đánh dấu một thông báo là đã đọc
const markAsRead = async (id) => {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Bạn chưa đăng nhập!');

    await secureFetch(`${apiBase}/notifications/${id}/read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    }, ['seller']);

    notifications.value = notifications.value.map(item =>
      item.id === id ? { ...item, is_read: true } : item
    );
    showNotificationMessage('Đã đánh dấu thông báo là đã đọc', 'success');
  } catch (error) {
    console.error('Lỗi khi đánh dấu đã đọc:', error);
    showNotificationMessage(`Không thể đánh dấu đã đọc: ${error.message}`, 'error');
  }
};

// Đánh dấu các thông báo đã chọn là đã đọc
const markSelectedAsRead = async () => {
  if (selectedNotificationIds.value.length === 0) return;

  const confirm = await Swal.fire({
    title: 'Đánh dấu đã đọc?',
    text: `Bạn có chắc chắn muốn đánh dấu ${selectedNotificationIds.value.length} thông báo là đã đọc?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Đánh dấu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280',
  });

  if (!confirm.isConfirmed) return;

  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Bạn chưa đăng nhập!');

    await secureFetch(`${apiBase}/notifications/mark-multiple-read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ ids: selectedNotificationIds.value }),
    }, ['seller']);

    notifications.value = notifications.value.map(item =>
      selectedNotificationIds.value.includes(item.id) ? { ...item, is_read: true } : item
    );
    selectedNotificationIds.value = [];
    selectAll.value = false;
    showNotificationMessage('Đã đánh dấu các thông báo đã chọn là đã đọc', 'success');
  } catch (error) {
    console.error('Lỗi khi đánh dấu đã đọc:', error);
    showNotificationMessage(`Không thể đánh dấu đã đọc: ${error.message}`, 'error');
  }
};

// Đánh dấu tất cả thông báo là đã đọc
const markAllAsRead = async () => {
  const confirm = await Swal.fire({
    title: 'Đánh dấu đọc tất cả?',
    text: 'Bạn có chắc chắn muốn đánh dấu tất cả thông báo là đã đọc?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Đánh dấu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280',
  });

  if (!confirm.isConfirmed) return;

  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Bạn chưa đăng nhập!');

    await secureFetch(`${apiBase}/notifications/mark-all-read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    }, ['seller']);

    notifications.value = notifications.value.map(item => ({ ...item, is_read: true }));
    selectedNotificationIds.value = [];
    selectAll.value = false;
    showNotificationMessage('Đã đánh dấu tất cả là đã đọc', 'success');
  } catch (error) {
    console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
    showNotificationMessage(`Không thể đánh dấu tất cả là đã đọc: ${error.message}`, 'error');
  }
};

// Xóa (ẩn) các thông báo đã chọn
const deleteSelectedNotifications = async (ids = selectedNotificationIds.value) => {
  if (ids.length === 0) return;

  const confirm = await Swal.fire({
    title: 'Xóa thông báo?',
    text: `Bạn có chắc chắn muốn xóa ${ids.length} thông báo đã chọn?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
  });

  if (!confirm.isConfirmed) return;

  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Bạn chưa đăng nhập!');

    await secureFetch(`${apiBase}/notifications/delete-multiple`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ ids }),
    }, ['seller']);

    notifications.value = notifications.value.filter(item => !ids.includes(item.id));
    selectedNotificationIds.value = selectedNotificationIds.value.filter(id => !ids.includes(id));
    selectAll.value = false;
    showNotificationMessage('Đã xóa thông báo đã chọn', 'success');
    await fetchNotifications(currentPage.value); // Làm mới danh sách
  } catch (error) {
    console.error('Lỗi khi xóa thông báo:', error);
    showNotificationMessage(`Không thể xóa thông báo: ${error.message}`, 'error');
  }
};

// Xóa tất cả thông báo
const deleteAllNotifications = async () => {
  const confirm = await Swal.fire({
    title: 'Xóa tất cả thông báo?',
    text: 'Bạn có chắc chắn muốn xóa tất cả thông báo?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
  });

  if (!confirm.isConfirmed) return;

  try {
    const token = localStorage.getItem('access_token');
    if (!token) throw new Error('Bạn chưa đăng nhập!');

    await secureFetch(`${apiBase}/notifications/delete-all`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    }, ['seller']);

    notifications.value = [];
    selectedNotificationIds.value = [];
    selectAll.value = false;
    totalNotifications.value = 0;
    showNotificationMessage('Đã xóa tất cả thông báo', 'success');
  } catch (error) {
    console.error('Lỗi khi xóa tất cả thông báo:', error);
    showNotificationMessage(`Không thể xóa tất cả thông báo: ${error.message}`, 'error');
  }
};

// Lọc và phân trang thông báo
const paginatedNotifications = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return notifications.value.slice(start, end);
});

onMounted(() => {
  fetchNotifications();
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
  clearTimeout(debounceTimeout);
  clearTimeout(notificationTimeout);
});
</script>