<template>
  <div class="bg-white p-4 rounded-lg shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Tất cả sản phẩm</h2>

    <!-- Bộ lọc -->
    <Filters @update:filters="handleBrandFilter" />

    <!-- Trạng thái tải -->
    <div v-if="loading" class="text-center py-4">
      <p class="text-gray-500">Đang tải sản phẩm...</p>
    </div>

    <!-- Lỗi API -->
    <div v-else-if="error" class="text-center py-4">
      <p class="text-red-500">Có lỗi xảy ra khi tải sản phẩm: {{ error }}</p>
    </div>

    <!-- Danh sách sản phẩm -->
    <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
      <div
        v-for="(item, index) in filteredProducts"
        :key="item.id"
        class="relative overflow-hidden p-2 bg-white rounded shadow transition transform hover:scale-[1.03] hover:-translate-y-1 hover:shadow-lg duration-300 text-left"
      >
        <nuxt-link :to="`/products/${item.slug}`" class="block group">
          <!-- Discount badge -->
          <div
            v-if="item.percent && item.percent > 0"
            class="absolute top-0 right-0 bg-red-100 text-red-500 text-xs font-semibold px-2 py-1 rounded z-10"
          >
            -{{ item.percent }}%
          </div>

          <!-- Hình ảnh sản phẩm -->
          <img
            :src="item.image"
            :alt="item.name"
            class="w-full h-40 object-cover rounded group-hover:brightness-95 transition duration-300"
            loading="lazy"
          />

          <!-- Tên sản phẩm -->
          <p
            class="text-sm mt-2 font-medium text-gray-700 line-clamp-2"
            :title="item.name"
          >
            {{ item.name }}
          </p>

          <!-- Giá -->
          <div class="text-red-500 font-semibold mt-1">
            {{ formatPrice(item.price) }}<sup>₫</sup>
          </div>

          <!-- Giá gạch ngang nếu có giảm -->
          <div v-if="item.discount" class="line-through text-gray-400 text-sm">
            {{ formatPrice(item.discount) }}<sup>₫</sup>
          </div>

          <!-- Đánh giá & đã bán -->
          <div class="flex items-center text-[12px] text-gray-400 space-x-2 mt-1">
            <div class="text-yellow-400">{{ item.rating }}</div>
            <div>| {{ item.sold.toLocaleString() }} đã bán</div>
          </div>
        </nuxt-link>
      </div>
    </div>

    <!-- Trạng thái không có sản phẩm -->
    <div v-else class="text-center py-4">
      <p class="text-gray-500">Không tìm thấy sản phẩm nào.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Filters from '~/components/shared/Filters.vue';
import { useSearchStore } from '~/stores/search';

// Runtime config for API and media base URLs
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

// Pinia store for search query
const searchStore = useSearchStore();

// Reactive state
const products = ref([]);
const loading = ref(false);
const error = ref(null);
const filters = ref({ brand: [] });

// Fetch products from API
const fetchProducts = async () => {
  try {
    loading.value = true;
    error.value = null;

    const response = await fetch(`${apiBase}/products/shop`);
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data = await response.json();
    console.log('API Response:', data);

    let productArray = [];

    // Dựa trên cấu trúc thực tế:
    if (data?.data?.products && Array.isArray(data.data.products)) {
      productArray = data.data.products;
    } else {
      throw new Error('Invalid data format: Expected data.data.products to be an array');
    }

    // Gán sản phẩm và xử lý hình ảnh, đảm bảo percent được xử lý
    products.value = productArray.map(p => ({
      ...p,
      image: p.image ? `${mediaBase}${p.image}` : '/default-image.jpg',
      sold: typeof p.sold === 'string' ? parseInt(p.sold) : p.sold,
      percent: p.percent ? parseFloat(p.percent) : 0, // Đảm bảo percent là số
    }));

    console.log('Processed Products:', products.value);
  } catch (err) {
    console.error('Error fetching products:', err);
    error.value = err.message || 'Không thể tải sản phẩm. Vui lòng thử lại sau.';
  } finally {
    loading.value = false;
  }
};

// Computed property for filtered products
const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchQuery = searchStore.query
      ? p.name.toLowerCase().includes(searchStore.query.toLowerCase())
      : true;
    const matchBrand = filters.value.brand.length > 0
      ? filters.value.brand.includes(p.brand)
      : true;
    return matchQuery && matchBrand;
  });
});

// Handle filter updates from Filters component
const handleBrandFilter = (filterData) => {
  filters.value.brand = filterData.brand || [];
};

// Format price with thousand separators
const formatPrice = (price) => {
  return price ? price.toLocaleString('vi-VN') : '0';
};

// Fetch products on component mount
onMounted(() => {
  fetchProducts();
});
</script>

<style scoped>
/* Limit product name to 2 lines */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>