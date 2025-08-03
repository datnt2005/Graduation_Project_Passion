<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
          <div class="w-6 h-6 bg-green-500 rounded-lg flex items-center justify-center">
            <OrdersIcon class="svg-icon text-white" />
          </div>
          Đơn Hàng Gần Đây
        </h3>
      <button 
        @click="$emit('toggle-view')"
        class="px-3 py-1.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm font-medium"
      >
        {{ showAll ? 'Thu gọn' : 'Xem tất cả' }}
      </button>
    </div>

    <LoadingSpinner v-if="loading" />
    <ErrorMessage v-else-if="error" :message="error" @retry="$emit('retry')" />
    <div v-else>
      <div class="space-y-3">
        <div 
          v-for="order in orders" 
          :key="order.id" 
          class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
              <span class="text-white text-sm font-bold">#{{ order.id }}</span>
            </div>
            <div>
              <div class="font-medium text-gray-800">
                {{ order.user?.name || 'N/A' }}
              </div>
              <div class="text-sm text-gray-500">
                {{ order.shipping?.tracking_code || 'Chưa có mã vận đơn' }}
              </div>
            </div>
          </div>
          <div class="text-right">
            <div class="font-bold text-green-600">
              {{ formatCurrency(order.final_price) }}
            </div>
            <div class="text-xs">
              <span :class="getOrderStatusClass(order.status)" class="px-2 py-1 rounded-full">
                {{ getOrderStatusLabel(order.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <Pagination 
        v-if="showAll && meta.last_page > 1"
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        @change="$emit('page-change', $event)"
        class="mt-6"
      />
    </div>
  </div>
</template>

<script setup>
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  orders: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  meta: {
    type: Object,
    default: () => ({})
  },
  showAll: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle-view', 'page-change', 'retry'])

function formatCurrency(value) {
  if (!value && value !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value)
}

function getOrderStatusClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getOrderStatusLabel(status) {
  const labels = {
    pending: 'Chờ xử lý',
    processing: 'Đang xử lý',
    shipped: 'Đang giao',
    delivered: 'Đã giao',
    cancelled: 'Đã hủy'
  }
  return labels[status] || status
}

// SVG Icon Component
const OrdersIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
      <line x1="3" y1="6" x2="21" y2="6"></line>
      <path d="M16 10a4 4 0 0 1-8 0"></path>
    </svg>
  `
}
</script> 