<template>
  <div class="flex justify-center w-full my-4">
    <div
      class="relative rounded-lg shadow overflow-hidden"
      style="width: 1200px; height: 400px; max-width: 100vw;"
    >
      <div class="w-full h-full relative">
        <div
          v-for="(img, i) in banners"
          :key="i"
          v-show="i === index"
          class="absolute inset-0 w-full h-full flex justify-center items-center bg-black transition-opacity duration-500"
        >
          <img
            :src="img"
            :alt="'banner-' + i"
            draggable="false"
            class="block w-full h-full"
            style="
              object-fit: contain;
              object-position: center;
              background: #000;
            "
          />
        </div>
      </div>
      <!-- Controls -->
      <button
        v-if="banners.length > 1"
        @click="prevSlide"
        class="absolute left-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-white flex items-center justify-center text-2xl rounded-full shadow hover:bg-gray-100 z-10 opacity-0 group-hover:opacity-80 transition-opacity duration-300"
        aria-label="Previous"
      >❮</button>
      <button
        v-if="banners.length > 1"
        @click="nextSlide"
        class="absolute right-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-white flex items-center justify-center text-2xl rounded-full shadow hover:bg-gray-100 z-10 opacity-0 group-hover:opacity-80 transition-opacity duration-300"
        aria-label="Next"
      >❯</button>
      <!-- Dots -->
      <div v-if="banners.length > 1" class="absolute bottom-3 left-0 right-0 flex justify-center gap-2 z-10">
        <span
          v-for="(img, i) in banners"
          :key="'dot-'+i"
          @click="goToSlide(i)"
          :class="[
            'block w-2.5 h-2.5 rounded-full bg-white border transition-all duration-200 cursor-pointer',
            i === index ? 'bg-blue-500 border-blue-600 scale-110' : 'bg-gray-300 border-gray-400 opacity-60'
          ]"
        ></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const banners = ref([])
const index = ref(0)
let timer = null

async function fetchBanners() {
  try {
    const res = await $fetch('http://localhost:8000/api/banners?status=active')
    banners.value = (res.data || []).map(b => b.image_url).filter(Boolean)
    if (index.value >= banners.value.length) index.value = 0
  } catch (e) {
    banners.value = []
  }
}

function nextSlide() {
  if (!banners.value.length) return
  index.value = (index.value + 1) % banners.value.length
}
function prevSlide() {
  if (!banners.value.length) return
  index.value = (index.value - 1 + banners.value.length) % banners.value.length
}
function goToSlide(i) {
  index.value = i
}

onMounted(() => {
  fetchBanners()
  timer = setInterval(nextSlide, 4000)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
})
</script>
