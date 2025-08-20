<template>
  <main class="bg-[#F5F5FA] py-6 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-5">
      <div class="text-sm text-gray-500">
        <NuxtLink to="/" class="text-gray-400">Trang chủ</NuxtLink>
        <span class="mx-1">›</span>
        <span class="text-black font-medium">{{ tag?.name || 'Tag' }}</span>
      </div>
      <!-- Banner -->
      <section class="relative rounded-xl overflow-hidden shadow-lg">
        <img
          :src="BannerTag"
          alt="Tag Banner"
          class="w-full h-[300px] md:h-[400px] object-cover"
        />
        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
          <h1 class="text-2xl md:text-4xl font-bold text-white drop-shadow-lg">
            #{{ tag?.name || 'Tag' }}
          </h1>
        </div>
      </section>

      <!-- 🔥 Sản phẩm đang giảm giá -->
      <section>
        <h2 class="text-xl font-bold text-red-600 mb-4 flex items-center gap-2">
          <font-awesome-icon :icon="['fas', 'fire']" class="text-red-500" />
          Đang giảm giá
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
          <!-- Skeleton loading -->
          <template v-if="loading">
            <div
              v-for="n in 4"
              :key="'skeleton-sale-' + n"
              class="bg-white rounded-xl shadow-sm p-4 animate-pulse space-y-4"
            >
              <div class="w-full h-48 bg-gradient-to-r from-gray-200 to-gray-300 rounded-lg" />
              <div class="h-5 bg-gray-200 rounded w-3/4" />
              <div class="h-4 bg-gray-200 rounded w-1/2" />
              <div class="h-3 bg-gray-200 rounded w-1/3" />
            </div>
          </template>

          <!-- Nếu có sản phẩm giảm giá -->
          <template v-else-if="saleProducts.length">
            <NuxtLink 
              v-for="item in paginatedSaleProducts"
              :key="item.id"
              :to="`/products/${item.slug}`"
              class="bg-white rounded-xl shadow-sm p-4 hover:shadow-lg hover:scale-105 transition-all duration-300 transform"
            >
            
              <img
                :src="resolveImage(item.thumbnail)"
                alt=""
                class="w-full h-48 object-cover rounded-lg mb-3"
              />
              <h2 class="text-sm font-semibold text-gray-800 line-clamp-2 min-h-[3em]">
                {{ item.name }}
              </h2>
              <div class="flex items-center gap-2 mt-1">
                <p class="text-sm text-gray-500 line-through">{{ formatPrice(item.price) }}</p>
                <p class="text-red-600 font-semibold">{{ formatPrice(item.sale_price) }}</p>
              </div>
              <p class="text-xs text-gray-500 mt-1">{{ item.quantity }} trong kho</p>
            </NuxtLink>
          </template>

          <!-- Không có sản phẩm sale -->
          <template v-else>
            <div
              class="col-span-full text-center text-sm text-gray-500 bg-white p-6 rounded-lg shadow-sm"
            >
              Chưa có sản phẩm đang giảm giá.
            </div>
          </template>
        </div>
        <!-- Pagination cho sản phẩm giảm giá -->
        <div
          v-if="saleProducts.length > itemsPerPage"
          class="mt-6 flex justify-center items-center gap-2 text-sm"
        >
          <button
            :disabled="currentSalePage === 1"
            @click="currentSalePage--"
            class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 disabled:opacity-50"
          >
            <font-awesome-icon :icon="['fas', 'chevron-left']" />
          </button>
          <button
            v-for="page in totalSalePages"
            :key="page"
            @click="currentSalePage = page"
            class="px-3 py-1 rounded-full border transition"
            :class="currentSalePage === page
              ? 'bg-blue-600 text-white border-blue-600'
              : 'bg-white border-gray-300 hover:bg-blue-50'"
          >
            {{ page }}
          </button>
          <button
            :disabled="currentSalePage === totalSalePages"
            @click="currentSalePage++"
            class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 disabled:opacity-50"
          >
            <font-awesome-icon :icon="['fas', 'chevron-right']" />
          </button>
        </div>
      </section>

      <!-- 📂 Sản phẩm cùng danh mục -->
      <section>
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
          <font-awesome-icon :icon="['fas', 'folder']" class="text-blue-500" />
          Sản phẩm cùng danh mục
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
          <!-- Skeleton loading -->
          <template v-if="loading">
            <div
              v-for="n in 4"
              :key="'skeleton-rel-' + n"
              class="bg-white rounded-xl shadow-sm p-4 animate-pulse space-y-4"
            >
              <div class="w-full h-48 bg-gradient-to-r from-gray-200 to-gray-300 rounded-lg" />
              <div class="h-5 bg-gray-200 rounded w-3/4" />
              <div class="h-4 bg-gray-200 rounded w-1/2" />
              <div class="h-3 bg-gray-200 rounded w-1/3" />
            </div>
          </template>

          <!-- Nếu có sản phẩm cùng danh mục -->
          <template v-else-if="relatedProducts.length">
            <NuxtLink
              v-for="item in paginatedRelatedProducts"
              :key="item.id"
              :to="`/products/${item.slug}`"
              class="bg-white rounded-xl shadow-sm p-4 hover:shadow-lg hover:scale-105 transition-all duration-300 transform"
            >
              <img
                :src="resolveImage(item.thumbnail)"
                alt=""
                class="w-full h-48 object-cover rounded-lg mb-3"
              />
              <h2 class="text-sm font-semibold text-gray-800 line-clamp-2 min-h-[3em]">
                {{ item.name }}
              </h2>
              <template v-if="item.sale_price">
                <div class="flex items-center gap-2 mt-1">
                  <p class="text-sm text-gray-500 line-through">{{ formatPrice(item.price) }}</p>
                  <p class="text-red-600 font-semibold">{{ formatPrice(item.sale_price) }}</p>
                </div>
              </template>
              <template v-else>
                <p class="text-gray-800 font-semibold mt-1">{{ formatPrice(item.price) }}</p>
              </template>
              <p class="text-xs text-gray-500 mt-1">{{ item.quantity }} trong kho</p>
            </NuxtLink>
          </template>

          <!-- Không có sản phẩm cùng danh mục -->
          <template v-else>
            <div
              class="col-span-full text-center text-sm text-gray-500 bg-white p-6 rounded-lg shadow-sm"
            >
              Chưa có sản phẩm cùng danh mục.
            </div>
          </template>
        </div>
        <!-- Pagination cho sản phẩm cùng danh mục -->
        <div
          v-if="relatedProducts.length > itemsPerPage"
          class="mt-6 flex justify-center items-center gap-2 text-sm"
        >
          <button
            :disabled="currentRelatedPage === 1"
            @click="currentRelatedPage--"
            class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 disabled:opacity-50"
          >
            <font-awesome-icon :icon="['fas', 'chevron-left']" />
          </button>
          <button
            v-for="page in totalRelatedPages"
            :key="page"
            @click="currentRelatedPage = page"
            class="px-3 py-1 rounded-full border transition"
            :class="currentRelatedPage === page
              ? 'bg-blue-600 text-white border-blue-600'
              : 'bg-white border-gray-300 hover:bg-blue-50'"
          >
            {{ page }}
          </button>
          <button
            :disabled="currentRelatedPage === totalRelatedPages"
            @click="currentRelatedPage++"
            class="px-3 py-1 rounded-full border border-gray-300 bg-white shadow-sm hover:bg-blue-50 disabled:opacity-50"
          >
            <font-awesome-icon :icon="['fas', 'chevron-right']" />
          </button>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { ref, onMounted, computed } from 'vue';
import { useRuntimeConfig } from '#imports';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import BannerTag from '~/images/image-tag.png';
import { useHead } from '#imports'

useHead({
  title: 'Tag',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})

const route = useRoute();
const slug = route.params.slug;

const tag = ref(null);
const products = ref([]);
const loading = ref(true);
const currentSalePage = ref(1);
const currentRelatedPage = ref(1);
const itemsPerPage = 8;

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

onMounted(async () => {
  try {
    const res = await fetch(`${apiBase}/tags/${slug}/products`);
    const json = await res.json();
    tag.value = {
      ...json.data?.tag,
      banner: json.data?.tag?.banner || '/default-banner.jpg', // Fallback banner
    };
    products.value = json.data?.products || [];
  } catch (err) {
    console.error('Lỗi khi load sản phẩm theo tag:', err);
  } finally {
    loading.value = false;
  }
});

const saleProducts = computed(() =>
  products.value.filter(p => p.sale_price && parseFloat(p.sale_price) < parseFloat(p.price))
);

const categoryIds = computed(() =>
  [...new Set(products.value.flatMap(p => p.categories?.map(c => c.id) || []))]
);

const relatedProducts = computed(() =>
  products.value.filter(p =>
    !saleProducts.value.includes(p) &&
    p.categories?.some(c => categoryIds.value.includes(c.id))
  )
);

const paginatedSaleProducts = computed(() => {
  const start = (currentSalePage.value - 1) * itemsPerPage;
  return saleProducts.value.slice(start, start + itemsPerPage);
});

const paginatedRelatedProducts = computed(() => {
  const start = (currentRelatedPage.value - 1) * itemsPerPage;
  return relatedProducts.value.slice(start, start + itemsPerPage);
});

const totalSalePages = computed(() =>
  Math.ceil(saleProducts.value.length / itemsPerPage)
);

const totalRelatedPages = computed(() =>
  Math.ceil(relatedProducts.value.length / itemsPerPage)
);

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
  }).format(price);
}

function resolveImage(path) {
  if (!path) return '/no-image.png';
  return path.startsWith('http') ? path : `${mediaBase}${path}`;
}
</script>

<style scoped>
/* Skeleton loading animation */
.animate-pulse {
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Responsive grid adjustments */
@media (max-width: 640px) {
  .grid-cols-1 {
    grid-template-columns: 1fr;
  }
}

/* Hover effects */
.transition {
  transition: all 0.3s ease;
}

/* Custom shadow for cards */
.shadow-sm {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.shadow-md {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Line clamp for text */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}
</style>