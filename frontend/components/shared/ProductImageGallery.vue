<template>
  <div class="flex flex-col items-center md:items-start gap-3 md:w-1/3">
    <img :src="images[currentImage]" :alt="alts[currentImage]" class="w-full max-w-[300px] rounded-md border border-gray-300" height="400" width="300" loading="lazy" />
    <div class="flex gap-2 w-full max-w-[300px] overflow-x-auto">
      <img v-for="(img, idx) in images" :key="idx" :src="img" :alt="alts[idx]" class="rounded-md border border-gray-300 cursor-pointer" height="60" width="60" :class="{ 'ring-2 ring-red-400': idx === currentImage }" @click="currentImage = idx" loading="lazy" />
    </div>
  </div>
</template>

<script setup>
defineProps(['images', 'alts'])
const currentImage = defineModel('currentImage')
</script>