<template>
    <div class="bg-gray-100 text-gray-700 font-sans">
        <div class="max-w-full overflow-x-auto">
            <!-- Header with Create Button -->
            <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
                <h1 class="text-xl font-semibold text-gray-800">Quản lý kho hàng</h1>
                <button @click="openModal('create')"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nhập kho mới
                </button>
            </div>

            <!-- Filter Bar -->
            <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
                <div class="flex items-center gap-2">
                    <span class="font-bold">Tất cả sản phẩm</span>
                    <span>({{ totalInventories }})</span>
                </div>
                <div class="ml-auto flex flex-wrap gap-2 items-center">
                    <div class="relative">
                        <input v-model="searchQuery" type="text" placeholder="Tìm kiếm sản phẩm..."
                            class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64" />
                        <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <table class="min-w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-white border-b border-gray-300">
                    <tr>
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">SKU</th>
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tên sản phẩm
                        </th>
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Tồn kho</th>
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Giá vốn</th>
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Giá bán</th>
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Vị trí</th>
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Cập nhật</th>
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700">Thao tác</th>
                    </tr>
                </thead>
                <tbody v-if="!isLoading">
                    <tr v-for="item in paginatedInventories" :key="item.id"
                        :class="{ 'bg-gray-50': item.id % 2 === 0 }">
                        <td class="border px-3 py-2 text-left text-gray-600">{{ item.sku }}</td>
                        <td class="border px-3 py-2 text-left text-gray-700">
                            {{ item.product_name }}
                            <template v-if="item.attributes && item.attributes.length">
                                (
                                <span v-for="(attr, index) in item.attributes" :key="index">
                                    {{ attr.name }}: {{ attr.value }}<span v-if="index < item.attributes.length - 1">,
                                    </span>
                                </span>
                                )
                            </template>
                        </td>
                        <td class="border px-3 py-2 text-left text-gray-700">{{ item.quantity }}</td>
                        <td class="border px-3 py-2 text-left text-gray-700">{{ formatCurrency(item.cost_price) }}</td>
                        <td class="border px-3 py-2 text-left text-gray-700">{{ formatCurrency(item.sell_price) }}</td>
                        <td class="border px-3 py-2 text-left text-gray-600">{{ item.location }}</td>
                        <td class="border px-3 py-2 text-left text-gray-600">{{ formatDate(item.last_updated) }}</td>
                        <td class="border px-3 py-2 text-left">
                            <button @click="openModal('edit', item)"
                                class="text-blue-600 hover:underline text-sm">Sửa</button>
                            <span class="mx-2">|</span>
                            <button @click="openModal('damage', item)"
                                class="text-red-600 hover:underline text-sm">Hỏng</button>
                        </td>
                    </tr>
                </tbody>

                <!-- Skeleton loader -->
                <tbody v-else>
                    <tr v-for="n in 5" :key="n" class="animate-pulse bg-white">
                        <td v-for="i in 8" :key="i" class="border px-3 py-2">
                            <div class="h-4 bg-gray-200 rounded w-full"></div>
                        </td>
                    </tr>
                </tbody>


            </table>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="text-sm text-gray-700">
                    Hiển thị
                    <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
                    đến
                    <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredInventories.length)
                    }}</span>
                    của
                    <span class="font-medium">{{ filteredInventories.length }}</span>
                    kết quả
                </div>
                <nav class="inline-flex -space-x-px text-sm">
                    <button @click="prevPage" :disabled="currentPage === 1"
                        class="px-3 py-1 border border-gray-300 bg-white hover:bg-gray-50"
                        aria-label="Trước">&laquo;</button>
                    <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
                        :class="['px-3 py-1 border', currentPage === page ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-100']">
                        {{ page }}
                    </button>
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="px-3 py-1 border border-gray-300 bg-white hover:bg-gray-50"
                        aria-label="Sau">&raquo;</button>
                </nav>
            </div>

            <InventoryModal v-if="showModal" :mode="modalMode" :inventory="selectedInventory"
                @close="showModal = false; selectedInventory = null" @submitted="fetchInventories" />

            <StockMovementHistory :productVariantId="selectedInventory?.product_variant_id || null" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import InventoryModal from '@/components/inventories/InventoryModal.vue';
import StockMovementHistory from '@/components/inventories/StockMovementHistory.vue';
import { secureAxios } from '@/utils/secureAxios';

definePageMeta({
    layout: 'default-admin'
});

const router = useRouter();
const searchQuery = ref('');
const totalInventories = ref(0);
const inventories = ref([]);
const showModal = ref(false);
const modalMode = ref('create');
const selectedInventory = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const isLoading = ref(true);

const fetchInventories = async () => {
    isLoading.value = true;
    try {
        const { data } = await secureAxios(`${apiBase}/inventory/list`, {}, 'admin,seller');
        inventories.value = data;
        totalInventories.value = data.length;
    } finally {
        isLoading.value = false;
    }
};

fetchInventories();

const filteredInventories = computed(() => {
    const q = searchQuery.value.toLowerCase();
    return inventories.value.filter(inv => {
        const name = inv.product_name?.toLowerCase() || '';
        const sku = inv.sku?.toLowerCase() || '';
        return name.includes(q) || sku.includes(q);
    });
});

const paginatedInventories = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredInventories.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredInventories.value.length / itemsPerPage));
});

const visiblePages = computed(() => {
    const pages = [];
    const maxButtons = 5;
    let start = Math.max(currentPage.value - Math.floor(maxButtons / 2), 1);
    let end = start + maxButtons - 1;

    if (end > totalPages.value) {
        end = totalPages.value;
        start = Math.max(1, end - maxButtons + 1);
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

watch(searchQuery, () => {
    currentPage.value = 1;
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN').format(value) + ' đ';
const formatDate = (datetime) => new Date(datetime).toLocaleString();

const openModal = (mode, inventory = null) => {
    modalMode.value = mode;
    selectedInventory.value = inventory;
    showModal.value = true;
};
</script>

<style scoped>
/* loading speed */
.animate-pulse {
    animation: pulse .5s infinite;
}
</style>
