<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header with Create Button -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý mã giảm giá</h1>
        <button
          @click="router.push('/admin/coupons/create-coupon')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
        >
          <svg 
            xmlns="http://www.w3.org/2000/svg" 
            class="h-5 w-5 mr-2" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
          >
            <path 
              stroke-linecap="round" 
              stroke-linejoin="round" 
              stroke-width="2" 
              d="M12 4v16m8-8H4"
            />
          </svg>
          Thêm mã giảm giá
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ totalCoupons }})</span>
          <button 
            @click="filterStatus = 'active'"
            :class="[
              'text-blue-600 hover:underline',
              filterStatus === 'active' ? 'font-semibold' : ''
            ]"
          >
            Đang hoạt động
          </button>
          <span>({{ activeCoupons }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Tìm kiếm mã giảm giá..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64"
            />
            <svg 
              class="absolute left-2.5 top-2 h-4 w-4 text-gray-400"
              viewBox="0 0 20 20" 
              fill="currentColor"
            >
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Action Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-t border-gray-300">
        <select
          v-model="selectedAction"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Hành động hàng loạt</option>
          <option value="delete">Xóa</option>
          <option value="activate">Kích hoạt</option>
          <option value="deactivate">Vô hiệu hóa</option>
        </select>
        <button
          @click="applyBulkAction"
          :disabled="!selectedAction || selectedCoupons.length === 0 || loading"
          :class="[
            'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
            (!selectedAction || selectedCoupons.length === 0 || loading) 
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-blue-600 text-white hover:bg-blue-700'
          ]"
        >
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <select
          v-model="filterType"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Tất cả loại giảm giá</option>
          <option value="fixed">Giảm giá cố định</option>
          <option value="percentage">Giảm giá phần trăm</option>
        </select>
        <select
          v-model="filterStatus"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Tất cả trạng thái</option>
          <option value="active">Đang hoạt động</option>
          <option value="inactive">Không hoạt động</option>
        </select>
        <div class="ml-auto text-sm text-gray-600">
          {{ selectedCoupons.length }} mã được chọn / {{ filteredCoupons.length }} mã
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left w-10">
              <input 
                type="checkbox" 
                v-model="selectAll"
                @change="toggleSelectAll"
              />
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Mã số
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Loại phiếu giảm giá
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Số tiền phiếu giảm giá
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Mô tả
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Giá trị đơn tối thiểu
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Sử dụng / Giới hạn
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Ngày hết hạn
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Thao tác
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="coupon in filteredCoupons" :key="coupon.id" :class="{'bg-gray-50': coupon.id % 2 === 0}" class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input 
                type="checkbox" 
                v-model="selectedCoupons" 
                :value="coupon.id"
              />
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700 hover:underline cursor-pointer">
              {{ coupon.code }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left text-gray-500">
              {{ coupon.discount_type === 'fixed' ? 'Giảm giá cố định' : 'Giảm giá phần trăm' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ coupon.discount_value }}{{ coupon.discount_type === 'percentage' ? '%' : 'đ' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ coupon.description || '–' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ coupon.min_order_value ? formatCurrency(coupon.min_order_value) : '–' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ coupon.used_count }} / {{ coupon.usage_limit || '∞' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ coupon.end_date ? formatDate(coupon.end_date) : '–' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <div class="relative inline-block text-left">
                <button 
                  @click="toggleDropdown(coupon.id)"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-800 focus:outline-none"
                >
                  <svg 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="w-5 h-5" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                    />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Thêm portal container ở cuối template -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div 
        v-if="activeDropdown !== null"
        class="fixed inset-0 z-50"
        @click="closeDropdown"
      >
        <div 
          v-for="coupon in coupons" 
          :key="coupon.id"
          v-show="activeDropdown === coupon.id"
          class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right"
          :style="dropdownPosition"
        >
          <div 
            class="py-1" 
            role="menu" 
            aria-orientation="vertical" 
            aria-labelledby="options-menu"
          >
            <button
              @click="editCoupon(coupon.id)"
              class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
              role="menuitem"
            >
              <svg 
                xmlns="http://www.w3.org/2000/svg" 
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
              @click="confirmDelete(coupon)"
              class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
              role="menuitem"
            >
              <svg 
                xmlns="http://www.w3.org/2000/svg" 
                class="w-4 h-4 mr-2" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path 
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
              Xóa
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Thêm Notification Popup -->
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
            class="h-6 w-6 text-green-400"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
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

  <!-- Thêm Confirmation Dialog -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showConfirmDialog" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfirmDialog"></div>

          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

          <div 
            class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
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
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';

definePageMeta({
  layout: 'default-admin'
});

const router = useRouter();
const coupons = ref([]);
const selectedCoupons = ref([]);
const selectAll = ref(false);
const searchQuery = ref('');
const selectedAction = ref('');
const filterType = ref('');
const filterStatus = ref('');
const totalCoupons = ref(0);
const activeCoupons = ref(0);
const activeDropdown = ref(null);
const dropdownPosition = ref({ top: '0px', left: '0px', width: '192px' });
const loading = ref(false);
const showNotification = ref(false);
const notificationMessage = ref('');
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref(null);

// Fetch coupons from API
const fetchCoupons = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/discounts');
    const data = await response.json();
    coupons.value = data.data;
    totalCoupons.value = data.data.length;
    activeCoupons.value = data.data.filter(c => c.status === 'active').length;
  } catch (error) {
    console.error('Error fetching coupons:', error);
  }
};

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedCoupons.value = coupons.value.map(c => c.id);
  } else {
    selectedCoupons.value = [];
  }
};

// Search coupons
const searchCoupons = () => {
  // Implement search logic
};

// Filter coupons
const filterCoupons = () => {
  // Implement filter logic
};

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedCoupons.value.length === 0) {
    showSuccessNotification('Vui lòng chọn hành động và ít nhất một mã giảm giá');
    return;
  }

  if (selectedAction.value === 'delete') {
    showConfirmationDialog(
      'Xác nhận xóa hàng loạt',
      `Bạn có chắc chắn muốn xóa ${selectedCoupons.value.length} mã giảm giá đã chọn?`,
      async () => {
        try {
          loading.value = true;
          const deletePromises = selectedCoupons.value.map(id => 
            fetch(`http://localhost:8000/api/discounts/${id}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json'
              }
            })
          );

          await Promise.all(deletePromises);
          showSuccessNotification('Xóa các mã giảm giá thành công!');
          selectedCoupons.value = [];
          selectAll.value = false;
          selectedAction.value = '';
          await fetchCoupons();
        } catch (error) {
          console.error('Error:', error);
          showSuccessNotification('Có lỗi xảy ra khi xóa mã giảm giá');
        } finally {
          loading.value = false;
        }
      }
    );
  } else if (selectedAction.value === 'activate' || selectedAction.value === 'deactivate') {
    try {
      loading.value = true;
      const status = selectedAction.value === 'activate' ? 'active' : 'inactive';
      const updatePromises = selectedCoupons.value.map(id =>
        fetch(`http://localhost:8000/api/discounts/${id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ status })
        })
      );

      await Promise.all(updatePromises);
      showSuccessNotification('Cập nhật trạng thái thành công!');
      selectedCoupons.value = [];
      selectAll.value = false;
      selectedAction.value = '';
      await fetchCoupons();
    } catch (error) {
      console.error('Error:', error);
      showSuccessNotification('Có lỗi xảy ra khi cập nhật trạng thái');
    } finally {
      loading.value = false;
    }
  }
};

// Edit coupon
const editCoupon = (id) => {
  router.push(`/admin/coupons/edit-coupon/${id}`);
};

// Delete coupon
const confirmDelete = async (coupon) => {
  showConfirmationDialog(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa mã giảm giá "${coupon.code}" không?`,
    async () => {
      try {
        const response = await fetch(`http://localhost:8000/api/discounts/${coupon.id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          showSuccessNotification('Xóa mã giảm giá thành công!');
          await fetchCoupons();
        } else {
          const data = await response.json();
          showSuccessNotification(data.message || 'Có lỗi xảy ra khi xóa mã giảm giá');
        }
      } catch (error) {
        console.error('Error:', error);
        showSuccessNotification('Có lỗi xảy ra khi xóa mã giảm giá');
      }
    }
  );
};

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// Format date
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('vi-VN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const toggleDropdown = (id) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null;
  } else {
    activeDropdown.value = id;
    // Tính toán vị trí cho dropdown
    nextTick(() => {
      const button = event.target.closest('button');
      const rect = button.getBoundingClientRect();
      dropdownPosition.value = {
        top: `${rect.bottom + window.scrollY + 8}px`,
        left: `${rect.right + window.scrollX - 192}px`, // 192px là width của dropdown
        width: '192px'
      };
    });
  }
};

const closeDropdown = (event) => {
  // Chỉ đóng dropdown nếu click outside
  if (!event.target.closest('.relative') && !event.target.closest('.absolute')) {
    activeDropdown.value = null;
  }
};

// Thêm các ref mới cho tìm kiếm và lọc
const filteredCoupons = computed(() => {
  let result = [...coupons.value];

  // Lọc theo loại giảm giá
  if (filterType.value) {
    result = result.filter(coupon => coupon.discount_type === filterType.value);
  }

  // Lọc theo trạng thái
  if (filterStatus.value) {
    result = result.filter(coupon => coupon.status === filterStatus.value);
  }

  // Tìm kiếm theo mã hoặc tên
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(coupon => 
      coupon.code.toLowerCase().includes(query) ||
      coupon.name.toLowerCase().includes(query) ||
      (coupon.description && coupon.description.toLowerCase().includes(query))
    );
  }

  return result;
});

// Hàm hiển thị notification
const showSuccessNotification = (message) => {
  notificationMessage.value = message;
  showNotification.value = true;
  // Tự động ẩn sau 3 giây
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

// Hàm đóng confirm dialog
const closeConfirmDialog = () => {
  showConfirmDialog.value = false;
  confirmAction.value = null;
};

// Hàm xử lý action khi confirm
const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value();
  }
  closeConfirmDialog();
};

// Hàm hiển thị confirm dialog
const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title;
  confirmDialogMessage.value = message;
  confirmAction.value = action;
  showConfirmDialog.value = true;
};

// Fetch coupons on component mount
onMounted(() => {
  fetchCoupons();
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

/* Thêm hiệu ứng ripple cho các nút */
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
