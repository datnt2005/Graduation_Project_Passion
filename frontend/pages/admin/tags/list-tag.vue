<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header with Create Button -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý thẻ sản phẩm</h1>
        <button @click="router.push('/admin/tags/create-tag')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Thêm thẻ mới
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ totalTags }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm thẻ..."
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
        </select>
        <button @click="applyBulkAction" :disabled="!selectedAction || selectedTags.length === 0 || loading" :class="[
          'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
          (!selectedAction || selectedTags.length === 0 || loading)
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-blue-600 text-white hover:bg-blue-700'
        ]">
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <div class="ml-auto text-sm text-gray-600">
          {{ selectedTags.length }} được chọn / {{ filteredTags.length }}
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
              Hình ảnh
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Tên thẻ
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
          <tr v-for="tag in filteredTags" :key="tag.id" :class="{ 'bg-gray-50': tag.id % 2 === 0 }"
            class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectedTags" :value="tag.id" />
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <img v-if="tag.image" :src="`${mediaBase}${tag.image}`" alt="Tag Image"
                class="w-12 h-12 object-cover rounded" />
              <span v-else class="text-gray-500">Không có hình</span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left text-gray-500">
              {{ tag.name }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left text-gray-500">
              {{ tag.slug }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <div class="relative inline-block text-left">
                <button @click="toggleDropdown(tag.id)"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-800 focus:outline-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <Pagination :currentPage="currentPage" :lastPage="lastPage" @change="fetchTags" />

  <!-- Dropdown Portal -->
  <Teleport to="body">
    <Transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
      <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
        <div v-for="tag in tags" :key="tag.id" v-show="activeDropdown === tag.id"
          class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right"
          :style="dropdownPosition">
          <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
            <button @click="editTag(tag.id)"
              class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
              role="menuitem">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Sửa
            </button>
            <button @click="confirmDelete(tag)"
              class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
              role="menuitem">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Xóa
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

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
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useRuntimeConfig } from '#app';
import Pagination from '~/components/Pagination.vue';

definePageMeta({
  layout: 'default-admin'
});

const router = useRouter();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const tags = ref([]);
const selectedTags = ref([]);
const selectAll = ref(false);
const searchQuery = ref('');
const selectedAction = ref('');
const totalTags = ref(0);
const activeDropdown = ref(null);
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' });
const loading = ref(false);
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref(null);

const currentPage = ref(1);
const lastPage = ref(1);
const perPage = 10;
// Fetch tags from API
const fetchTags = async (page = 1) => {
  try {
    loading.value = true;
    currentPage.value = page;

    const response = await fetch(`${apiBase}/tags?page=${page}&per_page=${perPage}`);
    const result = await response.json();

    // ✅ Kiểm tra đúng cấu trúc mới
    if (!result || !result.data || !Array.isArray(result.data.tags)) {
      throw new Error('Phản hồi API không hợp lệ');
    }

    // ✅ Gán đúng dữ liệu
    tags.value = result.data.tags;
    currentPage.value = result.data.current_page || 1;
    lastPage.value = result.data.last_page || 1;
    totalTags.value = result.data.total || result.data.tags.length;
  } catch (error) {
    console.error('Lỗi khi fetch tags:', error);
    showNotificationMessage('Lỗi khi tải dữ liệu', 'error');
  } finally {
    loading.value = false;
  }
};

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedTags.value = tags.value.map(t => t.id);
  } else {
    selectedTags.value = [];
  }
};

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedTags.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một thẻ', 'error');
    return;
  }

  if (selectedAction.value === 'delete') {
    showConfirmationDialog(
      'Xác nhận xóa hàng loạt',
      `Bạn có chắc chắn muốn xóa ${selectedTags.value.length} thẻ đã chọn?`,
      async () => {
        try {
          loading.value = true;
          const deletePromises = selectedTags.value.map(id =>
            fetch(`${apiBase}/tags/${id}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json'
              }
            })
          );

          const responses = await Promise.all(deletePromises);
          const allSuccessful = responses.every(res => res.ok);
          if (allSuccessful) {
            showNotificationMessage('Xóa các thẻ thành công!', 'success');
            selectedTags.value = [];
            selectAll.value = false;
            selectedAction.value = '';
            await fetchTags();
          } else {
            showNotificationMessage('Có lỗi xảy ra khi xóa một số thẻ', 'error');
          }
        } catch (error) {
          console.error('Error deleting tags:', error);
          showNotificationMessage('Có lỗi xảy ra khi xóa thẻ', 'error');
        } finally {
          loading.value = false;
        }
      }
    );
  }
};

// Edit tag
const editTag = (id) => {
  router.push(`/admin/tags/edit-tag/${id}`);
};

// Delete tag
const confirmDelete = async (tag) => {
  showConfirmationDialog(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa thẻ "${tag.name}" không?`,
    async () => {
      try {
        const response = await fetch(`${apiBase}/tags/${tag.id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          showNotificationMessage('Xóa thẻ thành công!', 'success');
          await fetchTags();
        } else {
          const data = await response.json();
          showNotificationMessage(data.message || 'Có lỗi xảy ra khi xóa thẻ', 'error');
        }
      } catch (error) {
        console.error('Error deleting tag:', error);
        showNotificationMessage('Có lỗi xảy ra khi xóa thẻ', 'error');
      }
    }
  );
};

// Toggle dropdown
const toggleDropdown = (id) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null;
  } else {
    activeDropdown.value = id;
    nextTick(() => {
      const button = event.target.closest('button');
      if (button) {
        const rect = button.getBoundingClientRect();
        dropdownPosition.value = {
          top: `${rect.bottom + window.scrollY + 8}px`,
          left: `${rect.right + window.scrollX - 192}px`,
          width: '192px'
        };
      }
    });
  }
};

// Close dropdown
const closeDropdown = (event) => {
  if (!event.target.closest('.relative') && !event.target.closest('.absolute')) {
    activeDropdown.value = null;
  }
};

// Filtered tags
const filteredTags = computed(() => {
  let result = tags.value || [];
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(tag =>
      (tag.name || '').toLowerCase().includes(query) ||
      (tag.slug || '').toLowerCase().includes(query)
    );
  }
  return result;
});

// Show success notification
const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

// Close confirm dialog
const closeConfirmDialog = () => {
  showConfirmDialog.value = false;
  confirmAction.value = null;
};

// Handle confirm action
const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value();
  }
  closeConfirmDialog();
};

// Show confirmation dialog
const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title;
  confirmDialogMessage.value = message;
  confirmAction.value = action;
  showConfirmDialog.value = true;
};

// Fetch tags on component mount
onMounted(() => {
  fetchTags();
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
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
  transition: transform .5s, opacity 1s;
}

button:active::after {
  transform: scale(0, 0);
  opacity: .2;
  transition: 0s;
}
</style>