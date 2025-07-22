<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header with Create Button -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý ảnh quảng cáo</h1>
        <NuxtLink
          to="/admin/banners/create-banner"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Thêm hình ảnh
        </NuxtLink>
      </div>

      <!-- Search -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="relative w-full max-w-xs">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Tìm kiếm banner..."
            class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-full"
          />
          <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
              d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
              clip-rule="evenodd" />
          </svg>
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left w-10">ID</th>
            <th class="border border-gray-300 px-3 py-2 text-left w-32">Hình ảnh</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tiêu đề</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Trạng thái</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Loại</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Ngày tạo</th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="banner in filteredBanners"
            :key="banner.id"
            :class="{ 'bg-gray-50': banner.id % 2 === 0 }"
            class="border-b border-gray-300"
          >
            <td class="border border-gray-300 px-3 py-2 text-left w-10">{{ banner.id }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left w-32">
              <img
                v-if="banner.image_url"
                :src="banner.image_url"
                alt="banner"
                class="w-20 h-12 object-cover rounded border border-gray-200 bg-white"
              />
              <div v-else class="w-20 h-12 flex items-center justify-center bg-gray-100 text-gray-400 rounded border border-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700">
              {{ banner.title }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <span
                :class="{
                  'px-2 py-1 rounded text-xs font-semibold': true,
                  'bg-green-100 text-green-700': banner.status === 'active',
                  'bg-gray-200 text-gray-600': banner.status === 'inactive'
                }"
              >
                {{ banner.status === 'active' ? 'Đang hiển thị' : 'Ẩn' }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <span v-if="banner.type === 'popup'" class="text-red-500 font-bold">Popup</span>
              <span v-else>Banner</span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">{{ formatDate(banner.created_at) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <div class="relative inline-block text-left">
                <button
                  @click="toggleDropdown(banner.id)"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-800 focus:outline-none"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01" />
                  </svg>
                </button>
                <div
                  v-if="activeDropdown === banner.id"
                  class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                >
                  <div class="py-1" role="menu">
                    <button
                      @click="editBanner(banner.id)"
                      class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                    >
                      <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Sửa
                    </button>
                    <button
                      @click="confirmDelete(banner)"
                      class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                    >
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
          <tr v-if="filteredBanners.length === 0">
            <td colspan="6" class="text-center text-gray-500 py-6">Chưa có banner nào.</td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination Component -->
      <Pagination :currentPage="currentPage" :lastPage="lastPage" @change="fetchBanners" class="mt-4" />
    </div>
  </div>

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
        class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50"
      >
        <div class="flex-shrink-0">
          <svg
            class="h-6 w-6"
            :class="notificationType === 'success' ? 'text-green-400' : 'text-red-500'"
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
              d="M12 9v2m0 4h.01M5.06 20h13.88c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 17c-.77 1.333.19 3 1.72 3z"
            />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">
            {{ notificationMessage }}
          </p>
        </div>
        <div class="flex-shrink-0">
          <button @click="showNotification = false" class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
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
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
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
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

definePageMeta({
  layout: 'default-admin'
})

const banners = ref([])
const searchQuery = ref('')
const activeDropdown = ref(null)

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

const fetchBanners = async (page = 1) => {
  try {
    const token = localStorage.getItem('access_token')
    const response = await $fetch(`${apiBase}/banners?page=${page}&per_page=${perPage}&search=${searchQuery.value}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    banners.value = response.data.data || [] // Get the data array
    currentPage.value = response.data.current_page || 1 // Set the current page
    lastPage.value = response.data.last_page || 1 // Set the last page
  } catch (err) {
    console.error('API Error:', err)
  }
}

const editBanner = (id) => {
  window.location.href = `/admin/banners/edit-banner/${id}`
}

const confirmDelete = (banner) => {
  showConfirmationDialog(
    'Xác nhận xóa banner',
    `Bạn có chắc chắn muốn xóa banner "${banner.title}" không?`,
    async () => {
      try {
        const token = localStorage.getItem('access_token')
        await $fetch(`${apiBase}/banners/${banner.id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` }
        })
        showNotificationMessage('Đã xóa banner', 'success')
        banners.value = banners.value.filter(b => b.id !== banner.id)
      } catch (err) {
        showNotificationMessage('Xóa thất bại', 'error')
      }
    }
  )
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('vi-VN')
}

const filteredBanners = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return query
    ? banners.value.filter(b => b.title?.toLowerCase().includes(query))
    : banners.value
})

const toggleDropdown = (id) => {
  activeDropdown.value = activeDropdown.value === id ? null : id
}

const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  setTimeout(() => (showNotification.value = false), 3000)
}

const showSuccessNotification = (message) => {
  notificationMessage.value = message
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
  if (confirmAction.value) await confirmAction.value()
  closeConfirmDialog()
}

onMounted(fetchBanners)
</script>

