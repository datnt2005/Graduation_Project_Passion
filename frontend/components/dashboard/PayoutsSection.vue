<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <div class="w-6 h-6 bg-emerald-500 rounded-lg flex items-center justify-center">
          <span class="text-white text-xs">💸</span>
        </div>
        Đơn Hàng Đã Thanh Toán
      </h3>
      <button 
        @click="$emit('toggle-view')"
        class="px-3 py-1.5 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors text-sm font-medium"
      >
        {{ showAll ? 'Thu gọn' : 'Xem tất cả' }}
      </button>
    </div>

    <LoadingSpinner v-if="loading" />
    <ErrorMessage v-else-if="error" :message="error" @retry="$emit('retry')" />
    <div v-else-if="!payouts.length" class="text-center py-8 text-gray-500">
      Không có đơn hàng đã thanh toán
    </div>
    <div v-else>
      <div class="space-y-3">
        <div 
          v-for="item in payouts" 
          :key="item.id" 
          class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center">
              <span class="text-white text-xs font-bold">💸</span>
            </div>
            <div>
              <div class="font-medium text-gray-800">
                {{ getTrackingCode(item) !== '-' ? getTrackingCode(item) : 'Chưa có mã vận đơn' }}
              </div>
              <div class="text-sm text-gray-500">
                {{ item.seller?.store_name || item.seller?.user?.name || 'N/A' }}
              </div>
            </div>
          </div>
          <div class="text-right">
            <div class="font-bold text-emerald-600">
              {{ formatCurrency(item.amount) }}
            </div>
            <div class="text-xs text-gray-500">
              {{ formatDate(item.transferred_at) }}
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
  payouts: {
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
  },
  orderMap: {
    type: Object,
    default: () => ({})
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

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('vi-VN', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

function getTrackingCode(payout) {
  if (payout.order_id && props.orderMap[payout.order_id]) return props.orderMap[payout.order_id]
  if (payout.tracking_code) return payout.tracking_code
  if (payout.order && payout.order.shipping && payout.order.shipping.tracking_code) return payout.order.shipping.tracking_code
  return '-'
}
</script> 