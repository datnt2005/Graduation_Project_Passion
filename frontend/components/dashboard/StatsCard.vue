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
          <component :is="getIconComponent(icon)" class="svg-icon" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

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
    users: UsersIcon,
    orders: OrdersIcon,
    revenue: RevenueIcon,
    sellers: SellersIcon,
    chart: ChartIcon,
    money: MoneyIcon,
    shop: ShopIcon,
    stats: StatsIcon
  }
  return iconMap[icon] || UsersIcon
}

// SVG Icon Components
const UsersIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
      <circle cx="9" cy="7" r="4"></circle>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
    </svg>
  `
}

const OrdersIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
      <line x1="3" y1="6" x2="21" y2="6"></line>
      <path d="M16 10a4 4 0 0 1-8 0"></path>
    </svg>
  `
}

const RevenueIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="12" y1="1" x2="12" y2="23"></line>
      <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
    </svg>
  `
}

const SellersIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
      <polyline points="9,22 9,12 15,12 15,22"></polyline>
    </svg>
  `
}

const ChartIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="18" y1="20" x2="18" y2="10"></line>
      <line x1="12" y1="20" x2="12" y2="4"></line>
      <line x1="6" y1="20" x2="6" y2="14"></line>
    </svg>
  `
}

const MoneyIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="8"></circle>
      <line x1="12" y1="8" x2="12" y2="12"></line>
      <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
  `
}

const ShopIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
      <polyline points="9,22 9,12 15,12 15,22"></polyline>
    </svg>
  `
}

const StatsIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M18 20V10"></path>
      <path d="M12 20V4"></path>
      <path d="M6 20v-6"></path>
    </svg>
  `
}
</script> 