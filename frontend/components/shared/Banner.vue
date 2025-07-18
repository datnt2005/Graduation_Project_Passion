<template>
  <div class="bg-white w-full">
    <!-- Top banner -->
    <div
      class="flex flex-wrap justify-center md:justify-between gap-4 mb-6 px-2"
    >
      <!-- Slide bên trái -->
      <div
        class="flex-1 min-w-[280px] max-w-full relative overflow-hidden shadow-md"
      >
        <div
          id="bannerSlides"
          class="flex transition-transform duration-700 ease-in-out"
          :style="{ transform: `translateX(-${index * 100}%)` }"
        >
          <img
            v-for="(img, i) in banners"
            :key="i"
            :src="img"
            :alt="`Slide ${i + 1}`"
            class="w-full flex-shrink-0 object-contain"
          />
        </div>

        <!-- Dots -->
        <div
          class="absolute bottom-3 left-1/2 transform -translate-x-1/2 flex gap-2 z-10"
        >
          <button
            v-for="(img, i) in banners"
            :key="i"
            class="dot w-3 h-3 rounded-full bg-white opacity-70 hover:bg-[#1BA0E2]"
            :class="{ 'bg-[#1BA0E2]': i === index }"
            @click="goToSlide(i)"
          ></button>
        </div>
      </div>

      <!-- Banner phải -->
      <div class="hidden sm:flex flex-col gap-4 min-w-[250px] max-w-[360px]">
        <img
          src="https://sf-static.upanhlaylink.com/img/image_20250718176342fcca97b9ab168d67eb5c15616e.jpg"
          alt="Banner nhỏ 1"
          class="w-full h-auto object-contain shadow-md"
        />
        <img
          src="https://sf-static.upanhlaylink.com/img/image_202507181fb34ba202f43ade448a5eeb1078e9d5.jpg"
          alt="Banner nhỏ 2"
          class="w-full h-auto object-contain shadow-md"
        />
      </div>
    </div>

    <!-- Navigation bar -->
    <nav
      class="bg-white border border-t-0 border-b-gray-300 flex justify-around text-[10px] sm:text-[12px] md:text-[14px] py-3 shadow-sm"
    >
      <a
        class="flex flex-col items-center text-[#1BA0E2] font-semibold hover:text-[#1BA0E2] transition duration-200"
        href="#"
      >
        <i class="fas fa-home text-[18px] sm:text-[22px]"></i>
        <span class="mt-1">Mới Cập Nhật</span>
      </a>
      <a
        class="flex flex-col items-center text-gray-600 hover:text-[#1BA0E2] transition duration-200"
        href="#"
      >
        <i class="fas fa-truck text-[18px] sm:text-[22px]"></i>
        <span class="mt-1">Vận Chuyển</span>
      </a>
      <a
        class="flex flex-col items-center text-gray-600 hover:text-[#1BA0E2] transition duration-200"
        href="#"
      >
        <i class="fas fa-tags text-[18px] sm:text-[22px]"></i>
        <span class="mt-1">Ưu Đãi</span>
      </a>
      <a
        class="flex flex-col items-center text-gray-600 hover:text-[#1BA0E2] transition duration-200"
        href="#"
      >
        <i class="fas fa-star text-[18px] sm:text-[22px]"></i>
        <span class="mt-1">Shop Yêu Thích</span>
      </a>
      <a
        class="flex flex-col items-center text-gray-600 hover:text-[#1BA0E2] transition duration-200"
        href="#"
      >
        <i class="fas fa-gift text-[18px] sm:text-[22px]"></i>
        <span class="mt-1">Quà Tặng</span>
      </a>
    </nav>
  </div>
</template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useRuntimeConfig } from "#app";

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const banners = ref([]);
const index = ref(0);
let timer = null;

async function fetchBanners() {
  try {
    const res = await $fetch(`${apiBase}/banners?status=active&type=banner`)
    banners.value = (res.data || []).map(b => b.image_url).filter(Boolean)
    if (index.value >= banners.value.length) index.value = 0
  } catch (e) {
    banners.value = [];
  }
}

function nextSlide() {
  if (!banners.value.length) return;
  index.value = (index.value + 1) % banners.value.length;
}
function prevSlide() {
  if (!banners.value.length) return;
  index.value = (index.value - 1 + banners.value.length) % banners.value.length;
}
function goToSlide(i) {
  index.value = i;
}

onMounted(() => {
  fetchBanners();
  timer = setInterval(nextSlide, 4000);
});

onBeforeUnmount(() => {
  if (timer) clearInterval(timer);
});
</script>
