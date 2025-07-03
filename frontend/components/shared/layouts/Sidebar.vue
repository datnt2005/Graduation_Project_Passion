<template>
  <aside class="hidden md:block w-1/6 bg-white p-2 rounded shadow-sm">
    <ul class="space-y-2">
      <NuxtLink v-for="(item, index) in categories" :key="index" :to="`/shop/${item.slug}`">
        <li class="flex items-center gap-4 bg-white p-2 rounded-lg hover:bg-gray-200 transition-all cursor-pointer">
          <img :src="`${mediaBase}${item.image}`" :alt="item.name" class="w-8 h-8 object-contain rounded-full" />
          <span class="text-sm font-medium">{{ item.name }}</span>
        </li>
      </NuxtLink>
    </ul>
    <ul class="mt-4 space-y-2">
      <li>
        <NuxtLink to="/sell-together-passion"
          class="flex items-center gap-3 px-4 py-2 bg-[#E6F4FB] hover:bg-[#D1ECFA] text-[#1BA0E2] font-medium rounded-lg transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#1BA0E2]" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M6.5 14.5l2.8 2.8c.6.6 1.4.9 2.2.9h.1c.9 0 1.8-.4 2.4-1.1l3.4-3.6a1.7 1.7 0 00-2.4-2.4l-.9.9-2.2-2.2a1.7 1.7 0 00-2.4 0 1.7 1.7 0 000 2.4l2.2 2.2-.9.9-1.7-1.7" />
          </svg>
          Bán hàng cùng Passion
        </NuxtLink>

      </li>
      <li>
        <NuxtLink to="/post"
          class="flex items-center gap-3 px-4 py-2 bg-[#E6F4FB] hover:bg-[#D1ECFA] text-[#1BA0E2] font-medium rounded-lg transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#1BA0E2]" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M6.5 14.5l2.8 2.8c.6.6 1.4.9 2.2.9h.1c.9 0 1.8-.4 2.4-1.1l3.4-3.6a1.7 1.7 0 00-2.4-2.4l-.9.9-2.2-2.2a1.7 1.7 0 00-2.4 0 1.7 1.7 0 000 2.4l2.2 2.2-.9.9-1.7-1.7" />
          </svg>
          Tin tức
        </NuxtLink>

      </li>
    </ul>
  </aside>
</template>

<script setup>
import { NuxtLink } from '#components';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useRuntimeConfig } from '#imports';
const router = useRouter();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;
const categories = ref([]);

const fetchCategories = async function () {
  try {
    const response = await fetch(`${apiBase}/categories/parents`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    const data = await response.json();
    categories.value = data.categories;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
}

onMounted(() => {
  fetchCategories();
});
</script>
