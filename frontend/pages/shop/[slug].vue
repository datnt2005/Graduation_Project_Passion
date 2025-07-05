<template>
  <main class="bg-[#F5F5FA] py-2">
    <div class="container mx-auto p-4">
      <!-- Breadcrumb -->
      <div class="w-full max-w-6xl mb-4 text-sm text-gray-500 rounded">
        <nuxt-link to="/" class="text-gray-400">Trang chủ</nuxt-link>
        <span class="mx-1">›</span>
        <span v-if="isSearchMode" class="text-gray-600 font-semibold">
          Kết quả tìm kiếm: “{{ searchQuery }}”
        </span>
        <span v-else class="text-gray-600 font-semibold capitalize">{{ route.params.slug }}</span>
      </div>

      <!-- Active Filters -->
      <div v-if="activeFilters.length" class="mb-4 flex flex-wrap gap-2 ">
        <span class="text-sm font-semibold text-gray-600">Bộ lọc đã chọn:</span>
        <span v-for="filter in activeFilters" :key="filter.key"
          class="flex items-center gap-1 bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
          {{ filter.label }}
          <button @click="removeFilter(filter)" class="text-red-500 hover:text-red-700 mx-2">
            <i class="fas fa-times"></i>
          </button>
        </span>
      </div>

      <div class="flex flex-col md:flex-row gap-4">
        <!-- Sidebar -->
        <aside class="w-full md:w-1/5 bg-white rounded p-4 shadow-md">
          <h2 class="text-md font-bold mb-4">
            <i class="fa fa-list mr-2"></i>
            {{ sidebarTitle }}
          </h2>
          <div v-if="categories.length" class="space-y-2">
            <div v-for="cat in categories" :key="cat.id">
              <div class="flex items-center justify-between">
                <nuxt-link :to="`/shop/${cat.slug}`"
                  class="font-medium text-gray-800 hover:text-blue-600 text-sm leading-snug"
                  :class="{ 'text-blue-600 font-bold': isSearchMode && searchQuery && cat.name.toLowerCase().includes(searchQuery.toLowerCase()) }">
                  {{ cat.name }}
                </nuxt-link>
                <button v-if="cat.children?.length" @click.prevent="toggleCategory(cat.id)">
                  <i :class="expandedCategories[cat.id] ? 'fa fa-caret-up' : 'fa fa-caret-down'"></i>
                </button>
              </div>
              <ul v-if="expandedCategories[cat.id]" class="ml-4 mt-1 space-y-0.5">
                <li v-for="child in cat.children" :key="child.id">
                  <nuxt-link :to="`/shop/${child.slug}`" class="text-gray-600 hover:text-blue-600 text-sm mx-3"
                    :class="{ 'text-blue-600 font-medium': isSearchMode && searchQuery && child.name.toLowerCase().includes(searchQuery.toLowerCase()) }">
                    {{ child.name }}
                  </nuxt-link>
                </li>
              </ul>
            </div>
          </div>
          <div v-else class="text-gray-400">
            {{ isSearchMode ? 'Không có danh mục liên quan' : 'Không có danh mục' }}
          </div>

          <!-- Lọc thương hiệu -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-2">Thương hiệu</h3>
            <div class="ml-2 space-y-1">
              <label v-for="brand in brands" :key="brand" class="flex items-center gap-2">
                <input type="checkbox" :value="brand" v-model="filters.brand" @change="applyFilters"
                  class="h-3 w-3 text-blue-500 rounded accent-white" />
                <span class="text-sm">{{ brand }}</span>
              </label>
            </div>
          </div>

          <!-- Lọc theo sao -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-2">Đánh giá</h3>
            <div class="ml-2 space-y-2">
              <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="flex items-center gap-2 cursor-pointer"
                @click="selectRating(star)" :class="{ 'bg-gray-100 rounded': filters.rating.includes(star) }">
                <span class="text-lg mx-2">
                  <span class="text-yellow-400">{{ '★'.repeat(star) }}</span>
                  <span class="text-gray-300">{{ '☆'.repeat(5 - star) }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Khuyến mãi -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-2">Khuyến mãi</h3>
            <div class="ml-2 space-y-1">
              <label class="flex items-center gap-2">
                <input type="radio" :value="false" v-model="filters.onSale" @change="applyFilters"
                  class="w-4 text-blue-500 rounded accent-transparent" />
                <span class="text-sm">Tất cả</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" :value="true" v-model="filters.onSale" @change="applyFilters"
                  class="w-4 text-blue-500 rounded accent-transparent" />
                <span class="text-sm">Đang giảm giá</span>
              </label>
            </div>
          </div>

          <!-- Lọc khoảng giá -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-2">Khoảng giá (₫)</h3>
            <div class="px-1 text-sm text-gray-600">
              <input type="range" v-model.number="priceRange[0]" :min="priceMin" :max="priceRange[1]" :step="10000"
                @input="applyFiltersDebounced"
                class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-500" />
              <input type="range" v-model.number="priceRange[1]" :min="priceRange[0]" :max="priceMax" :step="10000"
                @input="applyFiltersDebounced"
                class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-500 mt-2" />
              <div class="flex justify-between text-xs text-gray-500 mt-2">
                <span>{{ priceRange[0].toLocaleString('vi-VN') }} ₫</span>
                <span>{{ priceRange[1].toLocaleString('vi-VN') }} ₫</span>
              </div>
            </div>
            <div class="mt-3 flex gap-2 justify-center">
              <button class="px-3 py-1 bg-[#1BA0E2] text-white text-sm rounded-sm hover:bg-blue-400"
                @click="applyFilters">
                Áp dụng
              </button>
              <button class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-sm hover:bg-gray-300"
                @click="resetFilters">
                Đặt lại
              </button>
            </div>
          </div>
        </aside>

        <!-- Main content -->
        <main class="w-full md:w-4/5 bg-white rounded p-4 shadow">
          <!-- Bộ lọc sắp xếp -->
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
            <div class="flex flex-wrap gap-2">
              <p class="text-sm font-semibold text-gray-600 mr-2">Sắp xếp theo:</p>
              <button class="px-3 py-1 border rounded text-sm hover:bg-blue-50"
                :class="{ 'bg-blue-100 text-blue-600': filters.sort === 'popular' }" @click="sortBy('popular')">
                Phổ Biến
              </button>
              <button class="px-3 py-1 border rounded text-sm hover:bg-blue-50"
                :class="{ 'bg-blue-100 text-blue-600': filters.sort === 'newest' }" @click="sortBy('newest')">
                Mới Nhất
              </button>
              <button class="px-3 py-1 border rounded text-sm hover:bg-blue-50"
                :class="{ 'bg-blue-100 text-blue-600': filters.sort === 'bestseller' }" @click="sortBy('bestseller')">
                Bán Chạy
              </button>
              <!-- Dropdown lọc giá -->
              <div class="relative inline-block text-left" ref="dropdownRef">
                <button @click="show = !show"
                  class="inline-flex justify-center w-full px-3 py-1 text-sm font-medium border rounded shadow-sm bg-white hover:bg-blue-50 focus:outline-none">
                  Giá: {{ labelMap[filters.priceOrder] || 'Mặc định' }}
                  <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                <div v-show="show"
                  class="absolute z-10 mt-1 w-full rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <ul class="py-1 text-sm">
                    <li v-for="(label, val) in labelMap" :key="val">
                      <button @click="select(val)"
                        class="block w-full px-4 py-2 text-left hover:bg-blue-50 hover:text-blue-600 transition">
                        {{ label }}
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="text-center py-4 text-gray-500">Đang tải sản phẩm...</div>
          <div v-else-if="error" class="text-center py-4 text-red-500">Lỗi: {{ error }}</div>
          <!-- Products -->
          <div v-else>
            <div v-if="products.length === 0" class="text-center py-8 text-gray-400 text-base">
              Không có sản phẩm nào phù hợp.
            </div>
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
              <ProductCard v-for="item in products" :key="item.id" :item="item" />
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-8 flex justify-center items-center gap-1 text-sm flex-wrap" v-if="pagination.last_page > 1">
            <button
              class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 hover:border-blue-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
              <i class="fas fa-chevron-left mr-1"></i>
            </button>
            <template v-for="(page, i) in visiblePages" :key="i">
              <span v-if="page === '...'" class="px-3 py-1 text-gray-400 font-semibold select-none">...</span>
              <button v-else class="px-3 py-1 rounded-full border transition font-semibold shadow-sm" :class="page === pagination.current_page
                ? 'bg-[#1BA0E2] text-white border-[#1BA0E2] shadow-md sm:scale-100 md:scale-105'
                : 'bg-white border-gray-300 hover:bg-blue-50 hover:border-blue-400 text-gray-700'"
                @click="changePage(page)">
                {{ page }}
              </button>
            </template>
            <button
              class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 hover:border-blue-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="pagination.current_page === pagination.last_page"
              @click="changePage(pagination.current_page + 1)">
              <i class="fas fa-chevron-right ml-1"></i>
            </button>
          </div>
        </main>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSearchStore } from '~/stores/search';
import { debounce } from 'lodash';
import ProductCard from '~/components/shared/products/ProductCard.vue';

const route = useRoute();
const router = useRouter();
const searchStore = useSearchStore();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const isSearchMode = computed(() => route.params.slug === 'search');
const searchQuery = computed(() => decodeURIComponent(route.query.search?.trim() || ''));
const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const loading = ref(false);
const error = ref(null);
const expandedCategories = ref({});
const currentCategoryName = ref(''); // Tên danh mục cha
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});
const filters = ref({
  brand: [],
  rating: [],
  onSale: false,
  priceOrder: '',
  sort: 'default',
});
const priceMin = 0;
const priceMax = 100000000;
const priceRange = ref([priceMin, priceMax]);

// Tiêu đề sidebar động
const sidebarTitle = computed(() => {
  if (isSearchMode.value) {
    // Nếu là search, hiển thị "Danh mục liên quan"
    return 'Danh Mục Liên Quan';
  }
  // Nếu không có slug (tức là trang tất cả sản phẩm), hiển thị "Tất cả danh mục"
  if (!route.params.slug || route.params.slug === '' || route.params.slug === undefined) {
    return 'Tất Cả Danh Mục';
  }
  // Nếu có slug, hiển thị "Danh mục con của ..."
  return 'Tất Cả Danh Mục';
});

// Active filters
const activeFilters = computed(() => {
  const active = [];
  filters.value.brand.forEach(brand => {
    active.push({
      key: `brand_${brand}`,
      label: `Thương hiệu: ${brand}`,
      type: 'brand',
      value: brand,
    });
  });
  filters.value.rating.forEach(star => {
    active.push({
      key: `rating_${star}`,
      label: star === 0 ? 'Không có đánh giá' : `Đánh giá: ${'★'.repeat(star)}`,
      type: 'rating',
      value: star,
    });
  });
  if (filters.value.onSale) {
    active.push({
      key: 'onSale',
      label: 'Đang giảm giá',
      type: 'onSale',
    });
  }
  if (priceRange.value[0] > priceMin || priceRange.value[1] < priceMax) {
    active.push({
      key: 'price',
      label: `Giá: ${priceRange.value[0].toLocaleString('vi-VN')} - ${priceRange.value[1].toLocaleString('vi-VN')} ₫`,
      type: 'price',
    });
  }
  return active;
});

const show = ref(false);
const labelMap = {
  '': 'Mặc định',
  asc: 'Giá tăng dần',
  desc: 'Giá giảm dần',
};

function toggleCategory(catId) {
  expandedCategories.value[catId] = !expandedCategories.value[catId];
}

async function fetchCategories() {
  try {
    const url = new URL(`${apiBase}/categories/tree`);
    // Nếu là search thì lấy danh mục liên quan đến từ khóa
    if (isSearchMode.value && searchQuery.value) {
      url.searchParams.append('search', searchQuery.value);
    } else if (route.params.slug && !isSearchMode.value) {
      // Nếu có slug thì lấy danh mục con của slug cha
      url.searchParams.append('slug', route.params.slug);
    }
    const res = await fetch(url);
    const data = await res.json();
    if (data.success) {
      categories.value = data.data?.categories || [];
      // Nếu có slug và không phải search, lấy tên danh mục cha
      if (!isSearchMode.value && route.params.slug && data.data?.categories?.length) {
        currentCategoryName.value = data.data.categories[0]?.name || route.params.slug;
      }
      // Nếu là search, không cần currentCategoryName
      if (isSearchMode.value) {
        currentCategoryName.value = '';
      }
    } else {
      categories.value = [];
      currentCategoryName.value = '';
    }
  } catch (e) {
    categories.value = [];
    currentCategoryName.value = '';
  }
}

async function fetchProducts(page = 1) {
  loading.value = true;
  error.value = null;
  try {
    const slug = isSearchMode.value ? 'search' : route.params.slug;
    const url = new URL(`${apiBase}/products/search/${slug}`); // Sửa thành /products/[slug]
    const params = new URLSearchParams({ page: page.toString(), per_page: '24' });
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (filters.value.brand.length) params.append('brands', filters.value.brand.join(','));
    if (filters.value.rating.length) {
      filters.value.rating.forEach(rating => params.append('ratings[]', rating.toString()));
    }
    if (filters.value.onSale) params.append('on_sale', 'true');
    if (priceRange.value[0] > priceMin) params.append('price_min', priceRange.value[0].toString());
    if (priceRange.value[1] < priceMax) params.append('price_max', priceRange.value[1].toString());
    if (filters.value.priceOrder) params.append('price_order', filters.value.priceOrder);
    if (filters.value.sort !== 'default') params.append('sort', filters.value.sort);

    url.search = params.toString();
    console.log('Fetching products with URL:', url.toString());

    const res = await fetch(url);
    const data = await res.json();

    if (res.ok && data.success) {
      products.value = (data.data.products || []).map(p => ({
        ...p,
        image: p.image ? `${mediaBase}${p.image}` : `${mediaBase}/default-image.jpg`,
        sold: parseInt(p.sold) || 0,
        percent: parseFloat(p.percent) || 0,
        price: Number(p.price),
        discount: p.discount ? Number(p.discount) : null,
      }));
      
      pagination.value = {
        current_page: data.data.current_page || 1,
        last_page: data.data.last_page || 1,
        total: data.data.total || 0,
      };
      brands.value = data.data.brands || [];
    } else {
      products.value = [];
      error.value = data.message || 'Không thể tải sản phẩm';
    }
  } catch (e) {
    console.error('Fetch Error:', e.message);
    products.value = [];
    error.value = 'Không thể tải sản phẩm';
  } finally {
    loading.value = false;
  }
}

function updateQueryParams() {
  const query = {};
  if (searchQuery.value) query.search = searchQuery.value;
  if (filters.value.brand.length) query.brands = filters.value.brand.join(',');
  if (filters.value.rating.length) query.ratings = filters.value.rating.join(',');
  if (filters.value.onSale) query.on_sale = 'true';
  if (priceRange.value[0] > priceMin) query.price_min = priceRange.value[0].toString();
  if (priceRange.value[1] < priceMax) query.price_max = priceRange.value[1].toString();
  if (filters.value.sort !== 'default') query.sort = filters.value.sort;
  if (filters.value.priceOrder) query.price_order = filters.value.priceOrder;
  if (pagination.value.current_page > 1) query.page = pagination.value.current_page.toString();

  router.push({ path: `/shop/${route.params.slug}`, query });
}

const applyFiltersDebounced = debounce(() => {
  fetchProducts(1);
  updateQueryParams();
}, 500);

function applyFilters() {
  fetchProducts(1);
  updateQueryParams();
}

function resetFilters() {
  filters.value.brand = [];
  filters.value.rating = [];
  filters.value.onSale = false;
  filters.value.priceOrder = '';
  filters.value.sort = 'default';
  priceRange.value = [priceMin, priceMax];
  applyFilters();
}

function removeFilter(filter) {
  if (filter.type === 'brand') {
    filters.value.brand = filters.value.brand.filter(b => b !== filter.value);
  } else if (filter.type === 'rating') {
    filters.value.rating = filters.value.rating.filter(r => r !== filter.value);
  } else if (filter.type === 'onSale') {
    filters.value.onSale = false;
  } else if (filter.type === 'price') {
    priceRange.value = [priceMin, priceMax];
  }
  applyFilters();
}

function selectRating(star) {
  if (filters.value.rating.includes(star)) {
    filters.value.rating = filters.value.rating.filter(r => r !== star);
  } else {
    filters.value.rating.push(star);
  }
  applyFilters();
}

function select(val) {
  filters.value.priceOrder = val;
  applyFilters();
  show.value = false;
}

function sortBy(type) {
  filters.value.sort = type;
  applyFilters();
}

function changePage(page) {
  fetchProducts(page);
  updateQueryParams();
}

const visiblePages = computed(() => {
  const total = pagination.value.last_page;
  const current = pagination.value.current_page;
  const range = [];

  if (total <= 7) {
    for (let i = 1; i <= total; i++) range.push(i);
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

const dropdownRef = ref(null);
function handleClickOutside(event) {
  if (show.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    show.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  if (route.query.search) searchStore.updateSearch(decodeURIComponent(route.query.search));

  // Initialize filters from query parameters
  filters.value.brand = route.query.brands ? route.query.brands.split(',') : [];
  filters.value.rating = route.query.ratings ? route.query.ratings.split(',').map(Number) : [];
  filters.value.onSale = route.query.on_sale === 'true';
  filters.value.sort = route.query.sort || 'default';
  filters.value.priceOrder = route.query.price_order || '';
  priceRange.value[0] = route.query.price_min ? Number(route.query.price_min) : priceMin;
  priceRange.value[1] = route.query.price_max ? Number(route.query.price_max) : priceMax;
  pagination.value.current_page = parseInt(route.query.page) || 1;

  fetchCategories();
  fetchProducts(pagination.value.current_page);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

watch(() => [route.params.slug, route.query.search], () => {
  if (route.query.search) searchStore.updateSearch(decodeURIComponent(route.query.search));
  fetchCategories();
  fetchProducts(1);
});

definePageMeta({
  meta: [
    {
      name: 'title',
      content: computed(() => isSearchMode.value
        ? `Kết quả tìm kiếm: ${searchQuery.value}`
        : `Sản phẩm danh mục ${currentCategoryName.value || route.params.slug}`),
    },
    {
      name: 'description',
      content: computed(() => isSearchMode.value
        ? `Tìm kiếm sản phẩm với từ khóa ${searchQuery.value}" tại quán của chúng tôi.`
        : `Khám phá các sản phẩm trong danh mục ${currentCategoryName.value} || route.params.slug }.`),
    },
  ],
});
</script>

<style scoped>
ul {
  list-style-type: none;
  padding-left: 0;
  margin: 0;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  background: #1ba0e2;
  border-radius: 50%;
  border: none;
  border-color: 2px solid #fff;
  box-shadow: 0 0 2px #1ba0e2 cursor;
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  width: 16px;
  height: 16px;
  background: #1ba0e2;
  border-radius: 50%;
  border: none;
  border-width: 2px solid #fff;
  box-shadow: 0 0 2px #1ba0e2 cursor;
  cursor: pointer;
}

input[type="range"]::-ms-thumb {
  width: 16px;
  height: 16px;
  background: #1ba0e2;
  border-radius: 50%;
  border: none;
  border-color: 2px solid #fff;
  box-shadow: none0 0 2px #1ba0e2;
  cursor: pointer;
}

input[type="range"]:focus {
  outline: none;
}
</style>