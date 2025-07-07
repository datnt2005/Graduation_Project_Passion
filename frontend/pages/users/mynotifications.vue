<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="flex flex-col md:flex-row max-w-screen-2xl mx-auto px-4 sm:px-6 py-6 gap-6">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
        <div class="min-h-full">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-4">Thông báo của tôi</h2>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="flex border-b border-gray-200 relative">
              <button @click="activeTab = 'all'"
                :class="['flex-1 py-3 px-4 text-center text-sm font-medium', activeTab === 'all' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300']">
                Thông báo của tôi
              </button>

              <button @click="activeTab = 'promotion'"
                :class="['flex-1 py-3 px-4 text-center text-sm font-medium', activeTab === 'promotion' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300']">
                Khuyến mãi
              </button>

              <button @click="activeTab = 'order'"
                :class="['flex-1 py-3 px-4 text-center text-sm font-medium', activeTab === 'order' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300']">
                Cập nhật đơn hàng
              </button>

              <button @click="activeTab = 'history'"
                :class="['flex-1 py-3 px-4 text-center text-sm font-medium', activeTab === 'history' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300']">
                Lịch sử
              </button>

              <!-- Menu dropdown -->
              <div class="relative inline-block text-left py-3 px-4 z-10" @click.stop="toggleDropdown">
                <button type="button" class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                  <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                  </svg>
                </button>
                <div v-if="showDropdown"
                  class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                  <div class="py-1">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                      @click.prevent="markAllAsRead">Đánh dấu đọc tất cả</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                      @click.prevent="deleteAllNotifications">Xóa tất cả thông báo</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Checkbox chọn tất cả -->
            <div v-if="filteredNotifications.length > 0"
              class="flex items-center gap-2 px-6 py-3 bg-gray-50 border-b border-gray-200">
              <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                v-model="selectAll" @change="toggleSelectAll" />
              <label class="text-sm text-gray-700">Chọn tất cả</label>
            </div>


            <!-- Khi có thông báo đã chọn -->
            <div v-if="selectedNotificationIds.length > 0"
              class="flex justify-between items-center px-6 py-3 bg-gray-50 border-b">
              <p class="text-sm text-gray-700">Đã chọn {{ selectedNotificationIds.length }} thông báo</p>
              <div class="flex gap-2">
                <button @click="markSelectedAsRead"
                  class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 text-sm rounded">Đánh dấu
                  đã đọc</button>
                <button @click="deleteSelectedNotifications"
                  class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 text-sm rounded">Xóa</button>
              </div>
            </div>

            <!-- Danh sách thông báo -->
            <div class="p-6">
              <div v-if="filteredNotifications.length === 0" class="flex flex-col items-center justify-center py-12">
                <!-- ICON đẹp dạng notification bell -->
                <div class="bg-blue-50 p-6 rounded-full shadow-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-blue-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                </div>

                <!-- Tiêu đề chính -->
                <h3 class="mt-6 text-lg font-semibold text-gray-800">Không có thông báo nào</h3>

                <!-- Mô tả phụ -->
                <p class="text-sm text-gray-500 mt-1 text-center max-w-sm">
                  Khi có cập nhật về đơn hàng, khuyến mãi hoặc hoạt động tài khoản, chúng tôi sẽ thông báo cho bạn tại
                  đây.
                </p>
              </div>


              <ul v-else class="space-y-4">
                <li v-for="item in filteredNotifications" :key="item.id"
                  class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 shadow-sm transition cursor-pointer"
                  :class="item.is_read === 1 ? 'bg-gray-50 text-gray-500' : 'bg-white text-gray-900 hover:bg-gray-50'">
                  <input type="checkbox" v-model="selectedNotificationIds" :value="item.id"
                    class="mt-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />

                  <img v-if="item.image_url" :src="item.image_url" alt="Ảnh thông báo"
                    class="w-16 h-16 rounded-lg object-cover border" />

                  <div class="flex-1 min-w-0" @click="handleNotificationClick(item, 'item')">
                    <div class="flex justify-between items-start">
                      <h3 class="text-base font-semibold text-gray-900 mb-1"
                        :class="{ 'font-bold': item.is_read === 0 }">{{ item.title }}</h3>
                      <span v-if="item.is_read === 0" class="mt-1 w-2 h-2 rounded-full bg-blue-500 shrink-0"
                        title="Chưa đọc"></span>
                    </div>

                    <p class="text-gray-600 text-sm mb-1 line-clamp-2">{{ stripHTML(item.content) ||
                      'Không có nội dung' }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-400">
                      <span>{{ item.time_ago }}</span>
                      <button v-if="item.link" @click.stop="handleNotificationClick(item, 'detail')"
                        class="text-blue-600 hover:underline font-medium transition-colors duration-200">
                        Xem chi tiết
                      </button>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </main>

    </div>
  </div>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
          <button @click="showModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            ✕
          </button>

          <!-- Title -->
          <h3 class="text-lg font-semibold text-gray-800 mb-2">
            {{ currentNotification?.title || 'Không có tiêu đề' }}
          </h3>

          <!-- Image -->
          <div v-if="currentNotification?.image_url" class="mb-4">
            <img :src="currentNotification.image_url" alt="Ảnh thông báo"
              class="w-full h-auto rounded-md border object-cover max-h-64 mx-auto" />
          </div>

          <!-- Content -->
          <div class="prose prose-sm text-gray-700 max-h-80 overflow-y-auto mb-4"
            v-html="currentNotification?.content || 'Không có nội dung'"></div>

          <!-- Info -->
          <div class="text-sm text-gray-500 mb-2">
            <span>Trạng thái: </span>
            <span class="font-medium" :class="currentNotification?.is_read ? 'text-green-600' : 'text-red-500'">
              {{ currentNotification?.is_read ? 'Đã đọc' : 'Chưa đọc' }}
            </span>
          </div>

          <!-- Time -->
          <div class="text-xs text-gray-400 mb-4">
            Gửi: {{ currentNotification?.time_ago || 'Không rõ thời gian' }}
          </div>

        </div>
      </div>
    </transition>
  </Teleport>

</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'
import { useToast } from '~/composables/useToast'

const { showError, showSuccess } = useToast()

const api = useRuntimeConfig().public.apiBaseUrl
const notifications = ref([])
const unreadCount = ref(0)
const showDropdown = ref(false)
const selectedNotificationIds = ref([])
const showModal = ref(false)
const currentNotification = ref(null)
const activeTab = ref('all') // all | promotion | order | history


const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const handleClickOutside = (e) => {
  if (showDropdown.value && !e.target.closest('.relative.inline-block')) {
    showDropdown.value = false
  }
}

const filteredNotifications = computed(() => {
  if (activeTab.value === 'all') return notifications.value
  return notifications.value.filter(item => item.type === activeTab.value)
})

const selectAll = ref(false)

watch(selectedNotificationIds, (newVal) => {
  selectAll.value = newVal.length === filteredNotifications.value.length
})

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedNotificationIds.value = filteredNotifications.value.map(item => item.id)
  } else {
    selectedNotificationIds.value = []
  }
}


onMounted(() => {
  fetchNotifications()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const fetchNotifications = async () => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) return

    const res = await fetch(`${api}/my-notifications`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const data = await res.json()
    if (data?.data) {
      notifications.value = data.data
      unreadCount.value = data.data.filter(n => !n.is_read).length
    }
  } catch (e) {
    showError('Không thể lấy thông báo')
  }
}

const handleNotificationClick = async (item, action = 'item') => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) return

    // Đánh dấu đã đọc nếu chưa
    if (item.is_read === 0) {
      await fetch(`${api}/notifications/${item.id}/read`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      })
      item.is_read = 1
      unreadCount.value = notifications.value.filter(n => !n.is_read).length
    }

    // Xử lý theo loại hành động
    if (action === 'detail') {
      // Luôn mở popup khi ấn "Xem chi tiết"
      currentNotification.value = item
      showModal.value = true
    } else if (item.link) {
      // Click toàn bộ item sẽ mở link nếu có
      window.open(item.link, '_blank')
    } else {
      // Không có link thì hiện modal
      currentNotification.value = item
      showModal.value = true
    }

  } catch (err) {
    showError('Không thể xử lý thông báo')
  }
}

const markAllAsRead = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) return

  try {
    await Promise.all(notifications.value.map(item =>
      fetch(`${api}/notifications/${item.id}/read`, {
        method: 'POST', headers: { Authorization: `Bearer ${token}` }
      })
    ))

    notifications.value.forEach(item => item.is_read = 1)
    unreadCount.value = 0
    showSuccess('Đã đánh dấu tất cả là đã đọc')
  } catch (err) {
    showError('Không thể đánh dấu tất cả là đã đọc')
  }
}

const deleteAllNotifications = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) return

  try {
    await Promise.all(notifications.value.map(item =>
      fetch(`${api}/notifications/${item.id}`, {
        method: 'DELETE', headers: { Authorization: `Bearer ${token}` }
      })
    ))

    notifications.value = []
    selectedNotificationIds.value = []
    unreadCount.value = 0
    showSuccess('Đã xóa tất cả thông báo')
  } catch (err) {
    showError('Không thể xóa tất cả thông báo')
  }
}

const markSelectedAsRead = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) return

  try {
    await Promise.all(selectedNotificationIds.value.map(id =>
      fetch(`${api}/notifications/${id}/read`, {
        method: 'POST', headers: { Authorization: `Bearer ${token}` }
      })
    ))

    notifications.value.forEach(item => {
      if (selectedNotificationIds.value.includes(item.id)) {
        item.is_read = 1
      }
    })
    unreadCount.value = notifications.value.filter(n => !n.is_read).length
    selectedNotificationIds.value = []
    showSuccess('Đã đánh dấu đã đọc')
  } catch (err) {
    showError('Không thể đánh dấu đã đọc')
  }
}

const deleteSelectedNotifications = async () => {
  const token = localStorage.getItem('access_token')
  if (!token || selectedNotificationIds.value.length === 0) return

  try {
    await fetch(`${api}/notifications/delete-multiple`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify({ ids: selectedNotificationIds.value })
    })

    notifications.value = notifications.value.filter(
      item => !selectedNotificationIds.value.includes(item.id)
    )
    selectedNotificationIds.value = []
    unreadCount.value = notifications.value.filter(n => !n.is_read).length
    showSuccess('Đã xóa thông báo đã chọn')
  } catch (err) {
    showError('Không thể xóa thông báo đã chọn')
  }
}

const stripHTML = (html) => {
  const div = document.createElement('div')
  div.innerHTML = html
  return div.textContent || div.innerText || ''
}
</script>


<style scoped>
.prose {
  scrollbar-width: thin;
  scrollbar-color: #ccc transparent;
}

.prose::-webkit-scrollbar {
  width: 6px;
}

.prose::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 4px;
}
</style>