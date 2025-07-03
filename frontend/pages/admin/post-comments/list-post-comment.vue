<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-4xl mx-auto mt-8 bg-white rounded shadow p-6">
      <h1 class="text-xl font-semibold text-gray-800 mb-6">Quản lý bình luận bài viết</h1>
      <!-- Search Bar -->
      <div class="mb-4">
        <input v-model="searchQuery" type="text" placeholder="Tìm kiếm bình luận..."
          class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 w-full" />
      </div>
      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left">ID</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Bài viết</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Người bình luận</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Nội dung</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Ngày tạo</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Hành động</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Phản hồi</th> <!-- Thêm tiêu đề cột phản hồi -->
          </tr>
        </thead>
        <tbody>
          <tr v-for="comment in filteredComments" :key="comment.id" :class="{ 'bg-gray-50': comment.id % 2 === 0 }"
            class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2">{{ comment.id }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ comment.post?.title }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ comment.user?.name }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ truncateText(comment.content, 60) }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ formatDate(comment.created_at) }}</td>
            <td class="border border-gray-300 px-3 py-2">
              <button @click="confirmDelete(comment)"
                class="px-3 py-1 rounded bg-red-100 text-red-600 hover:bg-red-200 transition">Xóa</button>
            </td>
            <td>
              <input v-model="comment.admin_reply" placeholder="Phản hồi..." class="border rounded px-2 py-1 text-xs" />
              <button @click="replyComment(comment)" class="ml-2 px-2 py-1 bg-blue-500 text-white rounded text-xs">Gửi</button>
            </td> <!-- Thêm ô nhập phản hồi và nút gửi -->
          </tr>
          <tr v-if="filteredComments.length === 0">
            <td colspan="7" class="text-center text-gray-500 py-6">Chưa có bình luận nào.</td> <!-- Cập nhật colspan -->
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
        <div v-if="showConfirmDialog" class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfirmDialog"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
            <div
              class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div
                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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
                <button type="button"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="handleConfirmAction">
                  Xác nhận
                </button>
                <button type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="closeConfirmDialog">
                  Hủy
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'


definePageMeta({
  layout: 'default-admin'
});
const comments = ref([])
const searchQuery = ref('')
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const confirmAction = ref(null)

const fetchComments = async () => {
  try {
    const token = localStorage.getItem('access_token')
    comments.value = await $fetch('http://localhost:8000/api/post-comments', {
      headers: { Authorization: `Bearer ${token}` }
    })
  } catch (err) {
    showNotificationMessage('Không thể tải danh sách bình luận', 'error')
    console.error(err)
  }
}

const confirmDelete = (comment) => {
  showConfirmationDialog(
    'Xác nhận xóa bình luận',
    `Bạn có chắc chắn muốn xóa bình luận này không?`,
    async () => {
      try {
        const token = localStorage.getItem('access_token')
        await $fetch(`http://localhost:8000/api/post-comments/${comment.id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` }
        })
        showNotificationMessage('Đã xóa bình luận', 'success')
        comments.value = comments.value.filter(c => c.id !== comment.id)
      } catch (err) {
        showNotificationMessage('Xóa thất bại', 'error')
      }
    }
  )
}

const replyComment = async (comment) => {
  const token = localStorage.getItem('access_token')
  await $fetch(`http://localhost:8000/api/post-comments/${comment.id}`, {
    method: 'PATCH',
    headers: { Authorization: `Bearer ${token}` },
    body: { admin_reply: comment.admin_reply }
  })
  showNotificationMessage('Đã phản hồi', 'success')
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('vi-VN')
}

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text
}

const filteredComments = computed(() => {
  if (!searchQuery.value) return comments.value
  const query = searchQuery.value.toLowerCase()
  return comments.value.filter(comment =>
    comment.content?.toLowerCase().includes(query) ||
    comment.user?.name?.toLowerCase().includes(query) ||
    comment.post?.title?.toLowerCase().includes(query)
  )
})

const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => {
    showNotification.value = false
  }, 3000)
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

const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title
  confirmDialogMessage.value = message
  confirmAction.value = action
  showConfirmDialog.value = true
}

onMounted(fetchComments)
</script>