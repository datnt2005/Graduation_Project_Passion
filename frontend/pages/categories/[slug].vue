<template>
  <main class="bg-[#F5F5FA] py-2">
    <div class="container mx-auto p-4">
      <!-- Breadcrumb -->
      <div class="w-full max-w-6xl mb-4 text-sm text-gray-500 rounded">
        <nuxt-link to="/" class="text-gray-400 hover:text-blue-500">Trang chủ</nuxt-link>
        <span class="mx-1">›</span>
        <span class="text-gray-600 font-semibold capitalize">{{ route.params.slug }}</span>
      </div>

      <div class="flex flex-col md:flex-row gap-4">
        <!-- Sidebar -->
        <aside class="w-full md:w-1/5 bg-white rounded-xl p-4 shadow">
          <h2 class="text-md font-bold mb-4"><i class="fa fa-list mr-2"></i>Tất Cả Danh Mục</h2>
          <div v-if="categories.length" class="space-y-2">
            <div v-for="cat in categories" :key="cat.id">
              <div class="flex items-center justify-between">
                <nuxt-link :to="`/categories/${cat.slug}`"
                  class="font-medium text-gray-800 hover:text-blue-600 text-sm leading-snug"
                  :class="{ 'text-blue-600': route.params.slug === cat.slug }">
                  {{ cat.name }}
                </nuxt-link>
                <button v-if="cat.children?.length" class="text-sm text-gray-500 hover:text-blue-600"
                  @click.prevent="toggleCategory(cat.id)">
                  <i :class="expandedCategories[cat.id] ? 'fa fa-caret-up' : 'fa fa-caret-down'"></i>
                </button>
              </div>
              <ul v-if="expandedCategories[cat.id]" class="ml-4 mt-1 space-y-0.5">
                <li v-for="child in cat.children" :key="child.id">
                  <nuxt-link :to="`/categories/${child.slug}`" class="text-sm text-gray-700 hover:text-blue-500 mx-5"
                    :class="{ 'font-bold underline text-blue-600': route.params.slug === child.slug }">
                    {{ child.name }}
                  </nuxt-link>
                </li>
              </ul>
            </div>
          </div>
          <div v-else class="text-gray-400">Không có danh mục</div>

          <!-- Lọc thương hiệu -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-2">Thương hiệu</h3>
            <div class="ml-2 space-y-1">
              <label v-for="brand in brands" :key="brand" class="flex items-center gap-2">
                <input type="checkbox" :value="brand" v-model="filters.brand" @change="applyFilters"
                  class="accent-blue-500" />
                <span class="text-sm">{{ brand }}</span>
              </label>
            </div>
          </div>

          <!-- Lọc khoảng giá -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-2">Khoảng giá (VNĐ)</h3>
            <div class="px-1 text-sm text-gray-600">
              <input type="range" v-model.number="priceRange[0]" :min="priceMin" :max="priceMax" :step="10000"
                @input="onPriceInput(0, $event)"
                class="w-full h-1 bg-gray-200 rounded appearance-none cursor-pointer" />
              <input type="range" v-model.number="priceRange[1]" :min="priceMin" :max="priceMax" :step="10000"
                @input="onPriceInput(1, $event)"
                class="w-full h-1 bg-gray-200 rounded appearance-none cursor-pointer absolute top-0 left-0" />
              <div class="flex justify-between text-xs text-gray-500 mt-2">
                <span>{{ priceRange[0].toLocaleString('vi-VN') }} VNĐ</span>
                <span>{{ priceRange[1].toLocaleString('vi-VN') }} VNĐ</span>
              </div>
            </div>
            <div class="mt-3 flex gap-2">
              <button class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600" @click="applyFilters">
                Áp dụng bộ lọc
              </button>
              <button class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300"
                @click="resetFilters">
                Đặt lại bộ lọc
              </button>
            </div>
          </div>
        </aside>

        <!-- Main content -->
        <main class="w-full md:w-4/5 bg-white rounded-xl p-4 shadow">
          <!-- Bộ lọc sắp xếp -->
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
            <div class="flex flex-wrap gap-2">
              <p class="text-sm font-semibold text-gray-600 mr-2">Sắp xếp theo:</p>
              <button class="px-3 py-1 border rounded text-sm hover:bg-blue-50" @click="sortBy('popular')">Phổ
                Biến</button>
              <button class="px-3 py-1 border rounded text-sm hover:bg-blue-50" @click="sortBy('newest')">Mới
                Nhất</button>
              <button class="px-3 py-1 border rounded text-sm hover:bg-blue-50" @click="sortBy('bestseller')">Bán
                Chạy</button>
              <!-- Dropdown lọc giá -->
              <div class="relative inline-block text-left" ref="dropdownRef">
                <button @click="show = !show"
                  class="inline-flex justify-center w-full px-3 py-1 text-sm font-medium border rounded shadow-sm bg-white hover:bg-blue-50 focus:outline-none"
                  type="button">
                  Giá: {{ labelMap[modelValue] || 'Mặc định' }}
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
          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            <ProductCard v-for="item in products" :key="item.id" :item="item" />
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
                ? 'bg-[#1BA0E2] text-white border-[#1BA0E2] shadow-md scale-105'
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
import { useRoute } from 'vue-router';
import ProductCard from '~/components/shared/products/ProductCard.vue';

const route = useRoute();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const loading = ref(false);
const error = ref(null);
const expandedCategories = ref({});
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const filters = ref({
  category: null,
  brand: [],
  priceOrder: '',
  sort: 'default'
});

const priceMin = 0;
const priceMax = 10000000;
const priceRange = ref([priceMin, priceMax]);

function toggleCategory(catId) {
  expandedCategories.value[catId] = !expandedCategories.value[catId];
}

async function fetchCategories() {
  try {
    const res = await fetch(`${apiBase}/categories/tree/${route.params.slug}`);
    const data = await res.json();
    categories.value = data.categories || [];
  } catch (e) {
    error.value = 'Không thể tải danh mục';
  }
}
async function fetchProducts(page = 1) {
  try {
    loading.value = true;
    error.value = null;

    // Base URL
    let url = `${apiBase}/products/category/${route.params.slug}?page=${page}`;

    // Thêm lọc giá nếu thay đổi
    if (priceRange.value[0] > priceMin) {
      url += `&price_min=${priceRange.value[0]}`;
    }
    if (priceRange.value[1] < priceMax) {
      url += `&price_max=${priceRange.value[1]}`;
    }

    // Thêm lọc theo brand
    if (filters.value.brand.length > 0) {
      const brandsQuery = filters.value.brand.map(encodeURIComponent).join(',');
      url += `&brands=${brandsQuery}`;
    }

    // Thêm lọc sắp xếp giá
    if (filters.value.priceOrder) {
      url += `&price_order=${filters.value.priceOrder}`; // asc | desc
    }

    // Thêm lọc sắp xếp khác
    if (filters.value.sort && filters.value.sort !== 'default') {
      url += `&sort=${filters.value.sort}`; // popular | newest | bestseller
    }

    const res = await fetch(url);
    const data = await res.json();

    const productArray = data?.data?.products || [];
    products.value = productArray.map(p => ({
      ...p,
      image: p.image ? `${mediaBase}${p.image}` : '/default-image.jpg',
      sold: parseInt(p.sold) || 0,
      percent: parseFloat(p.percent) || 0,
      price: Number(p.price),              // ⬅️ ép kiểu giá
      discount: p.discount ? Number(p.discount) : null // ⬅️ ép kiểu nếu có
    }));

    pagination.value.current_page = data.data.current_page;
    pagination.value.last_page = data.data.last_page;
    pagination.value.total = data.data.total;

    // Lấy danh sách thương hiệu từ kết quả sản phẩm
    const brandSet = new Set(products.value.map(p => p.brand).filter(Boolean));
    brands.value = Array.from(brandSet);
  } catch (e) {
    error.value = 'Không thể tải sản phẩm';
  } finally {
    loading.value = false;
  }
}


const show = ref(false);
const modelValue = ref(filters.value.priceOrder);

const labelMap = {
  '': 'Mặc định',
  asc: 'Giá tăng dần',
  desc: 'Giá giảm dần'
};

function select(val) {
  modelValue.value = val;
  filters.value.priceOrder = val;
  fetchProducts(1);
  show.value = false;
}

function applyFilters() {
  fetchProducts(1);
}
function resetFilters() {
  filters.value.brand = [];
  filters.value.priceOrder = '';
  filters.value.sort = 'default';
  priceRange.value = [priceMin, priceMax];
  modelValue.value = '';
  fetchProducts(1);
}
// Đảm bảo min luôn nhỏ hơn max và ngược lại
function onPriceInput(index, event) {
  let val = Number(event.target.value);
  if (index === 0) {
    if (val > priceRange.value[1] - 10000) val = priceRange.value[1] - 10000;
    priceRange.value[0] = Math.max(priceMin, val);
  } else {
    if (val < priceRange.value[0] + 10000) val = priceRange.value[0] + 10000;
    priceRange.value[1] = Math.min(priceMax, val);
  }
}

function sortBy(type) {
  filters.value.sort = type;
  fetchProducts(1);
}

function changePage(page) {
  if (page !== pagination.value.current_page && page >= 1 && page <= pagination.value.last_page) {
    fetchProducts(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

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

// Đóng dropdown khi click ra ngoài
const dropdownRef = ref(null);
function handleClickOutside(event) {
  if (show.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    show.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  fetchCategories();
  fetchProducts(1);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Theo dõi thay đổi slug để load lại dữ liệu
watch(() => route.params.slug, () => {
  fetchCategories();
  fetchProducts(1);
});
</script>

<style scoped>
ul {
  list-style-type: none;
  padding-left: 0;
  margin: 0;
}

.range-slider {
  -webkit-appearance: none;
  appearance: none;
  background: transparent;
  pointer-events: all;
}

.range-slider::-webkit-slider-thumb {
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

.range-slider::-moz-range-thumb {
  width: 16px;
  height: 16px;
  background: #1ba0e2;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 2px #1ba0e2;
  cursor: pointer;
}

.range-slider::-ms-thumb {
  width: 16px;
  height: 16px;
  background: #1ba0e2;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 2px #1ba0e2;
  cursor: pointer;
}

.range-slider:focus {
  outline: none;
}
</style>