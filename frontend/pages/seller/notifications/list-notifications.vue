
<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Thông báo của bạn</h1>
        <!-- Menu dropdown -->
        <div class="relative inline-block text-left" @click.stop="toggleDropdown">
          <button
            type="button"
            class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
            aria-label="Mở menu tùy chọn thông báo"
          >
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
              <path
                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
              />
            </svg>
          </button>
          <div
            v-if="showDropdown"
            class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
          >
            <div class="py-1">
              <a
                href="#"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click.prevent="markAllAsRead"
                aria-label="Đánh dấu đọc tất cả thông báo"
              >Đánh dấu đọc tất cả</a>
              <a
                href="#"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click.prevent="deleteAllNotifications"
                aria-label="Xóa tất cả thông báo"
              >Xóa tất cả thông báo</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-semibold">Tổng số thông báo: {{ totalNotifications }}</span>
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

      <!-- Loading indicator -->
      <div v-if="loading" class="text-center py-4">
        <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </div>

      <!-- Checkbox chọn tất cả -->
      <div
        v-if="!loading && paginatedNotifications.length > 0"
        class="flex items-center gap-2 px-6 py-3 bg-gray-50 border-b border-gray-200"
      >
        <input
          type="checkbox"
          class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
          v-model="selectAll"
          @change="toggleSelectAll"
          aria-label="Chọn tất cả thông báo"
        />
        <label class="text-sm text-gray-700">Chọn tất cả</label>
      </div>

      <!-- Khi có thông báo đã chọn -->
      <div
        v-if="!loading && selectedNotificationIds.length > 0"
        class="flex justify-between items-center px-6 py-3 bg-gray-50 border-b"
      >
        <p class="text-sm text-gray-700">Đã chọn {{ selectedNotificationIds.length }} thông báo</p>
        <div class="flex gap-2">
          <button
            @click="markSelectedAsRead"
            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 text-sm rounded"
            aria-label="Đánh dấu các thông báo đã chọn là đã đọc"
          >Đánh dấu đã đọc</button>
          <button
            @click="deleteSelectedNotifications"
            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 text-sm rounded"
            aria-label="Xóa các thông báo đã chọn"
          >Xóa</button>
        </div>
      </div>

      <!-- Notification Table -->
      <table v-if="!loading" class="min-w-full border-collapse border border-gray-300 text-sm mt-4 bg-white">
        <thead>
          <tr>
            <th class="border px-3 py-2 text-left font-semibold">Chọn</th>
            <th class="border px-3 py-2 text-left font-semibold">ID</th>
            <th class="border px-3 py-2 text-left font-semibold">Tiêu đề</th>
            <th class="border px-3 py-2 text-left font-semibold">Ảnh</th>
            <th class="border px-3 py-2 text-left font-semibold">Loại</th>
            <th class="border px-3 py-2 text-left font-semibold">Ngày gửi</th>
            <th class="border px-3 py-2 text-left font-semibold">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in paginatedNotifications" :key="item.id" class="border-b" :class="{ 'bg-gray-50 text-gray-500 opacity-75': item.is_read === 1, 'bg-white text-gray-900': item.is_read === 0 }">
            <td class="px-3 py-2">
              <input
                type="checkbox"
                v-model="selectedNotificationIds"
                :value="item.id"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                :aria-label="`Chọn thông báo: ${item.title}`"
              />
            </td>
            <td class="px-3 py-2">#{{ item.id }}</td>
            <td class="px-3 py-2 font-semibold text-blue-700 hover:underline cursor-pointer" :class="{ 'font-bold': item.is_read === 0 }" @click="viewNotification(item.id)">
              {{ truncateText(item.title, 30) }}
              <span v-if="item.is_read === 0" class="ml-2 w-2 h-2 rounded-full bg-blue-500 inline-block" title="Chưa đọc"></span>
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
            <td class="px-3 py-2">{{ formatDate(item.sent_at) }}</td>
            <td class="px-3 py-2 relative">
              <button
                @click="toggleItemDropdown(item.id, $event)"
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
            <td colspan="7" class="text-center py-4 text-gray-500">Không có thông báo nào</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <Pagination v-if="!loading" :currentPage="currentPage" :lastPage="lastPage" @change="fetchNotifications" />

      <!-- Item Dropdown -->
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
                  v-if="notifications.find(item => item.id === activeDropdown)?.is_read === 0"
                  @click="markAsRead(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg> Đánh dấu đã đọc
                </button>
                <button
                  @click="deleteNotification(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg> Xóa
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter, useRuntimeConfig, useCookie } from '#app'
import { Eye } from 'lucide-vue-next'
import { secureFetch } from '@/utils/secureFetch'
import Pagination from '~/components/Pagination.vue'
import Swal from 'sweetalert2'

definePageMeta({ layout: 'default-seller' })

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl || 'http://localhost:8000/api'
const mediaBase = config.public.mediaBaseUrl || ''
const router = useRouter()

const notifications = ref([])
const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' })
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(10)
const sortBy = ref('newest')
const filterType = ref('')
const searchQuery = ref('')
const totalNotifications = ref(0)
const userId = ref(null)
const loading = ref(true) // Khởi tạo loading là true để hiển thị spinner ngay khi vào trang
const showDropdown = ref(false)
const selectedNotificationIds = ref([])
const selectAll = ref(false)

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

// Toggle dropdown cho item
const toggleItemDropdown = (id, event) => {
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

// Toggle dropdown cho menu chính
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

// Đóng dropdown
const closeDropdown = (event = null) => {
  if (event && (!event.target.closest('.relative') && !event.target.closest('.absolute') && !event.target.closest('.relative.inline-block'))) {
    activeDropdown.value = null
    showDropdown.value = false
  } else if (!event) {
    activeDropdown.value = null
    showDropdown.value = false
  }
}

// Xem chi tiết thông báo
const viewNotification = async (id) => {
  try {
    const notification = notifications.value.find(item => item.id === id)
    if (!notification) throw new Error('Không tìm thấy thông báo')

    if (notification.is_read === 0) {
      await markAsRead(id, false) // Không hiển thị confirm khi xem
    }
    router.push(`/seller/notifications/view/${id}`)
  } catch (error) {
    console.error('Lỗi khi xem thông báo:', error)
    showNotificationMessage(`Không thể xem thông báo: ${error.message}`, 'error')
  }
}

// Đánh dấu một thông báo là đã đọc
const markAsRead = async (id, showConfirm = true) => {
  if (showConfirm) {
    const confirm = await Swal.fire({
      title: 'Đánh dấu đã đọc?',
      text: 'Bạn có chắc chắn muốn đánh dấu thông báo này là đã đọc?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Đánh dấu',
      cancelButtonText: 'Hủy',
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#6b7280'
    })

    if (!confirm.isConfirmed) return
  }

  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    const response = await secureFetch(`${apiBase}/seller/notifications/${id}/read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      cache: 'no-store'
    }, ['seller'])

    if (response.success) {
      notifications.value = notifications.value.map(item => 
        item.id === id ? { ...item, is_read: 1 } : item
      )
      showNotificationMessage('Đã đánh dấu thông báo là đã đọc', 'success')
    } else {
      throw new Error(response.message || 'Không thể đánh dấu đã đọc')
    }
  } catch (error) {
    console.error('Lỗi khi đánh dấu đã đọc:', error)
    showNotificationMessage(`Không thể đánh dấu đã đọc: ${error.message}`, 'error')
  }
}

// Đánh dấu các thông báo đã chọn là đã đọc
const markSelectedAsRead = async () => {
  if (selectedNotificationIds.value.length === 0) {
    showNotificationMessage('Vui lòng chọn ít nhất một thông báo', 'error')
    return
  }

  const confirm = await Swal.fire({
    title: 'Đánh dấu đã đọc?',
    text: `Bạn có chắc chắn muốn đánh dấu ${selectedNotificationIds.value.length} thông báo là đã đọc?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Đánh dấu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    const response = await secureFetch(`${apiBase}/seller/notifications/mark-multiple-read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify({ ids: selectedNotificationIds.value }),
      cache: 'no-store'
    }, ['seller'])

    if (response.success) {
      notifications.value = notifications.value.map(item => 
        selectedNotificationIds.value.includes(item.id) ? { ...item, is_read: 1 } : item
      )
      selectedNotificationIds.value = []
      selectAll.value = false
      showNotificationMessage('Đã đánh dấu các thông báo đã chọn là đã đọc', 'success')
    } else {
      throw new Error(response.message || 'Không thể đánh dấu đã đọc')
    }
  } catch (error) {
    console.error('Lỗi khi đánh dấu các thông báo đã đọc:', error)
    showNotificationMessage(`Không thể đánh dấu đã đọc: ${error.message}`, 'error')
  }
}

// Đánh dấu tất cả thông báo là đã đọc
const markAllAsRead = async () => {
  if (notifications.value.length === 0) {
    showNotificationMessage('Không có thông báo nào để đánh dấu', 'info')
    return
  }

  const confirm = await Swal.fire({
    title: 'Đánh dấu đọc tất cả?',
    text: 'Bạn có chắc chắn muốn đánh dấu tất cả thông báo là đã đọc?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Đánh dấu',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    const response = await secureFetch(`${apiBase}/seller/notifications/mark-all-read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      cache: 'no-store'
    }, ['seller'])

    if (response.success) {
      notifications.value = notifications.value.map(item => ({ ...item, is_read: 1 }))
      selectedNotificationIds.value = []
      selectAll.value = false
      showNotificationMessage('Đã đánh dấu tất cả thông báo là đã đọc', 'success')
    } else {
      throw new Error(response.message || 'Không thể đánh dấu tất cả đã đọc')
    }
  } catch (error) {
    console.error('Lỗi khi đánh dấu tất cả đã đọc:', error)
    showNotificationMessage(`Không thể đánh dấu tất cả đã đọc: ${error.message}`, 'error')
  }
}

// Xóa một thông báo
const deleteNotification = async (id) => {
  const confirm = await Swal.fire({
    title: 'Xóa thông báo?',
    text: 'Bạn có chắc chắn muốn xóa thông báo này?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    const response = await secureFetch(`${apiBase}/seller/notifications/delete-multiple`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify({ ids: [id] }),
      cache: 'no-store'
    }, ['seller'])

    if (response.success) {
      notifications.value = notifications.value.filter(item => item.id !== id)
      selectedNotificationIds.value = selectedNotificationIds.value.filter(itemId => itemId !== id)
      totalNotifications.value = notifications.value.length
      showNotificationMessage('Đã xóa thông báo', 'success')
    } else {
      throw new Error(response.message || 'Không thể xóa thông báo')
    }
  } catch (error) {
    console.error('Lỗi khi xóa thông báo:', error)
    showNotificationMessage(`Không thể xóa thông báo: ${error.message}`, 'error')
  }
}

// Xóa các thông báo đã chọn
const deleteSelectedNotifications = async () => {
  if (selectedNotificationIds.value.length === 0) {
    showNotificationMessage('Vui lòng chọn ít nhất một thông báo', 'error')
    return
  }

  const confirm = await Swal.fire({
    title: 'Xóa thông báo?',
    text: `Bạn có chắc chắn muốn xóa ${selectedNotificationIds.value.length} thông báo đã chọn?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    const response = await secureFetch(`${apiBase}/seller/notifications/delete-multiple`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify({ ids: selectedNotificationIds.value }),
      cache: 'no-store'
    }, ['seller'])

    if (response.success) {
      notifications.value = notifications.value.filter(
        item => !selectedNotificationIds.value.includes(item.id)
      )
      totalNotifications.value = notifications.value.length
      selectedNotificationIds.value = []
      selectAll.value = false
      showNotificationMessage('Đã xóa các thông báo đã chọn', 'success')
    } else {
      throw new Error(response.message || 'Không thể xóa các thông báo')
    }
  } catch (error) {
    console.error('Lỗi khi xóa các thông báo:', error)
    showNotificationMessage(`Không thể xóa các thông báo: ${error.message}`, 'error')
  }
}

// Xóa tất cả thông báo
const deleteAllNotifications = async () => {
  if (notifications.value.length === 0) {
    showNotificationMessage('Không có thông báo nào để xóa', 'info')
    return
  }

  const confirm = await Swal.fire({
    title: 'Xóa tất cả thông báo?',
    text: 'Bạn có chắc chắn muốn xóa tất cả thông báo?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    const response = await secureFetch(`${apiBase}/seller/notifications/delete-all`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      cache: 'no-store'
    }, ['seller'])

    if (response.success) {
      notifications.value = []
      totalNotifications.value = 0
      selectedNotificationIds.value = []
      selectAll.value = false
      showNotificationMessage('Đã xóa tất cả thông báo', 'success')
    } else {
      throw new Error(response.message || 'Không thể xóa tất cả thông báo')
    }
  } catch (error) {
    console.error('Lỗi khi xóa tất cả thông báo:', error)
    showNotificationMessage(`Không thể xóa tất cả thông báo: ${error.message}`, 'error')
  }
}

// Chọn tất cả thông báo
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedNotificationIds.value = paginatedNotifications.value.map(item => item.id)
  } else {
    selectedNotificationIds.value = []
  }
}

const typeLabel = (type) => ({
  order: 'Đơn hàng',
  promotion: 'Khuyến mãi',
  message: 'Tin nhắn',
  system: 'Hệ thống'
})[type] || 'Không xác định'

const formatDate = (dateStr) => {
  if (!dateStr) return '–'
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'Asia/Ho_Chi_Minh'
  })
}

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text
}

// Lấy user_id từ token hoặc API
const fetchUserId = async () => {
  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Không tìm thấy token')
    const response = await secureFetch(`${apiBase}/me`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      cache: 'no-store'
    }, ['seller'])
    userId.value = response.data?.id
    console.log('User ID:', userId.value)
  } catch (error) {
    console.error('Lỗi khi lấy user_id:', error)
    showNotificationMessage('Không thể xác định người dùng. Vui lòng đăng nhập lại.', 'error')
    router.push('/login')
  }
}

// Lấy số lượng thông báo
const fetchNotificationCounts = async () => {
  try {
    const queryParams = new URLSearchParams({
      per_page: '1' // Chỉ cần lấy tổng số
    })
    const response = await secureFetch(`${apiBase}/seller/notifications?${queryParams.toString()}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('access_token') || useCookie('access_token')?.value}`
      },
      cache: 'no-store'
    }, ['seller'])
    totalNotifications.value = response.data?.total || 0
    console.log('Total Notifications:', totalNotifications.value)
  } catch (error) {
    console.error('Lỗi khi lấy số lượng thông báo:', error)
    showNotificationMessage('Có lỗi khi tải số lượng thông báo', 'error')
  }
}

// Lấy danh sách thông báo
const fetchNotifications = async (page = 1) => {
  if (!userId.value) await fetchUserId()
  if (!userId.value) return

  try {
    loading.value = true
    currentPage.value = page
    const queryParams = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.value.toString(),
      ...(filterType.value && { type: filterType.value }),
      ...(searchQuery.value && { search: searchQuery.value }),
      sort_order: sortBy.value === 'newest' ? 'desc' : 'asc'
    })
    const endpoint = `${apiBase}/seller/notifications?${queryParams.toString()}`
    const data = await secureFetch(endpoint, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('access_token') || useCookie('access_token')?.value}`
      },
      cache: 'no-store'
    }, ['seller'])
    console.log('API Response:', JSON.stringify(data, null, 2))
    notifications.value = data.data?.data || data.data || []
    lastPage.value = data.data?.last_page || Math.ceil((data.data?.total || notifications.value.length) / perPage.value)
    currentPage.value = data.data?.current_page || page
    totalNotifications.value = data.data?.total || notifications.value.length
    if (!notifications.value.length) {
      showNotificationMessage('Không có thông báo nào', 'info')
    }
  } catch (error) {
    console.error('Lỗi khi lấy thông báo:', error, error.response)
    showNotificationMessage(`Có lỗi khi tải thông báo: ${error.message}`, 'error')
    notifications.value = []
  } finally {
    loading.value = false
  }
}

// Lọc và phân trang thông báo
const filteredNotifications = computed(() => {
  let result = [...notifications.value]
  if (filterType.value) {
    result = result.filter(n => n.type === filterType.value)
  }
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(n =>
      n.title?.toLowerCase().includes(query) ||
      n.content?.toLowerCase().includes(query)
    )
  }
  if (sortBy.value === 'newest') {
    result.sort((a, b) => new Date(b.sent_at || 0) - new Date(a.sent_at || 0))
  } else if (sortBy.value === 'oldest') {
    result.sort((a, b) => new Date(a.sent_at || 0) - new Date(b.sent_at || 0))
  }
  return result
})

const paginatedNotifications = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredNotifications.value.slice(start, end)
})

onMounted(() => {
  fetchNotifications()
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
  clearTimeout(notificationTimeout)
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

.bg-gray-100 {
  overflow: visible !important;
}

/* Đảm bảo chấm xanh và chữ đậm hiển thị rõ ràng */
.bg-white.text-gray-900 {
  background-color: #fff !important;
}
.font-bold {
  font-weight: 700 !important;
}
.bg-gray-50.text-gray-500 {
  background-color: #f9fafb !important;
  color: #6b7280 !important;
}
.bg-blue-500 {
  background-color: #3b82f6 !important;
}

/* Style cho loading indicator */
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
