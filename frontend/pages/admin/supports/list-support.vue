<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Danh sách hỗ trợ</h1>
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-600">
            {{ selectedSupports.length }} được chọn / {{ totalSupports }}
          </span>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ totalSupports }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <select
            v-model="selectedStatus"
            @change="debouncedFetchSupports"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Tất cả trạng thái</option>
            <option value="replied">Đã phản hồi</option>
            <option value="pending">Chờ phản hồi</option>
          </select>
          <div class="relative">
            <input
              v-model="searchQuery"
              @input="debouncedFetchSupports"
              type="text"
              placeholder="Tìm kiếm theo tên, email, số điện thoại, chủ đề..."
              class="pl-10 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64"
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

      <!-- Action Bar -->
      <div
        class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-t border-gray-300"
      >
        <select
          v-model="selectedAction"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Hành động hàng loạt</option>
          <option value="delete">Xóa</option>
        </select>
        <button
          @click="applyBulkAction"
          :disabled="isBulkActionDisabled"
          :class="[
            'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
            isBulkActionDisabled
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-blue-600 text-white hover:bg-blue-700',
          ]"
        >
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <div class="ml-auto text-sm text-gray-600">
          {{ selectedSupports.length }} được chọn / {{ totalSupports }}
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">ID</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tên</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Số điện thoại</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Email</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Chủ đề</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Nội dung</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thời gian gửi</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Phản hồi</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thời gian phản hồi</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in supports"
            :key="item.id"
            :class="{ 'bg-gray-50': item.id % 2 === 0 }"
            class="border-b border-gray-300"
          >
            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectedSupports" :value="item.id" />
            </td>
            <td class="border border-gray-300 px-3 py-2">{{ item.id }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ item.name || 'Ẩn danh' }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ item.phone || 'N/A' }}</td>
            <td class="border border-gray-300 px-3 py-2 break-all">{{ item.email || 'N/A' }}</td>
            <td class="border border-gray-300 px-3 py-2 max-w-[120px]">
              <span
                class="block truncate cursor-pointer"
                :title="item.subject"
                style="max-width: 120px;"
              >
                {{ truncateText(item.subject, 30) }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 max-w-[220px]">
              <span
                class="block truncate cursor-pointer whitespace-pre-line"
                :title="item.content"
                style="max-width: 220px;"
              >
                {{ truncateText(item.content, 60) }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-xs text-gray-500 text-center">
              {{ formatDate(item.created_at) }}
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <div v-if="editingId === item.id">
                <textarea
                  v-model="replyContent"
                  rows="2"
                  class="w-full border rounded p-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500"
                  :disabled="isProcessing[item.id] || false"
                  @input="validateReply"
                  @change="validateReply"
                ></textarea>
                <div class="flex gap-2 mt-1">
                  <button
                    @click="sendReply(item)"
                    class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600 transition"
                    :disabled="isProcessing[item.id] || !replyContent.trim() || !!replyError"
                  >
                    {{ isProcessing[item.id] ? 'Đang gửi...' : 'Gửi' }}
                  </button>
                  <button
                    @click="cancelEdit"
                    class="text-gray-500 px-2 py-1 text-xs hover:text-gray-700"
                    :disabled="isProcessing[item.id] || false"
                  >
                    Hủy
                  </button>
                </div>
                <p v-if="replyError" class="text-red-500 text-xs mt-1">{{ replyError }}</p>
              </div>
              <div v-else>
                <div v-if="item.admin_reply" class="text-green-700 whitespace-pre-line font-semibold">
                  {{ truncateText(item.admin_reply, 50) }}
                </div>
                <div v-else class="text-gray-400 italic">Chưa phản hồi</div>
              </div>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-xs text-gray-500 text-center">
              {{ item.replied_at ? formatDate(item.replied_at) : '-' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-center">
              <span
                :class="[
                  item.admin_reply
                    ? 'bg-green-100 text-green-700'
                    : 'bg-yellow-100 text-yellow-700',
                  'px-2 py-1 rounded text-xs'
                ]"
              >
                {{ item.admin_reply ? 'Đã phản hồi' : 'Chờ phản hồi' }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-center">
              <div class="relative inline-block text-left">
                <button
                  v-if="editingId !== item.id && !item.admin_reply"
                  @click="startEdit(item)"
                  class="text-blue-500 hover:underline text-xs"
                  :disabled="isProcessing[item.id] || false"
                >
                  Phản hồi
                </button>
                <button
                  @click="confirmDelete(item)"
                  class="text-red-500 hover:underline text-xs ml-2"
                  :disabled="isProcessing[item.id] || false"
                >
                  Xóa
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="supports.length === 0">
            <td colspan="12" class="text-center text-gray-500 py-6">Chưa có yêu cầu hỗ trợ nào.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <Pagination
        :currentPage="currentPage"
        :lastPage="lastPage"
        @change="fetchSupports"
        class="mt-6"
      />

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

      <!-- Confirmation Dialog -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="showConfirmDialog" class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
              <div
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="closeConfirmDialog"
              ></div>
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
              <div
                class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
              >
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div
                      class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                    >
                      <svg
                        class="h-6 w-6 text-red-600"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                      <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ confirmDialogTitle }}
                      </h3>
                      <div class="mt-2">
                        <p class="text-sm text-gray-500">
                          {{ confirmDialogMessage }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button
                    type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="handleConfirmAction"
                  >
                    Xác nhận
                  </button>
                  <button
                    type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="closeConfirmDialog"
                  >
                    Hủy
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRuntimeConfig, useCookie } from '#app'
import Pagination from '~/components/Pagination.vue'
import { secureFetch } from '@/utils/secureFetch'

definePageMeta({
  layout: 'default-admin',
})

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

// State
const supports = ref([])
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedSupports = ref([])
const selectAll = ref(false)
const selectedAction = ref('')
const editingId = ref(null)
const replyContent = ref('')
const replyError = ref('')
const isProcessing = ref({})
const loading = ref(false)
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const confirmAction = ref(null)
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = 10
const totalSupports = ref(0)

// Debounce function for search and status changes
const debounce = (fn, delay) => {
  let timeout
  return (...args) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fn(...args), delay)
  }
}

// Fetch supports with server-side filtering
const fetchSupports = async (page = 1) => {
  try {
    loading.value = true
    currentPage.value = page
    const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
    if (!token) throw new Error('Không tìm thấy token truy cập')

    const params = new URLSearchParams({ page, per_page: perPage })
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedStatus.value) params.append('status', selectedStatus.value)

    const response = await secureFetch(`${apiBase}/admin/supports?${params.toString()}`, {
      headers: { Authorization: `Bearer ${token}` },
      cache: 'no-store'
    }, ['admin'])

    supports.value = response.data.data.map(item => ({
      ...item,
      id: item.id || null,
      name: item.name || 'Ẩn danh',
      phone: item.phone || 'N/A',
      email: item.email || 'N/A',
      subject: item.subject || 'Không có chủ đề',
      content: item.content || 'Không có nội dung',
      created_at: item.created_at || null,
      admin_reply: item.admin_reply || '',
      replied_at: item.replied_at || null
    }))
    currentPage.value = response.data.current_page || 1
    lastPage.value = response.data.last_page || 1
    totalSupports.value = response.data.total || 0
  } catch (err) {
    showNotificationMessage(
      err?.response?.data?.message || `Không thể tải danh sách hỗ trợ: ${err.message}`,
      'error'
    )
    console.error('Error fetching supports:', err)
  } finally {
    loading.value = false
  }
}

const debouncedFetchSupports = debounce(() => fetchSupports(1), 300)

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedSupports.value = supports.value
      .map(s => s.id)
      .filter(id => id !== null && id !== undefined)
  } else {
    selectedSupports.value = []
  }
}

// Computed properties
const isBulkActionDisabled = computed(() => {
  return !selectedAction.value || selectedSupports.value.length === 0 || loading.value
})

const totalPages = computed(() => lastPage.value)

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedSupports.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một yêu cầu hỗ trợ', 'error')
    return
  }

  if (selectedAction.value === 'delete') {
    showConfirmationDialog(
      'Xác nhận xóa hàng loạt',
      `Bạn có chắc chắn muốn xóa ${selectedSupports.value.length} yêu cầu hỗ trợ đã chọn?`,
      async () => {
        try {
          loading.value = true
          const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
          if (!token) throw new Error('Không tìm thấy token truy cập')

          const deletePromises = selectedSupports.value.map(id =>
            secureFetch(`${apiBase}/admin/supports/${id}`, {
              method: 'DELETE',
              headers: { Authorization: `Bearer ${token}` },
              cache: 'no-store'
            }, ['admin'])
          )
          await Promise.all(deletePromises)
          showNotificationMessage('Xóa các yêu cầu hỗ trợ thành công!', 'success')
          selectedSupports.value = []
          selectAll.value = false
          selectedAction.value = ''
          await fetchSupports(currentPage.value)
        } catch (error) {
          showNotificationMessage(
            error?.response?.data?.message || `Có lỗi xảy ra khi xóa yêu cầu hỗ trợ: ${error.message}`,
            'error'
          )
          console.error('Bulk delete error:', error)
        } finally {
          loading.value = false
        }
      }
    )
  }
}

// Reply to support request
const startEdit = (item) => {
  if (!item?.id) {
    showNotificationMessage('ID yêu cầu hỗ trợ không hợp lệ', 'error')
    return
  }
  editingId.value = item.id
  replyContent.value = item.admin_reply || ''
  replyError.value = ''
  validateReply()
}

const cancelEdit = () => {
  if (editingId.value && isProcessing.value[editingId.value]) {
    showNotificationMessage('Đang xử lý, vui lòng chờ...', 'error')
    return
  }
  editingId.value = null
  replyContent.value = ''
  replyError.value = ''
  isProcessing.value = { ...isProcessing.value, [editingId.value]: false }
}

const validateReply = () => {
  console.log('Validating reply:', {
    replyContent: replyContent.value,
    trimmed: replyContent.value?.trim(),
    length: replyContent.value?.length
  })
  replyError.value = ''
  if (!replyContent.value?.trim()) {
    replyError.value = 'Vui lòng nhập nội dung phản hồi'
  } else if (replyContent.value.length > 500) {
    replyError.value = 'Phản hồi không được vượt quá 500 ký tự'
  }
}

const sendReply = async (item) => {
  validateReply()
  console.log('Send button disabled state:', {
    isProcessing: isProcessing.value[item.id],
    hasContent: !!replyContent.value?.trim(),
    hasError: !!replyError.value
  })
  if (replyError.value || !replyContent.value?.trim()) {
    showNotificationMessage('Vui lòng kiểm tra nội dung phản hồi', 'error')
    return
  }

  try {
    isProcessing.value = { ...isProcessing.value, [item.id]: true }
    const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
    if (!token) throw new Error('Không tìm thấy token truy cập')

    console.log('Sending reply to:', `${apiBase}/admin/supports/${item.id}/reply`, {
      admin_reply: replyContent.value.trim()
    })

    const response = await secureFetch(`${apiBase}/admin/supports/${item.id}/reply`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ admin_reply: replyContent.value.trim() }),
      cache: 'no-store'
    }, ['admin'])

    console.log('Reply response:', response)

    // Update local state
    const updatedItem = supports.value.find(s => s.id === item.id)
    if (updatedItem) {
      updatedItem.admin_reply = replyContent.value.trim()
      updatedItem.replied_at = new Date().toISOString()
    }

    showNotificationMessage('Đã gửi phản hồi và email cho người dùng.', 'success')
    editingId.value = null
    replyContent.value = ''
    replyError.value = ''
    await fetchSupports(currentPage.value)
  } catch (err) {
    replyError.value = err?.response?.data?.message || `Gửi phản hồi thất bại: ${err.message}`
    showNotificationMessage(replyError.value, 'error')
    console.error('Error replying to support:', {
      message: err.message,
      response: err?.response?.data,
      status: err?.response?.status
    })
  } finally {
    isProcessing.value = { ...isProcessing.value, [item.id]: false }
  }
}

// Delete support request
const confirmDelete = (item) => {
  if (!item?.id) {
    showNotificationMessage('Yêu cầu hỗ trợ không hợp lệ', 'error')
    return
  }
  showConfirmationDialog(
    'Xác nhận xóa yêu cầu hỗ trợ',
    `Bạn có chắc chắn muốn xóa yêu cầu hỗ trợ ID ${item.id} của ${item.name || 'Ẩn danh'}?`,
    async () => {
      try {
        isProcessing.value = { ...isProcessing.value, [item.id]: true }
        const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
        if (!token) throw new Error('Không tìm thấy token truy cập')

        await secureFetch(`${apiBase}/admin/supports/${item.id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` },
          cache: 'no-store'
        }, ['admin'])
        showNotificationMessage('Đã xóa yêu cầu hỗ trợ thành công', 'success')
        await fetchSupports(currentPage.value)
      } catch (err) {
        showNotificationMessage(
          err?.response?.data?.message || `Xóa thất bại: ${err.message}`,
          'error'
        )
        console.error('Error deleting support:', err)
      } finally {
        isProcessing.value = { ...isProcessing.value, [item.id]: false }
      }
    }
  )
}

// Utility functions
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleString('vi-VN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return 'Ngày không hợp lệ'
  }
}

const truncateText = (text, maxLength) => {
  if (!text) return 'Không có'
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text
}

// Notification and confirmation dialog
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => {
    showNotification.value = false
  }, 3000)
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
  if (confirmAction.value) {
    await confirmAction.value()
  }
  closeConfirmDialog()
}

// Lifecycle hooks
onMounted(() => {
  fetchSupports()
})

// Debugging watchers
watch(supports, (newSupports) => {
  console.log('Supports updated:', newSupports)
})

watch(loading, (newValue) => {
  console.log('Loading state changed:', newValue)
})

watch(isProcessing, (newValue) => {
  console.log('Processing state changed:', newValue)
})

watch(replyContent, (newValue) => {
  console.log('Reply content changed:', newValue)
})

watch(replyError, (newValue) => {
  console.log('Reply error changed:', newValue)
})
</script>

<style scoped>
table tr:hover {
  background-color: #f1f5f9;
}
input:focus,
textarea:focus,
select:focus {
  outline: none;
}
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
button {
  position: relative;
  overflow: hidden;
  z-index: 10;
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
  transition: transform 0.5s, opacity 1s;
}
button:active::after {
  transform: scale(0, 0);
  opacity: 0.2;
  transition: 0s;
}
</style>