
<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="min-h-screen flex flex-col md:flex-row max-w-[1200px] mx-auto p-4 sm:p-6">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />
      
      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
        <div class="min-h-full">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-4 text-center">
            Đơn hàng của tôi
          </h2>

          <!-- Tabs (Dropdown on mobile) -->
          <div class="mb-6">
            <!-- Mobile dropdown -->
            <div class="sm:hidden">
              <select
                v-model="selectedTab"
                class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600"
                aria-label="Chọn trạng thái đơn hàng"
              >
                <option v-for="tab in tabs" :key="tab.value" :value="tab.value">
                  {{ tab.label }}
                </option>
              </select>
            </div>
            <!-- Desktop tabs -->
            <div class="hidden sm:flex border-b border-gray-200">
              <button
                v-for="tab in tabs"
                :key="tab.value"
                @click="selectedTab = tab.value"
                :class="[
                  'py-2 px-4 text-sm font-medium focus:outline-none transition-colors duration-200',
                  selectedTab === tab.value
                    ? 'border-b-2 border-blue-600 text-blue-600'
                    : 'text-gray-600 hover:text-blue-600',
                ]"
                :aria-label="`Xem ${tab.label.toLowerCase()}`"
              >
                {{ tab.label }}
              </button>
            </div>
          </div>

          <!-- Loading state -->
          <div v-if="isLoading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-blue-600"></div>
          </div>

          <!-- Nếu có đơn hàng -->
          <div v-else-if="filteredOrders.length > 0">
            <!-- Table for desktop -->
            <div class="hidden sm:block overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200" role="table">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                      #
                    </th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                      Mã đơn hàng
                    </th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                      Trạng thái
                    </th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                      Tổng tiền
                    </th>
                    <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                      Thao tác
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(order, index) in paginateOrders" :key="order.id">
                    <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-900">
                      {{ index + 1 }}
                    </td>
                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                      #{{ order.code }}
                    </td>
                    <td class="px-4 sm:px-6 py-4">
                      <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="{
                          'bg-green-100 text-green-800': order.status === 'Đã thanh toán',
                          'bg-yellow-100 text-yellow-800': order.status === 'Đang xử lý',
                          'bg-red-100 text-red-800': order.status === 'Chưa thanh toán',
                          'bg-blue-100 text-blue-800': order.status === 'Đang vận chuyển',
                          'bg-gray-100 text-gray-800': order.status === 'Đã giao' || order.status === 'Đã huỷ',
                        }"
                      >
                        {{ order.status }}
                      </span>
                    </td>
                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-700 font-semibold">
                      {{ order.total }}
                    </td>
                    <td class="px-4 sm:px-6 py-4 text-center text-sm">
                      <div class="flex justify-center space-x-2">
                        <NuxtLink
                          :to="`/orders/${order.id}`"
                          class="text-blue-600 hover:text-blue-900 px-2 sm:px-3 py-1 border border-blue-600 rounded-md text-xs transition-colors"
                          aria-label="Xem chi tiết đơn hàng"
                        >
                          Chi tiết
                        </NuxtLink>
                        <button
                          class="text-red-600 hover:text-red-900 px-2 sm:px-3 py-1 border border-red-600 rounded-md text-xs transition-colors"
                          aria-label="Hủy đơn hàng"
                          @click="cancelOrder(order.id)"
                        >
                          Hủy đơn
                        </button>
                        <button
                          class="text-gray-600 hover:text-gray-900 px-2 sm:px-3 py-1 border border-gray-400 rounded-md text-xs transition-colors"
                          aria-label="In hóa đơn"
                        >
                          In hoá đơn
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Card layout for mobile -->
            <div class="block sm:hidden space-y-4">
              <div
                v-for="(order, index) in paginateOrders"
                :key="order.id"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                role="article"
              >
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-900">Đơn hàng #{{ index + 1 }}</span>
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': order.status === 'Đã thanh toán',
                      'bg-yellow-100 text-yellow-800': order.status === 'Đang xử lý',
                      'bg-red-100 text-red-800': order.status === 'Chưa thanh toán',
                      'bg-blue-100 text-blue-800': order.status === 'Đang vận chuyển',
                      'bg-gray-100 text-gray-800': order.status === 'Đã giao' || order.status === 'Đã huỷ',
                    }"
                  >
                    {{ order.status }}
                  </span>
                </div>
                <div class="text-sm text-gray-700 mb-2">Mã đơn: #{{ order.code }}</div>
                <div class="text-sm text-gray-700 font-semibold mb-4">Tổng tiền: {{ order.total }}</div>
                <div class="flex justify-center space-x-2">
                  <NuxtLink
                    :to="`/orders/${order.id}`"
                    class="text-blue-600 hover:text-blue-900 px-2 py-1 border border-blue-600 rounded-md text-xs transition-colors"
                    aria-label="Xem chi tiết đơn hàng"
                  >
                    Chi tiết
                  </NuxtLink>
                  <button
                    class="text-red-600 hover:text-red-900 px-2 py-1 border border-red-600 rounded-md text-xs transition-colors"
                    aria-label="Hủy đơn hàng"
                    @click="cancelOrder(order.id)"
                  >
                    Hủy đơn
                  </button>
                  <button
                    class="text-gray-600 hover:text-gray-900 px-2 py-1 border border-gray-400 rounded-md text-xs transition-colors"
                    aria-label="In hóa đơn"
                  >
                    In hoá đơn
                  </button>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-6 space-x-2">
              <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="px-3 py-1 border rounded disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                aria-label="Trang trước"
              >
                &lt;
              </button>
              <button
                v-for="page in totalPages"
                :key="page"
                @click="currentPage = page"
                :class="[
                  'px-3 py-1 border rounded transition-colors',
                  currentPage === page
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-100',
                ]"
                :aria-label="`Đi tới trang ${page}`"
              >
                {{ page }}
              </button>
              <button
                @click="currentPage++"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 border rounded disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                aria-label="Trang tiếp theo"
              >
                &gt;
              </button>
            </div>
          </div>

          <!-- Không có đơn -->
          <div
            v-else
            class="flex flex-col items-center justify-center bg-white border border-gray-200 p-8 rounded-lg shadow-md mt-6"
          >
            <img
              src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png"
              alt="No orders"
              class="w-24 sm:w-32 h-24 sm:h-32 mb-4"
            />
            <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">
              Chưa có đơn hàng
            </h3>
            <p class="text-gray-500 text-sm text-center">
              Bạn chưa có đơn nào thuộc trạng thái này.
            </p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'

const tabs = [
  { label: 'Tất cả đơn', value: 'all' },
  { label: 'Chờ thanh toán', value: 'Chưa thanh toán' },
  { label: 'Đang xử lý', value: 'Đang xử lý' },
  { label: 'Đang vận chuyển', value: 'Đang vận chuyển' },
  { label: 'Đã giao', value: 'Đã giao' },
  { label: 'Đã thanh toán', value: 'Đã thanh toán' },
  { label: 'Đã huỷ', value: 'Đã huỷ' },
]

const currentPage = ref(1)
const itemsPerPage = ref(2)
const selectedTab = ref('all')
const isLoading = ref(false)

const orders = ref([
  { id: 1, code: 'DH001', status: 'Chưa thanh toán', total: '1.000.000₫' },
  { id: 2, code: 'DH002', status: 'Đang xử lý', total: '550.000₫' },
  { id: 3, code: 'DH003', status: 'Đã thanh toán', total: '1.200.000₫' },
])

const filteredOrders = computed(() => {
  if (selectedTab.value === 'all') return orders.value
  return orders.value.filter((order) => order.status === selectedTab.value)
})

const paginateOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredOrders.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredOrders.value.length / itemsPerPage.value)
})

watch(selectedTab, () => {
  currentPage.value = 1
})

const cancelOrder = (orderId) => {
  // Simulate API call
  console.log(`Canceling order ${orderId}`)
  // Thực tế: Gọi API để hủy đơn hàng
  // orders.value = orders.value.filter(order => order.id !== orderId)
}

// Simulate API fetch
const fetchOrders = async () => {
  isLoading.value = true
  try {
    // Thay bằng API thực tế
    // const response = await fetch('/api/orders')
    // orders.value = await response.json()
    await new Promise((resolve) => setTimeout(resolve, 1000)) // Simulate delay
  } catch (error) {
    console.error('Error fetching orders:', error)
  } finally {
    isLoading.value = false
  }
}

// Fetch orders on mount
onMounted(() => {
  fetchOrders()
})
</script>

<style scoped>
/* Custom responsive styles */
@media (max-width: 640px) {
  table {
    font-size: 0.875rem;
  }
  button, a {
    font-size: 0.75rem;
    padding: 0.5rem 1rem;
  }
}
</style>
```