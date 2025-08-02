<template>
  <main class="bg-[#F5F5FA] py-2">
    <div class="max-w-7xl mx-auto">
      <!-- Breadcrumb -->
      <div class="mb-4 mt-2 text-sm text-gray-500 rounded">
        <nuxt-link to="/" class="text-gray-400 hover:text-gray-600">Trang chủ</nuxt-link>
        <span class="mx-1">›</span>
        <span v-if="isSearchMode" class="text-gray-600 font-semibold">
          Kết quả tìm kiếm: “{{ searchQuery }}”
        </span>
        <span v-else class="text-gray-600 font-semibold capitalize">{{ currentCategoryName || route.params.slug }}</span>
      </div>

      <!-- Related Shop (Show only in shop mode or when shops are available in search mode) -->
      <div v-if="isSearchMode ? shops.length === 1 : (shop && shop.store_name !== 'N/A')">
        <h2 class="text-md font-bold mb-2">Cửa hàng liên quan</h2>
        <nuxt-link :to="`/seller/${isSearchMode ? shops[0]?.store_slug : shop.store_slug}`"
          class="block shadow hover:-translate-y-1 transition-transform duration-200 ease-in-out">
          <div class="mb-6 bg-white p-4 rounded shadow">
            <div class="flex items-center">
              <img :src="(isSearchMode ? shops[0]?.avatar : shop?.avatar) || `${mediaBase}/default-avatar.jpg`"
                alt="Shop Avatar" class="w-16 h-16 rounded-full object-cover mr-4 bg-gray-200" />
              <div>
                <h2 class="text-md font-bold">
                  {{ isSearchMode ? shops[0]?.store_name : (shop?.store_name || 'Tên cửa hàng') }}
                </h2>
                <p class="text-sm text-gray-600">
                  @{{ isSearchMode ? shops[0]?.store_slug : shop?.store_slug }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                  {{ isSearchMode ? (shops[0]?.followers ?? 0) : (shop?.followers ?? 0) }} Người Theo Dõi
                </p>
                <div class="flex items-center text-sm text-gray-700 mt-2">
                  <span class="mr-4">
                    <strong>{{ isSearchMode ? (shops[0]?.total_products ?? 0) : (shop?.total_products ?? 0) }}</strong> Sản Phẩm
                  </span>
                  <span>
                    <strong>{{ isSearchMode ? (shops[0]?.rating ?? '0.0') : (shop?.rating_value ?? '0.0') }}</strong> Đánh Giá
                  </span>
                </div>
              </div>
            </div>
          </div>
        </nuxt-link>
      </div>

      <!-- Related Shops (Show only in search mode with multiple shops) -->
      <div v-if="isSearchMode && shops.length > 1" class="mb-6">
        <h2 class="text-md font-bold mb-2">Cửa hàng liên quan</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <div v-for="shop in shops" :key="shop.id" class="bg-white p-2 rounded shadow hover:shadow-md transition">
            <nuxt-link :to="`/seller/${shop.store_slug}`" class="flex flex-col items-center">
              <img :src="shop.avatar ? `${mediaBase}/${shop.avatar}` : `${mediaBase}/avatars/default.jpg`" alt="Avatar"
                class="w-16 h-16 rounded-full object-cover mb-2 bg-gray-200" />
              <span class="text-sm font-medium text-gray-800 text-center">{{ shop.store_name }}</span>
              <span class="text-xs text-gray-500">@{{ shop.store_slug }}</span>
              <span class="text-xs text-gray-500">👥 {{ shop.followers ?? 0 }} theo dõi</span>
              <span class="text-xs text-gray-500">🛒 {{ shop.total_products ?? 0 }} SP | ⭐ {{ shop.rating ?? '0.0' }}</span>
            </nuxt-link>
          </div>
        </div>
      </div>

      <!-- Active Filters -->
      <div v-if="activeFilters.length" class="mb-4 flex flex-wrap gap-2">
        <span class="text-sm font-semibold text-gray-600">Bộ lọc đã chọn:</span>
        <span v-for="filter in activeFilters" :key="filter.key"
          class="flex items-center gap-1 bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
          {{ filter.label }}
          <button @click="removeFilter(filter)" class="text-red-500 hover:text-red-700">
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
                <nuxt-link :to="`/shop/${cat.slug}`" @click="() => trackCategoryClick(cat.id)"
                  class="font-medium text-gray-800 hover:text-blue-600 text-sm leading-snug"
                  :class="{ 'text-blue-600 font-bold': isSearchMode && searchQuery && cat.name.toLowerCase().includes(searchQuery.toLowerCase()) || route.params.slug === cat.slug }">
                  {{ cat.name }}
                </nuxt-link>
                <button v-if="cat.children?.length" @click.prevent="toggleCategory(cat.id)">
                  <i :class="expandedCategories[cat.id] ? 'fa fa-caret-up' : 'fa fa-caret-down'"></i>
                </button>
              </div>
              <ul v-if="expandedCategories[cat.id] && cat.children?.length" class="ml-4 mt-1 space-y-0.5">
                <li v-for="child in cat.children" :key="child.id">
                  <nuxt-link :to="`/shop/${child.slug}`" @click="() => trackCategoryClick(child.id)"
                    class="text-gray-600 hover:text-blue-600 text-sm mx-3"
                    :class="{ 'text-blue-600 font-medium': isSearchMode && searchQuery && child.name.toLowerCase().includes(searchQuery.toLowerCase()) || route.params.slug === child.slug }">
                    {{ child.name }}
                  </nuxt-link>
                </li>
              </ul>
            </div>
          </div>
          <div v-else class="text-gray-400">
            {{ isSearchMode ? 'Không có danh mục liên quan' : 'Không có danh mục' }}
          </div>

          <!-- Brand Filter -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-2">Thương hiệu</h3>
            <div class="ml-2 space-y-1">
              <label v-for="brand in brands" :key="brand" class="flex items-center gap-2">
                <input type="checkbox" :value="brand" v-model="filters.brand" @change="applyFilters"
                  class="h-3 w-3 text-blue-500 rounded accent-blue-500" />
                <span class="text-sm">{{ brand }}</span>
              </label>
            </div>
          </div>

          <!-- Rating Filter -->
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

          <!-- Sale Filter -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-2">Khuyến mãi</h3>
            <div class="ml-2 space-y-1">
              <label class="flex items-center gap-2">
                <input type="radio" :value="false" v-model="filters.onSale" @change="applyFilters"
                  class="w-4 text-blue-500 rounded accent-blue-500" />
                <span class="text-sm">Tất cả</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" :value="true" v-model="filters.onSale" @change="applyFilters"
                  class="w-4 text-blue-500 rounded accent-blue-500" />
                <span class="text-sm">Đang giảm giá</span>
              </label>
            </div>
          </div>

          <!-- Price Range Filter -->
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

        <!-- Main Content -->
        <main class="w-full md:w-4/5 bg-white rounded p-4 shadow">
          <!-- Sort Filters -->
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
            <div v-if="products.length === 0 && shops.length === 0" class="text-center py-8 text-gray-400 text-base">
              Không có sản phẩm hoặc cửa hàng nào phù hợp.
            </div>
            <div v-else-if="products.length === 0 && shops.length > 0" class="text-center py-8 text-gray-400 text-base">
              Không có sản phẩm nào phù hợp.
            </div>
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 gap-4">
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
              <button v-else class="px-3 py-1 rounded-full border transition font-semibold shadow-sm"
                :class="page === pagination.current_page ? 'bg-[#1BA0E2] text-white border-[#1BA0E2] shadow-md sm:scale-100 md:scale-105' : 'bg-white border-gray-300 hover:bg-blue-50 hover:border-blue-400 text-gray-700'"
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
import fontawesome from '~/plugins/fontawesome';

const route = useRoute();
const router = useRouter();
const searchStore = useSearchStore();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const isSearchMode = computed(() => route.params.slug === 'search');
const searchQuery = computed(() => decodeURIComponent(route.query.search?.trim() || ''));
const products = ref([]);
const shops = ref([]);
const categories = ref([]);
const brands = ref([]);
const loading = ref(false);
const error = ref(null);
const expandedCategories = ref({});
const currentCategoryName = ref('');
const shop = ref(null);
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

// Sidebar title
const sidebarTitle = computed(() => {
  if (isSearchMode.value) return 'Danh Mục Liên Quan';
  if (!route.params.slug || route.params.slug === '') return 'Tất Cả Danh Mục';
  return currentCategoryName.value || 'Tất Cả Danh Mục';
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

async function fetchProducts(page = 1) {
  loading.value = true;
  error.value = null;
  try {
    const slug = isSearchMode.value ? 'search' : route.params.slug;
    const url = new URL(`${apiBase}/products/search/${slug}`);
    const params = new URLSearchParams({ page: page.toString(), per_page: '20' });
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
    const res = await fetch(url);
    const data = await res.json();

    if (res.ok && data.success) {
      shops.value = Array.isArray(data.data.shops) ? data.data.shops : [];
      products.value = Array.isArray(data.data.products) ? data.data.products.map(p => ({
        ...p,
        image: p.image ? `${mediaBase}${p.image}` : `${mediaBase}/default-image.jpg`,
        sold: parseInt(p.sold) || 0,
        percent: parseFloat(p.percent) || 0,
        price: Number(p.price),
        discount: p.discount ? Number(p.discount) : null,
      })) : [];
      pagination.value = {
        current_page: data.data.current_page || 1,
        last_page: data.data.last_page || 1,
        total: data.data.total || 0,
      };
      brands.value = Array.isArray(data.data.brands) ? data.data.brands : [];
      shop.value = data.data.shop && data.data.shop.store_name !== 'N/A' ? data.data.shop : null;
      categories.value = Array.isArray(data.data.categories) ? data.data.categories : [];

      // Set current category name and expand current category in category mode
      if (!isSearchMode.value && route.params.slug && data.data.categories?.length) {
        const currentCat = data.data.categories.find(cat => cat.slug === route.params.slug);
        currentCategoryName.value = currentCat?.name || route.params.slug;
        if (currentCat) {
          expandedCategories.value[currentCat.id] = true;
        }
      } else {
        currentCategoryName.value = '';
      }
    } else {
      shops.value = [];
      products.value = [];
      shop.value = null;
      categories.value = [];
      error.value = data.message || 'Không thể tải sản phẩm';
    }
  } catch (e) {
    console.error('Fetch Error:', e.message);
    shops.value = [];
    products.value = [];
    shop.value = null;
    categories.value = [];
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

const trackCategoryClick = async (categoryId) => {
  try {
    const token = localStorage.getItem('access_token');
    await $fetch(`${apiBase}/search/track-category-click`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: {
        category_id: categoryId,
      },
    });
  } catch (error) {
    console.error('Lỗi khi tracking danh mục:', error);
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  if (route.query.search) searchStore.updateSearch(decodeURIComponent(route.query.search));

  filters.value.brand = route.query.brands ? route.query.brands.split(',') : [];
  filters.value.rating = route.query.ratings ? route.query.ratings.split(',').map(Number) : [];
  filters.value.onSale = route.query.on_sale === 'true';
  filters.value.sort = route.query.sort || 'default';
  filters.value.priceOrder = route.query.price_order || '';
  priceRange.value[0] = route.query.price_min ? Number(route.query.price_min) : priceMin;
  priceRange.value[1] = route.query.price_max ? Number(route.query.price_max) : priceMax;
  pagination.value.current_page = parseInt(route.query.page) || 1;

  fetchProducts(pagination.value.current_page);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

watch(() => [route.params.slug, route.query.search], () => {
  if (route.query.search) searchStore.updateSearch(decodeURIComponent(route.query.search));
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
        ? `Tìm kiếm sản phẩm với từ khóa "${searchQuery.value}" tại cửa hàng của chúng tôi.`
        : `Khám phá các sản phẩm trong danh mục ${currentCategoryName.value || route.params.slug}.`),
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
  border: 2px solid #fff;
  box-shadow: 0 0 2px #1ba0e2;
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  width: 16px;
  height: 16px;
  background: #1ba0e2;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 2px #1ba0e2;
  cursor: pointer;
}

input[type="range"]::-ms-thumb {
  width: 16px;
  height: 16px;
  background: #1ba0e2;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 2px #1ba0e2;
  cursor: pointer;
}

input[type="range"]:focus {
  outline: none;
}
</style>