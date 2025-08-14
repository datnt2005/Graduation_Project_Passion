<template>
  <div class="mt-6 bg-white p-3 mb-6 ">
    <div class="text-[#1BA0E2] font-bold text-sm uppercase mb-3">
      GỢI Ý HÔM NAY
    </div>
    <div
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-xs text-gray-700"
    >
      <ProductCard
        v-for="(item, index) in products"
        :key="index"
        :item="item"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import ProductCard from "~/components/shared/products/ProductCard.vue";
import { useSearchStore } from "~/stores/search";

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
      const brandsQuery = filters.value.brand.map(encodeURIComponent).join(",");
      url += `&brands=${brandsQuery}`;
    }

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data = await response.json();

    if (!data?.data?.products || !Array.isArray(data.data.products)) {
      throw new Error(
        "Invalid data format: Expected data.data.products to be an array"
      );
    }

    products.value = data.data.products.map((p) => ({
      ...p,
      image: p.image ? `${mediaBase}${p.image}` : "/default-image.jpg",
      sold: typeof p.sold === "string" ? parseInt(p.sold) : p.sold,
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
    console.log("Pagination:", pagination.value);
  } catch (err) {
    console.error("Error fetching products:", err);
    error.value =
      err.message || "Không thể tải sản phẩm. Vui lòng thử lại sau.";
  } finally {
    loading.value = false;
  }
};

// Bộ lọc đã chọn
const activeFilters = computed(() => {
  const active = [];

  // Bộ lọc thương hiệu
  filters.value.brand.forEach((brand) => {
    active.push({
      key: `brand_${brand}`,
      label: `Thương hiệu: ${brand}`,
      type: "brand",
      value: brand,
    });
  });

  // Bộ lọc giá
  if (priceRange.value[0] > priceMin || priceRange.value[1] < priceMax) {
    active.push({
      key: "price",
      label: `Giá: ${priceRange.value[0].toLocaleString(
        "vi-VN"
      )} ₫ - ${priceRange.value[1].toLocaleString("vi-VN")} ₫`,
      type: "price",
    });
  }

  return active;
});

// Xóa bộ lọc
const removeFilter = (filter) => {
  if (filter.type === "brand") {
    filters.value.brand = filters.value.brand.filter((b) => b !== filter.value);
  } else if (filter.type === "price") {
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
      range.push(1, 2, 3, 4, 5, "...", total);
    } else if (current >= total - 3) {
      range.push(1, "...", total - 4, total - 3, total - 2, total - 1, total);
    } else {
      range.push(1, "...", current - 1, current, current + 1, "...", total);
    }
  }
  return range;
});

// Đảm bảo không click vào dấu "..."
const handlePageClick = (page) => {
  if (typeof page === "number") {
    changePage(page);
  }
};

const changePage = (page) => {
  // Log để debug
  // console.log('Change to page:', page, 'Current:', pagination.value.current_page, 'Last:', pagination.value.last_page);
  if (
    page !== pagination.value.current_page &&
    page >= 1 &&
    page <= pagination.value.last_page
  ) {
    fetchProducts(page);
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

onMounted(() => {
  fetchProducts(1);
});
</script>
