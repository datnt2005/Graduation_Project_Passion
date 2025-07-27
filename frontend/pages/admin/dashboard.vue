<template>
  <!-- Box tổng chiết khấu admin -->
  <div class="bg-white p-4 rounded shadow mt-6 w-full max-w-md mx-auto">
    <h3 class="text-lg font-semibold text-blue-600 mb-2">Tổng chiết khấu admin đã thu</h3>
    <div v-if="adminCommissionLoading" class="text-gray-400">Đang tải...</div>
    <div v-else-if="adminCommissionError" class="text-red-500">{{ adminCommissionError }}</div>
    <div v-else class="text-2xl font-bold text-blue-700">{{ formatNumber(adminCommission) }} đ</div>
  </div>

  <!-- Shop selector for admin -->
  <div class="bg-white p-4 rounded shadow mt-6 w-full max-w-4xl mx-auto">
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
      <div class="w-full md:w-1/3">
        <label class="font-semibold text-gray-700 block mb-2">Chọn shop:</label>
        <select 
          v-model="selectedSellerId" 
          @change="onSellerChange" 
          class="w-full border border-gray-300 rounded px-3 py-2"
        >
          <option value="">Tất cả shop</option>
          <option v-for="seller in filteredSellers" :key="seller.id" :value="seller.id">
            {{ seller.store_name || seller.user?.name || 'Shop #' + seller.id }}
          </option>
        </select>
      </div>

      <div class="w-full md:w-1/3">
        <label class="font-semibold text-gray-700 block mb-2">Tìm kiếm:</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tên shop, email..."
          class="w-full border border-gray-300 rounded px-3 py-2"
          @input="debounceSearch"
        />
      </div>

      <div class="w-full md:w-1/3">
        <label class="font-semibold text-gray-700 block mb-2">Sắp xếp theo:</label>
        <select
          v-model="sortBy"
          class="w-full border border-gray-300 rounded px-3 py-2"
          @change="fetchSellers"
        >
          <option value="">Mặc định</option>
          <option value="revenue_desc">Doanh thu cao → thấp</option>
          <option value="revenue_asc">Doanh thu thấp → cao</option>
          <option value="orders_desc">Đơn hàng nhiều → ít</option>
          <option value="orders_asc">Đơn hàng ít → nhiều</option>
        </select>
      </div>
    </div>

    <!-- Danh sách shop -->
    <div class="mt-6 border-t pt-4">
      <div v-if="sellersLoading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <div class="mt-2 text-gray-500">Đang tải...</div>
      </div>

      <div v-else-if="sellersError" class="text-center py-8">
        <div class="text-red-500">{{ sellersError }}</div>
        <button 
          @click="fetchSellers"
          class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Thử lại
        </button>
      </div>

      <div v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="seller in paginatedSellers"
            :key="seller.id"
            :class="[
              'p-4 rounded-lg border transition cursor-pointer',
              selectedSellerId === seller.id
                ? 'border-blue-500 bg-blue-50 shadow-md'
                : 'border-gray-200 hover:border-blue-300 hover:shadow'
            ]"
            @click="selectSeller(seller.id)"
          >
            <div class="font-semibold text-blue-600 mb-1">{{ seller.store_name }}</div>
            <div class="text-sm text-gray-500 mb-3">{{ seller.user.email }}</div>
            <div class="flex gap-8">
              <div>
                <div class="text-gray-500 text-sm">Đơn hàng</div>
                <div class="font-semibold">{{ seller.total_orders }}</div>
              </div>
              <div>
                <div class="text-gray-500 text-sm">Doanh thu</div>
                <div class="font-semibold text-green-600">{{ formatCurrency(seller.total_revenue) }}</div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="totalShopPages > 1" class="flex justify-center mt-4 gap-2">
          <button
            :disabled="currentShopPage === 1"
            @click="changeShopPage(currentShopPage - 1)"
            class="px-3 py-1 rounded border bg-gray-100 hover:bg-gray-200 disabled:opacity-50"
          >
            Trước
          </button>
          <button
            v-for="page in totalShopPages"
            :key="page"
            @click="changeShopPage(page)"
            :class="[
              'px-3 py-1 rounded border',
              page === currentShopPage ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'
            ]"
          >
            {{ page }}
          </button>
          <button
            :disabled="currentShopPage === totalShopPages"
            @click="changeShopPage(currentShopPage + 1)"
            class="px-3 py-1 rounded border bg-gray-100 hover:bg-gray-200 disabled:opacity-50"
          >
            Sau
          </button>
        </div>

        <div v-if="!sellers.length" class="text-center py-8">
          <div class="text-gray-500">Không tìm thấy shop nào</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Thống kê doanh thu shop -->
  <div v-if="selectedSellerId" class="bg-white p-4 rounded shadow mt-6 w-full max-w-4xl mx-auto">
    <h3 class="text-lg font-semibold text-blue-600 mb-4">Thống kê doanh thu shop</h3>
    
    <div v-if="shopStatsLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <div class="mt-2 text-gray-500">Đang tải...</div>
    </div>

    <div v-else-if="shopStatsError" class="text-center py-8">
      <div class="text-red-500">{{ shopStatsError }}</div>
      <button 
        @click="fetchShopStats"
        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Thử lại
      </button>
    </div>

    <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-6">
      <!-- Cột 1 -->
      <div>
        <div class="text-gray-600 mb-1">Tổng đơn hàng</div>
        <div class="text-2xl font-bold">{{ shopStats?.total_orders || 0 }}</div>
      </div>
      <div>
        <div class="text-gray-600 mb-1">Đơn đã bán</div>
        <div class="text-2xl font-bold">{{ shopStats?.sold_orders || 0 }}</div>
      </div>
      <div>
        <div class="text-gray-600 mb-1">Tổng doanh thu</div>
        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(shopStats?.total_revenue || 0) }}</div>
      </div>

      <!-- Cột 2 -->
      <div>
        <div class="text-gray-600 mb-1">Tổng vốn đã bán</div>
        <div class="text-2xl font-bold">{{ formatCurrency(shopStats?.total_cost || 0) }}</div>
      </div>
      <div>
        <div class="text-gray-600 mb-1">Tổng lợi nhuận</div>
        <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(shopStats?.total_profit || 0) }}</div>
      </div>
      <div>
        <div class="text-gray-600 mb-1">Tổng lỗ</div>
        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(shopStats?.total_loss || 0) }}</div>
      </div>
    </div>
  </div>

  <!-- Biểu đồ doanh thu chiết khấu admin -->
  <div class="bg-white p-4 sm:p-6 rounded shadow mt-6 w-full overflow-x-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
      <h2 class="text-lg sm:text-xl font-semibold text-gray-700">Biểu đồ Doanh Thu Chiết Khấu Admin</h2>
      <div class="mt-2 md:mt-0 flex flex-wrap items-center gap-2">
        <label for="filter" class="text-sm text-gray-600">Lọc theo:</label>
        <select
          id="filter"
          v-model="chartType"
          class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-200"
        >
          <option value="day">Ngày</option>
          <option value="month">Tháng</option>
          <option value="year">Năm</option>
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

  <!-- Bảng danh sách các đơn hàng đã được thanh toán (payout completed) -->
  <div class="bg-white p-4 rounded shadow mt-6 w-full max-w-4xl mx-auto">
    <h3 class="text-lg font-semibold text-green-700 mb-2 flex items-center gap-2">
      <span>💸</span> Đơn hàng đã thanh toán gần đây
    </h3>
    <div v-if="payoutListLoading" class="text-gray-400">Đang tải...</div>
    <div v-else-if="payoutListError" class="text-red-500">{{ payoutListError }}</div>
    <div v-else-if="!payoutList.length" class="text-gray-400">Không có đơn hàng đã thanh toán</div>
    <div v-else>
      <table class="w-full table-auto divide-y divide-gray-200">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">MÃ VẬN ĐƠN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">NGƯỜI BÁN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">SỐ TIỀN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">NGÀY CHUYỂN KHOẢN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">GHI CHÚ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in payoutList" :key="item.id" class="hover:bg-blue-50 transition">
            <td class="px-4 py-2 whitespace-nowrap text-sm font-semibold text-blue-700">
              <a
                href="#"
                class="underline hover:text-orange-600 cursor-pointer"
                @click.prevent="goToOrderWithTracking(getTrackingCode(item))"
              >
                {{ getTrackingCode(item) }}
              </a>
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm">
              {{ item.seller?.store_name || item.seller?.user?.name || 'N/A' }}
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(item.amount) }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.transferred_at) }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.note || '-' }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Phân trang -->
      <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-500">
          Hiển thị {{ payoutMeta.from || 0 }}-{{ payoutMeta.to || 0 }} trên tổng số {{ payoutMeta.total || 0 }} kết quả
        </div>
        <div class="flex gap-2">
          <button
            v-for="page in payoutMeta.last_page"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-1 rounded text-sm',
              page === payoutMeta.current_page
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { Bar, Line, Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, LineElement, PointElement, ArcElement, CategoryScale, LinearScale } from 'chart.js'
import { secureFetch } from '@/utils/secureFetch' 
import { useRouter } from 'vue-router'
ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend)

const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl
const router = useRouter()

// Shop selector state
const sellers = ref([])
const sellersLoading = ref(false)
const sellersError = ref('')
const selectedSellerId = ref('')
const searchQuery = ref('')
const sortBy = ref('')
const selectedSellerStats = ref(null)

// Computed
const filteredSellers = computed(() => {
  if (!searchQuery.value) return sellers.value;
  const search = searchQuery.value.toLowerCase();
  return sellers.value.filter(seller => {
    return seller.store_name?.toLowerCase().includes(search) ||
           seller.user?.email?.toLowerCase().includes(search) ||
           seller.user?.name?.toLowerCase().includes(search);
  });
});

// Debounce search
let searchTimeout;
function debounceSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchSellers();
  }, 300);
}

async function fetchSellers() {
  sellersLoading.value = true;
  sellersError.value = '';
  try {
    let url = `${apiBaseUrl}/admin/sellers?`;
    if (searchQuery.value) {
      url += `search=${encodeURIComponent(searchQuery.value)}&`;
    }
    if (sortBy.value) {
      url += `sort=${sortBy.value}&`;
    }
    const data = await secureFetch(url, {}, ['admin']);
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được danh sách seller');
    }
    sellers.value = data.data;
  } catch (error) {
    console.error('Error fetching sellers:', error);
    sellersError.value = error.message || 'Không thể tải danh sách seller!';
    sellers.value = [];
  } finally {
    sellersLoading.value = false;
  }
}

function selectSeller(id) {
  selectedSellerId.value = id === selectedSellerId.value ? '' : id;
  if (selectedSellerId.value) {
    selectedSellerStats.value = sellers.value.find(s => s.id === selectedSellerId.value);
  } else {
    selectedSellerStats.value = null;
  }
  onSellerChange();
}

const shopStats = ref(null)
const shopStatsLoading = ref(false)
const shopStatsError = ref('')

async function fetchShopStats() {
  if (!selectedSellerId.value) {
    shopStats.value = null
    return
  }
  shopStatsLoading.value = true
  shopStatsError.value = ''
  try {
    const token = localStorage.getItem('access_token');
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

function onSellerChange() {
  fetchAdminCommission()
  fetchPayoutChartData(chartType.value)
  fetchPayoutList()
  fetchShopStats()
}

// Tổng chiết khấu admin
const adminCommission = ref(0)
const adminCommissionLoading = ref(false)
const adminCommissionError = ref('')

// Dữ liệu payout completed để vẽ biểu đồ chiết khấu
const payoutChartData = ref([])
const chartType = ref('month')
const chartLoading = ref(false)
const chartError = ref('')

const payoutList = ref([])
const payoutListLoading = ref(false)
const payoutListError = ref('')
const payoutMeta = ref({})
const currentPage = ref(1)
const recentPayouts = computed(() => {
  return payoutList.value
    .filter(p => p.status === 'completed')
    .sort((a, b) => parseVNDate(b.transferred_at || 0) - parseVNDate(a.transferred_at || 0))
    .slice(0, 10)
})

// Map order_id -> tracking_code từ danh sách orders
const orderMap = ref({})
async function fetchOrderMap() {
  try {
    const token = localStorage.getItem('access_token');
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

async function fetchAdminCommission() {
  adminCommissionLoading.value = true
  adminCommissionError.value = ''
  try {
    let url = `${apiBaseUrl}/admin/payouts/stats`
    if (selectedSellerId.value) url += `?seller_id=${selectedSellerId.value}`
    const data = await secureFetch(url, {}, ['admin'])
    if (!data.success) {
      throw new Error(data.message || 'Không lấy được thống kê payout')
    }
    adminCommission.value = data.data.total_commission
  } catch (error) {
    console.error('Error fetching admin commission:', error)
    adminCommissionError.value = error.message || 'Không thể tải dữ liệu chiết khấu!'
  } finally {
    adminCommissionLoading.value = false
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

function parseVNDate(dateStr) {
  // Hỗ trợ dd/mm/yyyy hh:mm:ss
  if (!dateStr) return null;
  if (/^\d{2}\/\d{2}\/\d{4}/.test(dateStr)) {
    const [d, m, yAndTime] = dateStr.split('/');
    let y = '', time = '';
    if (yAndTime) [y, time] = yAndTime.trim().split(' ');
    const [h = '00', min = '00', s = '00'] = (time || '').split(':');
    return new Date(`${y}-${m}-${d}T${h}:${min}:${s}`);
  }
  // ISO hoặc yyyy-mm-dd
  return new Date(dateStr);
}

function formatDate(dateStr) {
  const date = parseVNDate(dateStr);
  if (!date || isNaN(date.getTime())) return '-';
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function getTrackingCode(payout) {
  // Ưu tiên lấy từ orderMap nếu có
  if (payout.order_id && orderMap.value[payout.order_id]) return orderMap.value[payout.order_id]
  if (payout.tracking_code) return payout.tracking_code
  if (payout.order && payout.order.shipping && payout.order.shipping.tracking_code) return payout.order.shipping.tracking_code
  return '-'
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

function changePage(page) {
  if (page !== currentPage.value) {
    currentPage.value = page
    fetchPayoutList(page)
  }
}

const shopsPerPage = 4
const currentShopPage = ref(1)
const totalShopPages = computed(() => Math.ceil(filteredSellers.value.length / shopsPerPage))
const paginatedSellers = computed(() => {
  const start = (currentShopPage.value - 1) * shopsPerPage
  return filteredSellers.value.slice(start, start + shopsPerPage)
})
function changeShopPage(page) {
  if (page >= 1 && page <= totalShopPages.value) {
    currentShopPage.value = page
  }
}

onMounted(() => {
  fetchSellers()
  fetchAdminCommission()
  fetchPayoutChartData(chartType.value)
  fetchPayoutList()
  fetchOrderMap()
  fetchShopStats()
})

const chartTypeMode = ref('bar')
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
          ? ['#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#dbeafe', '#eff6ff']
          : '#3b82f6',
        borderColor: chartTypeMode.value === 'line' ? '#3b82f6' : undefined,
        borderWidth: chartTypeMode.value === 'line' ? 2 : undefined,
        borderRadius: chartTypeMode.value === 'bar' ? 6 : undefined,
        barThickness: chartTypeMode.value === 'bar' ? 30 : undefined,
        fill: chartTypeMode.value === 'line' ? false : undefined,
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
        labels: { padding: 20 }
      },
      tooltip: {
        callbacks: {
          label: (context) => {
            const value = context.parsed.y || context.parsed;
            return `${context.dataset.label}: ${formatCurrency(value)}`;
          }
        }
      },
      title: {
        display: true,
        text: 'Biểu đồ Doanh Thu Chiết Khấu Admin',
        font: { size: 16 },
        padding: { top: 10, bottom: 10 }
      }
    },
    scales: chartTypeMode.value === 'pie' ? undefined : {
      y: {
        beginAtZero: true,
        ticks: {
          callback: (value) => formatCurrency(value)
        },
        title: {
          display: true,
          text: 'VND'
        }
      },
      x: {
        title: {
          display: true,
          text: chartType.value === 'day' ? 'Ngày' : chartType.value === 'month' ? 'Tháng' : 'Năm'
        }
      }
    }
  }
})

// Format tiền tệ
function formatCurrency(value) {
  if (!value && value !== 0) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value);
}

// Watch chartType để load lại dữ liệu khi thay đổi
watch(chartType, (val) => {
  fetchPayoutChartData(val);
});

// Watch chartTypeMode để cập nhật lại biểu đồ
watch(chartTypeMode, () => {
  // Chart.js sẽ tự động cập nhật khi data hoặc options thay đổi
});

// Gọi API khi component mounted
onMounted(() => {
  fetchSellers();
  fetchAdminCommission();
  fetchPayoutChartData(chartType.value);
  fetchPayoutList();
  fetchOrderMap();
  fetchShopStats();
});

function formatNumber(val) {
  if (typeof val === 'number') return val.toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  if (!isNaN(val) && val !== null && val !== undefined && val !== '') return Number(val).toLocaleString('vi-VN', { maximumFractionDigits: 0 })
  return val || '0'
}

function goToOrderWithTracking(trackingCode) {
  if (!trackingCode || trackingCode === '-') return;
  router.push({ path: '/admin/orders/list-order', query: { tracking_code: trackingCode } })
}

function payoutStatusClass(status) {
  if (status === 'completed') return 'text-green-600 font-bold';
  if (status === 'pending') return 'text-yellow-600 font-bold';
  if (status === 'rejected') return 'text-red-600 font-bold';
  return '';
}

function payoutStatusLabel(status) {
  if (status === 'completed') return 'Đã chuyển khoản';
  if (status === 'pending') return 'Chờ duyệt';
  if (status === 'rejected') return 'Từ chối';
  return status;
}

definePageMeta({
  layout: 'default-admin'
})
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>