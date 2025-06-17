<template>
 <div class="flex flex-col items-center md:items-start md:w-[480px]">
    <!-- Main image -->
    <div class="w-[480px] h-[480px] border border-gray-200 relative">
      <img
        :src="images[currentIndex]"
        alt="Ảnh chính"
        class="w-full h-full object-contain"
      />
    </div>

    <!-- Thumbnails -->
    <div class="mt-4 flex items-center space-x-2 overflow-x-auto thumbs w-full max-w-[480px]">
      <button @click="prevImage" class="w-8 h-24 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 flex items-center justify-center">
        <i class="fas fa-chevron-left"></i>
      </button>

      <img
        v-for="(img, index) in images"
        :key="index"
        :src="img"
        :class="['w-16 h-24 object-cover rounded-md cursor-pointer border', currentIndex === index ? 'border-red-500' : 'border-transparent']"
        @click="currentIndex = index"
      />

      <button @click="nextImage" class="w-8 h-24 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 flex items-center justify-center">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps(['images', 'alts'])
const currentImage = defineModel('currentImage')
import { ref, onMounted, onUnmounted } from 'vue'

const images = [
  'https://storage.googleapis.com/a1aa/image/a3d0ac10-d42a-4d73-6f3a-52faa642cf97.jpg',
  'https://storage.googleapis.com/a1aa/image/bf482517-6de4-4f11-2c54-9080989ae482.jpg',
  'https://storage.googleapis.com/a1aa/image/6b15f3c8-8202-4bcf-fef5-647fd4957f1f.jpg',
  'https://storage.googleapis.com/a1aa/image/efced401-69cf-417d-5c03-f8d8a5b54c2a.jpg',
  'https://storage.googleapis.com/a1aa/image/48c0d4f2-1d8d-4433-1f27-81a32df34bf1.jpg',
  'https://storage.googleapis.com/a1aa/image/9fbbacf6-18d4-4573-2e33-cab1978f0087.jpg'
]

const currentIndex = ref(0)

function nextImage() {
  currentIndex.value = (currentIndex.value + 1) % images.length
}

function prevImage() {
  currentIndex.value = (currentIndex.value - 1 + images.length) % images.length
}

// Auto slide
let interval
onMounted(() => {
  interval = setInterval(nextImage, 4000)
})

onUnmounted(() => {
  clearInterval(interval)
})
</script>

<style scoped>
.thumbs::-webkit-scrollbar {
  display: none;
}
</style>
