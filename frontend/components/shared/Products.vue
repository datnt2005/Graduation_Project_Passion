<template>
  <div class="bg-white p-4 rounded-lg shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Tất cả sản phẩm</h2>

    <!-- Hiển thị bộ lọc đã chọn -->
    <div v-if="activeFilters.length" class="mb-4 flex flex-wrap gap-2">
      <span class="text-sm font-semibold text-gray-600">Bộ lọc đã chọn:</span>
      <div v-for="filter in activeFilters" :key="filter.key"
        class="flex items-center gap-1 bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
        <span>{{ filter.label }}</span>
        <button @click="removeFilter(filter)" class="text-red-500 hover:text-red-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- Bộ lọc -->
    <Filters @update:filters="handleFilterUpdate" :brands="brands" :priceMin="priceMin" :priceMax="priceMax"
      :priceRange="priceRange" />

    <!-- Trạng thái tải -->
    <div v-if="loading" class="text-center py-4">
      <p class="text-gray-500">Đang tải sản phẩm...</p>
    </div>

    <!-- Lỗi API -->
    <div v-else-if="error" class="text-center py-4">
      <p class="text-red-500">Có lỗi xảy ra khi tải sản phẩm: {{ error }}</p>
    </div>

    <!-- Danh sách sản phẩm -->
    <div v-else-if="products.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
      <ProductCard v-for="item in products" :key="item.id" :item="item" />
    </div>

    <!-- Trạng thái không có sản phẩm -->
    <div v-else class="text-center py-4">
      <p class="text-gray-500">Không tìm thấy sản phẩm nào.</p>
    </div>

    <!-- Phân trang -->
    <div class="mt-8 flex justify-center items-center gap-1 text-sm flex-wrap" v-if="pagination.last_page > 1">
      <button
        class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 hover:border-blue-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
        <i class="fas fa-chevron-left mr-1"></i>
      </button>
      <template v-for="(page, i) in visiblePages" :key="i">
        <span v-if="page === '...'" class="px-3 py-1 text-gray-400 font-semibold select-none">...</span>
        <button v-else class="px-3 py-1 rounded-full border transition font-semibold shadow-sm" :class="page === pagination.current_page
          ? 'bg-[#1BA0E2] text-white border-[#1BA0E2] shadow-md scale-105'
          : 'bg-white border-gray-300 hover:bg-blue-50 hover:border-blue-400 text-gray-700'"
          @click="() => handlePageClick(page)">
          {{ page }}
        </button>
      </template>
      <button
        class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 hover:border-blue-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">
        <i class="fas fa-chevron-right ml-1"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Filters from '~/components/shared/filters/Filters.vue';
import ProductCard from '~/components/shared/products/ProductCard.vue';
import { useSearchStore } from '~/stores/search';

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const searchStore = useSearchStore();

const products = ref([]);
const brands = ref([]);
const loading = ref(false);
const error = ref(null);
const priceMin = 0;
const priceMax = 10000000;
const priceRange = ref([priceMin, priceMax]);
const filters = ref({ brand: [] });
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

// Fetch products from API
const fetchProducts = async (page = 1) => {
  try {
    loading.value = true;
    error.value = null;

    let url = `${apiBase}/products/shop?page=${page}`;

    // Thêm tìm kiếm
    if (searchStore.query) {
      url += `&search=${encodeURIComponent(searchStore.query)}`;
    }

    // Thêm lọc giá
    if (priceRange.value[0] > priceMin) {
      url += `&price_min=${priceRange.value[0]}`;
    }
    if (priceRange.value[1] < priceMax) {
      url += `&price_max=${priceRange.value[1]}`;
    }

    // Thêm lọc thương hiệu
    if (filters.value.brand.length > 0) {
      const brandsQuery = filters.value.brand.map(encodeURIComponent).join(',');
      url += `&brands=${brandsQuery}`;
    }

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data = await response.json();

    if (!data?.data?.products || !Array.isArray(data.data.products)) {
      throw new Error('Invalid data format: Expected data.data.products to be an array');
    }

    products.value = data.data.products.map(p => ({
      ...p,
      image: p.image ? `${mediaBase}${p.image}` : '/default-image.jpg',
      sold: typeof p.sold === 'string' ? parseInt(p.sold) : p.sold,
      percent: p.percent ? parseFloat(p.percent) : 0,
    }));

    brands.value = data.data.brands || [];
    const { current_page, last_page, total } = data.data || {};

    pagination.value = {
      current_page: parseInt(current_page || 1),
      last_page: parseInt(last_page || 1),
      total: parseInt(total || 0),
    };

    // Debug log
    console.log('Pagination:', pagination.value);
  } catch (err) {
    console.error('Error fetching products:', err);
    error.value = err.message || 'Không thể tải sản phẩm. Vui lòng thử lại sau.';
  } finally {
    loading.value = false;
  }
};

// Bộ lọc đã chọn
const activeFilters = computed(() => {
  const active = [];

  // Bộ lọc thương hiệu
  filters.value.brand.forEach(brand => {
    active.push({
      key: `brand_${brand}`,
      label: `Thương hiệu: ${brand}`,
      type: 'brand',
      value: brand
    });
  });

  // Bộ lọc giá
  if (priceRange.value[0] > priceMin || priceRange.value[1] < priceMax) {
    active.push({
      key: 'price',
      label: `Giá: ${priceRange.value[0].toLocaleString('vi-VN')} ₫ - ${priceRange.value[1].toLocaleString('vi-VN')} ₫`,
      type: 'price'
    });
  }

  return active;
});

// Xóa bộ lọc
const removeFilter = (filter) => {
  if (filter.type === 'brand') {
    filters.value.brand = filters.value.brand.filter(b => b !== filter.value);
  } else if (filter.type === 'price') {
    priceRange.value = [priceMin, priceMax];
  }
  fetchProducts(1);
};

// Xử lý cập nhật bộ lọc
const handleFilterUpdate = (filterData) => {
  filters.value.brand = filterData.brand || [];
  priceRange.value = filterData.priceRange || [priceMin, priceMax];
  fetchProducts(1);
};

// Phân trang thông minh
const visiblePages = computed(() => {
  const total = pagination.value.last_page;
  const current = pagination.value.current_page;
  const range = [];

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      range.push(i);
    }
  } else {
    if (current <= 4) {
      range.push(1, 2, 3, 4, 5, '...', total);
    } else if (current >= total - 3) {
      range.push(1, '...', total - 4, total - 3, total - 2, total - 1, total);
    } else {
      range.push(1, '...', current - 1, current, current + 1, '...', total);
    }
  }
  return range;
});

// Đảm bảo không click vào dấu "..."
const handlePageClick = (page) => {
  if (typeof page === 'number') {
    changePage(page);
  }
};

const changePage = (page) => {
  // Log để debug
  // console.log('Change to page:', page, 'Current:', pagination.value.current_page, 'Last:', pagination.value.last_page);
  if (page !== pagination.value.current_page && page >= 1 && page <= pagination.value.last_page) {
    fetchProducts(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

onMounted(() => {
  fetchProducts(1);
});
</script>