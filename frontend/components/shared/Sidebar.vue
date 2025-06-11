<template>
  <aside class="hidden md:block w-1/6 bg-white p-2 rounded shadow-sm">
    <ul class="space-y-2">
      <li
        v-for="(item, index) in categories"
        :key="index"
        class="flex items-center gap-4 bg-white p-2 rounded-lg hover:bg-gray-200 transition-all cursor-pointer"
      >
        <img
          :src="`${mediaBase}${item.image}`"
          :alt="item.name"
          class="w-8 h-8 object-contain rounded-full"
        />
        <span class="text-sm font-medium">{{ item.name }}</span>
      </li>
    </ul>
    <ul class="mt-4 space-y-2">
      <li>
        <NuxtLink
         to="/sell-together-passion"
          class="flex items-center gap-3 px-4 py-2 bg-pink-50 hover:bg-pink-100 text-pink-600 font-medium rounded-lg transition-all"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-pink-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 10h4l3 10 4-18 3 8h4"
            />
          </svg>
          Bán hàng cùng Passion
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

const fetchCategories = async function() {
  try{
    const response = await fetch(`${apiBase}/categories`, {
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
