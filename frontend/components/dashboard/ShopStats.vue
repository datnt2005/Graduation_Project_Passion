<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
        <div class="w-6 h-6 bg-indigo-500 rounded-lg flex items-center justify-center">
          <StatsIcon class="svg-icon text-white" />
        </div>
        Thống Kê Shop
      </h3>
    
    <LoadingSpinner v-if="loading" />
    <ErrorMessage v-else-if="error" :message="error" @retry="$emit('retry')" />
    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="text-center p-4 bg-blue-50 rounded-lg">
        <div class="text-2xl font-bold text-blue-600">{{ stats?.total_orders || 0 }}</div>
        <div class="text-sm text-gray-600">Tổng đơn hàng</div>
      </div>
      <div class="text-center p-4 bg-green-50 rounded-lg">
        <div class="text-2xl font-bold text-green-600">{{ stats?.sold_orders || 0 }}</div>
        <div class="text-sm text-gray-600">Đã bán</div>
      </div>
      <div class="text-center p-4 bg-purple-50 rounded-lg">
        <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats?.total_revenue || 0) }}</div>
        <div class="text-sm text-gray-600">Doanh thu</div>
      </div>
      <div class="text-center p-4 bg-orange-50 rounded-lg">
        <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(stats?.total_cost || 0) }}</div>
        <div class="text-sm text-gray-600">Vốn</div>
      </div>
      <div class="text-center p-4 bg-emerald-50 rounded-lg">
        <div class="text-2xl font-bold text-emerald-600">{{ formatCurrency(stats?.total_profit || 0) }}</div>
        <div class="text-sm text-gray-600">Lợi nhuận</div>
      </div>
      <div class="text-center p-4 bg-red-50 rounded-lg">
        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(stats?.total_loss || 0) }}</div>
        <div class="text-sm text-gray-600">Lỗ</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'

const props = defineProps({
  stats: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  }
})

defineEmits(['retry'])

function formatCurrency(value) {
  if (!value && value !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value)
}

// SVG Icon Component
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