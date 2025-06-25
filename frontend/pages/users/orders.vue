<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="min-h-screen flex flex-col md:flex-row w-full max-w-screen-2xl mx-auto px-4 sm:px-6 py-6">

      <!-- Sidebar -->
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <!-- Main content -->
      <main class="flex-1 px-4 sm:px-8 py-6 w-full overflow-y-auto">
        <!-- Tiêu đề -->
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2 text-center">
          Đơn hàng của tôi
        </h2>
        <p class="text-sm text-gray-500 mb-6 text-center">Quản lý và theo dõi tất cả đơn hàng của bạn</p>

        <!-- Tabs -->
        <div class="mb-6 flex flex-wrap justify-center gap-2">
          <button v-for="tab in tabs" :key="tab.value" @click="selectedTab = tab.value" :class="[
            'px-4 py-2 text-sm rounded-full border flex items-center gap-1',
            selectedTab === tab.value
              ? 'bg-blue-600 text-white border-blue-600'
              : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50'
          ]">
            {{ tab.label }}
            <span v-if="tab.count !== undefined"
              class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-blue-600 bg-blue-100 rounded-full">
              {{ tab.count }}
            </span>
          </button>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex justify-center py-10">
          <div class="animate-spin w-8 h-8 border-t-2 border-blue-500 rounded-full"></div>
        </div>

        <!-- Danh sách đơn hàng -->
        <div v-else>
          <div v-if="filteredOrders.length > 0">
            <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
              <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">STT</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Mã đơn hàng</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Khách hàng</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Số điện thoại</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tổng tiền</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Ngày đặt</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(order, index) in filteredOrders" :key="order.id"
                    class="border-t hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-sm">{{ index + 1 }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-blue-600">
                      #ORD{{ String(order.id).padStart(3, '0') }}
                    </td>
                    <td class="px-4 py-3 text-sm font-medium">{{ order.user.name }}</td>
                    <td class="px-4 py-3 text-sm">{{ order.address.phone }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-green-600">{{ order.final_price }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ order.created_at }}</td>
                    <td class="px-4 py-3 text-sm">
                      <span :class="statusClass(order.status)"
                        class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap">
                        {{ statusText(order.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-3 text-center text-sm relative overflow-visible min-w-[220px]">
                      <div class="flex justify-center gap-2 flex-wrap">
                        <button class="px-3 py-1 text-xs text-blue-600 hover:bg-blue-50 flex items-center gap-1"
                          @click="viewOrder(order.id)">
                          <i class="fas fa-eye"></i> Chi tiết
                        </button>
                        <button class="px-3 py-1 text-xs text-red-500 hover:bg-red-50 flex items-center gap-1"
                          @click="cancelOrder(order.id)">
                          <i class="fas fa-times-circle"></i> Hủy
                        </button>
                        <button class="px-3 py-1 text-xs text-gray-600 hover:bg-gray-50 flex items-center gap-1">
                          <i class="fas fa-print"></i> In hóa đơn
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div v-else class="text-center text-gray-600 mt-10">Không có đơn hàng nào.</div>
        </div>
      </main>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'

const orders = ref([])
const isLoading = ref(true)
const selectedTab = ref('all')
const user = ref(null)

const statusText = (status) => {
  const map = {
    pending: 'Chờ xử lý',
    processing: 'Đang xử lý',
    paid: 'Đã thanh toán',
    shipping: 'Đang vận chuyển',
    completed: 'Đã giao',
    success: 'Hoàn thành',
    canceled: 'Đã huỷ'
  }
  return map[status] || status
}

const statusClass = (status) => {
  return {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-indigo-100 text-indigo-700',
    paid: 'bg-green-100 text-green-700',
    shipping: 'bg-blue-100 text-blue-700',
    completed: 'bg-emerald-100 text-emerald-700',
    success: 'bg-teal-100 text-teal-700',
    canceled: 'bg-red-100 text-red-700'
  }[status] || 'bg-gray-100 text-gray-700'
}

const tabs = ref([
  { label: 'Tất cả', value: 'all', count: 0 },
  { label: 'Chờ xử lý', value: 'pending', count: 0 },
  { label: 'Đang xử lý', value: 'processing', count: 0 },
  { label: 'Đã giao', value: 'completed', count: 0 },
  { label: 'Hoàn thành', value: 'success', count: 0 },
  { label: 'Đã huỷ', value: 'canceled', count: 0 }
])

const fetchOrders = async () => {
  isLoading.value = true

  try {
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Chưa đăng nhập')

    // Lấy thông tin người dùng
    const userRes = await axios.get('http://localhost:8000/api/me', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    user.value = userRes.data

    // Lấy danh sách đơn hàng của người dùng
    const { data } = await axios.get('http://localhost:8000/api/user/orders', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })

    orders.value = data.data
    updateTabCounts()
  } catch (err) {
    console.error('Lỗi khi tải đơn hàng:', err)
  } finally {
    isLoading.value = false
  }
}

const updateTabCounts = () => {
  const counts = {
    all: orders.value.length
  }
  orders.value.forEach(o => {
    counts[o.status] = (counts[o.status] || 0) + 1
  })
  tabs.value = tabs.value.map(tab => ({
    ...tab,
    count: counts[tab.value] || 0
  }))
}

const filteredOrders = computed(() => {
  if (selectedTab.value === 'all') return orders.value
  return orders.value.filter((o) => o.status === selectedTab.value)
})

const viewOrder = (id) => {
  console.log('Xem chi tiết đơn:', id)
}

const cancelOrder = async (id) => {
  try {
    const token = localStorage.getItem('access_token')
    await axios.post(`http://localhost:8000/api/user/orders/${id}/cancel`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })
    fetchOrders()  
  } catch (err) {
    console.error('Lỗi khi huỷ đơn hàng:', err)
  }
}

const reorder = async (id) => {
  try {
    const token = localStorage.getItem('access_token')
    const { data } = await axios.get(`http://localhost:8000/api/user/orders/${id}/reorder`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    console.log('Sản phẩm mua lại:', data.items)
  } catch (err) {
    console.error('Lỗi khi mua lại:', err)
  }
}


onMounted(() => {
  fetchOrders()
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.2s ease;
  transform-origin: top right;
}

.fade-enter-from {
  opacity: 0;
  transform: scale(0.95);
}

.fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
