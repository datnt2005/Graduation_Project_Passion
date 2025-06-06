<template>
  <div class="flex flex-col gap-4 mb-4 text-xs text-gray-700">
    <div v-for="(items, key) in options" :key="key">
      <span class="font-semibold">{{ key.charAt(0).toUpperCase() + key.slice(1) }}:</span>
      <button v-for="item in items" :key="item" class="border border-gray-300 rounded px-2 py-0.5 text-gray-700 mr-2" :class="{ 'bg-blue-500 text-white border-blue-500': selected[key] === item }" @click="selected[key] = item" type="button">
        {{ item }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps(['options'])
const selected = defineModel('selected')
</script>