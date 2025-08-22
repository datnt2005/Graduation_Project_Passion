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
            <td colspan="11" class="text-center py-4 text-gray-500">Không có thông báo nào</td>
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter, useRuntimeConfig, useCookie } from '#app'
import { Eye, Pencil, Trash2, Send } from 'lucide-vue-next'
import { secureFetch } from '@/utils/secureFetch'
import Pagination from '~/components/Pagination.vue'

definePageMeta({ layout: 'default-admin' })

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl || ''
const mediaBase = config.public.mediaBaseUrl || ''
const router = useRouter()

const notifications = ref([])
const selectedIds = ref([])
const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' })
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(10)
const filterStatus = ref('')
const filterDraft = ref('')
const sortBy = ref('newest')
const filterType = ref('')
const filterRole = ref('')
const filterChannel = ref('')
const searchQuery = ref('')
const totalNotifications = ref(0)
const sentNotifications = ref(0)
const draftNotifications = ref(0)
const selectedAction = ref('')
const loading = ref(false)
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const confirmAction = ref(null)

// Quản lý timeout cho toast
let notificationTimeout = null

// Hiển thị thông báo toast
const showNotificationMessage = (message, type = 'success') => {
  clearTimeout(notificationTimeout)
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  notificationTimeout = setTimeout(() => {
    showNotification.value = false
  }, 3000)
}

// Kiểm tra chọn tất cả
const isAllSelected = computed(() => selectedIds.value.length === paginatedNotifications.value.length)

const toggleSelectAll = () => {
  selectedIds.value = isAllSelected.value ? [] : paginatedNotifications.value.map(n => n.id)
}

// Đặt bộ lọc
const setFilter = (type) => {
  if (type === 'all') {
    filterStatus.value = ''
    filterDraft.value = ''
  } else if (type === 'sent') {
    filterStatus.value = 'sent'
    filterDraft.value = ''
  } else if (type === 'draft') {
    filterDraft.value = 'draft'
    filterStatus.value = ''
  }
  currentPage.value = 1
  fetchNotifications()
}

// Debug dropdown
const logSelectedAction = () => {
  console.log('Hành động được chọn:', selectedAction.value)
}

const toggleDropdown = (id, event) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null
    return
  }
  activeDropdown.value = id
  nextTick(() => {
    const button = event.target.closest('button')
    if (button) {
      const rect = button.getBoundingClientRect()
      dropdownPosition.value = {
        top: `${rect.bottom + window.scrollY + 8}px`,
        left: `${rect.right + window.scrollX - 192}px`,
        width: '192px'
      }
    }
  })
}

const closeDropdown = (event = null) => {
  if (event && (!event.target.closest('.relative') && !event.target.closest('.absolute'))) {
    activeDropdown.value = null
  } else if (!event) {
    activeDropdown.value = null
  }
}

const viewNotification = (id) => {
  router.push(`/admin/notifications/view/${id}`)
}

const editNotification = (id) => {
  router.push(`/admin/notifications/edit-notifications/${id}`)
}

const channelLabel = (channel) => ({
  web: 'Web',
  email: 'Email'
})[channel] || channel

const typeLabel = (type) => ({
  order: 'Đơn hàng',
  promotion: 'Khuyến mãi',
  message: 'Tin nhắn',
  system: 'Hệ thống'
})[type] || 'Không xác định'

const roleLabel = (role) => ({
  user: 'Người dùng',
  seller: 'Người bán',
  admin: 'Quản trị viên'
})[role] || 'Không xác định'

const formatDate = (dateStr) => {
  if (!dateStr) return '–'
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text
}

// Lấy số lượng thông báo
const fetchNotificationCounts = async () => {
  try {
    const response = await secureFetch(`${apiBase}/notifications`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('access_token') || useCookie('access_token')?.value}`
      },
      cache: 'no-store'
    }, ['admin'])
    const allNotifications = response.data?.data || response.data || []
    totalNotifications.value = response.data?.total || allNotifications.length
    sentNotifications.value = allNotifications.filter(n => n.status === 'sent').length
    draftNotifications.value = allNotifications.filter(n => n.status === 'draft').length
  } catch (error) {
    console.error('Lỗi khi lấy số lượng thông báo:', error)
    showNotificationMessage('Có lỗi khi tải số lượng thông báo', 'error')
  }
}

// Lấy danh sách thông báo
const fetchNotifications = async (page = 1) => {
  try {
    loading.value = true
    currentPage.value = page
    const queryParams = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.value.toString(),
      ...(filterStatus.value && { status: filterStatus.value }),
      ...(filterDraft.value && { status: filterDraft.value }),
      ...(filterType.value && { type: filterType.value }),
      ...(filterRole.value && { to_roles: filterRole.value }),
      ...(filterChannel.value && { channels: filterChannel.value }),
      ...(searchQuery.value && { search: searchQuery.value })
    })
    const endpoint = `${apiBase}/notifications?${queryParams.toString()}`
    const data = await secureFetch(endpoint, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('access_token') || useCookie('access_token')?.value}`
      },
      cache: 'no-store'
    }, ['admin'])
    notifications.value = data.data?.data || data.data || []
    lastPage.value = Math.ceil((data.data?.total || notifications.value.length) / perPage.value)
    currentPage.value = data.data?.current_page || page
    if (!notifications.value.length) {
      showNotificationMessage(filterDraft.value === 'draft' ? 'Không có thông báo nháp' : 'Không có thông báo', 'info')
    }
    await fetchNotificationCounts()
  } catch (error) {
    console.error('Lỗi khi lấy thông báo:', error)
    showNotificationMessage(`Có lỗi khi tải thông báo: ${error.message}`, 'error')
    notifications.value = []
  } finally {
    loading.value = false
  }
}

// Lọc và phân trang thông báo
const filteredNotifications = computed(() => {
  let result = [...notifications.value]
  if (filterDraft.value === 'draft') {
    result = result.filter(n => n.status === 'draft')
  } else if (filterStatus.value === 'sent') {
    result = result.filter(n => n.status === 'sent')
  }
  if (filterType.value) {
    result = result.filter(n => n.type === filterType.value)
  }
  if (filterRole.value) {
    result = result.filter(n => n.to_roles?.includes(filterRole.value))
  }
  if (filterChannel.value) {
    result = result.filter(n => n.channels?.includes(filterChannel.value))
  }
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(n =>
      n.title?.toLowerCase().includes(query) ||
      n.content?.toLowerCase().includes(query)
    )
  }
  if (sortBy.value === 'newest') {
    result.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
  } else if (sortBy.value === 'oldest') {
    result.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0))
  }
  return result
})

const paginatedNotifications = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredNotifications.value.slice(start, end)
})

// Xóa một thông báo
const confirmDelete = async (id) => {
  const notification = notifications.value.find(n => n.id === id)
  if (!notification) {
    showNotificationMessage('Thông báo không tồn tại', 'error')
    return
  }
  showConfirmationDialog(
    'Xác nhận xóa thông báo',
    `Bạn có chắc chắn muốn xóa thông báo "${truncateText(notification.title, 30)}"?`,
    async () => {
      try {
        const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
        if (!token) throw new Error('Không tìm thấy token')
        const response = await secureFetch(`${apiBase}/notifications/${id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` },
          cache: 'no-store'
        }, ['admin'])
        showNotificationMessage(response.message || 'Xóa thông báo thành công', 'success')
        await fetchNotifications(currentPage.value)
      } catch (err) {
        console.error('Lỗi khi xóa thông báo:', err)
        showNotificationMessage(err.message || 'Xóa thông báo thất bại', 'error')
      }
    }
  )
}

// Xóa nhiều thông báo
const deleteSelected = async () => {
  if (selectedIds.value.length === 0) {
    showNotificationMessage('Vui lòng chọn ít nhất một thông báo', 'error')
    return
  }
  showConfirmationDialog(
    'Xác nhận xóa nhiều thông báo',
    `Bạn có chắc chắn muốn xóa ${selectedIds.value.length} thông báo đã chọn?`,
    async () => {
      try {
        loading.value = true
        const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
        if (!token) throw new Error('Không tìm thấy token')
        const response = await secureFetch(`${apiBase}/notifications/destroy-multiple`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`
          },
          body: JSON.stringify({ ids: selectedIds.value }),
          cache: 'no-store'
        }, ['admin'])
        showNotificationMessage(response.message || 'Xóa các thông báo thành công', 'success')
        selectedIds.value = []
        await fetchNotifications(currentPage.value)
      } catch (err) {
        console.error('Lỗi khi xóa nhiều thông báo:', err)
        showNotificationMessage(err.message || 'Xóa các thông báo thất bại', 'error')
      } finally {
        loading.value = false
      }
    }
  )
}

// Xóa tất cả thông báo
const deleteAll = async () => {
  if (loading.value) return
  showConfirmationDialog(
    'Xác nhận xóa tất cả thông báo',
    'Bạn có chắc chắn muốn xóa tất cả thông báo?',
    async () => {
      try {
        loading.value = true
        const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
        if (!token) throw new Error('Không tìm thấy token')
        const response = await secureFetch(`${apiBase}/notifications/destroy-all`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` },
          cache: 'no-store'
        }, ['admin'])
        showNotificationMessage(response.message || 'Xóa tất cả thông báo thành công', 'success')
        selectedIds.value = []
        await fetchNotifications(currentPage.value)
      } catch (err) {
        console.error('Lỗi khi xóa tất cả thông báo:', err)
        showNotificationMessage(err.message || 'Xóa tất cả thông báo thất bại', 'error')
      } finally {
        loading.value = false
      }
    }
  )
}

// Gửi các thông báo đã chọn
const sendSelected = async () => {
  if (selectedIds.value.length === 0) {
    showNotificationMessage('Vui lòng chọn ít nhất một thông báo', 'error')
    return
  }
  showConfirmationDialog(
    'Xác nhận gửi thông báo',
    `Bạn có chắc chắn muốn gửi ${selectedIds.value.length} thông báo đã chọn?`,
    async () => {
      try {
        loading.value = true
        const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
        if (!token) throw new Error('Không tìm thấy token')
        const response = await secureFetch(`${apiBase}/notifications/send-multiple`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`
          },
          body: JSON.stringify({ ids: selectedIds.value }),
          cache: 'no-store'
        }, ['admin'])
        showNotificationMessage(response.message || 'Gửi các thông báo thành công', 'success')
        selectedIds.value = []
        await fetchNotifications(currentPage.value)
      } catch (err) {
        console.error('Lỗi khi gửi các thông báo:', err)
        showNotificationMessage(err.message || 'Gửi các thông báo thất bại', 'error')
      } finally {
        loading.value = false
      }
    }
  )
}

// Gửi tất cả thông báo
const sendAllNotifications = async () => {
  showConfirmationDialog(
    'Xác nhận gửi tất cả thông báo',
    'Bạn có chắc chắn muốn gửi tất cả thông báo chưa gửi?',
    async () => {
      try {
        loading.value = true
        const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
        if (!token) throw new Error('Không tìm thấy token')
        const response = await secureFetch(`${apiBase}/notifications/send-all`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`
          },
          cache: 'no-store'
        }, ['admin'])
        showNotificationMessage(response.message || 'Gửi tất cả thông báo thành công', 'success')
        await fetchNotifications(currentPage.value)
      } catch (err) {
        console.error('Lỗi khi gửi tất cả thông báo:', err)
        showNotificationMessage(err.message || 'Gửi tất cả thông báo thất bại', 'error')
      } finally {
        loading.value = false
      }
    }
  )
}

// Áp dụng hành động hàng loạt
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedIds.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một thông báo', 'error')
    return
  }
  if (selectedAction.value === 'delete') {
    await deleteSelected()
  } else if (selectedAction.value === 'send') {
    await sendSelected()
  }
}

// Hiển thị dialog xác nhận
const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title
  confirmDialogMessage.value = message
  confirmAction.value = action
  showConfirmDialog.value = true
}

// Đóng dialog xác nhận
const closeConfirmDialog = () => {
  showConfirmDialog.value = false
  confirmAction.value = null
}

// Xử lý hành động xác nhận
const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value()
  }
  closeConfirmDialog()
}

onMounted(() => {
  fetchNotifications()
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
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
  transition: transform .5s, opacity 1s;
}
button:active::after {
  transform: scale(0, 0);
  opacity: .2;
  transition: 0s;
}

/* Đảm bảo container không ẩn toast */
.bg-gray-100 {
  overflow: visible !important;
}
</style>