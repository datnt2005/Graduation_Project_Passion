<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Thông báo của bạn</h1>
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

      <!-- Notification Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm mt-4 bg-white">
        <thead>
          <tr>
            <th class="border px-3 py-2 text-left font-semibold">ID</th>
            <th class="border px-3 py-2 text-left font-semibold">Tiêu đề</th>
            <th class="border px-3 py-2 text-left font-semibold">Ảnh</th>
            <th class="border px-3 py-2 text-left font-semibold">Loại</th>
            <th class="border px-3 py-2 text-left font-semibold">Ngày gửi</th>
            <th class="border px-3 py-2 text-left font-semibold">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in paginatedNotifications" :key="item.id" class="border-b">
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
            <td class="px-3 py-2">{{ formatDate(item.sent_at) }}</td>
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
            <td colspan="6" class="text-center py-4 text-gray-500">Không có thông báo nào</td>
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
const loading = ref(false)

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

// Toggle dropdown
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
  router.push(`/seller/notifications/view/${id}`)
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
      to_roles: 'seller',
      user_id: userId.value
    })
    const response = await secureFetch(`${apiBase}/notifications?${queryParams.toString()}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('access_token') || useCookie('access_token')?.value}`
      },
      cache: 'no-store'
    }, ['seller'])
    const allNotifications = response.data?.data || response.data || []
    totalNotifications.value = response.data?.total || allNotifications.length
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
      to_roles: 'seller',
      user_id: userId.value,
      ...(filterType.value && { type: filterType.value }),
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
    }, ['seller'])
    notifications.value = data.data?.data || data.data || []
    lastPage.value = Math.ceil((data.data?.total || notifications.value.length) / perPage.value)
    currentPage.value = data.data?.current_page || page
    if (!notifications.value.length) {
      showNotificationMessage('Không có thông báo nào', 'info')
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
</style>