<template>
  <!-- Box tổng chiết khấu admin -->
  <div class="bg-white p-4 rounded shadow mt-6 w-full max-w-md mx-auto">
    <h3 class="text-lg font-semibold text-blue-600 mb-2">Tổng chiết khấu admin đã thu</h3>
    <div v-if="adminCommissionLoading" class="text-gray-400">Đang tải...</div>
    <div v-else-if="adminCommissionError" class="text-red-500">{{ adminCommissionError }}</div>
    <div v-else class="text-2xl font-bold text-blue-700">{{ formatNumber(adminCommission) }} đ</div>
  </div>

  <!-- Shop selector for admin -->
  <div class="bg-white p-4 rounded shadow mt-6 w-full max-w-4xl mx-auto flex flex-wrap gap-4 items-center">
    <label class="font-semibold text-gray-700">Chọn shop:</label>
    <select v-model="selectedSellerId" @change="onSellerChange" class="border border-gray-300 rounded px-3 py-2 min-w-[200px]">
      <option value="">Tất cả shop</option>
      <option v-for="seller in sellers" :key="seller.id" :value="seller.id">
        {{ seller.store_name || seller.user?.name || seller.user?.email || 'Shop #' + seller.id }}
      </option>
    </select>
  </div>

  <!-- Shop stats for admin -->
  <div v-if="selectedSellerId && shopStats" class="bg-white p-4 rounded shadow mt-4 w-full max-w-4xl mx-auto">
    <h3 class="text-lg font-semibold text-blue-700 mb-2">Thống kê doanh thu shop</h3>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div>
        <div class="text-gray-500 text-xs">Tổng đơn hàng</div>
        <div class="text-xl font-bold">{{ formatNumber(shopStats.total_orders) }}</div>
      </div>
      <div>
        <div class="text-gray-500 text-xs">Đơn đã bán</div>
        <div class="text-xl font-bold">{{ formatNumber(shopStats.sold_orders) }}</div>
      </div>
      <div>
        <div class="text-gray-500 text-xs">Tổng doanh thu</div>
        <div class="text-xl font-bold text-green-700">{{ formatNumber(shopStats.total_revenue) }} đ</div>
      </div>
      <div>
        <div class="text-gray-500 text-xs">Tổng vốn đã bán</div>
        <div class="text-xl font-bold">{{ formatNumber(shopStats.total_cost) }} đ</div>
      </div>
      <div>
        <div class="text-gray-500 text-xs">Tổng lợi nhuận</div>
        <div class="text-xl font-bold text-blue-700">{{ formatNumber(shopStats.total_profit) }} đ</div>
      </div>
      <div>
        <div class="text-gray-500 text-xs">Tổng lỗ</div>
        <div class="text-xl font-bold text-red-600">{{ formatNumber(shopStats.total_loss) }} đ</div>
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
    <div v-else-if="!recentPayouts.length" class="text-gray-400">Không có đơn hàng đã thanh toán</div>
    <div v-else>
      <table class="w-full table-auto divide-y divide-gray-200">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">MÃ VẬN ĐƠN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">SỐ TIỀN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">NGÀY CHUYỂN KHOẢN</th>
            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">GHI CHÚ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in recentPayouts" :key="item.id" class="hover:bg-blue-50 transition">
            <td class="px-4 py-2 whitespace-nowrap text-sm font-semibold text-blue-700">
              <a
                href="#"
                class="underline hover:text-orange-600 cursor-pointer"
                @click.prevent="goToOrderWithTracking(getTrackingCode(item))"
              >
                {{ getTrackingCode(item) }}
              </a>
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(item.amount) }} đ</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.created_at) }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.transferred_at) }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm">
              <span :class="payoutStatusClass(item.status)">{{ payoutStatusLabel(item.status) }}</span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.note }}</td>
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
import { secureFetch } from '@/utils/secureFetch' 
import { useRouter } from 'vue-router'
ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend)

const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl
const router = useRouter()

// Shop selector state
const sellers = ref([])
const selectedSellerId = ref('')

async function fetchSellers() {
  try {
    const token = localStorage.getItem('access_token');
    const res = await fetch(`${apiBaseUrl}/admin/sellers`, {
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    })
    const data = await res.json()
    sellers.value = Array.isArray(data) ? data : (data.data || data)
  } catch (e) {
    sellers.value = []
  }
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
  try {
    let url = `${apiBaseUrl}/payout/list-approved`
    if (selectedSellerId.value) url += `?seller_id=${selectedSellerId.value}`
    const res = await secureFetch(url, {}, ['admin'])
    if (!res.ok) throw new Error('Không lấy được dữ liệu payout')
    const data = await res.json()
    const payouts = Array.isArray(data) ? data : (data.data || [])
    let total = 0
    payouts.forEach(p => {
      if (p.amount) total += Number(p.amount) * 5 / 95
    })
    adminCommission.value = Math.round(total)
  } catch (e) {
    adminCommissionError.value = 'Không thể tải dữ liệu chiết khấu!'
  } finally {
    adminCommissionLoading.value = false
  }
}

async function fetchPayoutChartData(type = 'month') {
  chartLoading.value = true
  chartError.value = ''
  try {
    let url = `${apiBaseUrl}/payout/list-approved`
    if (selectedSellerId.value) url += `?seller_id=${selectedSellerId.value}`
    const res = await secureFetch(url, {}, ['admin'])
    if (!res.ok) throw new Error('Không lấy được dữ liệu payout')
    const data = await res.json()
    const payouts = Array.isArray(data) ? data : (data.data || [])
    // Group payout theo mốc thời gian
    const groupMap = {};
    payouts.forEach(p => {
      if (!p.amount || !p.transferred_at) return;
      const date = parseVNDate(p.transferred_at)
      let key = ''
      if (type === 'year') key = date.getFullYear()
      else if (type === 'month') key = `${date.getFullYear()}-${(date.getMonth()+1).toString().padStart(2,'0')}`
      else if (type === 'day') key = date.toISOString().slice(0,10)
      else key = date.toISOString().slice(0,10)
      if (!groupMap[key]) groupMap[key] = 0
      groupMap[key] += Number(p.amount) * 5 / 95
    })
    // Chuyển thành mảng labels, data
    const labels = Object.keys(groupMap).sort()
    const dataArr = labels.map(l => Math.round(groupMap[l]))
    payoutChartData.value = { labels, data: dataArr }
  } catch (e) {
    chartError.value = 'Không thể tải dữ liệu biểu đồ chiết khấu!'
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

async function fetchPayoutList() {
  payoutListLoading.value = true
  payoutListError.value = ''
  try {
    let url = `${apiBaseUrl}/payout/list-approved`
    if (selectedSellerId.value) url += `?seller_id=${selectedSellerId.value}`
    const res = await secureFetch(url, {}, ['admin'])
    if (!res.ok) throw new Error('Không lấy được dữ liệu payout')
    const data = await res.json()
    payoutList.value = Array.isArray(data) ? data : (data.data || [])
  } catch (e) {
    payoutListError.value = 'Không thể tải danh sách payout!'
    payoutList.value = []
  } finally {
    payoutListLoading.value = false
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
watch(chartType, (val) => {
  fetchPayoutChartData(val)
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
    labels: payoutChartData.value.labels || [],
    datasets: [
      {
        label: 'Doanh thu chiết khấu admin',
        data: payoutChartData.value.data || [],
        backgroundColor: chartTypeMode.value === 'pie' ? ['#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'] : '#3b82f6',
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
            return `${context.dataset.label}: ${value.toLocaleString('vi-VN')} đ`;
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
    scales: chartTypeMode.value === 'pie' ? {} : {
      y: {
        beginAtZero: true,
        ticks: {
          callback: (value) => `${value.toLocaleString('vi-VN')} đ`
        },
        title: {
          display: true,
          text: 'VND'
        }
      },
      x: {
        title: {
          display: true,
          text: chartType.value === 'day' ? 'Ngày' : chartType.value === 'month' ? 'Tháng' : chartType.value === 'year' ? 'Năm' : 'Thời gian'
        }
      }
    }
  }
})

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