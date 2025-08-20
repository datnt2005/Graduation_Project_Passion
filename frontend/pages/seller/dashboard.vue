<template>
  <!-- Cảnh báo sản phẩm gần hết hàng và hết hàng -->
  <div v-if="lowStockProducts.length || outOfStockProducts.length" class="mb-4 relative">
    <button
      v-if="showLowStockAlert"
      @click="showLowStockAlert = false"
      class="absolute top-3 right-4 z-10 bg-yellow-200 hover:bg-yellow-300 text-yellow-900 px-3 py-1 rounded-full text-sm font-semibold shadow transition"
    >
      Ẩn tất cả
    </button>
    <button
      v-else
      @click="showLowStockAlert = true"
      class="absolute top-3 right-4 z-10 bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold shadow transition"
    >
      Hiện lại cảnh báo
    </button>
    <transition name="fade">
      <div
        v-if="showLowStockAlert"
        class="bg-gradient-to-r from-yellow-100 via-yellow-50 to-white border-l-8 border-yellow-400 text-yellow-900 p-5 rounded-xl shadow flex items-start gap-3"
      >
        <div class="text-3xl mt-1">⚠️</div>
        <div class="flex-1">
          <div v-if="outOfStockProducts.length" class="mb-2">
            <span class="font-bold text-red-700">❗ Cảnh báo:</span>
            <span class="font-semibold">Có {{ outOfStockProducts.length }} sản phẩm đã <span class="text-red-700">hết hàng</span>!</span>
            <ul class="list-disc pl-6 mt-1">
              <li v-for="item in outOfStockProducts" :key="item.id">
                <span class="font-medium">{{ item.product_name }}</span>
                <span class="text-red-700 font-bold"> - Hết hàng</span>
              </li>
            </ul>
          </div>
          <div v-if="lowStockProducts.length">
            <span class="font-bold">Cảnh báo:</span>
            <span class="font-semibold">Có {{ lowStockProducts.length }} sản phẩm gần hết hàng!</span>
            <ul class="list-disc pl-6 mt-1">
              <li v-for="item in lowStockProducts" :key="item.id">
                <span class="font-medium">{{ item.product_name }}</span>
                <span> - Số lượng còn: <b>{{ item.quantity }}</b></span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </transition>
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
      <component v-else :is="chartComponent" :data="combinedChartData" :options="combinedChartOptions" />
    </div>
      </div>

  <div class="bg-white p-4 sm:p-6 rounded shadow mt-4">
    <div class="flex flex-wrap items-center gap-3">
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">So sánh tháng:</label>
        <select v-model="monthCompareA" class="border border-gray-300 rounded px-3 py-1 text-sm">
          <option v-for="lbl in monthLabels" :key="'A-'+lbl" :value="lbl">{{ lbl }}</option>
        </select>
        <span class="text-gray-400">vs</span>
        <select v-model="monthCompareB" class="border border-gray-300 rounded px-3 py-1 text-sm">
          <option v-for="lbl in monthLabels" :key="'B-'+lbl" :value="lbl">{{ lbl }}</option>
        </select>
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
      <div class="bg-gray-50 rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Doanh thu</div>
        <div class="text-lg font-semibold text-gray-900">{{ formatNumber(compareRevenueA) }} đ</div>
        <div class="text-xs text-gray-500">{{ monthCompareA }}</div>
      </div>
      <div class="bg-gray-50 rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Doanh thu</div>
        <div class="text-lg font-semibold text-gray-900">{{ formatNumber(compareRevenueB) }} đ</div>
        <div class="text-xs text-gray-500">{{ monthCompareB }}</div>
      </div>
      <div class="bg-gray-50 rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Chênh lệch</div>
        <div :class="['text-lg font-semibold', revenueDiff >= 0 ? 'text-green-600' : 'text-red-600']">{{ formatNumber(revenueDiff) }} đ</div>
        <div class="text-xs text-gray-500">Tăng/Giảm</div>
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
      <div class="bg-gray-50 rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Lợi nhuận</div>
        <div class="text-lg font-semibold text-gray-900">{{ formatNumber(compareProfitA) }} đ</div>
        <div class="text-xs text-gray-500">{{ monthCompareA }}</div>
      </div>
      <div class="bg-gray-50 rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Lợi nhuận</div>
        <div class="text-lg font-semibold text-gray-900">{{ formatNumber(compareProfitB) }} đ</div>
        <div class="text-xs text-gray-500">{{ monthCompareB }}</div>
      </div>
      <div class="bg-gray-50 rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Chênh lệch</div>
        <div :class="['text-lg font-semibold', profitDiff >= 0 ? 'text-green-600' : 'text-red-600']">{{ formatNumber(profitDiff) }} đ</div>
        <div class="text-xs text-gray-500">Tăng/Giảm</div>
      </div>
    </div>
    <div class="mt-4">
      <div class="h-48">
        <Bar :data="comparisonChartData" :options="comparisonChartOptions" />
      </div>
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
      <div class="ml-auto flex items-center gap-2">
        <label class="text-sm text-gray-600 whitespace-nowrap">Hiển thị:</label>
        <select v-model.number="inventoryPageSize" class="border border-gray-300 rounded px-2 py-1 text-sm">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
          <option :value="0">Tất cả</option>
        </select>
      </div>
    </div>
    <!-- Bảng tồn kho hoặc bảng bán chạy -->
    <div v-if="!showBestSellers">
      <!-- Thông báo khi không có dữ liệu -->
      <div v-if="loadingInventory" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
      <div v-else-if="inventoryError" class="text-center text-red-500 py-10">{{ inventoryError }}</div>
      <div v-else-if="!filteredData.length" class="text-center text-gray-400 py-10">Không tìm thấy sản phẩm nào</div>
      <!-- Bảng dữ liệu -->
      <div v-else class="overflow-x-auto mt-4">
        <table class="w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tên sản phẩm</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Danh mục</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Số lượng tồn</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Giá nhập TB</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Giá bán TB</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
            </tr>
          </thead>
          <tbody v-for="item in paginatedInventoryData" :key="item.id" class="bg-white">
            <tr>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                <NuxtLink :to="`/seller/products/edit-product/${item.id}`" class="text-blue-600 hover:text-blue-800 hover:underline">
                  {{ item.product_name }}
                </NuxtLink>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.category_name }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.cost_price) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.sell_price) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.status }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="inventoryTotalPages > 1" class="flex justify-center mt-4">
          <button @click="inventoryPage--" :disabled="inventoryPage === 1" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&lt;</button>
          <button v-for="page in inventoryTotalPages" :key="page" @click="inventoryPage = page" :class="['px-3 py-1 mx-1 rounded border', inventoryPage === page ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{ page }}</button>
          <button @click="inventoryPage++" :disabled="inventoryPage === inventoryTotalPages" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&gt;</button>
        </div>
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
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tên sản phẩm</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Danh mục</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Số lượng tồn</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tổng bán</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Giá nhập TB</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Giá bán TB</th>
              <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
            </tr>
          </thead>
          <tbody v-for="item in paginatedBestSellers" :key="item.id" class="bg-white">
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                <NuxtLink :to="`/seller/products/edit-product/${item.id}`" class="text-blue-600 hover:text-blue-800 hover:underline">
                  {{ item.product_name }}
                </NuxtLink>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.category_name }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.quantity) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-bold">{{ formatNumber(item.total_sold) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.cost_price) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.sell_price) }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.status }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="bestSellersTotalPages > 1" class="flex justify-center mt-4">
          <button @click="bestSellersPage--" :disabled="bestSellersPage === 1" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&lt;</button>
          <button v-for="page in bestSellersTotalPages" :key="page" @click="bestSellersPage = page" :class="['px-3 py-1 mx-1 rounded border', bestSellersPage === page ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300']">{{ page }}</button>
          <button @click="bestSellersPage++" :disabled="bestSellersPage === bestSellersTotalPages" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&gt;</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  CategoryScale,
  LinearScale,
  PointElement
} from 'chart.js'
import { Line } from 'vue-chartjs'
import { ref, computed, watch, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { Bar, Pie } from 'vue-chartjs'
import { secureFetch } from '@/utils/secureFetch'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  CategoryScale,
  LinearScale,
  PointElement
)

const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl

const selectedFilter = ref('month')

const allData = {
  day: [100, 200, 150, 300, 250, 400, 350],
  week: [700, 800, 750, 900],
  month: [5000, 6000, 5500, 7000],
  year: [48000, 52000, 50000]
}

const labels = computed(() => {
  switch (selectedFilter.value) {
    case 'day': return ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']
    case 'week': return ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4']
    case 'month': return ['Th1', 'Th2', 'Th3', 'Th4']
    case 'year': return ['2022', '2023', '2024']
    default: return []
  }
})

const chartData = computed(() => ({
  labels: labels.value,
  datasets: [
    {
      label: 'Doanh thu',
      data: allData[selectedFilter.value],
      borderColor: '#3B82F6', // Blue-500
      backgroundColor: 'rgba(59, 130, 246, 0.2)',
      tension: 0.4, // Smooth line
      pointRadius: 4,
      pointBackgroundColor: '#3B82F6'
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top', labels: { font: { size: 12 } } },
    title: { display: true, text: 'Biểu đồ doanh thu', font: { size: 16 } },
    tooltip: { backgroundColor: '#1F2937', titleFont: { size: 14 }, bodyFont: { size: 12 } }
  },
  scales: {
    y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
    x: { grid: { display: false } }
  }
}

const topProducts = [
  { name: 'Sữa chua Pao', orders: 120 },
  { name: 'Bò một nắng', orders: 80 },
  { name: 'Mít sấy', orders: 60 }
]

const inventoryList = ref([])

// Cuối cùng, đổi layout về default-seller

definePageMeta({
  layout: 'default-seller'
})

const loadingStats = ref(false)
const statsError = ref('')
const dashboardStats = ref({})

const filteredData = ref([])

const loadingInventory = ref(false)
const inventoryError = ref('')

const showBestSellers = ref(false)

const chartType = ref('month')
const chartTypeMode = ref('bar')
const orderChartMode = ref('revenue')
const chartComponent = computed(() => {
  if (chartTypeMode.value === 'bar') return Bar
  if (chartTypeMode.value === 'line') return Line
  if (chartTypeMode.value === 'pie') return Pie
  return Bar
})
const showLowStockAlert = ref(true)

const lowStockProducts = computed(() => {
  return inventoryList.value.filter(item => item.quantity > 0 && item.quantity <= 5)
})
const outOfStockProducts = computed(() => {
  return inventoryList.value.filter(item => item.quantity === 0)
})

const inventoryPage = ref(1)
const inventoryPageSize = ref(10)
const inventoryTotalPages = computed(() => {
  if (!inventoryPageSize.value || inventoryPageSize.value <= 0) return 1
  return Math.ceil(filteredData.value.length / inventoryPageSize.value)
})
const paginatedInventoryData = computed(() => {
  if (!inventoryPageSize.value || inventoryPageSize.value <= 0) {
    return filteredData.value
  }
  const start = (inventoryPage.value - 1) * inventoryPageSize.value
  return filteredData.value.slice(start, start + inventoryPageSize.value)
})

onMounted(async () => {
  console.log('Dashboard mounted')
  loadingStats.value = true
  try {
    const res = await secureFetch(`${apiBaseUrl}/dashboard/seller-stats-list`, {}, ['seller'])
    console.log('Stats response:', res)
    if (!res.success) throw new Error(res.message || 'Không lấy được dữ liệu thống kê')
    dashboardStats.value = res.data
  } catch (e) {
    console.error('Stats error:', e)
    statsError.value = e.message || 'Không thể tải thống kê!'
  } finally {
    loadingStats.value = false
  }
  await fetchInventory();
  applyFilters()
  await fetchChartData(chartType.value);
})

const chartLoading = ref(false)
const chartError = ref('')
const chartDataApi = ref({})
const monthDataApi = ref({ labels: [], revenue: [], profit: [] })
// giữ chart theo ngày mặc định, bỏ lọc range ngày
const monthCompareA = ref('')
const monthCompareB = ref('')

async function fetchChartData(type = 'month') {
  chartLoading.value = true
  chartError.value = ''

  try {
    const url = new URL(`${apiBaseUrl}/dashboard/seller-revenue-profit-chart`)
    url.searchParams.set('type', type)

    const data = await secureFetch(url.toString(), {}, ['seller'])
    console.log('Chart response:', data)

    if (!data.success) throw new Error(data.message || 'Không lấy được dữ liệu biểu đồ')
    chartDataApi.value = data.data
    // Nếu là tháng, lưu để so sánh; nếu không, vẫn cố lấy 12 tháng để so sánh mặc định
    if (type === 'month') {
      monthDataApi.value = data.data
    } else {
      try {
        const monthly = await secureFetch(`${apiBaseUrl}/dashboard/seller-revenue-profit-chart?type=month`, {}, ['seller'])
        if (monthly.success) monthDataApi.value = monthly.data
      } catch {}
    }

  } catch (e) {
    console.error('Chart error:', e)
    chartError.value = e.message || 'Không thể tải dữ liệu biểu đồ!'
  } finally {
    chartLoading.value = false
  }
}

async function fetchInventory() {
  loadingInventory.value = true;
  inventoryError.value = '';
  try {
    const data = await secureFetch(`${apiBaseUrl}/inventory/seller-list`, {}, ['seller']);
    console.log('Inventory response:', data)
    if (!data.success) throw new Error(data.message || 'Không lấy được dữ liệu tồn kho');
    inventoryList.value = data.data;
    filteredData.value = [...data.data];
  } catch (e) {
    console.error('Inventory error:', e)
    inventoryError.value = e.message || 'Không thể tải dữ liệu tồn kho!';
    inventoryList.value = [];
    filteredData.value = [];
  } finally {
    loadingInventory.value = false;
  }
}

const filters = ref({
  keyword: '',
  status: '',
  maxQuantity: '',
  minPrice: '',
  maxPrice: ''
})

function applyFilters() {
  const keyword = (filters.value.keyword || '').toString().toLowerCase().trim()
  const status = filters.value.status
  const maxQty = filters.value.maxQuantity !== '' && filters.value.maxQuantity !== null ? Number(filters.value.maxQuantity) : null
  const minPrice = filters.value.minPrice !== '' && filters.value.minPrice !== null ? Number(filters.value.minPrice) : null
  const maxPrice = filters.value.maxPrice !== '' && filters.value.maxPrice !== null ? Number(filters.value.maxPrice) : null

  const source = inventoryList.value ? [...inventoryList.value] : []
  const result = source.filter(item => {
    const name = (item.product_name || '').toString().toLowerCase()
    const code = (item.product_code || '').toString().toLowerCase()
    const category = (item.category_name || '').toString().toLowerCase()
    const qty = Number(item.quantity || 0)
    const sell = Number(item.sell_price || 0)

    if (keyword && !(name.includes(keyword) || code.includes(keyword) || category.includes(keyword))) {
      return false
    }
    if (status) {
      if (status === 'Còn hàng' && !(qty > 5)) return false
      if (status === 'Gần hết' && !(qty > 0 && qty <= 5)) return false
      if (status === 'Hết hàng' && !(qty === 0)) return false
    }
    if (maxQty !== null && !Number.isNaN(maxQty) && qty > maxQty) return false
    if (minPrice !== null && !Number.isNaN(minPrice) && sell < minPrice) return false
    if (maxPrice !== null && !Number.isNaN(maxPrice) && sell > maxPrice) return false

    return true
  })

  filteredData.value = result
  inventoryPage.value = 1
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
    // Dùng dữ liệu đã lọc để chart khớp với bảng
    const source = Array.isArray(filteredData.value) && filteredData.value.length
      ? filteredData.value
      : (inventoryList.value || [])
    const inventoryLabels = source.map(item => item.product_name)
    const inventoryData = source.map(item => Number(item.quantity || 0))
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

const monthLabels = computed(() => monthDataApi.value.labels || [])

// bỏ visibleChartData theo range ngày, dùng combinedChartData mặc định

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
            : chartType.value === 'day' ? 'Biểu Đồ Doanh Thu' :
              chartType.value === 'week' ? 'Biểu Đồ Doanh Thu' :
              chartType.value === 'month' ? 'Biểu Đồ Doanh Thu' :
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

const bestSellers = ref([])
const loadingBestSellers = ref(false)
const bestSellersError = ref('')
const bestSellersPage = ref(1)
const bestSellersPageSize = ref(10)
const bestSellersTotalPages = computed(() => {
  return Math.ceil(bestSellers.value.length / bestSellersPageSize.value)
})
const paginatedBestSellers = computed(() => {
  const start = (bestSellersPage.value - 1) * bestSellersPageSize.value
  return bestSellers.value.slice(start, start + bestSellersPageSize.value)
})

async function fetchBestSellers() {
  loadingBestSellers.value = true
  bestSellersError.value = ''
  try {
    const data = await secureFetch(`${apiBaseUrl}/inventory/seller-best-sellers`, {}, ['seller'])
    console.log('Best sellers response:', data)
    if (!data.success) throw new Error(data.message || 'Không lấy được dữ liệu sản phẩm bán chạy')
    bestSellers.value = data.data
  } catch (e) {
    console.error('Best sellers error:', e)
    bestSellersError.value = e.message || 'Không thể tải dữ liệu sản phẩm bán chạy!'
    bestSellers.value = []
  } finally {
    loadingBestSellers.value = false
  }
}

function showBestSellersList() {
  showBestSellers.value = true
  fetchBestSellers()
  bestSellersPage.value = 1
}
function showInventoryList() {
  showBestSellers.value = false
  inventoryPage.value = 1
}

const hasChartData = computed(() => {
  if (!combinedChartData.value.labels || !combinedChartData.value.labels.length) return false
  return combinedChartData.value.datasets.some(ds =>
    Array.isArray(ds.data) && ds.data.some(val => Number(val) > 0)
  )
})

watch(combinedChartData, (val) => {
  console.log('combinedChartData', val)
}, { immediate: true })
watch(hasChartData, (val) => {
  console.log('hasChartData', val)
}, { immediate: true })

watch(chartType, (val) => {
  fetchChartData(val)
})

watch(inventoryPageSize, () => {
  inventoryPage.value = 1
})

watch(filters, () => {
  applyFilters()
}, { deep: true })

watch(combinedChartData, (val) => {
  if (chartType.value === 'month' && val.labels && val.labels.length) {
    const lbls = monthLabels.value
    monthCompareA.value = lbls[lbls.length - 2] || ''
    monthCompareB.value = lbls[lbls.length - 1] || ''
  }
})

const compareRevenueA = computed(() => {
  const labels = monthLabels.value
  const rev = { data: monthDataApi.value.revenue || [] }
  if (!rev) return 0
  const idx = labels.indexOf(monthCompareA.value)
  return idx >= 0 ? Number(rev.data[idx] || 0) : 0
})
const compareRevenueB = computed(() => {
  const labels = monthLabels.value
  const rev = { data: monthDataApi.value.revenue || [] }
  if (!rev) return 0
  const idx = labels.indexOf(monthCompareB.value)
  return idx >= 0 ? Number(rev.data[idx] || 0) : 0
})
const revenueDiff = computed(() => compareRevenueB.value - compareRevenueA.value)

const compareProfitA = computed(() => {
  const labels = monthLabels.value
  const pf = { data: monthDataApi.value.profit || [] }
  if (!pf) return 0
  const idx = labels.indexOf(monthCompareA.value)
  return idx >= 0 ? Number(pf.data[idx] || 0) : 0
})
const compareProfitB = computed(() => {
  const labels = monthLabels.value
  const pf = { data: monthDataApi.value.profit || [] }
  if (!pf) return 0
  const idx = labels.indexOf(monthCompareB.value)
  return idx >= 0 ? Number(pf.data[idx] || 0) : 0
})
const profitDiff = computed(() => compareProfitB.value - compareProfitA.value)

const comparisonChartData = computed(() => {
  const labels = ['Doanh thu', 'Lợi nhuận']
  return {
    labels,
    datasets: [
      {
        label: monthCompareA.value || 'Tháng A',
        data: [compareRevenueA.value, compareProfitA.value],
        backgroundColor: '#93c5fd'
      },
      {
        label: monthCompareB.value || 'Tháng B',
        data: [compareRevenueB.value, compareProfitB.value],
        backgroundColor: '#60a5fa'
      }
    ]
  }
})
const comparisonChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom' } },
  scales: { y: { beginAtZero: true, ticks: { callback: v => Number(v).toLocaleString('vi-VN') } } }
}
</script>

<style scoped>
/* Đảm bảo font chữ đồng nhất và tối ưu hóa khoảng cách */
* {
  font-family: 'Inter', sans-serif;
}

/* Tăng chiều cao cho biểu đồ */
canvas {
  max-height: 300px;
}

/* Hover effect cho bảng */
tbody tr:hover {
  background-color: #F9FAFB;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>