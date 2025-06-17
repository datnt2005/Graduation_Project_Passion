<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-4 rounded-md w-[400px]">
        <h2 class="text-lg font-semibold mb-4">Chọn địa chỉ giao hàng</h2>
        <ul class="space-y-2">
          <li
            v-for="addr in addresses"
            :key="addr.id"
            class="p-2 border rounded cursor-pointer hover:bg-gray-100"
            @click="selectAddress(addr.id)"
          >
            <p class="font-bold">{{ addr.name }} - {{ addr.phone }}</p>
            <p class="text-sm text-gray-600">{{ addr.street }} - {{ addr.full }}</p>
          </li>
        </ul>
        <button class="mt-4 text-blue-600 hover:underline" @click="$emit('close')">Đóng</button>
      </div>
    </div>
  </template>
  
  <script setup>
  defineProps(['addresses']);
  const emit = defineEmits(['close', 'update']);
  
  function selectAddress(id) {
    emit('update', id);  // emit ID chứ không phải object
    emit('close');
  }
  </script>