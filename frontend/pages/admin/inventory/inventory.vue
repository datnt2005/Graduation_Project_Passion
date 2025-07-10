<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">

      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Quản lý kho hàng</h1>
        <button @click="openModal('create')" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
          <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nhập kho mới
        </button>
      </div>

      <!-- Filter -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm">
        <div class="font-bold">Tất cả sản phẩm ({{ totalInventories }})</div>
        <div class="ml-auto relative">
          <input v-model="searchQuery" type="text" placeholder="Tìm kiếm..." class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:ring-blue-500 w-64" />
          <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white">
          <tr>
            <th v-for="header in headers" :key="header" class="border px-3 py-2 text-left font-semibold text-gray-700">{{ header }}</th>
          </tr>
        </thead>
        <tbody v-if="!isLoading">
          <tr v-for="item in paginatedInventories" :key="item.id" :class="{ 'bg-gray-50': item.id % 2 === 0 }">
            <td class="border px-3 py-2">{{ item.sku }}</td>
            <td class="border px-3 py-2">
              {{ item.product_name }}
              <template v-if="item.attributes?.length">
                (
                <span v-for="(attr, i) in item.attributes" :key="i">
                  {{ attr.name }}: {{ attr.value }}<span v-if="i < item.attributes.length - 1">, </span>
                </span>
                )
              </template>
            </td>
            <td class="border px-3 py-2" :class="item.quantity < 10 ? 'bg-red-100 text-red-700 font-semibold' : 'text-gray-700'">
              {{ item.quantity }}
            </td>
            <td class="border px-3 py-2">{{ formatCurrency(item.cost_price) }}</td>
            <td class="border px-3 py-2">{{ formatCurrency(item.sell_price) }}</td>
            <td class="border px-3 py-2">{{ item.location || '-' }}</td>
            <td class="border px-3 py-2">{{ item.batch_number || '-' }}</td>
            <td class="border px-3 py-2">{{ item.import_source || '-' }}</td>
            <td class="border px-3 py-2">{{ item.imported_by || '-' }}</td>
            <td class="border px-3 py-2">{{ item.note || '-' }}</td>
            <td class="border px-3 py-2">{{ formatDate(item.imported_at) }}</td>
            <td class="border px-3 py-2">{{ formatDate(item.last_updated) }}</td>
            <td class="border px-3 py-2 relative" data-dropdown>
              <div class="relative inline-block text-left">
                <button @click.stop="toggleDropdown(item)" class="p-1 hover:bg-gray-200 rounded-full">
                  <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h.01M12 12h.01M18 12h.01" />
                  </svg>
                </button>
                <div v-if="item.showMenu" class="absolute right-0 z-10 mt-1 w-32 bg-white border border-gray-200 rounded shadow-md">
                  <button @click="openModal('edit', item)" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Sửa</button>
                  <button @click="openModal('damage', item)" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 text-red-600">Hỏng/Xuất</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr v-for="n in 5" :key="n" class="animate-pulse bg-white">
            <td v-for="i in headers.length" :key="i" class="border px-3 py-2"><div class="h-4 bg-gray-200 rounded w-full"></div></td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200">
        <div class="text-sm">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredInventories.length) }} trong {{ filteredInventories.length }} sản phẩm</div>
        <div class="inline-flex -space-x-px text-sm">
          <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 border border-gray-300 bg-white hover:bg-gray-50">&laquo;</button>
          <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="['px-3 py-1 border', currentPage === page ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-100']">{{ page }}</button>
          <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 border border-gray-300 bg-white hover:bg-gray-50">&raquo;</button>
        </div>
      </div>

      <!-- Modal -->
      <InventoryModal v-if="showModal" :mode="modalMode" :inventory="selectedInventory" @close="showModal = false; selectedInventory = null" @submitted="onInventorySubmitted" />

      <!-- history -->
  <StockMovementHistory kMovementHistory :productVariantId="selectedInventory?.product_variant_id || null"
                :refreshKey="stockRefreshKey" />

    </div>
  </div>
</template>


<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import InventoryModal from '@/components/inventories/InventoryModal.vue';
import StockMovementHistory from '@/components/inventories/StockMovementHistory.vue';
import { secureAxios } from '@/utils/secureAxios';
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const searchQuery = ref('');
const totalInventories = ref(0);
const inventories = ref([]);
const showModal = ref(false);
const modalMode = ref('create');
const selectedInventory = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;
const isLoading = ref(true);

const headers = [
  'SKU', 'Tên sản phẩm', 'Tồn kho', 'Giá vốn', 'Giá bán', 'Vị trí', 'Số lô',
  'Nguồn nhập', 'Người nhập', 'Ghi chú', 'Ngày nhập', 'Cập nhật', 'Thao tác'
];

definePageMeta({
    layout: 'default-admin'
});

const fetchInventories = async () => {
  isLoading.value = true;
  try {
    const { data } = await secureAxios(`${apiBase}/inventory/list`, {}, 'admin,seller');
    inventories.value = data.map(item => ({ ...item, showMenu: false }));
    totalInventories.value = data.length;
  } finally {
    isLoading.value = false;
  }
};

const toggleDropdown = (item) => {
  inventories.value.forEach(i => (i.showMenu = false));
  item.showMenu = !item.showMenu;
};

onMounted(() => {
  fetchInventories();
  // Close dropdown on outside click
  document.addEventListener('click', (e) => {
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    dropdowns.forEach(el => {
      if (!el.contains(e.target)) {
        inventories.value.forEach(i => (i.showMenu = false));
      }
    });
  });
});

const onInventorySubmitted = () => {
  fetchInventories();
};

const filteredInventories = computed(() => {
  const q = searchQuery.value.toLowerCase();
  return inventories.value.filter(inv => {
    return inv.product_name?.toLowerCase().includes(q) || inv.sku?.toLowerCase().includes(q);
  });
});

const paginatedInventories = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredInventories.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredInventories.value.length / itemsPerPage)));

const visiblePages = computed(() => {
  const pages = [];
  const maxButtons = 5;
  let start = Math.max(currentPage.value - Math.floor(maxButtons / 2), 1);
  let end = start + maxButtons - 1;
  if (end > totalPages.value) {
    end = totalPages.value;
    start = Math.max(1, end - maxButtons + 1);
  }
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});

const goToPage = (page) => currentPage.value = page;
const prevPage = () => currentPage.value > 1 && currentPage.value--;
const nextPage = () => currentPage.value < totalPages.value && currentPage.value++;

watch(searchQuery, () => currentPage.value = 1);

const formatCurrency = (v) => new Intl.NumberFormat('vi-VN').format(v) + ' đ';
const formatDate = (d) => d ? new Date(d).toLocaleString() : '-';

const openModal = (mode, inventory = null) => {
  modalMode.value = mode;
  selectedInventory.value = inventory;
  showModal.value = true;
};
</script>


<style scoped>
.animate-pulse {
    animation: pulse .5s infinite;
}
</style>
