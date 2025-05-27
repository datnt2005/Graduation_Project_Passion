<template>
  <div class="overflow-hidden rounded shadow-sm relative w-full">
    <div
      class="flex transition-transform duration-500 ease-in-out"
      ref="slider"
      :style="{ width: slides.length * 100 + '%', transform: `translateX(-${index * 100}%)` }"
    >
      <div
        v-for="(slide, i) in slides"
        :key="i"
        class="flex w-full flex-shrink-0"
      >
        <img
          v-for="(img, j) in slide"
          :key="j"
          :src="img"
          class="w-1/2 h-[200px] sm:h-[300px] md:h-[400px] object-cover"
          :alt="'banner-' + j"
        />
      </div>
    </div>

    <!-- Controls -->
    <button
      @click="prevSlide"
      class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-100 z-10"
    >
      ❮
    </button>
    <button
      @click="nextSlide"
      class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-100 z-10"
    >
      ❯
    </button>
  </div>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue'

const images = [
  'https://salt.tikicdn.com/cache/w1240/ts/brickv2og/0c/3f/6e/ef498ab7b78dc82d7f18677408a1b6c1.jpg.webp',
  'https://salt.tikicdn.com/cache/w1240/ts/brickv2og/e2/15/7b/e2ec901dc5bfe758679d49540808970c.png.webp',
  'https://salt.tikicdn.com/cache/w1240/ts/brickv2og/4c/68/c1/8a126222900eea23911a7bee2a03b0c9.png.webp',
  'https://salt.tikicdn.com/cache/w1240/ts/brickv2og/72/8c/40/50f734028648cfd61a7f216c2e3b2138.png.webp',
]

const slider = ref(null)
const index = ref(0)

// Gom ảnh thành từng nhóm 2 ảnh
const slides = computed(() => {
  const chunked = []
  for (let i = 0; i < images.length; i += 2) {
    chunked.push(images.slice(i, i + 2))
  }
  return chunked
})

function updateSlider() {
  if (slider.value) {
    slider.value.style.transform = `translateX(-${index.value * 100}%)`
  }
}

function nextSlide() {
  index.value = (index.value + 1) % slides.value.length
  updateSlider()
}

function prevSlide() {
  index.value = (index.value - 1 + slides.value.length) % slides.value.length
  updateSlider()
}

onMounted(() => {
  setInterval(nextSlide, 4000)
})
</script>
