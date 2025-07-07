<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý thông báo</h1>

        <nuxt-link to="/admin/notifications/create-notifications"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 inline-block text-sm font-medium">
          + Tạo thông báo
        </nuxt-link>
      </div>

      <!-- Filter and Bulk Action -->
      <div class="p-4 flex gap-4 items-center">
        <button @click="sendSelected" :disabled="selectedIds.length === 0"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm flex items-center gap-2">
          <Send class="w-4 h-4" />
          Gửi các thông báo đã chọn
        </button>
        <!-- Gửi tất cả thông báo chưa gửi -->
        <button @click="sendAllNotifications"
          class="bg-green-100 text-green-700 px-4 py-2 rounded hover:bg-green-200 text-sm flex items-center gap-2">
          <Send class="w-4 h-4" /> Gửi tất cả thông báo chưa gửi
        </button>
        <!-- Xoá các thông báo đã chọn -->
        <button @click="deleteSelected" :disabled="selectedIds.length === 0"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm flex items-center gap-2">
          <Trash2 class="w-4 h-4" /> Xoá đã chọn
        </button>

        <!-- Xoá tất cả thông báo -->
        <button @click="deleteAll"
          class="bg-red-100 text-red-700 px-4 py-2 rounded hover:bg-red-200 text-sm flex items-center gap-2">
          <Trash2 class="w-4 h-4" /> Xoá tất cả
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
              <img v-if="item.image_url" :src="item.image_url" alt="Ảnh" class="w-14 h-14 object-cover rounded" />
              <span v-else class="text-gray-400 italic">Không có</span>
            </td>
            <td class="px-3 py-2">{{ typeLabel(item.type) }}</td>

            <!-- Người nhận -->
            <td class="px-3 py-2">
              <span v-if="item.to_roles && item.to_roles.length">
                <span v-for="(role, index) in item.to_roles" :key="index"
                  class="inline-block bg-blue-100 text-blue-800 rounded-full px-2 py-1 text-xs mr-1">
                  {{ roleLabel(role) }}
                </span>
              </span>
              <span v-else class="text-gray-400 italic">Không có</span>
            </td>

            <!-- Kênh gửi -->
            <td class="px-3 py-2">
              <span v-if="item.channels && Array.isArray(item.channels)">
                <span v-for="(channel, index) in item.channels" :key="index"
                  class="inline-block bg-gray-200 text-gray-700 rounded-full px-2 py-1 text-xs mr-1">
                  {{ channelLabel(channel) }}
                </span>
              </span>
              <span v-else class="text-gray-400 italic">Không có</span>
            </td>

            <!-- Trạng thái -->
            <td class="px-3 py-2">
              <span :class="item.status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
                class="px-2 py-1 text-xs font-semibold rounded">
                {{ item.status === 'draft' ? 'Lưu nháp' : 'Đã gửi' }}
              </span>
            </td>

            <!-- Hiển thị -->
            <td class="px-3 py-2">
              <span :class="item.is_hidden ? 'text-red-500' : 'text-green-600'">
                {{ item.is_hidden ? 'Đã ẩn' : 'Đang hiển thị' }}
              </span>
            </td>

            <!-- Ngày -->
            <td class="px-3 py-2">{{ formatDate(item.created_at) }}</td>

            <!-- Dropdown -->
            <td class="px-3 py-2 relative">
              <button @click="toggleDropdown(item.id, $event)"
                class="p-1 rounded hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6v.01M12 12v.01M12 18v.01" />
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="notifications.length === 0">
            <td colspan="10" class="text-center py-4 text-gray-500">Không có thông báo nào</td>
          </tr>
        </tbody>
      </table>

      <Teleport to="body">
        <Transition enter-active-class="transition duration-100 ease-out"
          enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0">
          <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
            <div
              class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right w-40"
              :style="dropdownPosition">
              <div class="py-1">
                <button @click="viewNotification(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <Eye class="w-4 h-4 mr-2" /> Xem
                </button>
                <button @click="editNotification(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50">
                  <Pencil class="w-4 h-4 mr-2" /> Sửa
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

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import axios from 'axios'
import { useRuntimeConfig } from '#app'
import { useRouter } from 'vue-router'
import { Eye, Pencil, Trash2, Send } from 'lucide-vue-next'
import Multiselect from 'vue-multiselect'
import { useNotification } from '~/composables/useNotification'

const { showNotification } = useNotification()
definePageMeta({ layout: 'default-admin' })
const usersByRole = ref([])
const usersByRoleEmpty = computed(() => !Array.isArray(usersByRole.value) || usersByRole.value.length === 0)


const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const notifications = ref([])
const router = useRouter()

const selectedIds = ref([])
const selectedRole = ref('')
const selectedUserIds = ref([])

const countUnread = (recipients) => {
  return recipients.filter(r => r.is_read === 0).length
}
const channelLabel = (channel) => {
  switch (channel) {
    case 'web':
      return 'Web'
    case 'email':
      return 'Email'
    default:
      return channel
  }
}

const typeLabel = (type) => {
  switch (type) {
    case 'order':
      return 'Đơn hàng'
    case 'promotion':
      return 'Khuyến mãi'
    case 'message':
      return 'Tin nhắn'
    case 'system':
      return 'Hệ thống'
    default:
      return 'Không xác định'
  }
}

const roleLabel = (role) => {
  switch (role) {
    case 'user':
      return 'Người dùng'
    case 'seller':
      return 'Người bán'
    case 'admin':
      return 'Quản trị viên'
    default:
      return 'Không xác định'
  }
}

const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px' })

const toggleDropdown = (id, event) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null
    return
  }

  // Tính vị trí dropdown
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


const isAllSelected = computed(() => selectedIds.value.length === notifications.value.length)

const toggleSelectAll = () => {
  selectedIds.value = isAllSelected.value ? [] : notifications.value.map(n => n.id)
}

const viewNotification = (id) => {
  router.push(`/admin/notifications/view/${id}`)
}

const editNotification = (id) => {
  router.push(`/admin/notifications/edit-notifications/${id}`)
}

watch(selectedRole, async (newRole) => {
  selectedUserIds.value = [] // Reset người dùng được chọn
  usersByRole.value = []

  if (!newRole) return

  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/users/by-role/${newRole}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const rawUsers = Array.isArray(res.data.data) ? res.data.data : res.data

    // Nếu không muốn chọn admin (dù là đang chọn role gì)
    usersByRole.value = rawUsers.filter(user => user.role !== 'admin')
  } catch (err) {
    console.error('Không thể tải danh sách người dùng:', err)
  }
})

const fetchNotifications = async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/notifications`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    notifications.value = res.data.data || res.data
  } catch (err) {
    console.error('Lỗi khi tải thông báo:', err)
  }
}

const formatDate = (dateStr) => {
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const confirmDelete = async (id) => {
  try {
    const token = localStorage.getItem('access_token')
    await axios.delete(`${apiBase}/notifications/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    notifications.value = notifications.value.filter(n => n.id !== id)
    showNotification('Đã xóa thông báo!', 'success')
  } catch (err) {
    console.error('Lỗi khi xóa thông báo:', err)
    showNotification('Không thể xóa thông báo!', 'error')
  }
}

const deleteSelected = async () => {
  if (selectedIds.value.length === 0) return // Không làm gì nếu chưa chọn

  try {
    const token = localStorage.getItem('access_token')
    await axios.post(`${apiBase}/notifications/destroy-multiple`, {
      ids: selectedIds.value
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    showNotification('Đã xoá các thông báo đã chọn!', 'success')
    selectedIds.value = []
    fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi xoá nhiều thông báo:', err)
    showNotification('Không thể xoá các thông báo đã chọn!', 'error')
  }
}

const deleteAll = async () => {
  try {
    const token = localStorage.getItem('access_token')
    await axios.delete(`${apiBase}/notifications/destroy-all`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    showNotification('Đã xoá tất cả thông báo!', 'success')
    selectedIds.value = []
    fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi xoá tất cả thông báo:', err)
    showNotification('Không thể xoá tất cả thông báo!', 'error')
  }
}



const sendSelected = async () => {
  try {
    const token = localStorage.getItem('access_token')
    await axios.post(`${apiBase}/notifications/send-multiple`, {
      ids: selectedIds.value
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    showNotification('Đã gửi các thông báo được chọn!', 'success')
    selectedIds.value = []
    fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi gửi hàng loạt:', err)
    showNotification('Không thể gửi thông báo hàng loạt!', 'error')
  }
}

const sendAllNotifications = async () => {
  try {
    const token = localStorage.getItem('access_token')
    await axios.post(`${apiBase}/notifications/send-all`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })

    showNotification('Đã gửi tất cả thông báo chưa gửi!', 'success')
    fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi gửi tất cả thông báo:', err)
    showNotification('Không thể gửi tất cả thông báo!', 'error')
  }
}

onMounted(() => {
  fetchNotifications()
})
</script>