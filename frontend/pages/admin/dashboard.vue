<template>
  <!-- Cảnh báo sản phẩm gần hết hàng -->
  <div v-if="showLowStockAlert && lowStockProducts.length" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded relative">
    <button @click="showLowStockAlert = false" class="absolute top-2 right-2 text-yellow-700 hover:text-yellow-900" aria-label="Đóng thông báo">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
    <div class="font-semibold mb-2">Cảnh báo: Có {{ lowStockProducts.length }} sản phẩm gần hết hàng!</div>
    <ul class="list-disc pl-5">
      <li v-for="item in lowStockProducts" :key="item.id">
        {{ item.product_name }} <span v-if="item.variant_name">({{ item.variant_name }})</span> - Số lượng còn: <b>{{ item.quantity }}</b>
      </li>
    </ul>
  </div>

  <div class="overflow-x-auto">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 min-w-full">
      <!-- Thẻ thống kê động -->
      <div v-if="loadingStats" class="col-span-6 text-center py-8 text-gray-400">Đang tải thống kê...</div>
      <div v-else-if="statsError" class="col-span-6 text-center py-8 text-red-500">{{ statsError }}</div>
      <template v-else>
        <div v-for="stat in dashboardStats.stats" :key="stat.key" class="bg-white p-4 rounded shadow text-center">
          <!-- Nếu là Tổng Người Dùng, dùng thẻ <a> -->
          <a
            v-if="stat.key === 'total_users'"
            href="/admin/users/list-user"
            class="block hover:bg-gray-100 transition"
          >
            <h3 class="text-gray-500 text-sm">{{ stat.label }}</h3>
            <p class="text-xl font-bold">{{ formatNumber(stat.value) }}</p>
          </a>
          <!-- Các mục khác giữ nguyên -->
          <div v-else>
            <h3 class="text-gray-500 text-sm">{{ stat.label }}</h3>
            <p class="text-xl font-bold">{{ formatNumber(stat.value) }}</p>
          </div>
        </div>
      </template>
    </div>
  </div>

  <!-- Bảng Tổng Lỗ riêng -->
  <div class="bg-white p-4 rounded shadow mt-6 w-full max-w-md mx-auto">
    <h3 class="text-lg font-semibold text-red-600 mb-2">Tổng Lỗ</h3>
    <div v-if="dashboardStats.lossStats && dashboardStats.lossStats.length">
      <div v-for="loss in dashboardStats.lossStats" :key="loss.key" class="text-center">
        <span class="text-2xl font-bold text-red-600">{{ formatNumber(loss.value) }}</span>
      </div>
    </div>
    <div v-else class="text-gray-400">Không có dữ liệu lỗ</div>
  </div>

  <!-- Biểu đồ doanh thu + lợi nhuận -->
  <div class="bg-white p-4 sm:p-6 rounded shadow mt-6 w-full overflow-x-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
      <h2 class="text-lg sm:text-xl font-semibold text-gray-700">Biểu đồ Doanh Thu & Lợi Nhuận</h2>
      <div class="mt-2 md:mt-0 flex flex-wrap items-center gap-2">
        <label for="filter" class="text-sm text-gray-600">Lọc theo:</label>
        <select
          id="filter"
          v-model="chartType"
          class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-200"
          :disabled="orderChartMode === 'inventory'"
        >
          <option value="day">Ngày</option>
          <option value="week">Tuần</option>
          <option value="month">Tháng</option>
          <option value="year">Năm</option>
        </select>
        <label for="chartMode" class="text-sm text-gray-600 ml-4">Biểu đồ:</label>
        <select
          id="chartMode"
          v-model="orderChartMode"
          class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-200"
        >
          <option value="revenue">Doanh Thu</option>
          <option value="profit">Lợi Nhuận</option>
          <option value="all">Doanh thu và Lợi nhuận</option>
          <option value="inventory">Tồn Kho</option>
          <option value="orders">Tổng Đơn Hàng</option>
        </select>
        <label for="chartTypeMode" class="text-sm text-gray-600 ml-4">Kiểu biểu đồ:</label>
        <select
          id="chartTypeMode"
          v-model="chartTypeMode"
          class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-200"
        >
          <option value="bar">Cột</option>
          <option value="line">Đường</option>
          <option value="pie">Tròn</option>
        </select>
      </div>
    </div>
    <div class="h-[300px] sm:h-[400px] min-w-[600px]">
      <div v-if="chartLoading" class="text-center text-gray-400 py-10">Đang tải biểu đồ...</div>
      <div v-else-if="chartError" class="text-center text-red-500 py-10">{{ chartError }}</div>
      <component :is="chartComponent" :data="combinedChartData" :options="combinedChartOptions" />
    </div>
  </div>

  <!-- Bảng xếp hạng sản phẩm -->
  <div class="bg-white p-4 sm:p-6 rounded shadow mt-6 w-full overflow-x-auto">
    <!-- Nút chuyển đổi -->
    <div class="flex gap-2 mb-4">
      <button @click="showInventoryList" :class="['px-4 py-2 rounded', !showBestSellers ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700']">Danh sách tồn kho</button>
      <button @click="showBestSellersList" :class="['px-4 py-2 rounded', showBestSellers ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700']">Danh sách sản phẩm bán chạy</button>
    </div>
    <!-- Bộ lọc luôn hiển thị -->
    <div class="flex flex-wrap gap-3 mt-4">
      <input v-model="filters.keyword" type="text" placeholder="Tìm theo tên hoặc mã SP"
        class="border p-2 rounded flex-1 min-w-[150px] sm:min-w-[200px]">
      <select v-model="filters.status"
        class="border p-2 rounded flex-1 min-w-[150px] sm:min-w-[160px]">
        <option value="">Tất cả trạng thái</option>
        <option value="Còn hàng">Còn hàng</option>
        <option value="Gần hết">Gần hết</option>
        <option value="Hết hàng">Hết hàng</option>
      </select>
      <input v-model="filters.maxQuantity" type="number" placeholder="Tồn kho <="
        class="border p-2 rounded flex-1 min-w-[130px]">
      <input v-model="filters.minPrice" type="number" placeholder="Giá từ"
        class="border p-2 rounded flex-1 min-w-[100px]">
      <input v-model="filters.maxPrice" type="number" placeholder="Giá đến"
        class="border p-2 rounded flex-1 min-w-[100px]">
      <button @click="applyFilters"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full sm:w-auto">Lọc</button>
      <button @click="resetFilters"
        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 w-full sm:w-auto">Reset</button>
    </div>
    <!-- Bảng tồn kho hoặc bảng bán chạy -->
    <div v-if="!showBestSellers">
      <!-- Thông báo khi không có dữ liệu -->
      <div v-if="loadingInventory" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
      <div v-else-if="inventoryError" class="text-center text-red-500 py-10">{{ inventoryError }}</div>
      <div v-else-if="!filteredData.length" class="text-center text-gray-400 py-10">Không tìm thấy sản phẩm nào</div>
      <!-- Bảng dữ liệu -->
      <div v-else class="overflow-x-auto mt-4">
        <table class="min-w-[1200px] divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Mã SP</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tên sản phẩm</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Danh mục</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Số lượng tồn</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Biến thể</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Giá nhập</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Giá bán</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
            </tr>
          </thead>
          <tbody v-for="item in filteredData" :key="item.id" class="bg-white">
            <tr>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.variant_sku }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.product_name }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.category_name }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.variant_name }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.cost_price) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.sell_price) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.status }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else>
      <div v-if="loadingBestSellers" class="text-center text-gray-400 py-10">Đang tải dữ liệu sản phẩm bán chạy...</div>
      <div v-else-if="bestSellersError" class="text-center text-red-500 py-10">{{ bestSellersError }}</div>
      <div v-else-if="!bestSellers.length" class="text-center text-gray-400 py-10">Không có sản phẩm nào bán chạy</div>
      <div v-else class="overflow-x-auto mt-4">
        <table class="min-w-[1200px] divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Mã SP</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tên sản phẩm</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Danh mục</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Số lượng tồn</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tổng bán</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Biến thể</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Giá nhập</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Giá bán</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
            </tr>
          </thead>
          <tbody v-for="item in bestSellers" :key="item.id" class="bg-white">
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.variant_sku || '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.product_name || '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.category_name || '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.quantity !== undefined ? formatNumber(item.quantity) : '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-bold">{{ formatNumber(item.total_sold) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.variant_name || '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.cost_price !== undefined ? formatNumber(item.cost_price) : '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.sell_price !== undefined ? formatNumber(item.sell_price) : '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.status || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { Bar, Line, Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, LineElement, PointElement, ArcElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend)

const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl

// Thống kê dashboard
const dashboardStats = ref({ stats: [], lossStats: [] })
const loadingStats = ref(false)
const statsError = ref('')

onMounted(async () => {
  loadingStats.value = true
  try {
    const res = await fetch(`${apiBaseUrl}/dashboard/stats-list`)
    if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`)
    dashboardStats.value = await res.json()
  } catch (e) {
    statsError.value = 'Không thể tải thống kê!'
  } finally {
    loadingStats.value = false
  }
})

// Biểu đồ doanh thu và lợi nhuận
const chartType = ref('month')
const chartLoading = ref(false)
const chartError = ref('')
const chartDataApi = ref({ labels: [], revenue: [], profit: [], orderCount: [] }) // Thêm orderCount

async function fetchChartData(type = 'month') {
  chartLoading.value = true
  chartError.value = ''
  try {
    const url = new URL(`${apiBaseUrl}/dashboard/revenue-profit-chart`)
    url.searchParams.set('type', type)
    const res = await fetch(url)
    if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`)
    chartDataApi.value = await res.json()
  } catch (e) {
    chartError.value = 'Không thể tải dữ liệu biểu đồ!'
  } finally {
    chartLoading.value = false
  }
}

onMounted(() => {
  fetchChartData(chartType.value)
})

watch(chartType, (val) => {
  fetchChartData(val)
})

const chartMode = ref('all')
const chartTypeMode = ref('bar')
const inventoryChartMode = ref('revenue') // 'revenue' | 'profit' | 'all' | 'inventory'
const orderChartMode = ref('revenue') // 'revenue' | 'profit' | 'all' | 'inventory' | 'orders'

const chartComponent = computed(() => {
  if (chartTypeMode.value === 'bar') return Bar
  if (chartTypeMode.value === 'line') return Line
  if (chartTypeMode.value === 'pie') return Pie
  return Bar
})

const combinedChartData = computed(() => {
  const labels = chartDataApi.value.labels || []
  const datasets = []
  if (orderChartMode.value === 'revenue' || orderChartMode.value === 'all') {
    datasets.push({
      label: 'Doanh Thu',
      data: chartDataApi.value.revenue,
      backgroundColor: chartTypeMode.value === 'pie' ? ['#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'] : '#3b82f6',
      borderColor: chartTypeMode.value === 'line' ? '#3b82f6' : undefined,
      borderWidth: chartTypeMode.value === 'line' ? 2 : undefined,
      borderRadius: chartTypeMode.value === 'bar' ? 6 : undefined,
      barThickness: chartTypeMode.value === 'bar' ? 30 : undefined,
      fill: chartTypeMode.value === 'line' ? false : undefined,
    })
  }
  if (orderChartMode.value === 'profit' || orderChartMode.value === 'all') {
    datasets.push({
      label: 'Lợi Nhuận',
      data: chartDataApi.value.profit,
      backgroundColor: chartTypeMode.value === 'pie' ? ['#22c55e', '#4ade80', '#86efac', '#bbf7d0'] : '#22c55e',
      borderColor: chartTypeMode.value === 'line' ? '#22c55e' : undefined,
      borderWidth: chartTypeMode.value === 'line' ? 2 : undefined,
      borderRadius: chartTypeMode.value === 'bar' ? 6 : undefined,
      barThickness: chartTypeMode.value === 'bar' ? 30 : undefined,
      fill: chartTypeMode.value === 'line' ? false : undefined,
    })
  }
  if (orderChartMode.value === 'inventory') {
    const inventoryLabels = inventoryList.value ? inventoryList.value.map(item => item.product_name + (item.variant_name ? ' - ' + item.variant_name : '')) : []
    const inventoryData = inventoryList.value ? inventoryList.value.map(item => item.quantity) : []
    datasets.push({
      label: 'Tồn kho',
      data: inventoryData,
      backgroundColor: '#f59e42',
      borderColor: '#f59e42',
      borderWidth: chartTypeMode.value === 'line' ? 2 : undefined,
      borderRadius: chartTypeMode.value === 'bar' ? 6 : undefined,
      barThickness: chartTypeMode.value === 'bar' ? 30 : undefined,
      fill: chartTypeMode.value === 'line' ? false : undefined,
    })
    return { labels: inventoryLabels, datasets }
  }
  if (orderChartMode.value === 'orders') {
    datasets.push({
      label: 'Tổng Đơn Hàng',
      data: chartDataApi.value.orderCount,
      backgroundColor: '#6366f1',
      borderColor: '#6366f1',
      borderWidth: chartTypeMode.value === 'line' ? 2 : undefined,
      borderRadius: chartTypeMode.value === 'bar' ? 6 : undefined,
      barThickness: chartTypeMode.value === 'bar' ? 30 : undefined,
      fill: chartTypeMode.value === 'line' ? false : undefined,
    })
    return { labels, datasets }
  }
  return { labels, datasets }
})

const combinedChartOptions = computed(() => {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: chartTypeMode.value === 'pie' ? 'right' : 'top',
        labels: { padding: 20 }
      },
      tooltip: {
        callbacks: {
          label: (context) => {
            const value = context.parsed.y || context.parsed;
            if (orderChartMode.value === 'inventory') {
              return `${context.dataset.label}: ${value.toLocaleString('vi-VN')} sản phẩm`;
            }
            if (orderChartMode.value === 'orders') {
              return `${context.dataset.label}: ${value.toLocaleString('vi-VN')} đơn hàng`;
            }
            return `${context.dataset.label}: ${value.toLocaleString('vi-VN')} đ`;
          }
        }
      },
      title: {
        display: true,
        text: orderChartMode.value === 'inventory'
          ? 'Biểu Đồ Tồn Kho'
          : orderChartMode.value === 'orders'
            ? 'Biểu Đồ Tổng Đơn Hàng'
            : chartMode.value === 'revenue' ? 'Biểu Đồ Doanh Thu' :
              chartMode.value === 'profit' ? 'Biểu Đồ Lợi Nhuận' :
              'Biểu đồ Doanh Thu & Lợi Nhuận',
        font: { size: 16 },
        padding: { top: 10, bottom: 10 }
      }
    },
    scales: chartTypeMode.value === 'pie' ? {} : {
      y: {
        beginAtZero: true,
        ticks: {
          callback: (value) => {
            if (orderChartMode.value === 'inventory') {
              return `${value.toLocaleString('vi-VN')} sản phẩm`;
            }
            if (orderChartMode.value === 'orders') {
              return `${value.toLocaleString('vi-VN')} đơn hàng`;
            }
            return `${value.toLocaleString('vi-VN')} đ`;
          }
        },
        title: {
          display: true,
          text: orderChartMode.value === 'inventory'
            ? 'Số lượng'
            : orderChartMode.value === 'orders'
              ? 'Số đơn hàng'
              : 'VND'
        }
      },
      x: {
        title: {
          display: true,
          text: orderChartMode.value === 'inventory' || orderChartMode.value === 'orders'
            ? 'Sản phẩm'
            : chartType.value === 'day' ? 'Ngày' :
              chartType.value === 'week' ? 'Tuần' :
              chartType.value === 'month' ? 'Tháng' :
              'Năm'
        }
      }
    }
  }
})

// Dữ liệu tồn kho
const { data: inventoryList, pending: loadingInventory, error: inventoryError } = await useFetch(`${apiBaseUrl}/inventory/list`, {
  default: () => []
})

const filters = ref({
  keyword: '',
  status: '',
  maxQuantity: '',
  minPrice: '',
  maxPrice: ''
})

const filteredData = ref([])

function doFilter() {
  if (!inventoryList.value || !Array.isArray(inventoryList.value)) {
    filteredData.value = []
    return
  }
  filteredData.value = inventoryList.value.filter(item => {
    const keyword = filters.value.keyword.toLowerCase().trim()
    const matchKeyword = !keyword ||
      (item.product_name?.toLowerCase() || '').includes(keyword) ||
      (item.variant_sku?.toLowerCase() || '').includes(keyword)
    const matchStatus = !filters.value.status ||
      (item.status || '') === filters.value.status
    const maxQuantity = parseFloat(filters.value.maxQuantity)
    const matchQuantity = isNaN(maxQuantity) || item.quantity <= maxQuantity
    const minPrice = parseFloat(filters.value.minPrice)
    const maxPrice = parseFloat(filters.value.maxPrice)
    const sellPrice = parseFloat(item.sell_price)
    const matchMinPrice = isNaN(minPrice) || sellPrice >= minPrice
    const matchMaxPrice = isNaN(maxPrice) || sellPrice <= maxPrice
    return matchKeyword && matchStatus && matchQuantity && matchMinPrice && matchMaxPrice
  })
}

watch(inventoryList, () => {
  filteredData.value = inventoryList.value ? [...inventoryList.value] : []
}, { immediate: true })

function applyFilters() {
  if (filters.value.maxQuantity && parseFloat(filters.value.maxQuantity) < 0) {
    alert('Số lượng tồn kho tối đa không thể âm.')
    filters.value.maxQuantity = ''
  }
  if (filters.value.minPrice && parseFloat(filters.value.minPrice) < 0) {
    alert('Giá tối thiểu không thể âm.')
    filters.value.minPrice = ''
  }
  if (filters.value.maxPrice && parseFloat(filters.value.maxPrice) < 0) {
    alert('Giá tối đa không thể âm.')
    filters.value.maxPrice = ''
  }
  if (filters.value.minPrice && filters.value.maxPrice && parseFloat(filters.value.minPrice) > parseFloat(filters.value.maxPrice)) {
    alert('Giá tối thiểu không thể lớn hơn giá tối đa.')
    filters.value.minPrice = ''
    filters.value.maxPrice = ''
  }
  doFilter()
}

function resetFilters() {
  filters.value = {
    keyword: '',
    status: '',
    maxQuantity: '',
    minPrice: '',
    maxPrice: ''
  }
  filteredData.value = inventoryList.value ? [...inventoryList.value] : []
}

function formatNumber(val) {
  if (typeof val === 'number') return val.toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  if (!isNaN(val) && val !== null && val !== undefined && val !== '') return Number(val).toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  return val || '0'
}

// Sản phẩm gần hết hàng
const lowStockProducts = ref([])
const loadingLowStock = ref(false)
const lowStockError = ref('')
const showLowStockAlert = ref(true)

onMounted(async () => {
  loadingLowStock.value = true
  try {
    const res = await fetch(`${apiBaseUrl}/inventory/low-stock`)
    if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`)
    lowStockProducts.value = await res.json()
  } catch (e) {
    lowStockError.value = 'Không thể tải dữ liệu sản phẩm gần hết hàng!'
  } finally {
    loadingLowStock.value = false
  }
})

watch(lowStockProducts, (val) => {
  if (val && val.length) {
    showLowStockAlert.value = true
    setTimeout(() => {
      showLowStockAlert.value = false
    }, 10000)
  }
})

// Thêm biến trạng thái và dữ liệu cho bảng bán chạy
const showBestSellers = ref(false)
const bestSellers = ref([])
const loadingBestSellers = ref(false)
const bestSellersError = ref('')

async function fetchBestSellers() {
  loadingBestSellers.value = true
  bestSellersError.value = ''
  try {
    const res = await fetch(`${apiBaseUrl}/inventory/best-sellers`)
    if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`)
    bestSellers.value = await res.json()
  } catch (e) {
    bestSellersError.value = 'Không thể tải dữ liệu sản phẩm bán chạy!'
  } finally {
    loadingBestSellers.value = false
  }
}

function showInventoryList() {
  showBestSellers.value = false
}
function showBestSellersList() {
  showBestSellers.value = true
  if (bestSellers.value.length === 0) fetchBestSellers()
}

definePageMeta({
  layout: 'default-admin'
})
</script>
