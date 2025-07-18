<template>
  <main class="bg-[#f5f5f5] py-6 min-h-screen">
    <div class="container mx-auto px-4 space-y-10">
      <!-- Tiêu đề tag -->
      <h1 class="text-xl font-semibold">#{{ tag?.name }}</h1>

      <!-- 🔥 Sản phẩm đang giảm giá -->
      <section>
        <h2 class="text-lg font-bold text-red-600 mb-3">Đang giảm giá</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <!-- Skeleton loading -->
          <template v-if="loading">
            <div
              v-for="n in 4"
              :key="'skeleton-sale-' + n"
              class="bg-white rounded-xl shadow-sm p-3 animate-pulse space-y-3"
            >
              <div class="w-full h-40 bg-gray-200 rounded-md" />
              <div class="h-4 bg-gray-200 rounded w-3/4" />
              <div class="h-4 bg-gray-200 rounded w-1/2" />
              <div class="h-3 bg-gray-200 rounded w-1/3" />
            </div>
          </template>

          <!-- Nếu có sản phẩm giảm giá -->
          <template v-else-if="saleProducts.length">
            <div
              v-for="item in saleProducts"
              :key="item.id"
              class="bg-white rounded-xl shadow-sm p-3 hover:shadow-md transition"
            >
              <img :src="resolveImage(item.thumbnail)" alt="" class="w-full h-40 object-cover rounded-md mb-2" />
              <h2 class="text-sm font-medium text-gray-800 line-clamp-2 min-h-[3em]">{{ item.name }}</h2>
              <p class="text-sm text-gray-500 line-through mt-1">{{ formatPrice(item.price) }}</p>
              <p class="text-red-600 font-semibold">{{ formatPrice(item.sale_price) }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ item.quantity }} trong kho</p>
            </div>
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
      </section>

      <!-- 📂 Sản phẩm cùng danh mục -->
      <section>
        <h2 class="text-lg font-bold text-gray-800 mb-3">Sản phẩm cùng danh mục</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <!-- Skeleton loading -->
          <template v-if="loading">
            <div
              v-for="n in 4"
              :key="'skeleton-rel-' + n"
              class="bg-white rounded-xl shadow-sm p-3 animate-pulse space-y-3"
            >
              <div class="w-full h-40 bg-gray-200 rounded-md" />
              <div class="h-4 bg-gray-200 rounded w-3/4" />
              <div class="h-4 bg-gray-200 rounded w-1/2" />
              <div class="h-3 bg-gray-200 rounded w-1/3" />
            </div>
          </template>

          <!-- Nếu có sản phẩm cùng danh mục -->
          <template v-else-if="relatedProducts.length">
            <div
              v-for="item in relatedProducts"
              :key="item.id"
              class="bg-white rounded-xl shadow-sm p-3 hover:shadow-md transition"
            >
              <img :src="resolveImage(item.thumbnail)" alt="" class="w-full h-40 object-cover rounded-md mb-2" />
              <h2 class="text-sm font-medium text-gray-800 line-clamp-2 min-h-[3em]">{{ item.name }}</h2>

              <template v-if="item.sale_price">
                <p class="text-sm text-gray-500 line-through mt-1">{{ formatPrice(item.price) }}</p>
                <p class="text-red-600 font-semibold">{{ formatPrice(item.sale_price) }}</p>
              </template>
              <template v-else>
                <p class="text-gray-800 font-semibold mt-1">{{ formatPrice(item.price) }}</p>
              </template>

              <p class="text-xs text-gray-500 mt-0.5">{{ item.quantity }} trong kho</p>
            </div>
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
      </section>
    </div>
  </main>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import { useRuntimeConfig } from '#imports'

const route = useRoute()
const slug = route.params.slug

const tag = ref(null)
const products = ref([])
const loading = ref(true)

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

onMounted(async () => {
  try {
    const res = await fetch(`${apiBase}/tags/${slug}/products`)
    const json = await res.json()
    tag.value = json.data?.tag
    products.value = json.data?.products || []
  } catch (err) {
    console.error('Lỗi khi load sản phẩm theo tag:', err)
  } finally {
    loading.value = false
  }
})

const saleProducts = computed(() =>
  products.value.filter(p => p.sale_price && parseFloat(p.sale_price) < parseFloat(p.price))
)

const categoryIds = computed(() =>
  [...new Set(products.value.flatMap(p => p.categories?.map(c => c.id) || []))]
)

const relatedProducts = computed(() =>
  products.value.filter(p =>
    !saleProducts.value.includes(p) &&
    p.categories?.some(c => categoryIds.value.includes(c.id))
  )
)

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
  }).format(price)
}

function resolveImage(path) {
  if (!path) return '/no-image.png'
  return path.startsWith('http') ? path : `${mediaBase}${path}`
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}
</style>
