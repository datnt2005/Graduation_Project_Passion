<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Khách hàng của tôi</h1>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả khách hàng</span>
          <span>({{ filteredCustomers.length }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div>
            <select v-model="filterType" @change="fetchCustomers" class="py-1.5 px-3 rounded-md border border-gray-300">
              <option value="all">Tất cả</option>
              <option value="ordered">Đã mua</option>
              <option value="follow_only">Chỉ theo dõi</option>
              <option value="reviewed">Đã đánh giá</option>
              <option value="messaged">Đã nhắn tin</option>
            </select>
          </div>
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Tìm tên, email..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64" />
            <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left">Tên khách</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Email</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Số đơn</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Tổng chi tiêu</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Ngày mua gần nhất</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Loại</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customer in filteredCustomers" :key="customer.id" class="border-b border-gray-300">
            <td class="border border-gray-300 px-3 py-2 text-left">{{ customer.name }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left">{{ customer.email }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left">{{ customer.order_count ?? 'không có' }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              {{ customer.total_spent ? formatCurrency(customer.total_spent) : 'không có' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">{{ customer.last_order_date || 'không có' }}</td>
            <td class="border border-gray-300 px-3 py-2 text-left capitalize">
              {{ customer.type === 'ordered' ? 'Đã mua' :
                 customer.type === 'follow_only' ? 'Theo dõi' :
                 customer.type === 'reviewed' ? 'Đánh giá' :
                 customer.type === 'messaged' ? 'Nhắn tin' : 'không có' }}
            </td>
            <td class="border border-gray-300 px-3 py-2 text-left">
              <button @click="viewCustomer(customer.id)" class="text-blue-600 hover:underline">Xem</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { secureAxios } from '@/utils/secureAxios'

const customers = ref([])
const allCustomers = ref([])
const searchQuery = ref('')
const filterType = ref('all')

const fetchCustomers = async () => {
  try {
    if (filterType.value === 'all') {
      const types = ['ordered', 'follow_only', 'reviewed', 'messaged']
      const all = []
      for (const type of types) {
        const res = await secureAxios(`http://localhost:8000/api/seller/customers?type=${type}`)
        all.push(...res.data)
      }
      customers.value = all
    } else {
      const res = await secureAxios(`http://localhost:8000/api/seller/customers?type=${filterType.value}`)
      customers.value = res.data
    }
  } catch (err) {
    console.error('Lỗi khi load danh sách khách:', err)
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value)
}

const filteredCustomers = computed(() => {
  return customers.value.filter((c) => {
    const search = searchQuery.value.toLowerCase()
    return (
      c.name.toLowerCase().includes(search) ||
      c.email.toLowerCase().includes(search)
    )
  })
})

const viewCustomer = (id) => {
  console.log('Xem chi tiết khách:', id)
}

onMounted(fetchCustomers)

definePageMeta({ layout: 'default-seller' })
</script>

<style scoped>
</style>
