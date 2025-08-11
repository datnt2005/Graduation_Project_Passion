<template>
  <main class="bg-[#f5f5f5] text-[#222222] mb-6 pt-4 py-6">
    <div class="max-w-7xl mx-auto px-2">
      <!-- Sidebar -->

      <!-- Main content -->
      <section class="flex-1 space-y-6">

        <!-- Banner -->
        <Banner />
        <!-- Tags -->
        <Tags />
        <!-- Categories -->
        <categories />
        <!-- Product Search -->
        <ProductSearch />
        <!-- Products -->
        <Products />
      </section>
    </div>
  </main>
  <!-- Popup Banner Modal -->
  <div v-if="showPopup && popupBanner && popupBanner.type === 'popup'"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="relative">
      <a v-if="popupBanner.link" :href="popupBanner.link" target="_blank" rel="noopener">
        <img :src="popupBanner.image_url" :alt="popupBanner.title"
        class="rounded-xl shadow-2xl border-4 border-white max-w-[90vw] max-h-[80vh] object-contain" />
      </a>
      <img
        v-else
        :src="popupBanner.image_url"
        :alt="popupBanner.title"
        class="rounded-xl shadow-2xl border-4 border-white max-w-[90vw] max-h-[80vh] object-contain"
      />
      <!-- Nút đóng -->
      <button @click="closePopup"
        class="absolute top-2 right-2 bg-white rounded-full shadow p-1 text-gray-600 hover:text-red-500 text-2xl font-bold"
        aria-label="Đóng">×</button>
      <!-- Nút MUA NGAY (nếu có link) -->
      <a v-if="popupBanner.link" :href="popupBanner.link" target="_blank"
        class="absolute left-1/2 -translate-x-1/2 bottom-6 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl px-8 py-3 rounded-full shadow-lg transition"
        style="min-width: 200px; text-align: center;">
        MUA NGAY
      </a>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '~/components/shared/layouts/Sidebar.vue'
import Banner from '~/components/shared/Banner.vue'
import Tags from '~/pages/tags/Tags.vue'
import Categories from '~/components/shared/Categories.vue'
import Products from '~/components/shared/Products.vue'
import ProductSearch from '~/components/shared/ProductSearch.vue'
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const popupBanner = ref(null)
const showPopup = ref(false)
let popupTimeout = null

// Hàm kiểm tra xem có nên hiện popup không
function shouldShowPopup() {
  // Kiểm tra thời gian đóng popup cuối cùng
  const lastClosedTime = localStorage.getItem('popupBannerClosedTime')
  if (lastClosedTime) {
    const now = Date.now()
    const timeDiff = now - parseInt(lastClosedTime)
    const thirtyMinutes = 30 * 60 * 1000 // 30 phút tính bằng milliseconds
    
    // Nếu chưa đủ 30 phút thì không hiện
    if (timeDiff < thirtyMinutes) {
      return false
    }
  }
  
  // Kiểm tra số lần load trang
  const loadCount = parseInt(localStorage.getItem('homepageLoadCount') || '0')
  if (loadCount >= 4) {
    return true
  }
  
  return false
}

// Hàm tăng số lần load trang
function incrementLoadCount() {
  const currentCount = parseInt(localStorage.getItem('homepageLoadCount') || '0')
  localStorage.setItem('homepageLoadCount', (currentCount + 1).toString())
}

async function fetchPopupBanner() {
  try {
    const res = await $fetch(`${apiBase}/banners/popups`)
    if (res.data && res.data.length > 0) {
      popupBanner.value = res.data[0]
      showPopup.value = true
      // Tự động đóng sau 7 giây
      popupTimeout = setTimeout(() => {
        closePopup()
      }, 7000)
    }
  } catch (e) {
    popupBanner.value = null
  }
}

function closePopup() {
  showPopup.value = false
  // Lưu thời gian đóng popup
  localStorage.setItem('popupBannerClosedTime', Date.now().toString())
  if (popupTimeout) clearTimeout(popupTimeout)
}

onMounted(() => {
  // Tăng số lần load trang
  incrementLoadCount()
  
  // Kiểm tra xem có nên hiện popup không
  if (shouldShowPopup()) {
    fetchPopupBanner()
  }
})
</script>
