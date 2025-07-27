<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header with Create Button -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý bài viết</h1>
        <button @click="router.push('/admin/posts/create-post')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Thêm bài viết
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ totalPosts }})</span>
        </div>
        <select v-model="selectedCategory"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả danh mục</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <select v-model="selectedStatus"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả trạng thái</option>
            <option value="published">Xuất bản</option>
            <option value="draft">Bản nháp</option>
          </select>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm bài viết..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64" />
            <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd" />
            </svg>
          </div>
          
        </div>
      </div>

      <!-- Action Bar -->
      <div
        class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-t border-gray-300">
        <select v-model="selectedAction"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Hành động hàng loạt</option>
          <option value="published">Xuất bản</option>
          <option value="draft">Chuyển thành bản nháp</option>
          <option value="delete">Xóa</option>
          <option value="reassign">Gán danh mục</option>
        </select>
        <select v-if="selectedAction === 'reassign'" v-model="reassignCategory"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Chọn danh mục</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <button @click="applyBulkAction" :disabled="isBulkActionDisabled" :class="[
          'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
          isBulkActionDisabled
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-blue-600 text-white hover:bg-blue-700',
        ]">
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <div class="ml-auto text-sm text-gray-600">
          {{ selectedPosts.length }} được chọn / {{ filteredPosts.length }}
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Tiêu đề
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Danh mục
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Người viết
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Ngày tạo
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Trạng thái
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Thao tác
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="post in filteredPosts" :key="post.id" :class="{ 'bg-gray-50': post.id % 2 === 0 }"
            class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectedPosts" :value="post.id" />
            </td>
            <td
              class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700 hover:underline cursor-pointer"
              @click="editPost(post.id)">
              {{ truncateText(post.title, 40) }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ post.category?.name || 'Không có' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ post.user?.name || 'Không xác định' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ formatDate(post.created_at) }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <span :class="[
                post.status === 'published'
                  ? 'text-green-600'
                  : post.status === 'draft'
                    ? 'text-yellow-600'
                    : 'text-gray-600',
              ]">
                {{ post.status === 'published' ? 'Xuất bản' : post.status === 'draft' ? 'Bản nháp' : 'Không xác định' }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <div class="relative inline-block text-left">
                <button @click="toggleDropdown(post.id, $event)"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-800 focus:outline-none z-10"
                  :disabled="postLoading[post.id]">
                  <svg v-if="!postLoading[post.id]" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01" />
                  </svg>
                  <svg v-else class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8h8a8 8 0 01-8 8 8 8 0 01-8-8z">
                    </path>
                  </svg>
                </button>
                <div v-if="activeDropdown === post.id"
                  class="fixed mt-2 w-36 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-[100]"
                  :style="dropdownPosition">
                  <div class="py-1" role="menu">
                    <button @click="editPost(post.id)"
                      class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                      role="menuitem">
                      <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Sửa
                    </button>
                    <button @click="confirmDelete(post)"
                      class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                      role="menuitem">
                      <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16" />
                      </svg>
                      Xóa
                    </button>
                  </div>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="filteredPosts.length === 0">
            <td colspan="8" class="text-center text-gray-500 py-6">
              Chưa có bài viết nào.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination Component -->
      <Pagination :currentPage="currentPage" :lastPage="lastPage" @change="fetchPosts" class="mt-4" />
    </div>
  </div>

  <!-- Notification Popup -->
  <Teleport to="body">
    <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100"
      leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
      <div v-if="showNotification"
        class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-[100]">
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
      <div v-if="showConfirmDialog" class="fixed inset-0 z-[100] overflow-y-auto">
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
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                @click="closeConfirmDialog">
                Hủy
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useRuntimeConfig } from '#app'
import Pagination from '~/components/Pagination.vue'
import { secureFetch } from '@/utils/secureFetch'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const router = useRouter()

definePageMeta({
  layout: 'default-admin'
})

const posts = ref([])
const categories = ref([])
const selectedPosts = ref([])
const selectAll = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')
const selectedAction = ref('')
const reassignCategory = ref('')
const totalPosts = ref(0)
const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' })
const loading = ref(false)
const postLoading = ref({})
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

// Computed property to disable bulk action button
const isBulkActionDisabled = computed(() => {
  return (
    !selectedAction.value ||
    selectedPosts.value.length === 0 ||
    loading.value ||
    (selectedAction.value === 'reassign' && !reassignCategory.value)
  )
})

// Computed property for filtered posts
const filteredPosts = computed(() => {
  let result = [...posts.value]

  if (searchQuery.value) {
    const query = searchQuery.value.trim().toLowerCase()
    result = result.filter(post =>
      post?.title?.toLowerCase()?.includes(query) ||
      post?.category?.name?.toLowerCase()?.includes(query) ||
      post?.user?.name?.toLowerCase()?.includes(query)
    )
  }

  if (selectedCategory.value) {
    result = result.filter(post => post?.category?.id === selectedCategory.value)
  }

  if (selectedStatus.value) {
    result = result.filter(post => post?.status === selectedStatus.value)
  }

  return result
})

// Fetch posts from API
const fetchPosts = async (page = 1) => {
  try {
    loading.value = true
    currentPage.value = page

    const response = await secureFetch(
      `${apiBase}/posts/all?page=${page}&per_page=${perPage}`,
      {
        headers: {
          Accept: 'application/json'
        }
      },
      ['admin'] 
    )
    // Gán dữ liệu bài viết sau khi xử lý
    posts.value = response.data.map(post => ({
      ...post,
      id: post.id ?? null,
      title: post.title || 'Không có tiêu đề',
      status: post.status || 'unknown',
      created_at: post.created_at || null,
      category: post.category || null,
      user: post.user || null,
      thumbnail_url: post.thumbnail_url || null
    }))

    // Gán thông tin phân trang
    currentPage.value = response.data.current_page || 1
    lastPage.value = response.data.last_page || 1
    totalPosts.value = response.data.total

  } catch (err) {
    // Hiển thị thông báo lỗi
    const errorMessage = err?.response?.data?.message || err.message || 'Đã xảy ra lỗi không xác định.'
    showNotificationMessage(`Không thể tải danh sách bài viết: ${errorMessage}`, 'error')
    console.error('Lỗi fetchPosts:', err)
  } finally {
    loading.value = false
  }
}

// Fetch categories for filter dropdown
const fetchCategories = async () => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Không tìm thấy token truy cập')

    const response = await $fetch(`${apiBase}/post-categories`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (!response?.data?.data || !Array.isArray(response.data.data)) {
      throw new Error('Phản hồi API danh mục không hợp lệ')
    }

    categories.value = response.data.data.map(category => ({
      ...category,
      id: category.id || null,
      name: category.name || 'Không có tên'
    }))
  } catch (err) {
    showNotificationMessage(`Không thể tải danh mục: ${err.message}`, 'error')
    console.error('Fetch categories error:', err)
  }
}

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedPosts.value = filteredPosts.value
      .map(p => p.id)
      .filter(id => id !== null && id !== undefined)
  } else {
    selectedPosts.value = []
  }
}

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedPosts.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một bài viết', 'error')
    return
  }

  if (selectedAction.value === 'delete') {
    showConfirmationDialog(
      'Xác nhận xóa hàng loạt',
      `Bạn có chắc chắn muốn xóa ${selectedPosts.value.length} bài viết đã chọn?`,
      async () => {
        try {
          loading.value = true
          const token = localStorage.getItem('access_token')
          if (!token) throw new Error('Không tìm thấy token truy cập')

          const deletePromises = selectedPosts.value.map(id =>
            $fetch(`${apiBase}/posts/${id}`, {
              method: 'DELETE',
              headers: { Authorization: `Bearer ${token}` }
            })
          )
          await Promise.all(deletePromises)
          showNotificationMessage('Xóa các bài viết thành công!', 'success')
          selectedPosts.value = []
          selectAll.value = false
          selectedAction.value = ''
          reassignCategory.value = ''
          await fetchPosts()
        } catch (error) {
          showNotificationMessage(`Có lỗi xảy ra khi xóa bài viết: ${error.message}`, 'error')
          console.error('Bulk delete error:', error)
        } finally {
          loading.value = false
        }
      }
    )
  } else if (selectedAction.value === 'published' || selectedAction.value === 'draft') {
    showConfirmationDialog(
      `Xác nhận cập nhật trạng thái`,
      `Bạn có chắc chắn muốn chuyển ${selectedPosts.value.length} bài viết sang trạng thái "${selectedAction.value === 'published' ? 'Xuất bản' : 'Bản nháp'}"?`,
      async () => {
        try {
          loading.value = true
          const token = localStorage.getItem('access_token')
          if (!token) throw new Error('Không tìm thấy token truy cập')

          const updatePromises = selectedPosts.value.map(id =>
            $fetch(`${apiBase}/posts/${id}`, {
              method: 'PUT',
              headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json'
              },
              body: { status: selectedAction.value }
            })
          )
          await Promise.all(updatePromises)
          showNotificationMessage('Cập nhật trạng thái bài viết thành công!', 'success')
          selectedPosts.value = []
          selectAll.value = false
          selectedAction.value = ''
          reassignCategory.value = ''
          await fetchPosts()
        } catch (error) {
          showNotificationMessage(`Có lỗi xảy ra khi cập nhật trạng thái: ${error.message}`, 'error')
          console.error('Bulk update status error:', error)
        } finally {
          loading.value = false
        }
      }
    )
  } else if (selectedAction.value === 'reassign') {
    if (!reassignCategory.value) {
      showNotificationMessage('Vui lòng chọn danh mục để gán', 'error')
      return
    }
    showConfirmationDialog(
      'Xác nhận gán danh mục',
      `Bạn có chắc chắn muốn gán ${selectedPosts.value.length} bài viết sang danh mục "${categories.value.find(c => c.id === reassignCategory.value)?.name || 'Không xác định'}"?`,
      async () => {
        try {
          loading.value = true
          const token = localStorage.getItem('access_token')
          if (!token) throw new Error('Không tìm thấy token truy cập')

          const updatePromises = selectedPosts.value.map(id =>
            $fetch(`${apiBase}/posts/${id}`, {
              method: 'PUT',
              headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json'
              },
              body: { category_id: reassignCategory.value }
            })
          )
          await Promise.all(updatePromises)
          showNotificationMessage('Gán danh mục bài viết thành công!', 'success')
          selectedPosts.value = []
          selectAll.value = false
          selectedAction.value = ''
          reassignCategory.value = ''
          await fetchPosts()
        } catch (error) {
          showNotificationMessage(`Có lỗi xảy ra khi gán danh mục: ${error.message}`, 'error')
          console.error('Bulk reassign category error:', error)
        } finally {
          loading.value = false
        }
      }
    )
  }
}

// Edit post
const editPost = (id) => {
  if (!id) {
    showNotificationMessage('ID bài viết không hợp lệ', 'error')
    return
  }
  router.push(`/admin/posts/edit/${id}`)
}

// Delete post
const confirmDelete = (post) => {
  if (!post || !post.id) {
    showNotificationMessage('Bài viết không hợp lệ', 'error')
    return
  }
  showConfirmationDialog(
    'Xác nhận xóa bài viết',
    `Bạn có chắc chắn muốn xóa bài viết "${post.title || 'Không có tiêu đề'}" không?`,
    async () => {
      try {
        postLoading.value = { ...postLoading.value, [post.id]: true }
        const token = localStorage.getItem('access_token')
        if (!token) throw new Error('Không tìm thấy token truy cập')

        await $fetch(`${apiBase}/posts/${post.id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` }
        })
        showNotificationMessage('Đã xóa bài viết thành công', 'success')
        await fetchPosts()
      } catch (err) {
        showNotificationMessage(`Xóa thất bại: ${err.message}`, 'error')
        console.error('Delete post error:', err)
      } finally {
        postLoading.value = { ...postLoading.value, [post.id]: false }
      }
    }
  )
}

// Toggle dropdown
const toggleDropdown = (id, event) => {
  if (!id) {
    console.error('Invalid post ID:', id)
    showNotificationMessage('ID bài viết không hợp lệ', 'error')
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
      const dropdownHeight = 100 // Approximate dropdown height in pixels

      // Adjust position to ensure dropdown stays within viewport
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

// Format date
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

// Truncate text
const truncateText = (text, maxLength) => {
  if (!text) return 'Không có'
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text
}

// Show notification
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => {
    showNotification.value = false
  }, 3000)
}

// Close confirm dialog
const closeConfirmDialog = () => {
  showConfirmDialog.value = false
  confirmAction.value = null
}

// Handle confirm action
const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value()
  }
  closeConfirmDialog()
}

// Show confirmation dialog
const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title
  confirmDialogMessage.value = message
  confirmAction.value = action
  showConfirmDialog.value = true
}

// Lifecycle hooks
onMounted(() => {
  fetchPosts()
  fetchCategories()
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
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