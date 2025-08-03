<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
        <div class="w-6 h-6 bg-orange-500 rounded-lg flex items-center justify-center">
          <ShopIcon class="svg-icon text-white" />
        </div>
        Quản Lý Shop
      </h3>
    
    <div class="space-y-4 mb-6">
      <SelectControl
        :model-value="selectedId"
        :options="shopOptions"
        label="Chọn shop"
        @update:model-value="$emit('seller-change', $event)"
      />
      <div class="relative">
        <input
          :value="searchQuery"
          type="text"
          placeholder="Tìm kiếm shop..."
          class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
          @input="$emit('search', $event.target.value)"
        />
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
          <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
      <SelectControl
        :model-value="sortBy"
        :options="sortOptions"
        label="Sắp xếp theo"
        @update:model-value="$emit('sort', $event)"
      />
    </div>

    <LoadingSpinner v-if="loading" />
    <ErrorMessage v-else-if="error" :message="error" @retry="$emit('retry')" />
    <div v-else>
      <div class="space-y-3">
        <div
          v-for="seller in sellers"
          :key="seller.id"
          :class="[
            'p-4 rounded-lg border transition-all duration-200 cursor-pointer',
            selectedId === seller.id
              ? 'border-orange-500 bg-orange-50 shadow-md'
              : 'border-gray-200 hover:border-orange-300 hover:shadow-sm'
          ]"
          @click="$emit('select-seller', seller.id)"
        >
          <div class="flex items-center justify-between">
            <div>
              <div class="font-semibold text-gray-800">{{ seller.store_name }}</div>
              <div class="text-sm text-gray-500">{{ seller.user?.email }}</div>
            </div>
            <div class="text-right">
              <div class="font-bold text-orange-600">{{ seller.total_orders }} đơn</div>
              <div class="text-sm text-green-600">{{ formatCurrency(seller.total_revenue) }}</div>
            </div>
          </div>
        </div>
      </div>

      <Pagination 
        v-if="totalPages > 1"
        :current-page="currentPage"
        :last-page="totalPages"
        @change="$emit('page-change', $event)"
        class="mt-6"
      />
    </div>
  </div>
</template>

<script setup>
import SelectControl from '@/components/ui/SelectControl.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  sellers: {
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
  selectedId: {
    type: [String, Number],
    default: ''
  },
  searchQuery: {
    type: String,
    default: ''
  },
  sortBy: {
    type: String,
    default: ''
  },
  currentPage: {
    type: Number,
    default: 1
  },
  totalPages: {
    type: Number,
    default: 1
  },
  shopOptions: {
    type: Array,
    default: () => []
  },
  sortOptions: {
    type: Array,
    default: () => []
  }
})

defineEmits(['select-seller', 'search', 'sort', 'page-change', 'seller-change', 'retry'])

function formatCurrency(value) {
  if (!value && value !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value)
}

// SVG Icon Component
const ShopIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
      <polyline points="9,22 9,12 15,12 15,22"></polyline>
    </svg>
  `
}
</script> 