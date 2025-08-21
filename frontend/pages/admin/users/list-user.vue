<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý người dùng</h1>
        <button @click="router.push('/admin/users/create-user')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Thêm người dùng
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ users.length }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm tên, email, vai trò..."
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
          <option value="delete">Xóa</option>
          <option value="set_admin">Đặt làm quản trị viên</option>
          <option value="set_shop_manager">Đặt làm quản lý cửa hàng</option>
        </select>
        <button @click="applyBulkAction" :disabled="!selectedAction || selectedUsers.length === 0 || loading" :class="[(!selectedAction || selectedUsers.length === 0 || loading) 
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-blue-600 text-white hover:bg-blue-700', 
              'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150']">
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <select v-model="filterRole"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Tất cả vai trò</option>
          <option value="admin">Quản trị viên</option>
          <option value="seller">Quản lý cửa hàng</option>
          <option value="user">Người dùng</option>
        </select>
        <div class="ml-auto text-sm text-gray-600">
          {{ selectedUsers.length }} đã chọn / {{ filteredUsers.length }} người dùng
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
              Ảnh đại diện
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Họ tên
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Email
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Vai trò
            </th>
              <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
                 Trạng thái
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Bài viết
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Thao tác
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in paginatedUsers" :key="user.id" :class="{'bg-gray-50': user.id % 2 === 0}"
  class="border-b border-gray-300">

            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectedUsers" :value="user.id" />
            </td>
            <td class="border border-gray-300 px-3 py-2 text-center align-middle">
              <div class="flex items-center justify-center">
                <img :src="getAvatarUrl(user.avatar)" alt="Avatar" class="w-12 h-12 rounded-full object-cover"
                  loading="lazy" />
              </div>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ user.name || '–' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ user.email }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ convertRole(user.role) }}
            </td>
              <td class="border border-gray-300 px-3 py-2 text-left">
              {{ statusLabel[user.status] || 'Chưa xác định' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ user.posts ?? 0 }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <button @click="editUser(user.id)" class="text-blue-600 hover:underline px-2">Sửa</button>
              <button @click="confirmDelete(user)" class="text-red-600 hover:underline px-2">Xóa</button>
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination -->
<div class="flex justify-between items-center px-4 py-3 bg-white border-t border-gray-200">
  <div class="text-sm text-gray-600">
    Trang {{ currentPage }} / {{ totalPages }}  
    ({{ filteredUsers.length }} người dùng)
  </div>
  <div class="flex items-center space-x-1">
    <button @click="currentPage = Math.max(1, currentPage - 1)"
      :disabled="currentPage === 1"
      class="px-3 py-1 rounded-md border text-sm"
      :class="currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'">
      Trước
    </button>

    <button v-for="page in totalPages" :key="page"
      @click="currentPage = page"
      class="px-3 py-1 rounded-md border text-sm"
      :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100'">
      {{ page }}
    </button>

    <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
      :disabled="currentPage === totalPages"
      class="px-3 py-1 rounded-md border text-sm"
      :class="currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'">
      Sau
    </button>
  </div>
</div>

    </div>

    <!-- Notification -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-100"
        leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <div v-if="showNotification"
          class="fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex items-center space-x-3 z-50">
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notificationMessage }}
            </p>
          </div>
          <button @click="showNotification = false"
            class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
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
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
definePageMeta({
  layout: 'default-admin'
});
import { secureFetch } from '@/utils/secureFetch' 

const router = useRouter()
const config = useRuntimeConfig()
const api = config.public.apiBaseUrl

const users = ref([])
const loading = ref(false)
const searchQuery = ref('')
const filterRole = ref('')
const selectedUsers = ref([])
const selectAll = ref(false)
const selectedAction = ref('')
const showNotification = ref(false)
const notificationMessage = ref('')
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const userToDelete = ref(null)


// 📌 Pagination
const currentPage = ref(1)
const itemsPerPage = ref(5)  
const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value))

 




const fetchUsers = async () => {
  loading.value = true
  try {
    const json = await secureFetch(`${api}/users`, {}, ['admin'])
    users.value = json.data.map(u => ({
      id: u.id,
      username: u.username || (u.email ? u.email.split('@')[0] : ''),
      name: u.name,
      email: u.email,
      role: u.role,
      avatar: u.avatar,
      status: u.status,
      posts: u.posts ?? 0,
    }))
  } catch (e) {
    users.value = []
    showNotification.value = true
    notificationMessage.value = 'Không lấy được danh sách người dùng.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchUsers)
 
const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const q = searchQuery.value.toLowerCase()
    const byRole = !filterRole.value || (user.role && user.role.toLowerCase() === filterRole.value)
    const byQuery =
      !q ||
      user.username?.toLowerCase().includes(q) ||
      user.name?.toLowerCase().includes(q) ||
      user.email?.toLowerCase().includes(q) ||
      convertRole(user.role).toLowerCase().includes(q)
    return byRole && byQuery
  })
})
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredUsers.value.slice(start, end)
})

watch([filteredUsers, itemsPerPage], () => {
  currentPage.value = 1 // reset về trang 1 khi filter hoặc thay đổi số dòng
})


const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedUsers.value = filteredUsers.value.map(u => u.id)
  } else {
    selectedUsers.value = []
  }
}

watch(filteredUsers, () => {
  if (selectedUsers.value.length === filteredUsers.value.length && filteredUsers.value.length > 0) {
    selectAll.value = true
  } else {
    selectAll.value = false
  }
})

const applyBulkAction = async () => {
  if (selectedUsers.value.length === 0 || !selectedAction.value) return

  if (selectedAction.value === 'delete') {
    confirmDialogTitle.value = 'Xác nhận xóa người dùng'
    confirmDialogMessage.value = `Bạn có chắc muốn xóa ${selectedUsers.value.length} người dùng đã chọn không?`
    showConfirmDialog.value = true
    userToDelete.value = null
  } else {
    let role = ''
    if (selectedAction.value === 'set_admin') role = 'admin'
    if (selectedAction.value === 'set_shop_manager') role = 'seller'
    if (!role) return

    loading.value = true
   await secureFetch(`${api}/users/batch-add-role`,
   {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ids: selectedUsers.value, role }),
  },
  ['admin'])

    await fetchUsers()
    notificationMessage.value = 'Đã cập nhật vai trò cho người dùng.'
    showNotification.value = true
    selectedAction.value = ''
    selectedUsers.value = []
    selectAll.value = false
    loading.value = false
  }
}

const editUser = (id) => {
  router.push(`/admin/users/${id}/edit`)
}

const DEFAULT_AVATAR = 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/avatars/default.jpg'

const getAvatarUrl = (avatar) => {
  if (!avatar) return DEFAULT_AVATAR
  const cleaned = avatar.trim()
  if (
    cleaned.startsWith('http://') ||
    cleaned.startsWith('https://')
  ) {
    return cleaned
  }
  return `https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/${cleaned}`
}

const statusLabel = {
  active: 'Đang hoạt động',
  inactive: 'Không hoạt động',
  banned: 'Đã bị khóa',
}



const confirmDelete = (user) => {
  userToDelete.value = user
  confirmDialogTitle.value = 'Xác nhận xoá người dùng'
  confirmDialogMessage.value = `Bạn có chắc chắn muốn xoá người dùng "${user.username}"? Hành động này không thể hoàn tác.`
  showConfirmDialog.value = true
}
const handleDeleteConfirmed = async () => {
  try {
    await secureFetch(`${api}/users/${userToDelete.value.id}`, {}, ['admin'], {
      method: 'DELETE'
    })
    notificationMessage.value = `Đã xoá người dùng "${userToDelete.value.username}" thành công.`
    showNotification.value = true
  } catch (error) {
    notificationMessage.value = `Xoá người dùng thất bại: ${error.message}`
    showNotification.value = true
  } finally {
    showConfirmDialog.value = false
    userToDelete.value = null
    await fetchUsers()
  }
}


const closeConfirmDialog = () => {
  showConfirmDialog.value = false
  userToDelete.value = null
}

const handleConfirmAction = async () => {
  showConfirmDialog.value = false
  loading.value = true

  try {
    if (!userToDelete.value) {
      const json = await secureFetch(`${api}/users/batch-delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: selectedUsers.value }),
      }, ['admin'])

      if (!json.success) throw new Error(json.status || 'Xoá người dùng thất bại.')
      
      notificationMessage.value = 'Đã xóa người dùng đã chọn.'
      selectedUsers.value = []
      selectAll.value = false
      selectedAction.value = ''
    } else {
      const json = await secureFetch(`${api}/users/${userToDelete.value.id}`, {
        method: 'DELETE'
      }, ['admin'])

      if (!json.success) throw new Error(json.status || 'Xoá người dùng thất bại.')

      notificationMessage.value = `Đã xoá người dùng "${userToDelete.value.username}".`
      userToDelete.value = null
    }
  } catch (err) {
    notificationMessage.value = `Lỗi: ${err.message}`
  } finally {
    await fetchUsers()
    showNotification.value = true
    loading.value = false
  }
}


const convertRole = (role) => {
  if (!role) return 'Người dùng'
  switch (role.toLowerCase()) {
    case 'admin': return 'Quản trị viên'
    case 'seller': return 'Quản lý cửa hàng'
    case 'user': return 'Người dùng'
    default: return 'Người dùng'
  }
}
</script>