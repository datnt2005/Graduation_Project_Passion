<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header with Create Button -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Xét duyệt sản phẩm</h1>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <button @click="filterStatus = ''; filterRejected = ''; fetchProducts()" :class="[
            'text-blue-600 hover:underline',
            filterStatus === '' && filterRejected === '' ? 'font-semibold' : ''
          ]">
            Tất cả
          </button>
          <span>({{ totalProducts }})</span>
          <button @click="filterStatus = 'instock'; filterRejected = ''; fetchProducts()" :class="[
            'text-blue-600 hover:underline',
            filterStatus === 'instock' && filterRejected === '' ? 'font-semibold' : ''
          ]">
            Còn hàng
          </button>
          <span>({{ inStockProducts }})</span>
          <button @click="filterRejected = 'rejected'; filterStatus = ''; fetchProducts()" :class="[
            'text-blue-600 hover:underline',
            filterRejected === 'rejected' ? 'font-semibold' : ''
          ]">
            Đã từ chối
          </button>
          <span>({{ rejectedProducts }})</span>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
          <!-- Sort by Date -->
          <select v-model="sortBy"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="newest">Mới nhất</option>
            <option value="oldest">Cũ nhất</option>
          </select>
          <!-- Category Filter -->
          <select v-model="filterCategory"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả danh mục</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <!-- Brand Filter -->
          <select v-model="filterBrand"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả cửa hàng</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.store_name }}
            </option>
          </select>
          <!-- Tag Filter -->
          <select v-model="filterTag"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Tất cả thẻ</option>
            <option v-for="tag in tags" :key="tag.id" :value="tag.id">
              {{ tag.name }}
            </option>
          </select>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm sản phẩm..."
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
      <div class="bg-gray-200 px-4 py-3 flex items-center gap-3 text-sm text-gray-700 border-t border-gray-300">
        <select v-model="selectedAction"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Hành động hàng loạt</option>
          <option value="approve">Phê duyệt</option>
          <option value="reject">Từ chối</option>
        </select>
        <button @click="applyBulkAction" :disabled="!selectedAction || selectedProducts.length === 0 || loading" :class="[
          'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150',
          (!selectedAction || selectedProducts.length === 0 || loading)
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-blue-600 text-white hover:bg-blue-700'
        ]">
          {{ loading ? 'Đang xử lý...' : 'Áp dụng' }}
        </button>
        <div class="ml-auto text-sm text-gray-600">
          {{ selectedProducts.length }} sản phẩm được chọn / {{ filteredProducts.length }} sản phẩm
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
              Ảnh
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Tên sản phẩm
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Danh mục
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Thẻ sản phẩm
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Ngày tạo
            </th>
            <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">
              Cửa hàng
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
          <tr v-for="product in filteredProducts" :key="product.id" :class="{ 'bg-gray-50': product.id % 2 === 0 }"
            class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left w-10">
              <input type="checkbox" v-model="selectedProducts" :value="product.id" />
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <img v-if="getProductImage(product)" :src="`${mediaBase}` + getProductImage(product)" alt="Product Image"
                class="w-12 h-12 object-cover rounded" />
              <span v-else>–</span>
            </td>
            <td
              class="border border-gray-300 px-3 py-2 text-left font-semibold text-blue-700 hover:underline cursor-pointer"
              @click="editProduct(product.id)">
              {{ truncateText(product.name, 30) }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{product.categories?.length ? product.categories.map(c => c.name).join(', ') : '–'}}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{product.tags?.length ? product.tags.map(t => t.name).join(', ') : '–'}}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ formatDate(product.created_at) }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ product.seller?.store_name || '–' }}
              <span v-if="product.is_admin_added === 1" class="text-xs text-gray-500">(Admin thêm sản phẩm)</span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <span class="inline-block px-3 py-1 text-xs rounded-full font-medium" :class="{
                'bg-green-100 text-green-700 font-semibold': product.admin_status === 'approved',
                'bg-yellow-100 text-yellow-600 font-semibold': product.admin_status === 'pending',
                'bg-red-100 text-red-600 font-semibold': product.admin_status === 'rejected',
                'bg-gray-100 text-gray-500 font-semibold': product.admin_status === 'trash'
              }">
                {{ getStatusLabel(product.admin_status) }}
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <button @click="openDetail(product)" class="text-blue-600 hover:underline text-sm font-medium">Xem chi
                tiết</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <Pagination :currentPage="currentPage" :lastPage="lastPage" @change="fetchProducts" />

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

  <!-- Product Detail Modal -->
  <div v-if="detailModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 md:p-8 overflow-y-auto max-h-screen relative">
      <div class="flex justify-between items-start mb-4 border-b pb-3">
        <div>
          <h3 class="text-2xl font-bold text-[#1564ff]">Chi tiết sản phẩm</h3>
          <p class="text-gray-500 text-sm mt-1">Xem thông tin & duyệt sản phẩm</p>
        </div>
        <div class="flex flex-col items-end gap-2">
          <button @click="editProduct(currentDetail?.id)"
            class="text-sm font-semibold text-blue-600 hover:underline hover:text-blue-800 transition-colors duration-300">
            Xem & Chỉnh sửa
          </button>
          <button @click="closeDetail"
            class="text-gray-400 hover:text-black text-xl transition-colors duration-200 -mt-1">✕</button>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
          <img :src="`${mediaBase}` + getProductImage(currentDetail)" alt="Ảnh sản phẩm"
            class="w-full rounded border object-contain max-h-[240px]" />
        </div>
        <div class="flex flex-col gap-2">
          <div><strong>Tên sản phẩm:</strong> {{ currentDetail?.name }}</div>
          <div>
            <strong>Mô tả:</strong>
            <div
              class="prose max-w-none text-sm text-gray-700 max-h-[200px] overflow-y-auto border border-gray-200 rounded p-2 bg-gray-50"
              v-html="currentDetail?.description"></div>
          </div>
          <div><strong>Giá:</strong> {{ formatCurrency(currentDetail?.price || getDefaultPrice(currentDetail)) }}</div>
          <div><strong>Danh mục:</strong> {{currentDetail?.categories?.map(c => c.name).join(', ') || '-'}}</div>
          <div><strong>Nhà bán:</strong> {{ currentDetail?.seller?.store_name || '-' }}</div>
          <div><strong>Trạng thái kho:</strong>
            <span class="inline-block ml-1 px-2 py-0.5 rounded-full text-xs"
              :class="getStockStatus(currentDetail) === 'instock' ? 'bg-green-100 text-green-700' : 'bg-gray-300 text-gray-700'">
              {{ getStockStatus(currentDetail) === 'instock' ? 'Còn hàng' : 'Hết hàng' }}
            </span>
          </div>
          <div><strong>Ngày tạo:</strong> {{ formatDate(currentDetail?.created_at) }}</div>
          <div><strong>Trạng thái:</strong>
             <span class="inline-block px-3 py-1 text-xs rounded-full font-medium" :class="{
                'bg-green-100 text-green-700 font-semibold': currentDetail.admin_status === 'approved',
                'bg-yellow-100 text-yellow-600 font-semibold': currentDetail.admin_status === 'pending',
                'bg-red-100 text-red-600 font-semibold': currentDetail.admin_status === 'rejected',
                'bg-gray-100 text-gray-500 font-semibold': currentDetail.admin_status === 'trash'
              }">
                {{ getStatusLabel(currentDetail.admin_status) }}
              </span>
          </div>

        </div>
      </div>
      <div v-if="currentDetail?.admin_status === 'pending'" class="mt-6 flex gap-3">
        <button @click="approveProduct(currentDetail.id)"
          class="flex-1 py-2 rounded bg-blue-700 hover:bg-blue-900 text-white font-semibold text-sm transition">
          ✅ Duyệt sản phẩm
        </button>
        <button @click="rejectProduct(currentDetail.id)"
          class="flex-1 py-2 rounded bg-red-500 hover:bg-red-700 text-white font-semibold text-sm transition">
          ❌ Từ chối
        </button>
      </div>
    </div>
  </div>
  <div v-if="reasonModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6">
      <h3 class="text-lg font-semibold mb-3 text-gray-800">
        {{ pendingAction === 'reject' ? 'Từ chối sản phẩm' : 'Duyệt sản phẩm' }}
      </h3>
      <div v-if="pendingAction === 'reject'">
        <label class="text-sm text-gray-600 mb-1 block">Lý do từ chối</label>
        <textarea v-model="reasonText" rows="4"
          class="w-full border rounded p-2 text-sm focus:outline-blue-500 resize-none"
          placeholder="Nhập lý do từ chối..."></textarea>
      </div>
      <p v-else class="text-sm text-gray-700">Bạn chắc chắn muốn <strong>duyệt</strong> sản phẩm này?</p>
      <div class="flex justify-end mt-5 gap-2">
        <button @click="reasonModal = false"
          class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm">Hủy</button>
        <button @click="submitApproval" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm">
          {{ pendingAction === 'reject' ? 'Xác nhận từ chối' : 'Xác nhận duyệt' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import Pagination from '~/components/Pagination.vue';

definePageMeta({
  layout: 'default-admin'
});

const router = useRouter();
const products = ref([]);
const selectedProducts = ref([]);
const selectAll = ref(false);
const searchQuery = ref('');
const selectedAction = ref('');
const filterStatus = ref('');
const filterRejected = ref('');
const sortBy = ref('newest');
const filterCategory = ref('');
const filterBrand = ref('');
const filterTag = ref('');
const categories = ref([]);
const brands = ref([]);
const tags = ref([]);
const totalProducts = ref(0);
const inStockProducts = ref(0);
const rejectedProducts = ref(0);
const loading = ref(false);
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref(null);
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = 10;

// For bulk approval/reject
const pendingProductIds = ref([]);
const reasonModal = ref(false);
const reasonText = ref('');
const pendingAction = ref(null);

// For detail modal
const detailModal = ref(false);
const currentDetail = ref(null);

// Fetch product counts (total, instock, trash)
const fetchProductCounts = async () => {
  try {
    const productsResponse = await secureFetch(`${apiBase}/approvals`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    } , ['admin']);
    const productsData = await productsResponse.json();
    const allProducts = productsData.data?.data || productsData.data || [];
    totalProducts.value = productsData.data?.total || allProducts.length || 0;
    inStockProducts.value = allProducts.filter(p => getStockStatus(p) === 'instock').length;

    // Fetch rejected products
    const rejectedResponse = await secureFetch(`${apiBase}/approvals/rejected`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    } , ['admin']);
    const rejectedData = await rejectedResponse.json();
    const rejectedProductsList = rejectedData.data?.data || rejectedData.data || [];
    rejectedProducts.value = rejectedData.data?.total || rejectedProductsList.length || 0;
  } catch (error) {
    console.error('Error fetching product counts:', error);
    showNotificationMessage('Có lỗi xảy ra khi tải số lượng sản phẩm', 'error');
  }
};

const getStatusLabel = (status) => {
  switch (status) {
    case 'approved': return 'Đã duyệt';
    case 'pending': return 'Chờ duyệt';
    case 'rejected': return 'Từ chối';
    case 'trash': return 'Đã xóa';
    default: return 'Không xác định';
  }
};

const fetchProducts = async (page = 1) => {
  try {
    loading.value = true;
    currentPage.value = page;
    const endpoint = filterRejected.value === 'rejected'
      ? `${apiBase}/approvals/rejected?page=${page}&per_page=${perPage}`
      : `${apiBase}/approvals?page=${page}&per_page=${perPage}`;
    const response = await secureFetch(endpoint, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    } , ['admin']);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    const data = await response.json();
    products.value = data.data?.data || data.data || data || [];
    lastPage.value = data.data?.last_page || 1;
    currentPage.value = data.data?.current_page || page;
    selectedProducts.value = []; // Reset selected products
    selectAll.value = false;
    if (!products.value.length) {
      showNotificationMessage(filterRejected.value === 'rejected' ? 'Không có sản phẩm nào đã từ chối' : 'Không có sản phẩm nào');
    }
    await fetchProductCounts();
  } catch (error) {
    console.error('Error fetching products:', error);
    showNotificationMessage(`Có lỗi xảy ra khi tải sản phẩm: ${error.message}`, 'error');
    products.value = [];
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await fetch(`${apiBase}/categories`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });
    const data = await response.json();
    categories.value = data.data.data || data.categories || [];
  } catch (error) {
    showNotificationMessage('Có lỗi xảy ra khi tải danh mục', 'error');
  }
};

const fetchBrands = async () => {
  try {
    const response = await fetch(`${apiBase}/sellers/verified`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });
    const data = await response.json();
    brands.value = data.data || [];
  } catch (error) {
    showNotificationMessage('Có lỗi xảy ra khi tải thương hiệu', 'error');
  }
};

const fetchTags = async () => {
  try {
    const response = await fetch(`${apiBase}/tags`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });
    const data = await response.json();
    tags.value = data.data.tags || [];
  } catch (error) {
    showNotificationMessage('Có lỗi xảy ra khi tải thẻ', 'error');
  }
};

const getProductImage = (product) => {
  if (product.product_pic?.[0]?.imagePath) return product.product_pic[0].imagePath;
  if (product.product_variants?.[0]?.thumbnail) return product.product_variants[0].thumbnail;
  return null;
};

const getStockStatus = (product) => {
  const totalQuantity = product.product_variants?.reduce((sum, variant) => sum + (variant.quantity || 0), 0) || 0;
  return totalQuantity > 0 ? 'instock' : 'outofstock';
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedProducts.value = filteredProducts.value.map(p => p.id);
  } else {
    selectedProducts.value = [];
  }
};

function truncateText(text, maxLength) {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text;
}

// Apply bulk action
const applyBulkAction = async () => {
  if (!selectedAction.value || selectedProducts.value.length === 0) {
    showNotificationMessage('Vui lòng chọn hành động và ít nhất một sản phẩm', 'error');
    return;
  }

  const validIds = selectedProducts.value.filter(id => !!id);
  if (!validIds.length) {
    showNotificationMessage('Sản phẩm chọn không hợp lệ.', 'error');
    return;
  }

  pendingProductIds.value = validIds;
  pendingAction.value = selectedAction.value;

  if (selectedAction.value === 'reject') {
    reasonText.value = '';
    reasonModal.value = true;
  } else {
    showConfirmationDialog(
      'Xác nhận duyệt sản phẩm',
      `Bạn có chắc chắn muốn duyệt ${validIds.length} sản phẩm đã chọn?`,
      submitBulkApproval
    );
  }
};

const editProduct = (id) => {
  router.push(`/admin/products/edit-product/${id}`);
};

const formatDate = (date) => {
  if (!date) return '–';
  return new Date(date).toLocaleDateString('vi-VN', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};


const closeDropdown = (event) => {
  const dropdowns = document.querySelectorAll('.dropdown-menu');
  dropdowns.forEach(dropdown => {
    if (!dropdown.contains(event.target)) {
      dropdown.classList.add('hidden');
    }
  });
};

const filteredProducts = computed(() => {
  let result = [...products.value];
  if (filterRejected.value === 'rejected') {
    result = result.filter(product => product.admin_status === 'rejected');
  } else {
    result = result.filter(product => product.admin_status !== 'rejected');
  }
  if (filterStatus.value && filterRejected.value !== 'rejected') {
    result = result.filter(product => getStockStatus(product) === filterStatus.value);
  }
  if (filterCategory.value) {
    result = result.filter(product =>
      product.categories?.some(category => category.id === filterCategory.value)
    );
  }
  if (filterBrand.value) {
    result = result.filter(product =>
      product.seller?.id === filterBrand.value
    );
  }
  if (filterTag.value) {
    result = result.filter(product =>
      product.tags?.some(tag => tag.id === filterTag.value)
    );
  }
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(product =>
      product.name?.toLowerCase().includes(query) ||
      product.slug?.toLowerCase().includes(query) ||
      (product.description && product.description.toLowerCase().includes(query))
    );
  }
  if (sortBy.value === 'newest') {
    result.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
  } else if (sortBy.value === 'oldest') {
    result.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));
  }
  return result;
});

const showNotificationMessage = (message, type = 'success') => {
  notificationMessage.value = message;
  notificationType.value = type;
  showNotification.value = true;
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
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

const openDetail = (product) => {
  currentDetail.value = product;
  detailModal.value = true;
};

const closeDetail = () => {
  detailModal.value = false;
  currentDetail.value = null;
};

const approveProduct = (id) => {
  pendingAction.value = 'approve';
  pendingProductIds.value = [id];
  reasonText.value = '';
  reasonModal.value = true;
};

const rejectProduct = (id) => {
  pendingAction.value = 'reject';
  pendingProductIds.value = [id];
  reasonText.value = '';
  reasonModal.value = true;
};

const submitBulkApproval = async () => {
  if (pendingAction.value === 'reject' && !reasonText.value.trim()) {
    showNotificationMessage('Vui lòng nhập lý do từ chối.', 'error');
    return;
  }
  try {
    loading.value = true;
    const updatePromises = pendingProductIds.value.map(id =>
      secureFetch(`${apiBase}/approvals/${id}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          admin_status: pendingAction.value === 'approve' ? 'approved' : 'rejected',
          reason: reasonText.value
        })
      } , ['admin'])
    );
    const responses = await Promise.all(updatePromises);
    const failed = responses.some(res => !res.ok);
    if (failed) {
      showNotificationMessage('Có lỗi xảy ra khi cập nhật trạng thái một số sản phẩm', 'error');
    } else {
      showNotificationMessage(
        pendingAction.value === 'approve'
          ? 'Duyệt các sản phẩm thành công!'
          : 'Từ chối các sản phẩm thành công!',
        'success'
      );
    }
    selectedProducts.value = [];
    selectAll.value = false;
    selectedAction.value = '';
    reasonModal.value = false;
    await fetchProducts();
  } catch (error) {
    showNotificationMessage('Lỗi khi xử lý hành động hàng loạt', 'error');
  } finally {
    loading.value = false;
  }
};

const submitApproval = async () => {
  if (!pendingProductIds.value.length && currentDetail.value?.id) {
    pendingProductIds.value = [currentDetail.value.id];
  }

  await submitBulkApproval();
  reasonModal.value = false;
  detailModal.value = false;
};

const formatCurrency = (value) => {
  if (!value) return '–';
  return Number(value).toLocaleString('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0 });
};

const getDefaultPrice = (product) => {
  return product?.product_variants?.[0]?.price || 0;
};

onMounted(() => {
  fetchProducts();
  fetchCategories();
  fetchBrands();
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