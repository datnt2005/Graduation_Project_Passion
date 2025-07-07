<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Danh sách hỗ trợ</h1>
      </div>
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left">ID</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Tên</th>
            <th class="border border-gray-300 px-3 py-2 text-left w-32">Email</th>
            <th class="border border-gray-300 px-3 py-2 text-left w-32">Chủ đề</th>
            <th class="border border-gray-300 px-3 py-2 text-left w-64">Nội dung</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Thời gian gửi</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Phản hồi</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Thời gian phản hồi</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Trạng thái</th>
            <th class="border border-gray-300 px-3 py-2 text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in supports" :key="item.id" :class="{ 'bg-gray-50': item.id % 2 === 0 }" class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left">{{ item.id }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left">{{ item.name }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left break-all">{{ item.email }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left max-w-[120px] align-top">
              <span
                class="block truncate cursor-pointer"
                :title="item.subject"
                style="max-width: 120px;"
              >
                {{ item.subject && item.subject.length > 30 ? item.subject.slice(0, 30) + '...' : item.subject }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left max-w-[220px] align-top">
              <span
                class="block truncate cursor-pointer whitespace-pre-line"
                :title="item.content"
                style="max-width: 220px;"
              >
                {{ item.content && item.content.length > 60 ? item.content.slice(0, 60) + '...' : item.content }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-xs text-gray-500 text-center">{{ formatDate(item.created_at) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-center">
              <div v-if="editingId === item.id">
                <textarea v-model="replyContent" rows="2" class="w-full border rounded p-1 text-xs"></textarea>
                <button @click="sendReply(item)" class="bg-blue-500 text-white px-2 py-1 rounded text-xs mt-1">Gửi</button>
                <button @click="cancelEdit" class="text-gray-500 px-2 py-1 text-xs">Hủy</button>
              </div>
              <div v-else>
                <div v-if="item.admin_reply" class="text-green-700 whitespace-pre-line font-semibold">
                  {{ item.admin_reply }}
                </div>
                <div v-else class="text-gray-400 italic">Chưa phản hồi</div>
              </div>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-xs text-gray-500 text-center">
              {{ item.replied_at ? formatDate(item.replied_at) : '-' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-center">
              <span v-if="item.admin_reply" class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Đã phản hồi</span>
              <span v-else class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Chờ phản hồi</span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-center">
              <div class="relative inline-block text-left">
                <button
                  v-if="editingId !== item.id && !item.admin_reply"
                  @click="startEdit(item)"
                  class="text-blue-500 hover:underline text-xs"
                >Phản hồi</button>
                <button
                  @click="confirmDelete(item)"
                  class="text-red-500 hover:underline text-xs ml-2"
                >Xóa</button>
              </div>
            </td>
          </tr>
          <tr v-if="supports.length === 0">
            <td colspan="10" class="text-center text-gray-500 py-6">Chưa có yêu cầu hỗ trợ nào.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Notification Popup -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <div v-if="showNotification"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6" :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="notificationType === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-if="notificationType === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button @click="showNotification = false"
              class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Confirmation Dialog -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeConfirmDialog"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
            <div class="flex items-start space-x-3">
              <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-full">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M5.06 20h13.88c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 17c-.77 1.333.19 3 1.72 3z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ confirmDialogTitle }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ confirmDialogMessage }}</p>
              </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
              <button
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium"
                @click="handleConfirmAction"
              >
                Xác nhận
              </button>
              <button
                class="px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-sm font-medium"
                @click="closeConfirmDialog"
              >
                Hủy
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

definePageMeta({
  layout: 'default-admin'
})
const supports = ref([])
const editingId = ref(null)
const replyContent = ref('')
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const confirmAction = ref(null)

const fetchSupports = async () => {
  const res = await $fetch(`${apiBase}/supports`)
  supports.value = Array.isArray(res.data) ? res.data : []
}

const startEdit = (item) => {
  editingId.value = item.id
  replyContent.value = item.admin_reply || ''
}

const cancelEdit = () => {
  editingId.value = null
  replyContent.value = ''
}

const sendReply = async (item) => {
  try {
    await $fetch(`${apiBase}/supports/${item.id}/reply`, {
      method: 'POST',
      body: { admin_reply: replyContent.value }
    })
    showNotificationMessage('Đã gửi phản hồi và email cho người dùng.', 'success')
    editingId.value = null
    replyContent.value = ''
    await fetchSupports()
  } catch (e) {
    showNotificationMessage('Gửi phản hồi thất bại.', 'error')
  }
}

const confirmDelete = (item) => {
  showConfirmationDialog(
    'Xác nhận xóa yêu cầu hỗ trợ',
    `Bạn có chắc chắn muốn xóa yêu cầu của "${item.name}" không?`,
    async () => {
      try {
        await $fetch(`${apiBase}/supports/${item.id}`, { method: 'DELETE' })
        showNotificationMessage('Đã xóa yêu cầu hỗ trợ.', 'success')
        await fetchSupports()
      } catch {
        showNotificationMessage('Xóa thất bại.', 'error')
      }
    }
  )
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('vi-VN')
}

const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => (showNotification.value = false), 3000)
}

const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title
  confirmDialogMessage.value = message
  confirmAction.value = action
  showConfirmDialog.value = true
}

const closeConfirmDialog = () => {
  showConfirmDialog.value = false
  confirmAction.value = null
}

const handleConfirmAction = async () => {
  if (typeof confirmAction.value === 'function') {
    await confirmAction.value()
  }
  closeConfirmDialog()
}

onMounted(fetchSupports)
</script>