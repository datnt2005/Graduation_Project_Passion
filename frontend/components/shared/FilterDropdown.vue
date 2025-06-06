<template>
  <div class="relative">
    <!-- Nút mở dropdown -->
    <button
      @click="$emit('toggle-open')"
      class="flex items-center gap-1 px-4 py-1.5 rounded-full border border-gray-300 hover:bg-gray-100 text-sm text-gray-700"
    >
      {{ label }}
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Dropdown nội dung -->
    <div v-if="isOpen" class="absolute left-0 mt-2 w-60 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
      <div class="p-4">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="item in items"
            :key="item"
            @click="$emit('toggle', item)"
            :class="[
              'px-3 py-1 rounded-full border text-sm',
              selected?.includes?.(item)
                ? 'bg-blue-500 text-white border-blue-500'
                : 'border-gray-300 text-gray-700 hover:bg-gray-100'
            ]"
          >
            {{ item }}
          </button>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <button @click="$emit('clear')" class="text-sm text-gray-500 hover:underline">Xóa</button>
          <button @click="$emit('apply')" class="text-sm text-blue-600 font-medium hover:underline">Áp dụng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  items: Array,
  selected: Array,
  isOpen: Boolean
})
defineEmits(['toggle', 'clear', 'apply', 'toggle-open'])
</script>
