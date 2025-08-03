<template>
  <div class="dashboard-container">
    <!-- Header -->
    <DashboardHeader />
    
    <!-- Stats Cards -->
    <div class="stats-grid">
      <StatsCard 
        v-for="stat in statsCards" 
        :key="stat.id"
        :title="stat.title"
        :value="stat.value"
        :subtitle="stat.subtitle"
        :icon="stat.icon"
        :color="stat.color"
        :loading="systemOverviewLoading"
      />
    </div>

    <!-- Chart Section -->
    <ChartSection 
      :chart-type="chartType"
      :chart-mode="chartTypeMode"
      :loading="chartLoading"
      :error="chartError"
      :data="combinedChartData"
      :options="combinedChartOptions"
      :selected-seller="selectedSellerId"
      :orders-data="ordersChartData"
      :revenue-data="revenueChartData"
      :users-data="usersChartData"
      @update-chart="fetchPayoutChartData"
      @type-change="chartType = $event"
      @mode-change="chartTypeMode = $event"
    />

    <!-- Main Content -->
    <div class="content-grid">
      <!-- Orders Section -->
      <OrdersSection 
        :orders="displayOrders"
        :loading="ordersLoading"
        :error="ordersError"
        :meta="ordersMeta"
        :show-all="showAllOrders"
        @toggle-view="showAllOrders = !showAllOrders"
        @page-change="changeOrdersPage"
        @retry="fetchOrders"
      />

      <!-- Users Section -->
      <UsersSection 
        :users="displayUsers"
        :loading="usersLoading"
        :error="usersError"
        :meta="usersMeta"
        :show-all="showAllUsers"
        @toggle-view="showAllUsers = !showAllUsers"
        @page-change="changeUsersPage"
        @retry="fetchUsers"
      />
    </div>

    <!-- Shop Management & Payouts -->
    <div class="content-grid">
      <!-- Shop Management -->
             <ShopManagement 
         :sellers="paginatedSellers"
         :loading="sellersLoading"
         :error="sellersError"
         :selected-id="selectedSellerId"
         :search-query="searchQuery"
         :sort-by="sortBy"
            :current-page="currentShopPage"
            :total-pages="totalShopPages"
         :shop-options="shopOptions"
         :sort-options="sortOptions"
         @select-seller="selectSeller"
         @search="searchQuery = $event"
         @sort="sortBy = $event"
         @page-change="changeShopPage"
         @seller-change="onSellerChange"
         @retry="fetchSellers"
       />

      <!-- Payouts Section -->
      <PayoutsSection 
        :payouts="displayPayouts"
        :loading="payoutListLoading"
        :error="payoutListError"
        :meta="payoutMeta"
        :show-all="showAllPayouts"
        :order-map="orderMap"
        @toggle-view="showAllPayouts = !showAllPayouts"
        @page-change="changePage"
        @retry="fetchPayoutList"
      />
        </div>

    <!-- Shop Stats -->
    <ShopStats 
      v-if="selectedSellerId"
      :stats="shopStats"
      :loading="shopStatsLoading"
      :error="shopStatsError"
      @retry="fetchShopStats"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { Bar, Line, Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, LineElement, PointElement, ArcElement, CategoryScale, LinearScale } from 'chart.js'
import { secureFetch } from '@/utils/secureFetch' 
import { useRouter } from 'vue-router'

// Components
import StatsCard from '@/components/dashboard/StatsCard.vue'
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue'
import ChartSection from '@/components/dashboard/ChartSection.vue'
import OrdersSection from '@/components/dashboard/OrdersSection.vue'
import UsersSection from '@/components/dashboard/UsersSection.vue'
import ShopManagement from '@/components/dashboard/ShopManagement.vue'
import PayoutsSection from '@/components/dashboard/PayoutsSection.vue'
import ShopStats from '@/components/dashboard/ShopStats.vue'

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend)

const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl
const router = useRouter()

// State
const chartType = ref('month')
const chartTypeMode = ref('bar')
const chartLoading = ref(false)
const chartError = ref('')
const payoutChartData = ref([])
const ordersChartData = ref({ labels: [], data: [] })
const revenueChartData = ref({ labels: [], data: [] })
const usersChartData = ref({ total: 0, active: 0, inactive: 0 })

const systemOverview = ref(null)
const systemOverviewLoading = ref(true)
const systemOverviewError = ref('')

const orders = ref([])
const ordersLoading = ref(true)
const ordersError = ref('')
const ordersMeta = ref({})
const showAllOrders = ref(false)
const currentOrdersPage = ref(1)

const users = ref([])
const usersLoading = ref(true)
const usersError = ref('')
const usersMeta = ref({})
const showAllUsers = ref(false)
const currentUsersPage = ref(1)

const sellers = ref([])
const sellersLoading = ref(false)
const sellersError = ref('')
const selectedSellerId = ref('')
const searchQuery = ref('')
const sortBy = ref('')
const currentShopPage = ref(1)

const payoutList = ref([])
const payoutListLoading = ref(false)
const payoutListError = ref('')
const payoutMeta = ref({})
const currentPage = ref(1)
const showAllPayouts = ref(false)

const shopStats = ref(null)
const shopStatsLoading = ref(false)
const shopStatsError = ref('')

const orderMap = ref({})

// Computed
const statsCards = computed(() => [
  {
    id: 'users',
    title: 'Tổng người dùng',
    value: systemOverview.value?.users?.total || 0,
    subtitle: `${systemOverview.value?.users?.active || 0} đang hoạt động`,
    icon: 'users',
    color: 'blue'
  },
  {
    id: 'orders',
    title: 'Tổng đơn hàng',
    value: systemOverview.value?.orders?.total || 0,
    subtitle: `${systemOverview.value?.orders?.delivered || 0} đã giao`,
    icon: 'orders',
    color: 'green'
  },
  {
    id: 'revenue',
    title: 'Tổng doanh thu',
    value: formatCurrency(systemOverview.value?.orders?.total_revenue || 0),
    subtitle: `${formatCurrency(systemOverview.value?.orders?.total_discount || 0)} giảm giá`,
    icon: 'revenue',
    color: 'purple'
  },
  {
    id: 'sellers',
    title: 'Người bán hàng',
    value: systemOverview.value?.sellers?.total || 0,
    subtitle: `${systemOverview.value?.sellers?.verified || 0} đã xác thực`,
    icon: 'sellers',
    color: 'orange'
  }
])

const shopOptions = computed(() => [
  { value: '', label: 'Tất cả shop' },
  ...sellers.value.map(seller => ({
    value: seller.id,
    label: seller.store_name || seller.user?.name || `Shop #${seller.id}`
  }))
])

const sortOptions = [
  { value: '', label: 'Mặc định' },
  { value: 'revenue_desc', label: 'Doanh thu cao → thấp' },
  { value: 'revenue_asc', label: 'Doanh thu thấp → cao' },
  { value: 'orders_desc', label: 'Đơn hàng nhiều → ít' },
  { value: 'orders_asc', label: 'Đơn hàng ít → nhiều' }
]

const displayOrders = computed(() => {
  if (showAllOrders.value) return orders.value
  return orders.value.slice(0, 5)
})

const displayUsers = computed(() => {
  if (showAllUsers.value) return users.value
  return users.value.slice(0, 5)
})

const displayPayouts = computed(() => {
  if (showAllPayouts.value) return payoutList.value
  return payoutList.value.slice(0, 5)
})

const filteredSellers = computed(() => {
  if (!searchQuery.value) return sellers.value
  const search = searchQuery.value.toLowerCase()
  return sellers.value.filter(seller => {
    return seller.store_name?.toLowerCase().includes(search) ||
           seller.user?.email?.toLowerCase().includes(search) ||
           seller.user?.name?.toLowerCase().includes(search)
  })
})

const shopsPerPage = 4
const totalShopPages = computed(() => Math.ceil(filteredSellers.value.length / shopsPerPage))
const paginatedSellers = computed(() => {
  const start = (currentShopPage.value - 1) * shopsPerPage
  return filteredSellers.value.slice(start, start + shopsPerPage)
})

const chartComponent = computed(() => {
  if (chartTypeMode.value === 'bar') return Bar
  if (chartTypeMode.value === 'line') return Line
  if (chartTypeMode.value === 'pie') return Pie
  return Bar
})

const combinedChartData = computed(() => {
  return {
    labels: payoutChartData.value?.labels || [],
    datasets: [
      {
        label: 'Doanh thu chiết khấu admin',
        data: payoutChartData.value?.data || [],
        backgroundColor: chartTypeMode.value === 'pie' 
          ? ['#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#dbeafe', '#eff6ff', '#1e40af', '#1d4ed8']
          : chartTypeMode.value === 'bar'
          ? 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)'
          : '#3b82f6',
        borderColor: chartTypeMode.value === 'line' ? '#3b82f6' : undefined,
        borderWidth: chartTypeMode.value === 'line' ? 3 : undefined,
        borderRadius: chartTypeMode.value === 'bar' ? 8 : undefined,
        barThickness: chartTypeMode.value === 'bar' ? 40 : undefined,
        fill: chartTypeMode.value === 'line' ? {
          target: 'origin',
          above: 'rgba(59, 130, 246, 0.1)'
        } : undefined,
        tension: chartTypeMode.value === 'line' ? 0.4 : undefined,
      }
    ]
  }
})

const combinedChartOptions = computed(() => {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: chartTypeMode.value === 'pie' ? 'right' : 'top',
        labels: { 
          padding: 20,
          font: { size: 12, weight: 'bold' },
          usePointStyle: true
        }
      },
      tooltip: {
        backgroundColor: 'rgba(0, 0, 0, 0.8)',
        titleColor: 'white',
        bodyColor: 'white',
        borderColor: '#3b82f6',
        borderWidth: 1,
        cornerRadius: 8,
        callbacks: {
          label: (context) => {
            const value = context.parsed.y || context.parsed
            return `${context.dataset.label}: ${formatCurrency(value)}`
          }
        }
      },
      title: { display: false }
    },
    scales: chartTypeMode.value === 'pie' ? undefined : {
      y: {
        beginAtZero: true,
        grid: { color: 'rgba(0, 0, 0, 0.1)', drawBorder: false },
        ticks: {
          callback: (value) => formatCurrency(value),
          font: { size: 11 },
          padding: 8
        },
        title: {
          display: true,
          text: 'VND',
          font: { size: 12, weight: 'bold' }
        }
      },
      x: {
        grid: { color: 'rgba(0, 0, 0, 0.1)', drawBorder: false },
        ticks: { font: { size: 11 }, padding: 8 },
        title: {
          display: true,
          text: chartType.value === 'day' ? 'Ngày' : chartType.value === 'month' ? 'Tháng' : 'Năm',
          font: { size: 12, weight: 'bold' }
        }
      }
    },
    elements: {
      point: {
        radius: 6,
        hoverRadius: 8,
        backgroundColor: '#3b82f6',
        borderColor: 'white',
        borderWidth: 2
      },
      line: { tension: 0.4 }
    }
  }
})

// Methods
let searchTimeout
let chartTimeout

function debounceSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchSellers()
  }, 300)
}

function debounceChartUpdate() {
  clearTimeout(chartTimeout)
  chartTimeout = setTimeout(() => {
    fetchPayoutChartData(chartType.value)
  }, 500)
}

async function fetchSystemOverview() {
  systemOverviewLoading.value = true
  systemOverviewError.value = ''
  try {
    const data = await secureFetch(`${apiBaseUrl}/dashboard/system-overview`, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được tổng quan hệ thống')
    }
    systemOverview.value = data.data
    
    // Cập nhật dữ liệu người dùng cho biểu đồ
    if (data.data?.users) {
      usersChartData.value = {
        total: data.data.users.total || 0,
        active: data.data.users.active || 0,
        inactive: (data.data.users.total || 0) - (data.data.users.active || 0)
      }
    }
  } catch (error) {
    console.error('Error fetching system overview:', error)
    systemOverviewError.value = error.message || 'Không thể tải tổng quan hệ thống!'
    systemOverview.value = null
  } finally {
    systemOverviewLoading.value = false
  }
}

async function fetchPayoutChartData(type = 'month') {
  chartLoading.value = true
  chartError.value = ''
  try {
    let url = `${apiBaseUrl}/admin/payouts/chart?type=${type}`
    if (selectedSellerId.value) url += `&seller_id=${selectedSellerId.value}`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu payout')
    }
    payoutChartData.value = data.data
  } catch (error) {
    console.error('Error fetching payout chart data:', error)
    chartError.value = error.message || 'Không thể tải dữ liệu biểu đồ chiết khấu!'
    payoutChartData.value = { labels: [], data: [] }
  } finally {
    chartLoading.value = false
  }
}

async function fetchOrdersChartData(type = 'month') {
  try {
    let url = `${apiBaseUrl}/dashboard/orders-chart?type=${type}`
    if (selectedSellerId.value) url += `&seller_id=${selectedSellerId.value}`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu đơn hàng')
    }
    return data.data
  } catch (error) {
    console.error('Error fetching orders chart data:', error)
    return { labels: [], data: [] }
  }
}

async function fetchRevenueChartData(type = 'month') {
  try {
    let url = `${apiBaseUrl}/dashboard/revenue-chart?type=${type}`
    if (selectedSellerId.value) url += `&seller_id=${selectedSellerId.value}`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu doanh thu')
    }
    return data.data
  } catch (error) {
    console.error('Error fetching revenue chart data:', error)
    return { labels: [], data: [] }
  }
}

async function fetchOrders(page = 1) {
  ordersLoading.value = true
  ordersError.value = ''
  try {
    let url = `${apiBaseUrl}/dashboard/orders-stats?page=${page}`
    if (selectedSellerId.value) url += `&seller_id=${selectedSellerId.value}`
    if (showAllOrders.value) url += `&per_page=15`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu đơn hàng')
    }
    orders.value = data.data
    ordersMeta.value = data.meta
  } catch (error) {
    console.error('Error fetching orders:', error)
    ordersError.value = error.message || 'Không thể tải danh sách đơn hàng!'
    orders.value = []
    ordersMeta.value = {}
  } finally {
    ordersLoading.value = false
  }
}

async function fetchUsers(page = 1) {
  usersLoading.value = true
  usersError.value = ''
  try {
    let url = `${apiBaseUrl}/dashboard/users-stats?page=${page}`
    if (showAllUsers.value) url += `&per_page=15`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu người dùng')
    }
    users.value = data.data
    usersMeta.value = data.meta
  } catch (error) {
    console.error('Error fetching users:', error)
    usersError.value = error.message || 'Không thể tải danh sách người dùng!'
    users.value = []
    usersMeta.value = {}
  } finally {
    usersLoading.value = false
  }
}

async function fetchSellers() {
  sellersLoading.value = true
  sellersError.value = ''
  try {
    let url = `${apiBaseUrl}/admin/sellers?`
    if (searchQuery.value) {
      url += `search=${encodeURIComponent(searchQuery.value)}&`
    }
    if (sortBy.value) {
      url += `sort=${sortBy.value}&`
    }
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được danh sách seller')
    }
    sellers.value = data.data
  } catch (error) {
    console.error('Error fetching sellers:', error)
    sellersError.value = error.message || 'Không thể tải danh sách seller!'
    sellers.value = []
  } finally {
    sellersLoading.value = false
  }
}

async function fetchPayoutList(page = 1) {
  payoutListLoading.value = true
  payoutListError.value = ''
  try {
    let url = `${apiBaseUrl}/admin/payouts/approved?page=${page}`
    if (selectedSellerId.value) url += `&seller_id=${selectedSellerId.value}`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được dữ liệu payout')
    }
    payoutList.value = data.data
    payoutMeta.value = data.meta
  } catch (error) {
    console.error('Error fetching payout list:', error)
    payoutListError.value = error.message || 'Không thể tải danh sách payout!'
    payoutList.value = []
    payoutMeta.value = {}
  } finally {
    payoutListLoading.value = false
  }
}

async function fetchShopStats() {
  if (!selectedSellerId.value) {
    shopStats.value = null
    return
  }
  shopStatsLoading.value = true
  shopStatsError.value = ''
  try {
    const token = localStorage.getItem('access_token')
    const res = await fetch(`${apiBaseUrl}/dashboard/stats?seller_id=${selectedSellerId.value}`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    })
    if (!res.ok) throw new Error('Không lấy được thống kê shop')
    const data = await res.json()
    shopStats.value = data
  } catch (e) {
    shopStatsError.value = 'Không thể tải thống kê shop!'
    shopStats.value = null
  } finally {
    shopStatsLoading.value = false
  }
}

async function fetchOrderMap() {
  try {
    const token = localStorage.getItem('access_token')
    const res = await fetch(`${apiBaseUrl}/orders?per_page=1000`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    })
    const data = await res.json()
    if (Array.isArray(data.data)) {
      const map = {}
      data.data.forEach(o => {
        map[o.id] = o.shipping && o.shipping.tracking_code ? o.shipping.tracking_code : '-'
      })
      orderMap.value = map
    }
  } catch {}
}

function selectSeller(id) {
  selectedSellerId.value = id === selectedSellerId.value ? '' : id
  onSellerChange()
}

function onSellerChange() {
  fetchPayoutChartData(chartType.value)
  fetchOrdersChartData(chartType.value).then(data => {
    ordersChartData.value = data
  })
  fetchRevenueChartData(chartType.value).then(data => {
    revenueChartData.value = data
  })
  fetchPayoutList()
  fetchShopStats()
}

function changeOrdersPage(page) {
  if (page >= 1 && page <= ordersMeta.value.last_page) {
    currentOrdersPage.value = page
    fetchOrders(page)
  }
}

function changeUsersPage(page) {
  if (page >= 1 && page <= usersMeta.value.last_page) {
    currentUsersPage.value = page
    fetchUsers(page)
  }
}

function changePage(page) {
  if (page !== currentPage.value) {
    currentPage.value = page
    fetchPayoutList(page)
  }
}

function changeShopPage(page) {
  if (page >= 1 && page <= totalShopPages.value) {
    currentShopPage.value = page
  }
}

function formatCurrency(value) {
  if (!value && value !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value)
}

function getTrackingCode(payout) {
  if (payout.order_id && orderMap.value[payout.order_id]) return orderMap.value[payout.order_id]
  if (payout.tracking_code) return payout.tracking_code
  if (payout.order && payout.order.shipping && payout.order.shipping.tracking_code) return payout.order.shipping.tracking_code
  return '-'
}

// Watchers
watch(chartType, () => {
  debounceChartUpdate()
  // Cũng cập nhật orders chart data
  fetchOrdersChartData(chartType.value).then(data => {
    ordersChartData.value = data
  })
  // Cập nhật revenue chart data
  fetchRevenueChartData(chartType.value).then(data => {
    revenueChartData.value = data
  })
})

// Lifecycle
onMounted(async () => {
  try {
    await Promise.all([
      fetchSystemOverview(),
      fetchPayoutChartData(chartType.value),
      fetchOrdersChartData(chartType.value).then(data => {
        ordersChartData.value = data
      }),
      fetchRevenueChartData(chartType.value).then(data => {
        revenueChartData.value = data
      }),
      fetchPayoutList(),
      fetchOrders(),
      fetchUsers(),
      fetchSellers(),
      fetchOrderMap()
    ])
    
    if (selectedSellerId.value) {
      fetchShopStats()
    }
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
})

definePageMeta({
  layout: 'default-admin'
})

// Import dashboard styles
import '@/assets/css/dashboard.css'
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Smooth transitions */
* {
  transition: all 0.2s ease-in-out;
}
</style>