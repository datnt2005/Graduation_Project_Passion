<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header with Create Button -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý danh mục bài viết</h1>
        <NuxtLink
          to="/admin/post-categories/create-post-category"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Thêm danh mục
        </NuxtLink>
      </div>

      <!-- Notification -->
      <div v-if="showNotification"
        class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
        <div class="flex-1">
          <p class="text-sm font-medium" :class="notificationType === 'success' ? 'text-green-700' : 'text-red-700'">
            {{ notificationMessage }}
          </p>
        </div>
        <button @click="showNotification = false" class="text-gray-400 hover:text-gray-500">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm mt-4">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left w-10">
              #
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Hình ảnh
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Tên danh mục
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Đường dẫn
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Thao tác
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.id" :class="{ 'bg-gray-50': cat.id % 2 === 0 }"
            class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left w-10">{{ cat.id }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <img v-if="cat.image_url" :src="cat.image_url" alt="Ảnh" class="w-12 h-12 object-cover rounded" />
              <span v-else class="text-gray-500">Không có hình</span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left text-gray-500">
              {{ cat.name }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left text-gray-500">
              {{ cat.slug }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <div class="relative inline-block text-left">
                <button @click="toggleDropdown(cat.id)"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-800 focus:outline-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01" />
                  </svg>
                </button>
                <div v-if="activeDropdown === cat.id"
                  class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                  <div class="py-1" role="menu">
                    <NuxtLink :to="`/admin/post-categories/edit-post-category/${cat.id}`"
                      class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                      role="menuitem">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Sửa
                    </NuxtLink>
                    <button @click="confirmDelete(cat)"
                      class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                      role="menuitem">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
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
          <tr v-if="categories.length === 0">
            <td colspan="5" class="text-center text-gray-500 py-6">Chưa có danh mục nào.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

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
            <button class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium"
              @click="handleConfirmAction">
              Xác nhận
            </button>
            <button class="px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-sm font-medium"
              @click="closeConfirmDialog">
              Hủy
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

definePageMeta({
  layout: 'default-admin'
})

const categories = ref([])
const activeDropdown = ref(null)

const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('success')

const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const confirmAction = ref(null)

const fetchCategories = async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await $fetch(`${apiBase}/post-categories`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    categories.value = res.data || []
  } catch (e) {
    showNotificationMessage('Không thể tải danh mục.', 'error')
  }
}

const toggleDropdown = (id) => {
  activeDropdown.value = activeDropdown.value === id ? null : id
}

const editPostCategory = (id) => {
  window.location.href = `/admin/post-categories/edit-post-category/${id}`
}

const confirmDelete = (cat) => {
  showConfirmationDialog(
    'Xác nhận xóa danh mục',
    `Bạn có chắc chắn muốn xóa danh mục "${cat.name}" không?`,
    async () => {
      try {
        const token = localStorage.getItem('access_token')
        await $fetch(`${apiBase}/post-categories/${cat.id}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token}` }
        })
        showNotificationMessage('Đã xóa danh mục.', 'success')
        categories.value = categories.value.filter(c => c.id !== cat.id)
      } catch (e) {
        showNotificationMessage('Không thể xóa danh mục.', 'error')
      }
    }
  )
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

onMounted(fetchCategories)
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
