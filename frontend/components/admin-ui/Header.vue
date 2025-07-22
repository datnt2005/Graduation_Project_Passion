<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { secureFetch } from '@/utils/secureFetch'

const emit = defineEmits(['toggle-sidebar'])
const hasNewNotifications = ref(false)
const notifications = ref([])
const isNotificationOpen = ref(false)
const router = useRouter()
let interval // Declare interval variable

// LocalStorage key cho notifications đã đọc
const STORAGE_KEY = 'admin_read_notifications'
const STORAGE_EXPIRY_DAYS = 30  

// Utility functions cho localStorage
const getReadNotificationsFromStorage = () => {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (!stored) return {}
    
    const data = JSON.parse(stored)
    const now = Date.now()
    
    // Xóa các entry đã hết hạn
    const filtered = Object.entries(data).reduce((acc, [id, timestamp]) => {
      if (now - timestamp < STORAGE_EXPIRY_DAYS * 24 * 60 * 60 * 1000) {
        acc[id] = timestamp
      }
      return acc
    }, {})
    
    // Cập nhật lại localStorage nếu có thay đổi
    if (Object.keys(filtered).length !== Object.keys(data).length) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(filtered))
    }
    
    return filtered
  } catch (error) {
    console.error('Error reading from localStorage:', error)
    return {}
  }
}

const saveReadNotificationToStorage = (notificationId) => {
  try {
    const readNotifications = getReadNotificationsFromStorage()
    readNotifications[notificationId] = Date.now()
    localStorage.setItem(STORAGE_KEY, JSON.stringify(readNotifications))
  } catch (error) {
    console.error('Error saving to localStorage:', error)
  }
}

const removeReadNotificationFromStorage = (notificationId) => {
  try {
    const readNotifications = getReadNotificationsFromStorage()
    delete readNotifications[notificationId]
    localStorage.setItem(STORAGE_KEY, JSON.stringify(readNotifications))
  } catch (error) {
    console.error('Error removing from localStorage:', error)
  }
}

// Merge server data với localStorage data
const mergeNotificationsWithLocalStorage = (serverNotifications) => {
  const readNotifications = getReadNotificationsFromStorage()
  
  return serverNotifications.map(notification => ({
    ...notification,
    read: notification.read || !!readNotifications[notification.id]
  }))
}

// Fetch notifications
const fetchNotifications = async () => {
  try {
    const response = await secureFetch('http://localhost:8000/api/admin/notifications/system', {}, ['admin'])
    const serverNotifications = response?.data || []
    
    // Merge với localStorage data
    notifications.value = mergeNotificationsWithLocalStorage(serverNotifications)
    hasNewNotifications.value = notifications.value.some(n => !n.read)
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

// Toggle notification popup
const toggleNotifications = () => {
  isNotificationOpen.value = !isNotificationOpen.value
}

// Mark all as read
const markAllAsRead = async () => {
  try {
    // Đánh dấu tất cả trong localStorage
    notifications.value.forEach(notification => {
      if (!notification.read) {
        saveReadNotificationToStorage(notification.id)
      }
    })
    
    // Cập nhật state
    notifications.value = notifications.value.map(n => ({ ...n, read: true }))
    hasNewNotifications.value = false
    
    // Gọi API server
    await secureFetch('http://localhost:8000/api/admin/notifications/mark-read', {
      method: 'POST'
    }, ['admin'])
  } catch (error) {
    console.error('Failed to mark notifications as read:', error)
    // Có thể rollback localStorage nếu cần
  }
}

// Handle notification click
const handleNotificationClick = async (notification) => {
  // Đánh dấu đã đọc ngay lập tức (optimistic update)
  const notificationIndex = notifications.value.findIndex(n => n.id === notification.id)
  if (notificationIndex !== -1 && !notifications.value[notificationIndex].read) {
    // Cập nhật localStorage
    saveReadNotificationToStorage(notification.id)
    
    // Cập nhật state
    notifications.value[notificationIndex].read = true
    hasNewNotifications.value = notifications.value.some(n => !n.read)
    
    // Gọi API để đánh dấu đã đọc trên server (background)
    try {
      await secureFetch(`http://localhost:8000/api/admin/notifications/${notification.id}/read`, {
        method: 'POST'
      }, ['admin'])
    } catch (error) {
      console.error('Failed to mark notification as read on server:', error)
      // Không rollback localStorage vì user đã đọc rồi
    }
  }
  
  // Navigate nếu có link
  if (notification.link) {
    router.push(notification.link)
  }
  
  // Đóng dropdown
  isNotificationOpen.value = false
}

// Đánh dấu một notification cụ thể là đã đọc
const markAsRead = async (notification, event) => {
  // Prevent event bubbling
  event.stopPropagation()
  
  const notificationIndex = notifications.value.findIndex(n => n.id === notification.id)
  if (notificationIndex !== -1 && !notifications.value[notificationIndex].read) {
    // Cập nhật localStorage
    saveReadNotificationToStorage(notification.id)
    
    // Optimistic update
    notifications.value[notificationIndex].read = true
    hasNewNotifications.value = notifications.value.some(n => !n.read)
    
    // Gọi API server (background)
    try {
      await secureFetch(`http://localhost:8000/api/admin/notifications/${notification.id}/read`, {
        method: 'POST'
      }, ['admin'])
    } catch (error) {
      console.error('Failed to mark notification as read on server:', error)
      // Không rollback localStorage
    }
  }
}

// Thêm function để đánh dấu chưa đọc (nếu cần)
const markAsUnread = async (notification, event) => {
  event.stopPropagation()
  
  const notificationIndex = notifications.value.findIndex(n => n.id === notification.id)
  if (notificationIndex !== -1 && notifications.value[notificationIndex].read) {
    // Xóa khỏi localStorage
    removeReadNotificationFromStorage(notification.id)
    
    // Cập nhật state
    notifications.value[notificationIndex].read = false
    hasNewNotifications.value = notifications.value.some(n => !n.read)
    
    // Gọi API server (nếu có endpoint)
    try {
      await secureFetch(`http://localhost:8000/api/admin/notifications/${notification.id}/unread`, {
        method: 'POST'
      }, ['admin'])
    } catch (error) {
      console.error('Failed to mark notification as unread on server:', error)
    }
  }
}

// Close notifications when clicking outside
const closeNotifications = (e) => {
  if (!e.target.closest('.notification-container')) {
    isNotificationOpen.value = false
  }
}

// Format date
const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now - date) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'Vừa xong'
  if (diffInMinutes < 60) return `${diffInMinutes} phút trước`
  if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} giờ trước`
  return `${Math.floor(diffInMinutes / 1440)} ngày trước`
}

// Cleanup localStorage định kỳ
const cleanupOldNotifications = () => {
  try {
    const readNotifications = getReadNotificationsFromStorage()
    const now = Date.now()
    const maxAge = STORAGE_EXPIRY_DAYS * 24 * 60 * 60 * 1000
    
    const cleaned = Object.entries(readNotifications).reduce((acc, [id, timestamp]) => {
      if (now - timestamp < maxAge) {
        acc[id] = timestamp
      }
      return acc
    }, {})
    
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cleaned))
  } catch (error) {
    console.error('Error cleaning up localStorage:', error)
  }
}

// Lifecycle
onMounted(() => {
  // Cleanup localStorage cũ
  cleanupOldNotifications()
  
  // Fetch notifications
  fetchNotifications()
  
  // Set interval để fetch định kỳ
  interval = setInterval(fetchNotifications, 30000) // Assign interval variable
  
  // Add click outside listener
  document.addEventListener('click', closeNotifications)
})

onUnmounted(() => {
  clearInterval(interval)
  document.removeEventListener('click', closeNotifications)
})
</script>

<template>
  <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-4 sticky top-0 z-40 backdrop-blur-sm bg-white/95">
    <div class="flex items-center justify-between">
      <!-- Left Section -->
      <div class="flex items-center gap-4">
        <!-- Mobile Sidebar Toggle -->
        <button
          @click="$emit('toggle-sidebar')"
          class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
          aria-label="Toggle Sidebar"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- Logo/Title -->
        <div class="hidden lg:flex items-center gap-3">
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <h1 class="text-xl font-semibold text-gray-900">Trang quản trị</h1>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-3">
        <!-- Notifications -->
        <div class="relative notification-container">
          <button
            @click="toggleNotifications"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
            aria-label="Notifications"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H7a2 2 0 01-2-2V7a2 2 0 012-2h4m0 14v-2.5" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19c0 1.105-.895 2-2 2s-2-.895-2-2 .895-2 2-2 2 .895 2 2zM9 7V5a2 2 0 114 0v2M9 7a2 2 0 104 0M9 7h4" />
            </svg>
            
            <!-- Notification Badge -->
            <span
              v-if="hasNewNotifications"
              class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full animate-pulse"
            >
              {{ notifications.filter(n => !n.read).length }}
            </span>
          </button>

          <!-- Notifications Dropdown -->
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95 translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-1"
          >
            <div
              v-if="isNotificationOpen"
              class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden z-50"
            >
              <!-- Header -->
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-gray-900">Thông báo</h3>
                  <button
                    v-if="hasNewNotifications"
                    @click="markAllAsRead"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200"
                  >
                    Đánh dấu tất cả đã đọc
                  </button>
                </div>
              </div>

              <!-- Notifications List -->
              <div class="max-h-80 overflow-y-auto">
                <div v-if="notifications.length > 0" class="divide-y divide-gray-100">
                  <div
                    v-for="notification in notifications.slice(0, 10)"
                    :key="notification.id"
                    @click="handleNotificationClick(notification)"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors duration-200 group relative"
                    :class="{ 'bg-blue-50': !notification.read }"
                  >
                    <div class="flex items-start gap-3">
                      <!-- Icon -->
                      <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                      
                      <!-- Content -->
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors duration-200">
                          {{ notification.title }}
                        </p>
                        <p v-if="notification.message" class="text-sm text-gray-600 mt-1 line-clamp-2">
                          {{ notification.message }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                          {{ formatDate(notification.created_at) }}
                        </p>
                      </div>
                      
                      <!-- Actions -->
                      <div class="flex-shrink-0 flex items-center gap-2">
                        <!-- Mark as read/unread button -->
                        <button
                          v-if="!notification.read"
                          @click="markAsRead(notification, $event)"
                          class="opacity-0 group-hover:opacity-100 w-6 h-6 rounded-full bg-blue-100 hover:bg-blue-200 flex items-center justify-center transition-all duration-200"
                          title="Đánh dấu đã đọc"
                        >
                          <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                        </button>
                        
                        <button
                          v-else
                          @click="markAsUnread(notification, $event)"
                          class="opacity-0 group-hover:opacity-100 w-6 h-6 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-all duration-200"
                          title="Đánh dấu chưa đọc"
                        >
                          <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636" />
                          </svg>
                        </button>
                        
                        <!-- Read/Unread indicator -->
                        <div 
                          v-if="!notification.read" 
                          class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"
                          title="Chưa đọc"
                        ></div>
                        <div 
                          v-else 
                          class="w-2 h-2 bg-gray-300 rounded-full"
                          title="Đã đọc"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Empty State -->
                <div v-else class="px-4 py-8 text-center">
                  <svg class="mx-auto w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 00-.707.293h-3.172a1 1 0 00-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <p class="text-sm text-gray-500">Không có thông báo mới</p>
                </div>
              </div>

              <!-- Footer -->
              <div v-if="notifications.length > 10" class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                <button class="w-full text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                  Xem tất cả thông báo
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <!-- User Profile -->
        <div class="flex items-center gap-3 pl-3 border-l border-gray-200">
          <div class="relative">
            <img
              src="https://i.pravatar.cc/40"
              alt="Avatar"
              class="w-9 h-9 rounded-full border-2 border-gray-200 hover:border-blue-300 transition-colors duration-200"
            />
            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
          </div>
          <div class="hidden sm:block">
            <p class="text-sm font-medium text-gray-900">Admin</p>
            <p class="text-xs text-gray-500">Quản trị viên</p>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
