```vue
<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="flex flex-col md:flex-row max-w-screen-2xl mx-auto px-4 sm:px-6 py-6 gap-6">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
        <div class="min-h-full">
          <h2 class="text-2xl text-center sm:text-3xl font-extrabold text-gray-900 mb-4">Thông báo của tôi</h2>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="flex border-b border-gray-200 relative">
              <button
                @click="activeTab = 'all'"
                :class="['flex-1 py-3 px-4 text-center text-sm font-medium', activeTab === 'all' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300']"
                aria-label="Xem tất cả thông báo"
              >
                Thông báo của tôi
              </button>

              <button
                @click="activeTab = 'promotion'"
                :class="['flex-1 py-3 px-4 text-center text-sm font-medium', activeTab === 'promotion' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300']"
                aria-label="Xem thông báo khuyến mãi"
              >
                Khuyến mãi
              </button>

              <button
                @click="activeTab = 'order'"
                :class="['flex-1 py-3 px-4 text-center text-sm font-medium', activeTab === 'order' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300']"
                aria-label="Xem cập nhật đơn hàng"
              >
                Cập nhật đơn hàng
              </button>

              <button
                @click="activeTab = 'history'"
                :class="['flex-1 py-3 px-4 text-center text-sm font-medium', activeTab === 'history' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-800 border-b-2 border-transparent hover:border-gray-300']"
                aria-label="Xem lịch sử thông báo"
              >
                Lịch sử
              </button>

              <!-- Menu dropdown -->
              <div class="relative inline-block text-left py-3 px-4 z-10" @click.stop="toggleDropdown">
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
                  class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
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

            <!-- Checkbox chọn tất cả -->
            <div
              v-if="filteredNotifications.length > 0"
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
              v-if="selectedNotificationIds.length > 0"
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

            <!-- Danh sách thông báo -->
            <div class="p-6">
              <div
                v-if="filteredNotifications.length === 0"
                class="flex flex-col items-center justify-center py-12"
              >
                <div class="bg-blue-50 p-6 rounded-full shadow-sm">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-14 w-14 text-blue-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                    />
                  </svg>
                </div>
                <h3 class="mt-6 text-lg font-semibold text-gray-800">Không có thông báo nào</h3>
                <p class="text-sm text-gray-500 mt-1 text-center max-w-sm">
                  Khi có cập nhật về đơn hàng, khuyến mãi hoặc hoạt động tài khoản, chúng tôi sẽ thông báo cho bạn tại
                  đây.
                </p>
              </div>

              <ul v-else class="space-y-4">
                <li
                  v-for="item in filteredNotifications"
                  :key="item.id"
                  class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 shadow-sm transition cursor-pointer"
                  :class="item.is_read === 1 ? 'bg-gray-50 text-gray-500' : 'bg-white text-gray-900 hover:bg-gray-50'"
                >
                  <input
                    type="checkbox"
                    v-model="selectedNotificationIds"
                    :value="item.id"
                    class="mt-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    :aria-label="`Chọn thông báo: ${item.title}`"
                  />
                  <img
                    v-if="item.image_url"
                    :src="item.image_url"
                    alt="Ảnh thông báo"
                    class="w-16 h-16 rounded-lg object-cover border"
                  />
                  <div class="flex-1 min-w-0" @click="handleNotificationClick(item, 'item')">
                    <div class="flex justify-between items-start">
                      <h3
                        class="text-base font-semibold text-gray-900 mb-1"
                        :class="{ 'font-bold': item.is_read === 0 }"
                      >{{ item.title }}</h3>
                      <span
                        v-if="item.is_read === 0"
                        class="mt-1 w-2 h-2 rounded-full bg-blue-500 shrink-0"
                        title="Chưa đọc"
                      ></span>
                    </div>
                    <p class="text-gray-600 text-sm mb-1 line-clamp-2">{{ stripHTML(item.content) ||
                      'Không có nội dung' }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-400">
                      <span>{{ item.time_ago }}</span>
                      <button
                        v-if="item.link"
                        @click.stop="handleNotificationClick(item, 'detail')"
                        class="text-blue-600 hover:underline font-medium transition-colors duration-200"
                        :aria-label="`Xem chi tiết thông báo: ${item.title}`"
                      >
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
    <Teleport to="body">
      <transition name="fade">
        <div
          v-if="showModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        >
          <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
            <button
              @click="showModal = false"
              class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
              aria-label="Đóng modal thông báo"
            >✕</button>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">
              {{ currentNotification?.title || 'Không có tiêu đề' }}
            </h3>
            <div v-if="currentNotification?.image_url" class="mb-4">
              <img
                :src="currentNotification.image_url"
                alt="Ảnh thông báo"
                class="w-full h-auto rounded-md border object-cover max-h-64 mx-auto"
              />
            </div>
            <div
              class="prose prose-sm text-gray-700 max-h-80 overflow-y-auto mb-4"
              v-html="currentNotification?.content || 'Không có nội dung'"
            ></div>
            <div class="text-sm text-gray-500 mb-2">
              <span>Trạng thái: </span>
              <span
                class="font-medium"
                :class="currentNotification?.is_read ? 'text-green-600' : 'text-red-500'"
              >
                {{ currentNotification?.is_read ? 'Đã đọc' : 'Chưa đọc' }}
              </span>
            </div>
            <div class="text-xs text-gray-400 mb-4">
              Gửi: {{ currentNotification?.time_ago || 'Không rõ thời gian' }}
            </div>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useHead, useRuntimeConfig } from '#app'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useToast } from '~/composables/useToast'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'

useHead({
  title: 'Thông báo của tôi | Quản lý thông báo',
  meta: [
    { name: 'description', content: 'Xem và quản lý thông báo về đơn hàng, khuyến mãi và hoạt động tài khoản của bạn.' },
    { name: 'robots', content: 'noindex, nofollow' }, // Trang thông báo không cần lập chỉ mục
    { property: 'og:title', content: 'Thông báo của tôi - Quản lý thông báo' },
    { property: 'og:description', content: 'Quản lý thông báo về đơn hàng, khuyến mãi và hoạt động tài khoản.' }
  ]
})

const { showError, showSuccess } = useToast()
const api = useRuntimeConfig().public.apiBaseUrl
const notifications = ref([])
const unreadCount = ref(0)
const showDropdown = ref(false)
const selectedNotificationIds = ref([])
const showModal = ref(false)
const currentNotification = ref(null)
const activeTab = ref('all') // all | promotion | order | history
const loading = ref(true)

const filteredNotifications = computed(() => {
  if (activeTab.value === 'all') return notifications.value
  return notifications.value.filter(item => item.type === activeTab.value)
})

const selectAll = ref(false)

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedNotificationIds.value = filteredNotifications.value.map(item => item.id)
  } else {
    selectedNotificationIds.value = []
  }
}

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const handleClickOutside = (e) => {
  if (showDropdown.value && !e.target.closest('.relative.inline-block')) {
    showDropdown.value = false
  }
}

const fetchNotifications = async () => {
    const cacheKey = 'user_notifications';
    const cache = localStorage.getItem(cacheKey);
    const headerUnreadCount = parseInt(localStorage.getItem('header_unread_count') || 0); // Giả sử header lưu count vào localStorage

    if (cache) {
        const cachedData = JSON.parse(cache);
        const cachedTime = cachedData.timestamp || 0;
        const cachedUnreadCount = cachedData.unreadCount || 0;
        if (Date.now() - cachedTime < 30 * 1000 && cachedUnreadCount === headerUnreadCount) { // 30 giây
            notifications.value = cachedData.notifications;
            unreadCount.value = cachedData.unreadCount;
            loading.value = false;
            return;
        }
    }

    loading.value = true;
    try {
        const token = localStorage.getItem('access_token');
        if (!token) throw new Error('Bạn chưa đăng nhập!');

        const res = await axios.get(`${api}/my-notifications`, {
            headers: { Authorization: `Bearer ${token}` }
        });

        if (res.data?.data) {
            notifications.value = res.data.data;
            unreadCount.value = notifications.value.filter(n => !n.is_read).length;
            localStorage.setItem(cacheKey, JSON.stringify({
                notifications: notifications.value,
                unreadCount: unreadCount.value,
                timestamp: Date.now()
            }));
        }
    } catch (e) {
        showError(e.message || 'Không thể lấy thông báo');
    } finally {
        loading.value = false;
    }
};

const handleNotificationClick = async (item, action = 'item') => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    if (item.is_read === 0) {
      await axios.post(`${api}/notifications/${item.id}/read`, {}, {
        headers: { Authorization: `Bearer ${token}` }
      })
      item.is_read = 1
      unreadCount.value = notifications.value.filter(n => !n.is_read).length
      localStorage.removeItem('user_notifications') // Xóa cache để đồng bộ
    }

    if (action === 'detail') {
      currentNotification.value = item
      showModal.value = true
    } else if (item.link) {
      window.open(item.link, '_blank')
    } else {
      currentNotification.value = item
      showModal.value = true
    }
  } catch (e) {
    showError(e.message || 'Không thể xử lý thông báo')
  }
}

const markAllAsRead = async () => {
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
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    await axios.post(`${api}/notifications/mark-all-read`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })

    notifications.value.forEach(item => { item.is_read = 1 })
    unreadCount.value = 0
    localStorage.removeItem('user_notifications')
    showSuccess('Đã đánh dấu tất cả là đã đọc')
  } catch (e) {
    showError(e.message || 'Không thể đánh dấu tất cả là đã đọc')
  }
}

const deleteAllNotifications = async () => {
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
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    await axios.post(`${api}/notifications/delete-all`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })

    notifications.value = []
    selectedNotificationIds.value = []
    unreadCount.value = 0
    localStorage.removeItem('user_notifications')
    showSuccess('Đã xóa tất cả thông báo')
  } catch (e) {
    showError(e.message || 'Không thể xóa tất cả thông báo')
  }
}

const markSelectedAsRead = async () => {
  if (selectedNotificationIds.value.length === 0) return

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
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    await axios.post(`${api}/notifications/mark-multiple-read`, { ids: selectedNotificationIds.value }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    notifications.value.forEach(item => {
      if (selectedNotificationIds.value.includes(item.id)) {
        item.is_read = 1
      }
    })
    unreadCount.value = notifications.value.filter(n => !n.is_read).length
    selectedNotificationIds.value = []
    localStorage.removeItem('user_notifications')
    showSuccess('Đã đánh dấu đã đọc')
  } catch (e) {
    showError(e.message || 'Không thể đánh dấu đã đọc')
  }
}

const deleteSelectedNotifications = async () => {
  if (selectedNotificationIds.value.length === 0) return

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
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Bạn chưa đăng nhập!')

    await axios.post(`${api}/notifications/delete-multiple`, { ids: selectedNotificationIds.value }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    notifications.value = notifications.value.filter(
      item => !selectedNotificationIds.value.includes(item.id)
    )
    selectedNotificationIds.value = []
    unreadCount.value = notifications.value.filter(n => !n.is_read).length
    localStorage.removeItem('user_notifications')
    showSuccess('Đã xóa thông báo đã chọn')
  } catch (e) {
    showError(e.message || 'Không thể xóa thông báo đã chọn')
  }
}

const stripHTML = (html) => {
  const div = document.createElement('div')
  div.innerHTML = html
  return div.textContent || div.innerText || ''
}

let refreshInterval;

onMounted(() => {
    fetchNotifications();
    document.addEventListener('click', handleClickOutside);
    // Làm mới tự động mỗi 30 giây
    refreshInterval = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    clearInterval(refreshInterval); // Dọn dẹp interval
});
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
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s }
.fade-enter-from, .fade-leave-to { opacity: 0 }
</style>
```