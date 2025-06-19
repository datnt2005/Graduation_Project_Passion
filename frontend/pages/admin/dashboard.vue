<template>
  <!-- Template của bạn giữ nguyên, không thay đổi -->
  <div class="overflow-x-auto">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 min-w-full">
      <!-- Thẻ thống kê động -->
      <div v-if="loadingStats" class="col-span-6 text-center py-8 text-gray-400">Đang tải thống kê...</div>
      <div v-else-if="statsError" class="col-span-6 text-center py-8 text-red-500">{{ statsError }}</div>
      <template v-else>
        <div v-for="stat in dashboardStats.stats" :key="stat.key" class="bg-white p-4 rounded shadow text-center">
          <h3 class="text-gray-500 text-sm">{{ stat.label }}</h3>
          <p class="text-xl font-bold">{{ formatNumber(stat.value) }}</p>
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

  <!-- Biểu đồ doanh thu + lời/lỗ -->
  <div class="bg-white p-4 sm:p-6 rounded shadow mt-6 w-full overflow-x-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
      <h2 class="text-lg sm:text-xl font-semibold text-gray-700">Biểu đồ doanh thu & lời/lỗ</h2>
      <div class="mt-2 md:mt-0 flex flex-wrap items-center gap-2">
        <label for="filter" class="text-sm text-gray-600">Lọc theo:</label>
        <select
          id="filter"
          v-model="chartType"
          class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-200"
        >
          <option value="day">Ngày</option>
          <option value="week">Tuần</option>
          <option value="month">Tháng</option>
          <option value="year">Năm</option>
        </select>
        <label for="chartMode" class="text-sm text-gray-600 ml-4">Biểu đồ:</label>
        <select
          id="chartMode"
          v-model="chartMode"
          class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-200"
        >
          <option value="revenue">Doanh thu</option>
          <option value="profit">Lời/Lỗ</option>
          <option value="all">Cả hai</option>
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
      <div v-if="chartLoading || profitChartLoading" class="text-center text-gray-400 py-10">Đang tải biểu đồ...</div>
      <div v-else-if="chartError || profitChartError" class="text-center text-red-500 py-10">{{ chartError || profitChartError }}</div>
      <component :is="chartComponent" :data="combinedChartData" :options="combinedChartOptions" />
    </div>
  </div>

  <!-- Bảng xếp hạng sản phẩm -->
  <div class="bg-white p-4 sm:p-6 rounded shadow mt-6 w-full overflow-x-auto">
    <h2 class="text-lg sm:text-xl font-semibold text-gray-700">Danh sách tồn kho</h2>

    <!-- Bộ lọc -->
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
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { Bar, Line, Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, LineElement, PointElement, ArcElement, CategoryScale, LinearScale } from 'chart.js'


ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend)

const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl

// --- GỘP LOGIC useDashboardStats.js ---
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

// Biểu đồ doanh thu động
const chartType = ref('month')
const chartLoading = ref(false)
const chartError = ref('')
const chartDataApi = ref({ labels: [], revenue: [], profit: [] })

async function fetchRevenueChart(type = 'month') {
  chartLoading.value = true
  chartError.value = ''
  try {
    const url = new URL(`${apiBaseUrl}/dashboard/revenue-chart`)
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
  fetchRevenueChart(chartType.value)
})

watch(chartType, (val) => {
  fetchRevenueChart(val)
})
// --- END GỘP LOGIC useDashboardStats.js ---

// Biểu đồ lợi nhuận động
const { data: profitChartApi, pending: profitChartLoading, error: profitChartError } = await useFetch(`${apiBaseUrl}/dashboard/revenue-profit-chart`, {
  query: { type: chartType },
  default: () => ({ labels: ['Đang tải'], profit: [0], loss: [0] })
})

watch(chartType, async (val) => {
  const { data } = await useFetch(`${apiBaseUrl}/dashboard/revenue-profit-chart`, {
    query: { type: val }
  })
  profitChartApi.value = data.value || { labels: ['Lỗi'], profit: [0], loss: [0] }
})

const profitChartData = computed(() => ({
  labels: profitChartApi.value.labels,
  datasets: [
    {
      label: 'Lời',
      data: profitChartApi.value.profit,
      backgroundColor: '#22c55e',
      borderRadius: 6,
      barThickness: 30,
    },
    {
      label: 'Lỗ',
      data: profitChartApi.value.loss,
      backgroundColor: '#ef4444',
      borderRadius: 6,
      barThickness: 30,
    }
  ]
}))

const profitChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' },
    tooltip: {
      callbacks: {
        label: (context) => `${context.dataset.label}: ${context.parsed.y} triệu`
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value) => `${value} triệu`
      }
    }
  }
}

// Dữ liệu tồn kho (giữ nguyên logic cũ)
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

// Thay đổi: filteredData chỉ cập nhật khi ấn nút Lọc
const filteredData = ref([])

function doFilter() {
  if (!inventoryList.value || !Array.isArray(inventoryList.value)) {
    filteredData.value = []
    return
  }
  filteredData.value = inventoryList.value.filter(item => {
    // Lọc theo từ khóa
    const keyword = filters.value.keyword.toLowerCase().trim()
    const matchKeyword = !keyword ||
      (item.product_name?.toLowerCase() || '').includes(keyword) ||
      (item.variant_sku?.toLowerCase() || '').includes(keyword)

    // Lọc theo trạng thái
    const matchStatus = !filters.value.status ||
      (item.status || '') === filters.value.status

    // Lọc theo số lượng tồn kho tối đa
    const maxQuantity = parseFloat(filters.value.maxQuantity)
    const matchQuantity = isNaN(maxQuantity) || item.quantity <= maxQuantity

    // Lọc theo giá bán
    const minPrice = parseFloat(filters.value.minPrice)
    const maxPrice = parseFloat(filters.value.maxPrice)
    const sellPrice = parseFloat(item.sell_price)
    const matchMinPrice = isNaN(minPrice) || sellPrice >= minPrice
    const matchMaxPrice = isNaN(maxPrice) || sellPrice <= maxPrice

    return matchKeyword && matchStatus && matchQuantity && matchMinPrice && matchMaxPrice
  })
}

// Khi dữ liệu inventoryList thay đổi, filteredData sẽ tự động hiển thị toàn bộ danh sách
watch(inventoryList, () => {
  filteredData.value = inventoryList.value ? [...inventoryList.value] : []
}, { immediate: true })

function applyFilters() {
  // Validation cho các trường số
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

// Định dạng số tiền kiểu vi-VN, không có phần thập phân
function formatNumber(val) {
  if (typeof val === 'number') return val.toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  if (!isNaN(val) && val !== null && val !== undefined && val !== '') return Number(val).toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  return val || '0'
}

const chartMode = ref('all')
const chartTypeMode = ref('bar')
const chartComponent = computed(() => {
  if (chartTypeMode.value === 'bar') return Bar
  if (chartTypeMode.value === 'line') return Line
  if (chartTypeMode.value === 'pie') return Pie
  return Bar
})

const combinedChartData = computed(() => {
  const labels = chartDataApi.value.labels
  const datasets = []
  if (chartMode.value === 'revenue' || chartMode.value === 'all') {
    datasets.push({
      label: 'Doanh thu',
      data: chartDataApi.value.revenue,
      backgroundColor: '#3b82f6',
      borderRadius: 6,
      barThickness: 30,
    })
  }
  if (chartMode.value === 'profit' || chartMode.value === 'all') {
    datasets.push({
      label: 'Lời',
      data: profitChartApi.value.profit,
      backgroundColor: '#22c55e',
      borderRadius: 6,
      barThickness: 30,
    })
    datasets.push({
      label: 'Lỗ',
      data: profitChartApi.value.loss,
      backgroundColor: '#ef4444',
      borderRadius: 6,
      barThickness: 30,
    })
  }
  return { labels, datasets }
})

const combinedChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' },
    tooltip: {
      callbacks: {
        label: (context) => `${context.dataset.label}: ${context.parsed.y} triệu`
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value) => `${value} triệu`
      }
    }
  }
}

definePageMeta({
  layout: 'default-admin'
})
</script>