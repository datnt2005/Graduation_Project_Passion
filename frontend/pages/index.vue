<template>
  <main class="bg-[#f5f5f5] text-[#222222] mb-6 pt-4 py-6">
    <div class="max-w-7xl mx-auto px-2">
      <section class="flex-1 space-y-6">
        <Banner />
        <Tags />
        <Categories />
        <ProductSearch />
        <!-- banner -->
        <div class="mb-4">
          <img
            :src="BannerSecondIndex"
            alt="Banner"
            class="w-full h-[350px] rounded-lg shadow-md"
          />
        </div>
        <Products />
      </section>
    </div>
  </main>

  <PopupBanner
    :banner="popupBanner"
    :cooldownMinutes="5"
    storageKey="popup_home_nan_1308"
    :autoCloseMs="7000"
  />
</template>

<script setup>
import Banner from '~/components/shared/Banner.vue'
import Tags from '~/pages/tags/Tags.vue'
import Categories from '~/components/shared/Categories.vue'
import Products from '~/components/shared/Products.vue'
import ProductSearch from '~/components/shared/ProductSearch.vue'
import PopupBanner from '~/components/shared/PopupBanner.vue'
import BannerSecondIndex from '~/images/banner.jpg'

import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const popupBanner = ref(null)

async function fetchPopupBanner() {
  try {
    const res = await $fetch(`${apiBase}/banners/popups`)
    popupBanner.value = res?.data?.[0] || null
  } catch (e) {
    popupBanner.value = null
  }
}

onMounted(() => {
  fetchPopupBanner()
})
</script>
