<template>
  <div class="flex items-center justify-center p-4">
    <div class="flex flex-col items-center space-y-2">
      <div 
        :class="[
          'animate-spin rounded-full border-2 border-gray-300 border-t-blue-600',
          sizeClasses[size]
        ]"
      ></div>
      <p v-if="message" class="text-sm text-gray-600">{{ message }}</p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  message: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const sizeClasses = {
  sm: 'w-4 h-4',
  md: 'w-8 h-8',
  lg: 'w-12 h-12'
}
</script> 