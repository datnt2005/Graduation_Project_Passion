<template>
  <div class="mb-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">
          Quản trị hệ thống
        </h1>
        <p class="text-gray-600 mt-1">Quản lý và theo dõi hệ thống</p>
      </div>
      <div class="text-sm text-gray-500">
        {{ currentDate }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const currentDate = computed(() => {
  return new Date().toLocaleDateString('vi-VN', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
})
</script> 