<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200 sticky top-0 z-10">
        <h1 class="text-xl font-semibold text-gray-800">Thông báo</h1>
        <div class="relative inline-block text-left">
          <button
            @click.stop="toggleDropdown"
            class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
          >
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-4 w-4 mr-1" 
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
            Tùy chọn
          </button>
          <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
          >
            <div
              v-if="showDropdown"
              class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
            >
              <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                <button
                  @click="markAllAsRead"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                  role="menuitem"
                >
                  <svg 
                    class="w-4 h-4 mr-2" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24" 
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Đánh dấu đọc tất cả
                </button>
                <button
                  @click="deleteAllNotifications"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                  role="menuitem"
                >
                  <svg 
                    class="w-4 h-4 mr-2" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24" 
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                  Xóa tất cả thông báo
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-b border-gray-200">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ totalNotifications }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
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
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Tìm kiếm thông báo..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-48 sm:w-64"
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

      <!-- Bulk Action Bar -->
      <div v-if="!loading && filteredNotifications.length > 0" class="bg-white px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-b border-gray-200 sticky top-[72px] z-10">
        <div class="flex items-center gap-2">
          <input
            type="checkbox"
            v-model="selectAll"
            @change="toggleSelectAll"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            aria-label="Chọn tất cả thông báo"
          />
          <span>{{ selectedNotificationIds.length }} được chọn</span>
        </div>
        <select
          v-model="selectedAction"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Hành động hàng loạt</option>
          <option value="read">Đánh dấu đã đọc</option>
          <option value="delete">Xóa</option>
        </select>
        <button
          @click="applyBulkAction"
          :disabled="!selectedAction || selectedNotificationIds.length === 0 || loading"
          :class="[
            'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
            (!selectedAction || selectedNotificationIds.length === 0 || loading) 
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-blue-600 text-white hover:bg-blue-700'
          ]"
        >
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
      </div>

      <!-- Loading Indicator -->
      <div v-if="loading" class="text-center py-8">
        <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </div>

      <!-- Notifications List -->
      <div v-if="!loading" class="py-4 space-y-4">
        <div
          v-for="item in paginatedNotifications"
          :key="item.id"
          :class="[
            'bg-white rounded-lg shadow-sm p-4 flex items-start space-x-4 transition-all duration-200 hover:shadow-md',
            item.is_hidden ? 'opacity-75' : 'bg-blue-50'
          ]"
        >
          <div class="flex-shrink-0">
            <input
              type="checkbox"
              v-model="selectedNotificationIds"
              :value="item.id"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              :aria-label="`Chọn thông báo: ${item.title}`"
              @click.stop
            />
          </div>
          <div class="flex-shrink-0" @click="openNotificationPopup(item)">
            <img
              v-if="item.image_url"
              :src="item.image_url.startsWith('http') ? item.image_url : `${mediaBase}${item.image_url}`"
              alt="Ảnh thông báo"
              class="w-12 h-12 object-cover rounded-md"
            />
            <div v-else class="w-12 h-12 bg-gray-200 rounded-md flex items-center justify-center">
              <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
          </div>
          <div class="flex-1" @click="openNotificationPopup(item)">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-semibold text-gray-900" :class="{ 'font-bold': !item.is_read }">
                {{ item.title }}
                <span v-if="!item.is_read" class="ml-2 w-2 h-2 rounded-full bg-blue-500 inline-block" title="Chưa đọc"></span>
              </h3>
              <div class="relative">
                <button
                  @click.stop="toggleItemDropdown(item.id, $event)"
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
              </div>
            </div>
            <p class="text-sm text-gray-600 mt-1">{{ truncateText(item.content, 100) }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ typeLabel(item.type) }} • {{ formatDate(item.sent_at) }}</p>
          </div>
        </div>
        <div v-if="paginatedNotifications.length === 0" class="text-center py-8 text-gray-500">
          Không có thông báo nào
        </div>
      </div>

      <!-- Pagination -->
      <Pagination v-if="!loading && paginatedNotifications.length > 0" :currentPage="currentPage" :lastPage="lastPage" @change="fetchNotifications" />

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
              v-for="item in notifications" 
              :key="item.id"
              v-show="activeDropdown === item.id"
              class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right"
              :style="dropdownPosition"
            >
              <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                <button
                  @click.stop="openNotificationPopup(item); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                  role="menuitem"
                >
                  <svg 
                    class="w-4 h-4 mr-2" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24" 
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                  Xem
                </button>
                <button
                  v-if="!item.is_read"
                  @click.stop="markAsRead(item.id); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                  role="menuitem"
                >
                  <svg 
                    class="w-4 h-4 mr-2" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24" 
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Đánh dấu đã đọc
                </button>
                <button
                  @click.stop="deleteNotification(item.id); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                  role="menuitem"
                >
                  <svg 
                    class="w-4 h-4 mr-2" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24" 
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                  Xóa
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
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-4"
        >
          <div
            v-if="selectedNotification"
            class="fixed inset-0 flex items-center justify-center z-50 p-2 bg-black bg-opacity-50"
            @click.self="closeNotificationPopup"
          >
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6 relative">
              <button
                @click="closeNotificationPopup"
                class="absolute top-2 right-4 text-gray-400 hover:text-gray-600"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
              <div v-if="selectedNotification.image_url" class="mb-4">
                <img
                  :src="selectedNotification.image_url.startsWith('http') ? selectedNotification.image_url : `${mediaBase}${selectedNotification.image_url}`"
                  alt="Ảnh thông báo"
                  class="w-full h-48 object-cover rounded-md"
                />
              </div>
              <h3 class="text-lg font-semibold text-gray-900">{{ selectedNotification.title }}</h3>
              <p class="text-sm text-gray-600 mt-2">{{ selectedNotification.content }}</p>
              <p class="text-xs text-gray-400 mt-4">{{ typeLabel(selectedNotification.type) }} • {{ formatDate(selectedNotification.sent_at) }}</p>
              <div class="mt-6 flex justify-end space-x-3">
                <button
                  v-if="!selectedNotification.is_read"
                  @click="markAsRead(selectedNotification.id)"
                  class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150"
                >
                  Đánh dấu đã đọc
                </button>
                <button
                  @click="deleteNotification(selectedNotification.id)"
                  class="px-4 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors duration-150"
                >
                  Xóa
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Toast Notification -->
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
            class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50"
          >
            <div class="flex-shrink-0">
              <svg
                class="h-6 w-6"
                :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
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
              <p class="text-sm font-medium text-gray-900">{{ notificationMessage }}</p>
            </div>
            <div class="flex-shrink-0">
              <button
                @click="showNotification = false"
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRuntimeConfig, useCookie } from '#app'
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
const loading = ref(true)
const showDropdown = ref(false)
const selectedNotification = ref(null)
const selectedNotificationIds = ref([])
const selectAll = ref(false)
const selectedAction = ref('')

let notificationTimeout = null

// Show notification toast
const showNotificationMessage = (message, type = 'success') => {
  clearTimeout(notificationTimeout)
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  notificationTimeout = setTimeout(() => {
    showNotification.value = false
  }, 3000)
}

// Toggle item dropdown
const toggleItemDropdown = (id, event) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null
    return
  }
  activeDropdown.value = id
  const button = event.target.closest('button')
  if (button) {
    const rect = button.getBoundingClientRect()
    dropdownPosition.value = {
      top: `${rect.bottom + window.scrollY + 8}px`,
      left: `${rect.right + window.scrollX - 192}px`,
      width: '192px'
    }
  }
}

// Toggle main menu dropdown
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

// Close dropdown
const closeDropdown = (event = null) => {
  if (event && (!event.target.closest('.relative') && !event.target.closest('.absolute') && !event.target.closest('.relative.inline-block'))) {
    activeDropdown.value = null
    showDropdown.value = false
  } else if (!event) {
    activeDropdown.value = null
    showDropdown.value = false
  }
}

// Open notification popup
const openNotificationPopup = async (notification) => {
  selectedNotification.value = notification
  if (!notification.is_read) {
    await markAsRead(notification.id, false)
  }
}

// Close notification popup
const closeNotificationPopup = () => {
  selectedNotification.value = null
}

// Mark single notification as read
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

    if (response.success !== false) {
      notifications.value = notifications.value.map(item => 
        item.id === id ? { ...item, is_read: 1 } : item
      )
      if (selectedNotification.value?.id === id) {
        selectedNotification.value = { ...selectedNotification.value, is_read: 1 }
      }
      showNotificationMessage('Đã đánh dấu thông báo là đã đọc', 'success')
    } else {
      throw new Error(response.message || 'Không thể đánh dấu đã đọc')
    }
  } catch (error) {
    console.error('Lỗi khi đánh dấu đã đọc:', error)
    showNotificationMessage(`Không thể đánh dấu đã đọc: ${error.message}`, 'error')
  }
}

// Mark selected notifications as read
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

    if (response.success !== false) {
      notifications.value = notifications.value.map(item => 
        selectedNotificationIds.value.includes(item.id) ? { ...item, is_read: 1 } : item
      )
      if (selectedNotification.value && selectedNotificationIds.value.includes(selectedNotification.value.id)) {
        selectedNotification.value = { ...selectedNotification.value, is_read: 1 }
      }
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

// Mark all notifications as read
const markAllAsRead = async () => {
  if (notifications.value.length === 0) {
    showNotificationMessage('Không có thông báo nào để đánh dấu', 'error')
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

    if (response.success !== false) {
      notifications.value = notifications.value.map(item => ({ ...item, is_read: 1 }))
      if (selectedNotification.value) {
        selectedNotification.value = { ...selectedNotification.value, is_read: 1 }
      }
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

// Delete single notification
const deleteNotification = async (id) => {
  const notification = notifications.value.find(item => item.id === id)
  if (!notification) {
    showNotificationMessage('Thông báo không tồn tại', 'error')
    return
  }
  if (notification.is_hidden) {
    showNotificationMessage('Thông báo này đã được ẩn', 'error')
    return
  }

  const confirm = await Swal.fire({
    title: 'Xóa thông báo?',
    text: `Bạn có chắc chắn muốn xóa thông báo "${notification.title}"?`,
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

    if (response.success !== false) {
      notifications.value = notifications.value.filter(item => item.id !== id)
      totalNotifications.value = notifications.value.length
      selectedNotificationIds.value = selectedNotificationIds.value.filter(itemId => itemId !== id)
      if (selectedNotification.value?.id === id) {
        selectedNotification.value = null
      }
      showNotificationMessage('Đã xóa thông báo', 'success')
    } else {
      throw new Error(response.message || 'Không thể xóa thông báo')
    }
  } catch (error) {
    console.error('Lỗi khi xóa thông báo:', error)
    let errorMessage = error.message || 'Không thể xóa thông báo'
    if (errorMessage.includes('Thông báo không tồn tại hoặc đã bị ẩn')) {
      errorMessage = 'Thông báo này đã được ẩn trước đó'
      notifications.value = notifications.value.filter(item => item.id !== id)
      totalNotifications.value = notifications.value.length
      selectedNotificationIds.value = selectedNotificationIds.value.filter(itemId => itemId !== id)
      if (selectedNotification.value?.id === id) {
        selectedNotification.value = null
      }
    }
    showNotificationMessage(errorMessage, 'error')
  }
}

// Delete selected notifications
const deleteSelectedNotifications = async () => {
  if (selectedNotificationIds.value.length === 0) {
    showNotificationMessage('Vui lòng chọn ít nhất một thông báo', 'error')
    return
  }

  const alreadyHidden = notifications.value.filter(
    item => selectedNotificationIds.value.includes(item.id) && item.is_hidden
  )
  if (alreadyHidden.length > 0) {
    showNotificationMessage('Một số thông báo đã được ẩn', 'error')
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

    if (response.success !== false) {
      notifications.value = notifications.value.filter(
        item => !selectedNotificationIds.value.includes(item.id)
      )
      totalNotifications.value = notifications.value.length
      if (selectedNotification.value && selectedNotificationIds.value.includes(selectedNotification.value.id)) {
        selectedNotification.value = null
      }
      selectedNotificationIds.value = []
      selectAll.value = false
      showNotificationMessage('Đã xóa các thông báo đã chọn', 'success')
    } else {
      throw new Error(response.message || 'Không thể xóa các thông báo')
    }
  } catch (error) {
    console.error('Lỗi khi xóa các thông báo:', error)
    let errorMessage = error.message || 'Không thể xóa các thông báo'
    if (errorMessage.includes('Đã ẩn các thông báo')) {
      errorMessage = 'Một số thông báo đã được ẩn trước đó'
      notifications.value = notifications.value.filter(
        item => !selectedNotificationIds.value.includes(item.id)
      )
      totalNotifications.value = notifications.value.length
      selectedNotificationIds.value = []
      selectAll.value = false
    }
    showNotificationMessage(errorMessage, 'error')
  }
}

// Delete all notifications
const deleteAllNotifications = async () => {
  if (notifications.value.length === 0) {
    showNotificationMessage('Không có thông báo nào để xóa', 'error')
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

    if (response.success !== false) {
      notifications.value = []
      totalNotifications.value = 0
      selectedNotificationIds.value = []
      selectAll.value = false
      selectedNotification.value = null
      showNotificationMessage('Đã xóa tất cả thông báo', 'success')
    } else {
      throw new Error(response.message || 'Không thể xóa tất cả thông báo')
    }
  } catch (error) {
    console.error('Lỗi khi xóa tất cả thông báo:', error)
    showNotificationMessage(`Không thể xóa tất cả thông báo: ${error.message}`, 'error')
  }
}

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedNotificationIds.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một thông báo', 'error')
    return
  }

  if (selectedAction.value === 'read') {
    await markSelectedAsRead()
  } else if (selectedAction.value === 'delete') {
    await deleteSelectedNotifications()
  }
  selectedAction.value = ''
}

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedNotificationIds.value = paginatedNotifications.value
      .filter(item => !item.is_hidden)
      .map(item => item.id)
  } else {
    selectedNotificationIds.value = []
  }
}

// Utility functions
const typeLabel = (type) => ({
  order: 'Đơn hàng',
  promotion: 'Khuyến mãi',
  message: 'Tin nhắn',
  system: 'Hệ thống'
})[type] || 'Không xác định'

const formatDate = (dateStr) => {
  if (!dateStr) return '–'
  const d = new Date(dateStr)
  return d.toLocaleString('vi-VN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    timeZone: 'Asia/Ho_Chi_Minh'
  })
}

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text
}

// Fetch user ID
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
  } catch (error) {
    console.error('Lỗi khi lấy user_id:', error)
    showNotificationMessage('Không thể xác định người dùng. Vui lòng đăng nhập lại.', 'error')
    router.push('/login')
  }
}

// Fetch notifications
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
    notifications.value = (data.data?.data || data.data || []).map(item => ({
      ...item,
      is_hidden: item.is_hidden || false // Ensure is_hidden is set
    }))
    lastPage.value = data.data?.last_page || Math.ceil((data.data?.total || notifications.value.length) / perPage.value)
    currentPage.value = data.data?.current_page || page
    totalNotifications.value = data.data?.total || notifications.value.length
    if (!notifications.value.length) {
      showNotificationMessage('Không có thông báo nào', 'error')
    }
  } catch (error) {
    console.error('Lỗi khi lấy thông báo:', error)
    showNotificationMessage(`Có lỗi khi tải thông báo: ${error.message}`, 'error')
    notifications.value = []
  } finally {
    loading.value = false
  }
}

// Filtered and paginated notifications
const filteredNotifications = computed(() => {
  let result = [...notifications.value].filter(n => !n.is_hidden)
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
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
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

.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>