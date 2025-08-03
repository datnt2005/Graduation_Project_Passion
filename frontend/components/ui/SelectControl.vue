<template>
  <div class="flex flex-col">
    <label v-if="label" class="text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <select
      :value="modelValue"
      @change="$emit('update:model-value', $event.target.value)"
      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
    >
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    default: ''
  }
})

defineEmits(['update:model-value'])
</script> 