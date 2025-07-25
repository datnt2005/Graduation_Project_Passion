<template>
  <div class="mt-10 bg-white p-6 rounded-xl shadow">
    <h2 class="text-lg font-semibold mb-4 text-gray-800">Lịch sử biến động kho</h2>
    <div v-if="isLoading" class="text-sm">
      <div class="grid grid-cols-8 gap-2 bg-gray-100 p-2 rounded text-gray-400 font-medium">
        <div v-for="i in 8" :key="'header-' + i" class="h-4 bg-gray-300 rounded"></div>
      </div>
      <div v-for="n in 5" :key="'row-' + n" class="grid grid-cols-8 gap-2 p-2">
        <div v-for="i in 8" :key="'cell-' + n + '-' + i" class="h-4 bg-gray-200 rounded animate-pulse"></div>
      </div>
    </div>

    <div v-else>
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="border px-3 py-2 text-left">Sản phẩm</th>
            <th class="border px-3 py-2 text-left">SKU</th>
            <th class="border px-3 py-2 text-left">Thao tác</th>
            <th class="border px-3 py-2 text-left">Số lượng</th>
            <th class="border px-3 py-2 text-left">Người tạo</th>
            <th class="border px-3 py-2 text-left">Thời gian</th>
            <th class="border px-3 py-2 text-left">Ghi chú</th>
            <th class="border px-3 py-2 text-left">Chi tiết</th>
          </tr>
        </thead>
        <tbody>
    <tr v-for="item in paginatedMovements" :key="item.id">          
            <td class="border px-3 py-2">{{ item.product_variant.product.name }}</td>
            <td class="border px-3 py-2">{{ item.product_variant.sku }}</td>
            <td class="border px-3 py-2">{{ translateType(item.action_type) }}</td>
            <td class="border px-3 py-2">
              <span :class="{
                'text-green-600': isPositive(item.action_type),
                'text-red-600': !isPositive(item.action_type)
              }">
                {{ isPositive(item.action_type) ? '+' : '-' }}{{ item.quantity }}
              </span>
            </td>
            <td class="border px-3 py-2">
              {{ item.created_by_type }}:
              {{ item.creator?.name || 'Không rõ' }}
            </td>
            <td class="border px-3 py-2">{{ formatDate(item.created_at) }}</td>
            <td class="border px-3 py-2">{{ item.note }}</td>
            <td class="border px-3 py-2">
              <button @click="selectedItem = item; showDetail = true" class="text-blue-600 hover:underline text-sm">
                Xem chi tiết
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination -->
      <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
        v-if="!isLoading">
        <div class="text-sm text-gray-700">
          Hiển thị
          <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
          đến
          <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, movements.length) }}</span>
          của
          <span class="font-medium">{{ movements.length }}</span>
          kết quả
        </div>
        <nav class="inline-flex -space-x-px text-sm">
          <button @click="prevPage" :disabled="currentPage === 1"
            class="px-3 py-1 border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50"
            aria-label="Trước">&laquo;</button>
          <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="['px-3 py-1 border',
            currentPage === page
              ? 'bg-blue-500 text-white'
              : 'bg-white hover:bg-gray-100']">
            {{ page }}
          </button>
          <button @click="nextPage" :disabled="currentPage === totalPages"
            class="px-3 py-1 border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50"
            aria-label="Sau">&raquo;</button>
        </nav>
      </div>

    </div>
    <!-- Modal xem chi tiết -->
    <div v-if="showDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-start">
          <div>
            <h2 class="text-xl font-bold text-gray-800">Chi tiết phiếu kho</h2>
            <p class="text-sm text-gray-500">Thông tin chi tiết giao dịch</p>
          </div>
          <span class="bg-black text-white text-xs font-medium px-3 py-1 rounded-full"> {{
            translateType(selectedItem.action_type) }}</span>
        </div>

        <!-- Thông tin sản phẩm -->
        <div class="border rounded-lg p-4 bg-gray-50">
          <div class="flex flex-col gap-1">
            <h3 class="text-lg font-semibold text-gray-800">{{ selectedItem.product_variant.product.name }}</h3>
            <div class="text-sm text-gray-600">
              SKU:
              <span class="inline-block px-2 py-0.5 bg-white border rounded text-gray-800 font-mono ml-1">
                {{ selectedItem.product_variant.sku }}
              </span>
            </div>
            <div class="mt-3 flex items-center space-x-3">
              <span class="bg-green-100 text-green-700 font-semibold text-lg px-3 py-1 rounded">
                +{{ selectedItem.quantity }}
              </span>
              <span class="text-sm text-gray-500">Số lượng</span>
            </div>
          </div>
        </div>

        <!-- Người tạo & thời gian -->
        <div class="grid md:grid-cols-2 gap-4">
          <div class="bg-gray-50 p-4 rounded border">
            <p class="text-sm font-medium text-gray-600 mb-1">Người thực hiện</p>
            <div class="flex items-center space-x-2">
              <span class="bg-gray-800 text-white text-xs font-semibold px-2 py-0.5 rounded"> {{
                selectedItem.created_by_type }}</span>
              <span class="text-gray-900 font-medium">{{ selectedItem.creator?.name || 'Không rõ' }}</span>
            </div>
            <p class="text-sm text-gray-500 break-all mt-1">{{ selectedItem.creator?.email || '' }}</p>
          </div>
          <div class="bg-gray-50 p-4 rounded border">
            <p class="text-sm font-medium text-gray-600 mb-1">Thời gian</p>
            <p class="text-gray-900 font-medium">{{ formatDate(selectedItem.created_at) }}</p>
          </div>
        </div>

        <!-- Ghi chú -->
        <div>
          <p class="text-sm font-medium text-gray-600 mb-1">Ghi chú</p>
          <div class="bg-gray-50 border rounded p-3 text-sm text-gray-800 whitespace-pre-line">
            {{ selectedItem.note || 'Không có' }}
          </div>
        </div>

        <!-- Nút đóng -->
        <div class="text-right mt-4">
          <button @click="showDetail = false"
            class="px-5 py-2 rounded-md text-sm bg-gray-100 hover:bg-gray-200 text-gray-700">
            Đóng
          </button>
        </div>

      </div>
    </div>



  </div>
</template>


<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';
import { secureAxios } from '@/utils/secureAxios'

const movements = ref([]);
const isLoading = ref(true);

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const showDetail = ref(false);
const selectedItem = ref(null);

const currentPage = ref(1);
const itemsPerPage = 5;

const totalPages = computed(() =>
  Math.ceil(movements.value.length / itemsPerPage)
);

const paginatedMovements = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return movements.value.slice(start, start + itemsPerPage);
});

const visiblePages = computed(() => {
  const range = [];
  const max = totalPages.value;
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(max, currentPage.value + 2);
  for (let i = start; i <= end; i++) range.push(i);
  return range;
});

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const fetchStockMovements = async () => {
  try {
    const { data } = await secureAxios(`${apiBase}/stock-movements`, {}, ['admin', 'seller']);
    movements.value = data.data || data;
  } catch (err) {
    console.error('Fetch failed:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchStockMovements();
});

const formatDate = (d) => dayjs(d).format('DD/MM/YYYY HH:mm');

const translateType = (type) => {
  switch (type) {
    case 'import': return 'Nhập kho';
    case 'export': return 'Xuất kho';
    case 'damage': return 'Hàng hỏng';
    case 'adjust': return 'Chỉnh sửa';
    case 'return': return 'Hoàn trả';
    default: return type;
  }
};

const isPositive = (type) => {
  return ['import'].includes(type);
};
</script>

<style scoped>
th,
td {
  white-space: nowrap;
}
</style>
