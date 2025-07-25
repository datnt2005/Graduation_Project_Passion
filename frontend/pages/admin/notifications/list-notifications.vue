<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý thông báo</h1>
        <NuxtLink
          to="/admin/notifications/create-notifications"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 inline-block text-sm font-medium"
        >
          + Tạo thông báo
        </NuxtLink>
      </div>

      <!-- Filter and Bulk Action -->
      <div class="p-4 flex gap-4 items-center">
        <button
          @click="sendSelected"
          :disabled="selectedIds.length === 0"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm flex items-center gap-2"
        >
          <Send class="w-4 h-4" />
          Gửi các thông báo đã chọn
        </button>
        <button
          @click="sendAllNotifications"
          class="bg-green-100 text-green-700 px-4 py-2 rounded hover:bg-green-200 text-sm flex items-center gap-2"
        >
          <Send class="w-4 h-4" />
          Gửi tất cả thông báo chưa gửi
        </button>
        <button
          @click="deleteSelected"
          :disabled="selectedIds.length === 0"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm flex items-center gap-2"
        >
          <Trash2 class="w-4 h-4" />
          Xóa đã chọn
        </button>
        <button
          @click="deleteAll"
          class="bg-red-100 text-red-700 px-4 py-2 rounded hover:bg-red-200 text-sm flex items-center gap-2"
        >
          <Trash2 class="w-4 h-4" />
          Xóa tất cả
        </button>
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
          <tr v-for="item in notifications" :key="item.id" class="border-b">
            <td class="px-3 py-2">
              <input type="checkbox" :value="item.id" v-model="selectedIds" />
            </td>
            <td class="px-3 py-2">#{{ item.id }}</td>
            <td class="px-3 py-2">{{ item.title }}</td>
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
                class="p-1 rounded hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-gray-300"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v.01M12 12v.01M12 18v.01" />
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="notifications.length === 0">
            <td colspan="11" class="text-center py-4 text-gray-500">Không có thông báo nào</td>
          </tr>
        </tbody>
      </table>

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
            class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-[100]"
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
              <p class="text-sm font-medium text-gray-900">
                {{ notificationMessage }}
              </p>
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
import { Eye, Pencil, Trash2, Send } from 'lucide-vue-next'
import { secureFetch } from '@/utils/secureFetch'

definePageMeta({ layout: 'default-admin' })

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl || ''
const mediaBase = config.public.mediaBaseUrl || ''
const router = useRouter()

const notifications = ref([])
const selectedIds = ref([])
const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px' })
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')

// Hàm hiển thị thông báo
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => {
    showNotification.value = false
  }, 3000)
}

const isAllSelected = computed(() => selectedIds.value.length === notifications.value.length)

const toggleSelectAll = () => {
  selectedIds.value = isAllSelected.value ? [] : notifications.value.map(n => n.id)
}

const toggleDropdown = (id, event) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null
    return
  }

  const rect = event.currentTarget.getBoundingClientRect()
  dropdownPosition.value = {
    top: `${rect.bottom + window.scrollY}px`,
    left: `${rect.left}px`
  }

  activeDropdown.value = id
}

const closeDropdown = () => {
  activeDropdown.value = null
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
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const fetchNotifications = async () => {
  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Không tìm thấy token truy cập')

    const data = await secureFetch(`${apiBase}/notifications`, {
      cache: 'no-store'
    }, ['admin'])

    notifications.value = Array.isArray(data.data) ? data.data : data
  } catch (err) {
    console.error('Lỗi khi tải thông báo:', err)
    showNotificationMessage(
      err?.response?.data?.message || 'Không thể tải danh sách thông báo',
      'error'
    )
  }
}

const confirmDelete = async (id) => {
  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Không tìm thấy token truy cập')

    await secureFetch(`${apiBase}/notifications/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token}` },
      cache: 'no-store'
    }, ['admin'])

    notifications.value = notifications.value.filter(n => n.id !== id)
    showNotificationMessage('Đã xóa thông báo!', 'success')
  } catch (err) {
    console.error('Lỗi khi xóa thông báo:', err)
    showNotificationMessage(
      err?.response?.data?.message || 'Không thể xóa thông báo!',
      'error'
    )
  }
}

const deleteSelected = async () => {
  if (selectedIds.value.length === 0) return

  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Không tìm thấy token truy cập')

    await secureFetch(`${apiBase}/notifications/destroy-multiple`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify({ ids: selectedIds.value }),
      cache: 'no-store'
    }, ['admin'])

    showNotificationMessage('Đã xóa các thông báo đã chọn!', 'success')
    selectedIds.value = []
    await fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi xóa nhiều thông báo:', err)
    showNotificationMessage(
      err?.response?.data?.message || 'Không thể xóa các thông báo đã chọn!',
      'error'
    )
  }
}

const deleteAll = async () => {
  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Không tìm thấy token truy cập')

    await secureFetch(`${apiBase}/notifications/destroy-all`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token}` },
      cache: 'no-store'
    }, ['admin'])

    showNotificationMessage('Đã xóa tất cả thông báo!', 'success')
    selectedIds.value = []
    await fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi xóa tất cả thông báo:', err)
    showNotificationMessage(
      err?.response?.data?.message || 'Không thể xóa tất cả thông báo!',
      'error'
    )
  }
}

const sendSelected = async () => {
  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Không tìm thấy token truy cập')

    await secureFetch(`${apiBase}/notifications/send-multiple`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify({ ids: selectedIds.value }),
      cache: 'no-store'
    }, ['admin'])

    showNotificationMessage('Đã gửi các thông báo được chọn!', 'success')
    selectedIds.value = []
    await fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi gửi hàng loạt:', err)
    showNotificationMessage(
      err?.response?.data?.message || 'Không thể gửi thông báo hàng loạt!',
      'error'
    )
  }
}

const sendAllNotifications = async () => {
  try {
    const token = localStorage.getItem('access_token') || useCookie('access_token')?.value
    if (!token) throw new Error('Không tìm thấy token truy cập')

    await secureFetch(`${apiBase}/notifications/send-all`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      cache: 'no-store'
    }, ['admin'])

    showNotificationMessage('Đã gửi tất cả thông báo chưa gửi!', 'success')
    await fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi gửi tất cả thông báo:', err)
    showNotificationMessage(
      err?.response?.data?.message || 'Không thể gửi tất cả thông báo!',
      'error'
    )
  }
}

onMounted(() => {
  fetchNotifications()
})

onUnmounted(() => {
  closeDropdown()
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
</style>