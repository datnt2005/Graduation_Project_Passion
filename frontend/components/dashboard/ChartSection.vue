<template>
  <div class="chart-grid">
    <!-- Main Chart -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
          <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
            <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
              <ChartIcon class="svg-icon text-white" />
            </div>
            Biểu Đồ Doanh Thu Chiết Khấu
          </h2>
          <p class="text-gray-600 text-sm">Theo dõi doanh thu chiết khấu theo thời gian</p>
        </div>
        <div class="mt-4 lg:mt-0 flex flex-wrap items-center gap-3">
          <SelectControl
            :model-value="chartType"
            :options="chartTypeOptions"
            label="Lọc theo"
            class="w-32"
            @update:model-value="$emit('type-change', $event)"
          />
          <SelectControl
            :model-value="chartMode"
            :options="chartModeOptions"
            label="Kiểu biểu đồ"
            class="w-36"
            @update:model-value="$emit('mode-change', $event)"
          />
        </div>
      </div>

      <div class="chart-container">
        <LoadingSpinner v-if="loading" message="Đang tải biểu đồ..." />
        <ErrorMessage 
          v-else-if="error" 
          :message="error"
          @retry="$emit('update-chart', chartType)"
        />
        <component v-else :is="chartComponent" :data="data" :options="options" />
      </div>
    </div>

    <!-- Users Donut Chart -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
          <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
            <UsersIcon class="svg-icon text-white" />
          </div>
          Tổng Người Dùng
        </h2>
        <p class="text-gray-600 text-sm">Thống kê người dùng hệ thống</p>
      </div>

      <div class="chart-container small">
        <LoadingSpinner v-if="loading" message="Đang tải biểu đồ..." />
        <ErrorMessage 
          v-else-if="error" 
          :message="error"
          @retry="$emit('update-chart', chartType)"
        />
        <component v-else :is="Doughnut" :data="usersChartData" :options="usersChartOptions" />
      </div>
    </div>
  </div>

  <!-- Small Charts Row -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Orders Line Chart -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="mb-4">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
            <div class="w-5 h-5 bg-green-500 rounded-lg flex items-center justify-center">
              <OrdersIcon class="svg-icon small text-white" />
            </div>
            Tổng Đơn Hàng
          </h3>
          <SelectControl
            :model-value="smallChartYear"
            :options="yearOptions"
            label="Năm"
            class="w-24"
            @update:model-value="onSmallChartYearChange"
          />
        </div>
        <p class="text-gray-600 text-xs">Thống kê đơn hàng theo thời gian</p>
      </div>

      <div class="chart-container small">
        <LoadingSpinner v-if="loading" message="Đang tải..." />
        <ErrorMessage 
          v-else-if="error" 
          :message="error"
          @retry="$emit('update-chart', chartType)"
        />
        <component v-else :is="Line" :data="ordersChartData" :options="smallChartOptions" />
      </div>
    </div>

    <!-- Revenue Line Chart -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="mb-4">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
            <div class="w-5 h-5 bg-orange-500 rounded-lg flex items-center justify-center">
              <RevenueIcon class="svg-icon small text-white" />
            </div>
            Doanh Thu Shop
          </h3>
          <SelectControl
            :model-value="smallChartYear"
            :options="yearOptions"
            label="Năm"
            class="w-24"
            @update:model-value="onSmallChartYearChange"
          />
        </div>
        <p class="text-gray-600 text-xs">Tổng tiền đơn hàng theo tháng</p>
      </div>

      <div class="chart-container small">
        <LoadingSpinner v-if="loading" message="Đang tải..." />
        <ErrorMessage 
          v-else-if="error" 
          :message="error"
          @retry="$emit('update-chart', chartType)"
        />
        <component v-else :is="Line" :data="revenueChartData" :options="smallRevenueOptions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar, Line, Doughnut } from 'vue-chartjs'
import SelectControl from '@/components/ui/SelectControl.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'

const props = defineProps({
  chartType: {
    type: String,
    default: 'month'
  },
  chartMode: {
    type: String,
    default: 'bar'
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    required: true
  },
  selectedSeller: {
    type: [String, Number],
    default: ''
  },
  ordersData: {
    type: Object,
    default: () => ({ labels: [], data: [] })
  },
  revenueData: {
    type: Object,
    default: () => ({ labels: [], data: [] })
  },
  usersData: {
    type: Object,
    default: () => ({ total: 0, active: 0, inactive: 0 })
  },
  smallChartYear: {
    type: [String, Number],
    default: new Date().getFullYear()
  }
})

const emit = defineEmits(['update-chart', 'type-change', 'mode-change', 'small-chart-year-change'])

const chartTypeOptions = [
  { value: 'day', label: 'Ngày' },
  { value: 'month', label: 'Tháng' },
  { value: 'year', label: 'Năm' }
]

const chartModeOptions = [
  { value: 'bar', label: 'Cột' },
  { value: 'line', label: 'Đường' }
]

// Year options for small charts
const yearOptions = computed(() => {
  const currentYear = new Date().getFullYear()
  const options = []
  for (let year = currentYear; year >= currentYear - 4; year--) {
    options.push({ value: year, label: year.toString() })
  }
  return options
})

// Function to handle year change for small charts
function onSmallChartYearChange(year) {
  emit('small-chart-year-change', year)
}

const chartComponent = computed(() => {
  if (props.chartMode === 'bar') return Bar
  if (props.chartMode === 'line') return Line
  return Bar
})



// Orders Line Chart Data
const ordersChartData = computed(() => {
  // Sử dụng dữ liệu từ props hoặc fallback về mock data
  const labels = props.ordersData?.labels || ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12']
  const orders = props.ordersData?.data || [120, 190, 300, 500, 200, 300, 450, 600, 350, 400, 550, 700]
  
  return {
    labels: labels,
    datasets: [
      {
        label: 'Đơn hàng',
        data: orders,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }
    ]
  }
})

// Revenue Chart Data
const revenueChartData = computed(() => {
  // Sử dụng dữ liệu từ props hoặc fallback về mock data
  const labels = props.revenueData?.labels || ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12']
  const revenue = props.revenueData?.data || [1200000, 1900000, 3000000, 5000000, 2000000, 3000000, 4500000, 6000000, 3500000, 4000000, 5500000, 7000000]
  
  return {
    labels: labels,
    datasets: [
      {
        label: 'Doanh thu',
        data: revenue,
        borderColor: '#f59e0b',
        backgroundColor: 'rgba(245, 158, 11, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }
    ]
  }
})

// Users Chart Data
const usersChartData = computed(() => {
  const total = props.usersData?.total || 0
  const active = props.usersData?.active || 0
  const inactive = props.usersData?.inactive || 0
  
  return {
    labels: ['Hoạt động', 'Không hoạt động'],
    datasets: [
      {
        data: [active, inactive],
        backgroundColor: ['#10b981', '#6b7280'],
        borderWidth: 0,
        cutout: '70%'
      }
    ]
  }
})

const smallChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: 'white',
      bodyColor: 'white',
      borderColor: '#10b981',
      borderWidth: 1,
      cornerRadius: 8,
      callbacks: {
        label: (context) => {
          return `${context.parsed.y} đơn hàng`
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.1)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}

const smallRevenueOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: 'white',
      bodyColor: 'white',
      borderColor: '#f59e0b',
      borderWidth: 1,
      cornerRadius: 8,
      callbacks: {
        label: (context) => {
          const value = context.parsed.y
          return `${formatCurrency(value)}`
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        callback: (value) => formatCurrency(value)
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}

const usersChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        padding: 10,
        font: { size: 10 },
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
          const value = context.parsed
          const total = context.dataset.data.reduce((a, b) => a + b, 0)
          const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0
          return `${context.label}: ${value} người (${percentage}%)`
        }
      }
    }
  }
}

function formatCurrency(value) {
  if (!value && value !== 0) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value)
}

// SVG Icon Components
const ChartIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="18" y1="20" x2="18" y2="10"></line>
      <line x1="12" y1="20" x2="12" y2="4"></line>
      <line x1="6" y1="20" x2="6" y2="14"></line>
    </svg>
  `
}



const OrdersIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
      <line x1="3" y1="6" x2="21" y2="6"></line>
      <path d="M16 10a4 4 0 0 1-8 0"></path>
    </svg>
  `
}

const RevenueIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="12" y1="1" x2="12" y2="23"></line>
      <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
    </svg>
  `
}

const UsersIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
      <circle cx="9" cy="7" r="4"></circle>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
    </svg>
  `
}
</script> 