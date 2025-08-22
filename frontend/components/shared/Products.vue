<template>
  <div class="mt-6 bg-white p-3 mb-6">
    <div class="text-[#1BA0E2] font-bold text-sm uppercase mb-3">
      GỢI Ý HÔM NAY
    </div>

    <!-- Error banner -->
    <div
      v-if="error"
      class="mb-3 rounded border border-red-200 bg-red-50 text-red-700 text-sm px-3 py-2"
    >
      {{ error }}
    </div>

    <!-- Grid -->
    <div
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-xs text-gray-700"
    >
      <!-- Loading skeletons -->
      <div
        v-if="loading && displayedProducts.length === 0"
        v-for="i in 12"
        :key="'sk-' + i"
        class="animate-pulse rounded-lg border p-2"
      >
        <div class="h-28 bg-gray-200 rounded mb-2"></div>
        <div class="h-3 bg-gray-200 rounded mb-1"></div>
        <div class="h-3 w-2/3 bg-gray-200 rounded"></div>
      </div>

      <!-- Products -->
      <ProductCard
        v-else
        v-for="item in displayedProducts"
        :key="item.id ?? item.slug ?? item.name"
        :item="item"
      />
    </div>

    <!-- Empty state -->
    <div
      v-if="!loading && !error && displayedProducts.length === 0"
      class="text-center text-sm text-gray-500 py-6"
    >
      Không có sản phẩm phù hợp.
    </div>

    <!-- Xem thêm -->
    <div v-if="showLoadMore" class="mt-4 flex justify-center">
      <button
        class="px-4 py-2 rounded border text-sm hover:bg-blue-50 disabled:opacity-50"
        :disabled="loading"
        @click="loadMore"
      >
        {{ loading ? 'Đang tải...' : 'Xem thêm' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import ProductCard from "~/components/shared/products/ProductCard.vue";
import { useSearchStore } from "~/stores/search";

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const searchStore = useSearchStore();

const products = ref([]);   // buffer đã tải (có thể gồm nhiều trang)
const brands = ref([]);
const loading = ref(false);
const error = ref(null);

const priceMin = 0;
const priceMax = 10_000_000;
const priceRange = ref([priceMin, priceMax]);
const filters = ref({ brand: [] });

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const perPage = ref(0);        // suy ra từ trang đầu
const displayCount = ref(0);   // số item đang hiển thị (chỉ dùng cho “xem thêm”)

// Danh sách hiển thị
const displayedProducts = computed(() => products.value.slice(0, displayCount.value));

// Điều kiện hiện nút “Xem thêm”
const showLoadMore = computed(() => {
  const moreInBuffer = products.value.length > displayCount.value;
  const moreOnServer = pagination.value.current_page < pagination.value.last_page;
  return moreInBuffer || moreOnServer;
});

// FETCH
const fetchProducts = async (page = 1, opts = { append: false }) => {
  try {
    loading.value = true;
    if (!opts.append) error.value = null;

    let url = `${apiBase}/products/shop?page=${page}`;

    // Tìm kiếm
    if (searchStore.query) {
      url += `&search=${encodeURIComponent(searchStore.query)}`;
    }
    // Lọc giá
    if (priceRange.value[0] > priceMin) url += `&price_min=${priceRange.value[0]}`;
    if (priceRange.value[1] < priceMax) url += `&price_max=${priceRange.value[1]}`;

    // Lọc thương hiệu
    if (filters.value.brand.length > 0) {
      const brandsQuery = filters.value.brand.map(encodeURIComponent).join(",");
      url += `&brands=${brandsQuery}`;
    }

    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
    const data = await res.json();

    if (!data?.data?.products || !Array.isArray(data.data.products)) {
      throw new Error("Invalid data format: Expected data.data.products to be an array");
    }

    const mapped = data.data.products.map((p) => ({
      ...p,
      image: p.image ? `${mediaBase}${p.image}` : "/default-image.jpg",
      sold: typeof p.sold === "string" ? parseInt(p.sold) : p.sold,
      percent: p.percent ? parseFloat(p.percent) : 0,
    }));

    // Suy ra perPage lần đầu
    if (perPage.value === 0) perPage.value = mapped.length || perPage.value;

    if (opts.append) {
      // Gộp, tránh trùng
      const keyOf = (x) => `${x.id ?? ""}|${x.slug ?? ""}`;
      const exist = new Set(products.value.map(keyOf));
      const fresh = mapped.filter((x) => !exist.has(keyOf(x)));
      products.value = products.value.concat(fresh);
    } else {
      products.value = mapped;
    }

    brands.value = data.data.brands || [];
    const { current_page, last_page, total } = data.data || {};

    pagination.value = {
      current_page: parseInt(current_page || 1),
      last_page: parseInt(last_page || 1),
      total: parseInt(total || products.value.length),
    };

    // Lần đầu load: hiển thị đúng số vừa tải
    if (displayCount.value === 0) {
      displayCount.value = products.value.length;
    }
  } catch (err) {
    console.error("Error fetching products:", err);
    if (!opts.append) {
      error.value = err.message || "Không thể tải sản phẩm. Vui lòng thử lại sau.";
    } else {
      console.warn("Append failed:", err.message);
    }
  } finally {
    loading.value = false;
  }
};

// XEM THÊM: tăng gấp đôi số hiển thị; fetch thêm trang nếu cần
const loadMore = async () => {
  const total = pagination.value.total || Number.MAX_SAFE_INTEGER;
  const current = displayCount.value || products.value.length;
  const target = Math.min(current * 2, total);

  // Nếu buffer chưa đủ và còn trang trên server → fetch thêm
  while (
    products.value.length < target &&
    pagination.value.current_page < pagination.value.last_page
  ) {
    await fetchProducts(pagination.value.current_page + 1, { append: true });
  }

  // Cập nhật số hiển thị (không vượt quá số đã tải)
  displayCount.value = Math.min(target, products.value.length);
};

// Bộ lọc đã chọn (nếu bạn cần render ở nơi khác)
const activeFilters = computed(() => {
  const active = [];
  filters.value.brand.forEach((brand) =>
    active.push({ key: `brand_${brand}`, label: `Thương hiệu: ${brand}`, type: "brand", value: brand })
  );
  if (priceRange.value[0] > priceMin || priceRange.value[1] < priceMax) {
    active.push({
      key: "price",
      label: `Giá: ${priceRange.value[0].toLocaleString("vi-VN")} ₫ - ${priceRange.value[1].toLocaleString("vi-VN")} ₫`,
      type: "price",
    });
  }
  return active;
});

// Xoá bộ lọc
const removeFilter = (filter) => {
  if (filter.type === "brand") {
    filters.value.brand = filters.value.brand.filter((b) => b !== filter.value);
  } else if (filter.type === "price") {
    priceRange.value = [priceMin, priceMax];
  }
  resetAndFetch();
};

// Nhận cập nhật bộ lọc từ component khác
const handleFilterUpdate = (filterData) => {
  filters.value.brand = filterData.brand || [];
  priceRange.value = filterData.priceRange || [priceMin, priceMax];
  resetAndFetch();
};

// Reset khi đổi filter/search
const resetAndFetch = async () => {
  products.value = [];
  displayCount.value = 0;
  perPage.value = 0;
  pagination.value = { current_page: 1, last_page: 1, total: 0 };
  await fetchProducts(1);
};

// Mount: load trang 1
onMounted(async () => {
  await fetchProducts(1);
});

// Khi search thay đổi → reset & load lại
watch(
  () => searchStore.query,
  () => {
    resetAndFetch();
  }
);
</script>
