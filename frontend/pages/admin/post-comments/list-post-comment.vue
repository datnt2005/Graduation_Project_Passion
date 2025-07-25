<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý bình luận bài viết</h1>
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-600">
            {{ selectedComments.length }} được chọn / {{ totalComments }}
          </span>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ totalComments }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <select
            v-model="selectedStatus"
            @change="debouncedFetchComments"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Tất cả trạng thái</option>
            <option value="approved">Đã duyệt</option>
            <option value="pending">Chờ duyệt</option>
            <option value="rejected">Từ chối</option>
          </select>
          <div class="relative">
            <input
              v-model="searchQuery"
              @input="debouncedFetchComments"
              type="text"
              placeholder="Tìm kiếm theo nội dung, người dùng, hoặc bài viết..."
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
          <option value="approved">Duyệt</option>
          <option value="rejected">Từ chối</option>
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
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">ID</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Bài viết</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Người bình luận</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Nội dung</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Đánh giá</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày tạo</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Phản hồi</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="comment in comments"
            :key="comment.id"
            :class="{ 'bg-gray-50': comment.id % 2 === 0 }"
            class="border-b border-gray-300"
          >
            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectedComments" :value="comment.id" />
            </td>
            <td class="border border-gray-300 px-3 py-2">{{ comment.id }}</td>
            <td class="border border-gray-300 px-3 py-2">
              <a
                :href="`/posts/${comment.post?.slug}`"
                class="text-blue-600 hover:underline"
                target="_blank"
              >
                {{ truncateText(comment.post?.title, 40) || 'Không xác định' }}
              </a>
            </td>
            <td class="border border-gray-300 px-3 py-2">
              {{ comment.user?.name || 'Ẩn danh' }}
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <span class="whitespace-pre-line">{{ truncateText(comment.content, 60) }}</span>
              <div v-if="comment.media && comment.media.length" class="mt-2 flex gap-2 flex-wrap">
                <img
                  v-for="m in comment.media"
                  :key="m.id"
                  :src="m.url?.startsWith('http') ? m.url : `${mediaBase}${m.url}`"
                  class="w-12 h-12 object-cover rounded border"
                />
              </div>
            </td>
            <td class="border border-gray-300 px-3 py-2">
              {{ comment.rating || 'N/A' }} <i class="fa fa-star text-yellow-500" v-if="comment.rating"></i>
            </td>
            <td class="border border-gray-300 px-3 py-2">{{ formatDate(comment.created_at) }}</td>
            <td class="border border-gray-300 px-3 py-2">
              <span :class="[comment.status === 'approved' ? 'text-green-600' : comment.status === 'rejected' ? 'text-red-600' : 'text-yellow-600']">
                {{ comment.status === 'approved' ? 'Đã duyệt' : comment.status === 'rejected' ? 'Từ chối' : 'Chờ duyệt' }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <div v-if="comment.admin_reply" class="mb-2 text-xs text-gray-600">
                <strong>Phản hồi:</strong> {{ truncateText(comment.admin_reply, 50) }}
              </div>
              <div class="flex items-center gap-2">
                <input
                  v-model="comment.new_reply"
                  placeholder="Nhập phản hồi..."
                  class="border rounded px-2 py-1 text-xs w-40 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  :disabled="isProcessing[comment.id]"
                  @input="validateReply(comment)"
                />
                <button
                  @click="replyComment(comment)"
                  class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 transition"
                  :disabled="isProcessing[comment.id] || !comment.new_reply?.trim() || !!comment.reply_error"
                >
                  {{ comment.admin_reply ? 'Cập nhật' : 'Gửi' }}
                </button>
              </div>
              <p v-if="comment.reply_error" class="text-red-500 text-xs mt-1">{{ comment.reply_error }}</p>
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <div class="relative inline-block text-left">
                <button
                  @click="toggleDropdown(comment.id, $event)"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-800 focus:outline-none z-10"
                  :disabled="isProcessing[comment.id]"
                >
                  <svg
                    v-if="!isProcessing[comment.id]"
                    class="w-5 h-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01"
                    />
                  </svg>
                  <svg
                    v-else
                    class="w-5 h-5 animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8v8h8a8 8 0 01-8 8 8 8 0 01-8-8z"
                    ></path>
                  </svg>
                </button>
                <div
                  v-if="activeDropdown === comment.id"
                  class="fixed mt-2 w-36 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-[100]"
                  :style="dropdownPosition"
                >
                  <div class="py-1" role="menu">
                    <button
                      @click="openEditModal(comment)"
                      class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                      role="menuitem"
                    >
                      <svg
                        class="w-4 h-4 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        />
                      </svg>
                      Sửa
                    </button>
                    <button
                      @click="confirmDelete(comment)"
                      class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                      role="menuitem"
                    >
                      <svg
                        class="w-4 h-4 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"
                        />
                      </svg>
                      Xóa
                    </button>
                  </div>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="comments.length === 0">
            <td colspan="10" class="text-center text-gray-500 py-6">Chưa có bình luận nào.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <Pagination
        :currentPage="currentPage"
        :lastPage="lastPage"
        @change="fetchComments"
        class="mt-6"
      />

      <!-- Edit Modal -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="showEditModal" class="fixed inset-0 z-[100] overflow-y-auto">
            <div
              class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
              <div
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="closeEditModal"
              ></div>
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
              <div
                class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
              >
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                      <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Chỉnh sửa bình luận ID {{ editingComment?.id || 'N/A' }}
                      </h3>
                      <div class="mt-4">
                        <div class="mb-4">
                          <label class="block text-sm font-medium text-gray-700">Nội dung</label>
                          <textarea
                            v-model="editingComment.content"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            rows="4"
                            maxlength="2000"
                            @input="validateEditComment"
                          ></textarea>
                          <p v-if="editErrors.content" class="text-red-500 text-xs mt-1">
                            {{ editErrors.content }}
                          </p>
                        </div>
                        <div class="mb-4">
                          <label class="block text-sm font-medium text-gray-700">Phản hồi quản trị</label>
                          <textarea
                            v-model="editingComment.admin_reply"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            rows="4"
                            maxlength="500"
                            @input="validateEditComment"
                          ></textarea>
                          <p v-if="editErrors.admin_reply" class="text-red-500 text-xs mt-1">
                            {{ editErrors.admin_reply }}
                          </p>
                        </div>
                        <div class="mb-4">
                          <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                          <select
                            v-model="editingComment.status"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                          >
                            <option value="approved">Đã duyệt</option>
                            <option value="pending">Chờ duyệt</option>
                            <option value="rejected">Từ chối</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button
                    type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                    :disabled="!editingComment || isProcessing[editingComment?.id] || Object.keys(editErrors).length > 0"
                    @click="saveEditComment"
                  >
                    {{ isProcessing[editingComment?.id] ? 'Đang lưu...' : 'Lưu' }}
                  </button>
                  <button
                    type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="closeEditModal"
                  >
                    Hủy
                  </button>
                </div>
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
            <div
              class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
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
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useRuntimeConfig, useCookie } from '#app'
import Pagination from '~/components/Pagination.vue'
import { secureFetch } from '@/utils/secureFetch'

definePageMeta({
  layout: 'default-admin',
})

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl
const router = useRouter()

// State
const comments = ref([])
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedComments = ref([])
const selectAll = ref(false)
const selectedAction = ref('')
const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' })
const loading = ref(false)
const isProcessing = ref({})
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const confirmAction = ref(null)
const currentPage = ref(1)
const lastPage = ref(1)
const itemsPerPage = 10
const totalComments = ref(0)
const showEditModal = ref(false)
const editingComment = ref(null)
const editErrors = ref({})

// Debounce function for search and status changes
const debounce = (fn, delay) => {
  let timeout
  return (...args) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fn(...args), delay)
  }
}

// Fetch comments with server-side filtering
const fetchComments = async (page = 1) => {
  try {
    loading.value = true
    currentPage.value = page
    const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
    if (!token) throw new Error('Không tìm thấy token truy cập')

    const params = new URLSearchParams({ page, per_page: itemsPerPage })
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedStatus.value) params.append('status', selectedStatus.value)

    const response = await secureFetch(`${apiBase}/admin/post-comments?${params.toString()}`, {
      headers: { Authorization: `Bearer ${token}` },
      cache: 'no-store'
    }, ['admin'])

    comments.value = response.data.data.map(comment => ({
      ...comment,
      id: comment.id || null,
      content: comment.content || 'Không có nội dung',
      status: comment.status || 'pending',
      created_at: comment.created_at || null,
      post: comment.post || null,
      user: comment.user || null,
      media: comment.media || [],
      admin_reply: comment.admin_reply || '',
      new_reply: comment.admin_reply || '',
      reply_error: ''
    }))
    currentPage.value = response.data.current_page || 1
    lastPage.value = response.data.last_page || 1
    totalComments.value = response.data.total || 0
  } catch (err) {
    showNotificationMessage(
      err?.response?.data?.message || `Không thể tải danh sách bình luận: ${err.message}`,
      'error'
    )
    console.error('Error fetching comments:', err)
  } finally {
    loading.value = false
  }
}

const debouncedFetchComments = debounce(() => fetchComments(1), 300)

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedComments.value = comments.value
      .map(c => c.id)
      .filter(id => id !== null && id !== undefined)
  } else {
    selectedComments.value = []
  }
}

// Computed properties
const isBulkActionDisabled = computed(() => {
  return !selectedAction.value || selectedComments.value.length === 0 || loading.value
})

const totalPages = computed(() => lastPage.value)

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedComments.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một bình luận', 'error')
    return
  }

  const actionText = selectedAction.value === 'approved' ? 'duyệt' : selectedAction.value === 'rejected' ? 'từ chối' : 'xóa'
  showConfirmationDialog(
    `Xác nhận ${actionText} hàng loạt`,
    `Bạn có chắc chắn muốn ${actionText} ${selectedComments.value.length} bình luận đã chọn?`,
    async () => {
      try {
        loading.value = true
        const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
        if (!token) throw new Error('Không tìm thấy token truy cập')

        if (selectedAction.value === 'delete') {
          const deletePromises = selectedComments.value.map(id =>
            secureFetch(`${apiBase}/admin/post-comments/${id}`, {
              method: 'DELETE',
              headers: { Authorization: `Bearer ${token}` },
              cache: 'no-store'
            }, ['admin'])
          )
          await Promise.all(deletePromises)
          showNotificationMessage('Xóa các bình luận thành công!', 'success')
        } else {
          const status = selectedAction.value
          const updatePromises = selectedComments.value.map(id =>
            secureFetch(`${apiBase}/admin/post-comments/${id}`, {
              method: 'PUT',
              headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({ status }),
              cache: 'no-store'
            }, ['admin'])
          )
          await Promise.all(updatePromises)
          showNotificationMessage(`Cập nhật trạng thái bình luận thành công!`, 'success')
        }

        selectedComments.value = []
        selectAll.value = false
        selectedAction.value = ''
        await fetchComments(currentPage.value)
      } catch (error) {
        showNotificationMessage(
          error?.response?.data?.message || `Có lỗi xảy ra khi ${actionText} bình luận: ${error.message}`,
          'error'
        )
        console.error(`Bulk ${selectedAction.value} error:`, error)
      } finally {
        loading.value = false
      }
    }
  )
}

// Edit comment modal
const openEditModal = (comment) => {
  if (!comment?.id) {
    showNotificationMessage('ID bình luận không hợp lệ', 'error')
    return
  }
  editingComment.value = {
    id: comment.id,
    content: comment.content || '',
    admin_reply: comment.admin_reply || '',
    status: comment.status || 'pending'
  }
  editErrors.value = {}
  showEditModal.value = true
}

const closeEditModal = () => {
  if (editingComment.value && isProcessing.value[editingComment.value.id]) {
    showNotificationMessage('Đang xử lý, vui lòng chờ...', 'error')
    return
  }
  showEditModal.value = false
  editingComment.value = null
  editErrors.value = {}
}

const validateEditComment = () => {
  editErrors.value = {}
  if (!editingComment.value?.content?.trim()) {
    editErrors.value.content = 'Nội dung không được để trống'
  } else if (editingComment.value.content.length > 2000) {
    editErrors.value.content = 'Nội dung không được vượt quá 2000 ký tự'
  }
  if (editingComment.value.admin_reply && editingComment.value.admin_reply.length > 500) {
    editErrors.value.admin_reply = 'Phản hồi không được vượt quá 500 ký tự'
  }
}

const saveEditComment = async () => {
  if (!editingComment.value) {
    showNotificationMessage('Không tìm thấy bình luận để chỉnh sửa', 'error')
    return
  }

  validateEditComment()
  if (Object.keys(editErrors.value).length > 0) return

  try {
    isProcessing.value = { ...isProcessing.value, [editingComment.value.id]: true }
    const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
    if (!token) throw new Error('Không tìm thấy token truy cập')

    await secureFetch(`${apiBase}/admin/post-comments/${editingComment.value.id}`, {
      method: 'PUT',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        content: editingComment.value.content.trim(),
        admin_reply: editingComment.value.admin_reply?.trim() || null,
        status: editingComment.value.status
      }),
      cache: 'no-store'
    }, ['admin'])

    showNotificationMessage('Cập nhật bình luận thành công', 'success')
    await fetchComments(currentPage.value)
    closeEditModal()
  } catch (err) {
    showNotificationMessage(
      err?.response?.data?.message || `Cập nhật bình luận thất bại: ${err.message}`,
      'error'
    )
    console.error('Error updating comment:', err)
  } finally {
    if (editingComment.value) {
      isProcessing.value = { ...isProcessing.value, [editingComment.value.id]: false }
    }
  }
}

// Delete comment
const confirmDelete = (comment) => {
  if (!comment?.id) {
    showNotificationMessage('Bình luận không hợp lệ', 'error')
    return
  }
  showConfirmationDialog(
    'Xác nhận xóa bình luận',
    `Bạn có chắc chắn muốn xóa bình luận ID ${comment.id} của ${comment.user?.name || 'Ẩn danh'}?`,
    async () => {
      try {
        isProcessing.value = { ...isProcessing.value, [comment.id]: true }
        const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
        if (!token) throw new Error('Không tìm thấy token truy cập')

        await secureFetch(`${apiBase}/admin/post-comments/${comment.id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` },
          cache: 'no-store'
        }, ['admin'])
        showNotificationMessage('Đã xóa bình luận thành công', 'success')
        await fetchComments(currentPage.value)
      } catch (err) {
        showNotificationMessage(
          err?.response?.data?.message || `Xóa thất bại: ${err.message}`,
          'error'
        )
        console.error('Error deleting comment:', err)
      } finally {
        isProcessing.value = { ...isProcessing.value, [comment.id]: false }
      }
    }
  )
}

// Reply to comment
const validateReply = (comment) => {
  comment.reply_error = ''
  if (!comment.new_reply?.trim()) {
    comment.reply_error = 'Vui lòng nhập nội dung phản hồi'
  } else if (comment.new_reply.length > 500) {
    comment.reply_error = 'Phản hồi không được vượt quá 500 ký tự'
  }
}

const replyComment = async (comment) => {
  if (!comment.new_reply?.trim()) {
    comment.reply_error = 'Vui lòng nhập nội dung phản hồi'
    return
  }
  if (comment.reply_error) return

  try {
    isProcessing.value = { ...isProcessing.value, [comment.id]: true }
    const token = useCookie('access_token')?.value || localStorage.getItem('access_token')
    if (!token) throw new Error('Không tìm thấy token truy cập')

    await secureFetch(`${apiBase}/admin/post-comments/${comment.id}`, {
      method: 'PUT',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ admin_reply: comment.new_reply.trim() }),
      cache: 'no-store'
    }, ['admin'])
    
    comment.admin_reply = comment.new_reply
    showNotificationMessage(
      comment.admin_reply ? 'Đã cập nhật phản hồi' : 'Đã gửi phản hồi',
      'success'
    )
  } catch (err) {
    comment.reply_error = err?.response?.data?.message || 'Gửi phản hồi thất bại'
    showNotificationMessage(comment.reply_error, 'error')
    console.error('Error replying to comment:', err)
  } finally {
    isProcessing.value = { ...isProcessing.value, [comment.id]: false }
  }
}

// Toggle dropdown
const toggleDropdown = (id, event) => {
  if (!id) {
    console.error('Invalid comment ID:', id)
    showNotificationMessage('ID bình luận không hợp lệ', 'error')
    return
  }

  if (activeDropdown.value === id) {
    activeDropdown.value = null
  } else {
    activeDropdown.value = id
    nextTick(() => {
      const button = event.target.closest('button')
      if (!button) {
        console.error('Button not found for dropdown positioning')
        showNotificationMessage('Không thể định vị menu', 'error')
        activeDropdown.value = null
        return
      }

      const rect = button.getBoundingClientRect()
      const scrollY = window.scrollY || window.pageYOffset
      const scrollX = window.scrollX || window.pageXOffset
      const windowHeight = window.innerHeight
      const dropdownHeight = 100 // Approximate dropdown height

      const top = rect.bottom + scrollY + 8
      const adjustedTop = top + dropdownHeight > scrollY + windowHeight
        ? rect.top + scrollY - dropdownHeight - 8
        : top

      dropdownPosition.value = {
        top: `${adjustedTop}px`,
        left: `${rect.right + scrollX - 192}px`,
        width: '192px'
      }
    })
  }
}

// Close dropdown on outside click
const closeDropdown = (event) => {
  if (!event.target.closest('.relative') && !event.target.closest('.fixed')) {
    activeDropdown.value = null
  }
}

// Utility functions
const formatDate = (dateStr) => {
  if (!dateStr) return 'Không xác định'
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

const closeConfirmDialog = () => {
  showConfirmDialog.value = false
  confirmAction.value = null
}

const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title
  confirmDialogMessage.value = message
  confirmAction.value = action
  showConfirmDialog.value = true
}

const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value()
  }
  closeConfirmDialog()
}

// Lifecycle hooks
onMounted(() => {
  fetchComments()
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})

// Debugging watcher for state changes
watch(comments, (newComments) => {
  console.log('Comments updated:', newComments)
})

watch(loading, (newValue) => {
  console.log('Loading state changed:', newValue)
})

watch(isProcessing, (newValue) => {
  console.log('Processing state changed:', newValue)
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