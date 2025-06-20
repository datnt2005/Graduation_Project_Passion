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
        <label class="text-sm">Vai trò người nhận:</label>
        <select v-model="selectedRole" class="border px-2 py-1 rounded">
          <option value="">-- Tất cả --</option>
          <option value="user">Người dùng</option>
          <option value="seller">Người bán</option>
          <option value="admin">Admin</option>
        </select>

        <label class="text-sm">Chọn người cụ thể:</label>
        <Multiselect v-model="selectedUserIds" :options="usersByRole" :multiple="true" :close-on-select="false"
          :clear-on-select="false" :preserve-search="true" label="name" track-by="id"
          placeholder="Chọn nhiều người dùng" class="w-64">
          <template #option="{ option }">
            <div class="flex items-center gap-2">
              <span class="font-medium">{{ option.name }}</span>
              <span class="text-xs text-gray-500">({{ option.id }})</span>
            </div>
          </template>
        </Multiselect>




        <button @click="sendSelected" :disabled="selectedIds.length === 0"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm flex items-center gap-2">
          <Send class="w-4 h-4" />
          Gửi các thông báo đã chọn
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
            <th class="border px-3 py-2 text-left font-semibold">Người nhận</th>
            <th class="border px-3 py-2 text-left font-semibold">Trạng thái</th>
            <th class="border px-3 py-2 text-left font-semibold">Tình trạng</th>
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
            <td class="px-3 py-2 capitalize">{{ item.type }}</td>
            <td class="px-3 py-2">{{ item.to_role }}</td>
            <td class="px-3 py-2">
              <span :class="item.is_read == 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
                class="px-2 py-1 text-xs font-semibold rounded">
                {{ item.is_read == 0 ? 'Chưa đọc' : 'Đã đọc' }}
              </span>
            </td>
            <td class="px-3 py-2">
              <span :class="item.status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
                class="px-2 py-1 text-xs font-semibold rounded">
                {{ item.status === 'draft' ? 'Lưu nháp' : 'Đã gửi' }}
              </span>
            </td>
            <td class="px-3 py-2">{{ formatDate(item.created_at) }}</td>
            <td class="px-3 py-2">
              <div class="flex space-x-2">
                <button @click="viewNotification(item.id)" class="text-blue-600 hover:text-blue-800">
                  <Eye class="w-5 h-5" />
                </button>
                <button @click="editNotification(item.id)" class="text-yellow-600 hover:text-yellow-800">
                  <Pencil class="w-5 h-5" />
                </button>
                <button @click="confirmDelete(item.id)" class="text-red-600 hover:text-red-800">
                  <Trash2 class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="notifications.length === 0">
            <td colspan="10" class="text-center py-4 text-gray-500">Không có thông báo nào</td>
          </tr>
        </tbody>
      </table>
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
  if (!confirm('Bạn có chắc chắn muốn xóa thông báo này?')) return
  try {
    const token = localStorage.getItem('access_token')
    await axios.delete(`${apiBase}/notifications/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    notifications.value = notifications.value.filter(n => n.id !== id)
    alert('Đã xóa thông báo!')
  } catch (err) {
    console.error('Lỗi khi xóa thông báo:', err)
    alert('Không thể xóa thông báo!')
  }
}

const sendSelected = async () => {
  if (!confirm('Bạn có chắc chắn muốn gửi các thông báo đã chọn?')) return
  try {
    const token = localStorage.getItem('access_token')
    await axios.post(`${apiBase}/notifications/send-multiple`, {
      ids: selectedIds.value,
      to_role: selectedRole.value || null,
      to_user_ids: selectedUserIds.value.length > 0 ? selectedUserIds.value : null
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })
    alert('Đã gửi các thông báo được chọn!')
    selectedIds.value = []
    fetchNotifications()
  } catch (err) {
    console.error('Lỗi khi gửi hàng loạt:', err)
    alert('Không thể gửi thông báo hàng loạt!')
  }
}

onMounted(() => {
  fetchNotifications()
})
</script>
