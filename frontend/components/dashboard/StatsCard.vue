<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <div class="text-2xl font-bold text-gray-900 mt-1">
          <LoadingSpinner v-if="loading" size="sm" />
          <span v-else>{{ value }}</span>
        </div>
        <p class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
      </div>
      <div class="flex-shrink-0">
        <div 
          :class="[
            'w-12 h-12 rounded-lg flex items-center justify-center',
            getColorClasses(color)
          ]"
        >
          <FontAwesomeIcon 
            :icon="[getIconComponent(icon).icon, getIconComponent(icon).name]" 
            class="text-xl" 
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: 'users',
    validator: (value) => ['users', 'orders', 'revenue', 'sellers', 'chart', 'money', 'shop', 'stats'].includes(value)
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'purple', 'orange', 'red', 'indigo', 'pink', 'yellow'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  }
})

function getColorClasses(color) {
  const colorMap = {
    blue: 'bg-blue-500 text-white',
    green: 'bg-green-500 text-white',
    purple: 'bg-purple-500 text-white',
    orange: 'bg-orange-500 text-white',
    red: 'bg-red-500 text-white',
    indigo: 'bg-indigo-500 text-white',
    pink: 'bg-pink-500 text-white',
    yellow: 'bg-yellow-500 text-white'
  }
  return colorMap[color] || colorMap.blue
}

function getIconComponent(icon) {
  const iconMap = {
    users: { icon: 'fas', name: 'users' },
    orders: { icon: 'fas', name: 'shopping-cart' },
    revenue: { icon: 'fas', name: 'dollar-sign' },
    sellers: { icon: 'fas', name: 'store' },
    chart: { icon: 'fas', name: 'chart-bar' },
    money: { icon: 'fas', name: 'money-bill-wave' },
    shop: { icon: 'fas', name: 'store' },
    stats: { icon: 'fas', name: 'chart-line' }
  }
  return iconMap[icon] || iconMap.users
}


</script> 