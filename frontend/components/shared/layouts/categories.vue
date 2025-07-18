<template>
  <!-- Danh mục -->
  <div class="max-w-7xl mx-auto mt-6 bg-white px-4">
    <div class="border-b border-gray-200 py-3">
      <h2
        class="text-[#1BA0E2] text-base font-semibold uppercase tracking-wide"
      >
        DANH MỤC
      </h2>
    </div>
    <div class="overflow-x-auto scrollbar-hide">
      <div class="min-w-[900px] grid grid-cols-10 gap-x-6 gap-y-4 py-4">
        <div
          v-for="category in categories"
          :key="category.id"
          @click="trackCategoryClick(category.id)"
          class="flex flex-col items-center text-center text-[12px] text-gray-800 cursor-pointer"
        >
          <div class="bg-gray-100 rounded-full p-3 mb-2">
            <img
              :alt="category.name"
              :src="`${mediaBase}${category.image}`"
              class="w-16 h-16 object-contain"
              width="64"
              height="64"
            />
          </div>
          {{ category.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRuntimeConfig } from "#imports";

const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;
const categories = ref([]);

// Lấy danh sách danh mục
const fetchCategories = async () => {
  try {
    const response = await fetch(`${apiBase}/categories/parents`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });
    const data = await response.json();
    categories.value = data.categories;
  } catch (error) {
    console.error("Lỗi khi lấy danh mục:", error);
  }
};

// Tracking click danh mục
const trackCategoryClick = async (categoryId) => {
  try {
    const token = localStorage.getItem("access_token");
    await $fetch(`${apiBase}/search/track-category-click`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: {
        category_id: categoryId,
      },
    });
  } catch (error) {
    console.error("Lỗi khi tracking danh mục:", error);
  }
};

onMounted(() => {
  fetchCategories();
});
</script>
