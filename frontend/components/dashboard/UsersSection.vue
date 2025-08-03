<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <div class="w-6 h-6 bg-purple-500 rounded-lg flex items-center justify-center">
          <span class="text-white text-xs">👥</span>
        </div>
        Người Dùng Đang Hoạt Động
      </h3>
      <button 
        @click="$emit('toggle-view')"
        class="px-3 py-1.5 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-sm font-medium"
      >
        {{ showAll ? 'Thu gọn' : 'Xem tất cả' }}
      </button>
    </div>

    <LoadingSpinner v-if="loading" />
    <ErrorMessage v-else-if="error" :message="error" @retry="$emit('retry')" />
    <div v-else>
      <div class="space-y-3">
        <div 
          v-for="user in users" 
          :key="user.id" 
          class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">
                {{ user.name?.charAt(0)?.toUpperCase() || 'U' }}
              </span>
            </div>
            <div>
              <div class="font-medium text-gray-800">{{ user.name }}</div>
              <div class="text-sm text-gray-500">{{ user.email || 'Chưa có email' }}</div>
            </div>
          </div>
          <div class="text-right">
            <div class="font-bold text-purple-600">
              {{ formatCurrency(user.total_spent) }}
            </div>
            <div class="text-xs">
              <span :class="getRoleClass(user.role)" class="px-2 py-1 rounded-full">
                {{ getRoleLabel(user.role) }}
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
  users: {
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

function getRoleClass(role) {
  const classes = {
    admin: 'bg-purple-100 text-purple-800',
    seller: 'bg-green-100 text-green-800',
    user: 'bg-blue-100 text-blue-800'
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

function getRoleLabel(role) {
  const labels = {
    admin: 'Quản trị viên',
    seller: 'Người bán',
    user: 'Khách hàng'
  }
  return labels[role] || role
}
</script> 