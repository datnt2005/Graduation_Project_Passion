<template>
  <div class="flex justify-end items-center gap-1 py-4 flex-wrap">
    <!-- Nút Prev -->
    <button
      @click="changePage(currentPage - 1)"
      :disabled="currentPage === 1"
      class="px-3 py-1 border rounded-md text-sm font-medium bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      Trước
    </button>

    <!-- Trang đầu -->
    <button
      v-if="startPage > 1"
      @click="changePage(1)"
      class="px-3 py-1 border rounded-md text-sm bg-white hover:bg-gray-100"
    >
      1
    </button>
    <span v-if="startPage > 2" class="px-2 text-gray-500">...</span>

    <!-- Các trang chính giữa -->
    <button
      v-for="page in visiblePages"
      :key="page"
      @click="changePage(page)"
      :class="[
        'px-3 py-1 border rounded-md text-sm font-medium transition-colors duration-150',
        page === currentPage
          ? 'bg-blue-600 text-white border-blue-600'
          : 'bg-white text-gray-700 hover:bg-gray-100'
      ]"
    >
      {{ page }}
    </button>

    <!-- Trang cuối -->
    <span v-if="endPage < lastPage - 1" class="px-2 text-gray-500">...</span>
    <button
      v-if="endPage < lastPage"
      @click="changePage(lastPage)"
      class="px-3 py-1 border rounded-md text-sm bg-white hover:bg-gray-100"
    >
      {{ lastPage }}
    </button>

    <!-- Nút Next -->
    <button
      @click="changePage(currentPage + 1)"
      :disabled="currentPage === lastPage"
      class="px-3 py-1 border rounded-md text-sm font-medium bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      Sau
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true }
});

const emit = defineEmits(['change']);

const maxButtons = 5;

const startPage = computed(() =>
  Math.max(1, props.currentPage - Math.floor(maxButtons / 2))
);

const endPage = computed(() =>
  Math.min(props.lastPage, startPage.value + maxButtons - 1)
);

const visiblePages = computed(() => {
  const pages = [];
  for (let i = startPage.value; i <= endPage.value; i++) {
    pages.push(i);
  }
  return pages;
});

const changePage = (page) => {
  if (page >= 1 && page <= props.lastPage && page !== props.currentPage) {
    emit('change', page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};
</script>
