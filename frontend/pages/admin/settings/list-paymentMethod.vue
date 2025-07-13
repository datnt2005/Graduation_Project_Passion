<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen flex">
    <!-- Sidebar -->
    <aside
      class="w-64 bg-white border-r border-gray-200 flex-shrink-0 hidden md:block"
    >
      <nav class="py-6 px-4 space-y-2">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Cài đặt hệ thống</h2>
        <ul class="space-y-1">
          <li>
            <router-link
              to="/admin/settings"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path === '/admin/settings'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa-solid fa-house"></i> Trang cài đặt chính
            </router-link>
          </li>
          <li>
            <router-link
              to="/admin/settings/list-paymentMethod"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path.includes('list-paymentMethod')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa fa-credit-card mr-2"></i> Quản lý phương thức thanh
              toán
            </router-link>
          </li>
          <li>
            <router-link
              to="/admin/settings/list-address"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path.includes('list-address')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa fa-map-marker-alt mr-2"></i> Quản lý địa chỉ
            </router-link>
          </li>
          <li>
            <router-link
              to="/admin/settings/list-shipping"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path.includes('list-shipping')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa fa-shipping-fast mr-2"></i> Quản lý vận chuyển
            </router-link>
          </li>
          <li>
            <router-link
              to="/admin/settings/list-other"
              class="block px-3 py-2 rounded-md font-medium"
              :class="
                $route.path.includes('list-other')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-50'
              "
            >
              <i class="fa fa-cogs mr-2"></i> Cài đặt khác
            </router-link>
          </li>
        </ul>
      </nav>
    </aside>
    <!-- Main Content -->
    <div class="flex-1">
      <div class="max-w-full overflow-x-auto">
        <!-- Header with Create Button -->
        <div
          class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200"
        >
          <h1 class="text-xl font-semibold text-gray-800">
            Quản lý phương thức thanh toán
          </h1>
          <button
            @click="openCreateModal"
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
            Thêm phương thức
          </button>
        </div>

        <!-- Filter Bar -->
        <div
          class="bg-gray-100 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-b"
        >
          <div class="flex items-center gap-4">
            <span
              @click="filterStatus = ''"
              :class="[
                'cursor-pointer font-semibold',
                filterStatus === '' ? 'text-blue-700' : 'text-gray-700',
              ]"
            >
              Tất cả <span class="font-normal">({{ totalMethods }})</span>
            </span>
            <span
              @click="filterStatus = 'active'"
              :class="[
                'cursor-pointer font-semibold',
                filterStatus === 'active' ? 'text-blue-700' : 'text-gray-700',
              ]"
            >
              Đang hoạt động
              <span class="font-normal">({{ activeMethods }})</span>
            </span>
            <span
              @click="filterStatus = 'inactive'"
              :class="[
                'cursor-pointer font-semibold',
                filterStatus === 'inactive' ? 'text-blue-700' : 'text-gray-700',
              ]"
            >
              Ngừng hoạt động
              <span class="font-normal">({{ inactiveMethods }})</span>
            </span>
          </div>
          <div class="ml-auto flex items-center gap-2 relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Tìm kiếm phương thức thanh toán..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64"
            />
            <svg
              class="absolute left-2.5 top-2 h-4 w-4 text-gray-400"
              viewBox="0 0 20 20"
              fill="currentColor"
              style="left: 10px; top: 10px; position: absolute"
            >
              <path
                fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
        </div>

        <!-- Table -->
        <table
          class="min-w-full border-collapse border border-gray-300 text-sm mt-4"
        >
          <thead class="bg-white border-b border-gray-300">
            <tr>
              <th
                class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700 w-10"
              >
                #
              </th>
              <th
                class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700"
              >
                Tên phương thức
              </th>
              <th
                class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700"
              >
                Trạng thái
              </th>
              <th
                class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700"
              >
                Thao tác
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(method, idx) in filteredMethods"
              :key="method.id"
              :class="{ 'bg-gray-50': idx % 2 === 0 }"
            >
              <td class="border border-gray-300 px-3 py-2">{{ idx + 1 }}</td>
              <td class="border border-gray-300 px-3 py-2">
                {{ method.name }}
              </td>
              <td class="border border-gray-300 px-3 py-2">
                <span
                  :class="
                    method.status === 'active'
                      ? 'text-green-600'
                      : 'text-red-600'
                  "
                >
                  {{
                    method.status === "active"
                      ? "Đang hoạt động"
                      : "Ngừng hoạt động"
                  }}
                </span>
              </td>
              <td class="border border-gray-300 px-3 py-2">
                <div class="relative inline-block text-left">
                  <button
                    @click="toggleDropdown(method.id, $event)"
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
                  <!-- Dropdown menu sẽ được Teleport ra ngoài -->
                </div>
              </td>
            </tr>
            <tr v-if="filteredMethods.length === 0">
              <td colspan="4" class="text-center py-4 text-gray-500">
                Không có phương thức thanh toán nào
              </td>
            </tr>
          </tbody>
        </table>
      </div>

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
              v-for="method in paymentMethods"
              :key="method.id"
              v-show="activeDropdown === method.id"
              class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right"
              :style="dropdownPosition"
            >
              <div class="py-1" role="menu" aria-orientation="vertical">
                <button
                  @click="editMethod(method)"
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
                  @click="confirmDelete(method)"
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
                class="h-6 w-6"
                :class="
                  notificationType === 'success'
                    ? 'text-green-400'
                    : 'text-red-500'
                "
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
          <div
            v-if="showConfirmDialog"
            class="fixed inset-0 z-50 overflow-y-auto"
          >
            <div
              class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
              <div
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="closeConfirmDialog"
              ></div>
              <span
                class="hidden sm:inline-block sm:align-middle sm:h-screen"
                aria-hidden="true"
                >&#8203;</span
              >
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
                <div
                  class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
                >
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
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import { useRuntimeConfig } from "#imports";

const router = useRouter();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const paymentMethods = ref([]);
const loading = ref(false);
const error = ref(null);
const filterStatus = ref("");
const searchQuery = ref("");
const activeDropdown = ref(null);
const dropdownPosition = ref({ top: "0px", left: "0px", width: "192px" });
const showNotification = ref(false);
const notificationMessage = ref("");
const notificationType = ref("success");
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref("");
const confirmDialogMessage = ref("");
const confirmAction = ref(null);

// Thêm các biến đếm số lượng phương thức
const totalMethods = computed(() => paymentMethods.value.length);
const activeMethods = computed(
  () => paymentMethods.value.filter((m) => m.status === "active").length
);
const inactiveMethods = computed(
  () => paymentMethods.value.filter((m) => m.status === "inactive").length
);

// Thêm các biến cho filter nâng cao và chọn hàng loạt
const selectedMethods = ref([]);
const selectAll = ref(false);
const bulkAction = ref("");
const filterType = ref(""); // loại phương thức
const filterStatusDropdown = ref("");

// Lấy danh sách loại phương thức nếu có (ví dụ hardcode)
const methodTypes = [
  { value: "", label: "Tất cả loại" },
  { value: "momo", label: "MOMO" },
  { value: "vnpay", label: "VNPAY" },
  { value: "cod", label: "COD" },
];

const fetchPaymentMethods = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await fetch(`${apiBase}/payment-methods`);
    const data = await res.json();
    paymentMethods.value = data.data;
  } catch (e) {
    error.value = "Không thể lấy danh sách phương thức thanh toán";
    paymentMethods.value = [];
  } finally {
    loading.value = false;
  }
};

const filteredMethods = computed(() => {
  let methods = paymentMethods.value;
  if (filterStatus.value) {
    methods = methods.filter((m) => m.status === filterStatus.value);
  }
  if (filterType.value) {
    methods = methods.filter((m) => m.type === filterType.value);
  }
  if (filterStatusDropdown.value) {
    methods = methods.filter((m) => m.status === filterStatusDropdown.value);
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    methods = methods.filter((m) => m.name.toLowerCase().includes(q));
  }
  return methods;
});

watch(filteredMethods, () => {
  // Reset chọn khi filter thay đổi
  selectedMethods.value = [];
  selectAll.value = false;
});

function toggleSelectAll() {
  if (selectAll.value) {
    selectedMethods.value = filteredMethods.value.map((m) => m.id);
  } else {
    selectedMethods.value = [];
  }
}

function applyBulkAction() {
  if (!bulkAction.value || selectedMethods.value.length === 0) return;
  // TODO: Gọi API thực hiện hành động hàng loạt (xóa/kích hoạt/vô hiệu hóa)
  alert(
    `Đã thực hiện: ${bulkAction.value} cho ${selectedMethods.value.length} phương thức!`
  );
  // Sau khi thực hiện xong, reset chọn
  selectedMethods.value = [];
  selectAll.value = false;
  bulkAction.value = "";
}

const toggleDropdown = (id, event) => {
  if (activeDropdown.value === id) {
    activeDropdown.value = null;
  } else {
    activeDropdown.value = id;
    // Tính vị trí dropdown
    const button = event.target.closest("button");
    if (button) {
      const rect = button.getBoundingClientRect();
      dropdownPosition.value = {
        top: `${rect.bottom + window.scrollY + 8}px`,
        left: `${rect.right + window.scrollX - 192}px`,
        width: "192px",
      };
    }
  }
};

const closeDropdown = (event) => {
  if (
    !event.target.closest(".relative") &&
    !event.target.closest(".absolute")
  ) {
    activeDropdown.value = null;
  }
};

const editMethod = (method) => {
  router.push(`/admin/settings/edit-paymentMethod/${method.id}`);
  activeDropdown.value = null;
};

const closeConfirmDialog = () => {
  showConfirmDialog.value = false;
  confirmAction.value = null;
};

const handleConfirmAction = async () => {
  if (confirmAction.value) {
    await confirmAction.value();
  }
  closeConfirmDialog();
};

const showConfirmationDialog = (title, message, action) => {
  confirmDialogTitle.value = title;
  confirmDialogMessage.value = message;
  confirmAction.value = action;
  showConfirmDialog.value = true;
};

// Sửa lại hàm confirmDelete:
const confirmDelete = (method) => {
  activeDropdown.value = null;
  showConfirmationDialog(
    "Xác nhận xóa",
    `Bạn có chắc chắn muốn xóa phương thức "${method.name}" không?`,
    async () => {
      try {
        const res = await fetch(`${apiBase}/payment-methods/${method.id}`, {
          method: "DELETE",
        });
        if (res.ok) {
          await fetchPaymentMethods();
          showNotificationMessage("Đã xóa phương thức!", "success");
        } else {
          const data = await res.json();
          showNotificationMessage(
            data.message || "Không thể xóa phương thức.",
            "error"
          );
        }
      } catch (e) {
        showNotificationMessage("Không thể kết nối máy chủ.", "error");
      }
    }
  );
};

const openCreateModal = () => {
  router.push("/admin/settings/create-paymentMethod");
};

const showNotificationMessage = (message, type = "success") => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

onMounted(() => {
  fetchPaymentMethods();
  document.addEventListener("click", closeDropdown);
});
onUnmounted(() => {
  document.removeEventListener("click", closeDropdown);
});
definePageMeta({
  layout: "default-admin",
});
</script>
