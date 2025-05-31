<template>
  <div class="bg-gray-50 text-gray-900 min-h-screen font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm p-4 flex justify-between items-center">
      <h1 class="text-2xl font-semibold text-gray-800">Dashboard Người Bán</h1>
      <div class="flex items-center space-x-4">
        <span class="text-sm text-gray-600">Xin chào, Pao Núi</span>
        <button class="text-sm text-blue-600 hover:underline">Đăng xuất</button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="p-6 grid grid-cols-1 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
      <!-- Card Tổng Quan Doanh Thu -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-medium text-gray-700 mb-3">Tổng quan doanh thu</h2>
        <p class="text-3xl font-bold text-green-600">12.000.000đ</p>
        <p class="text-sm text-gray-500 mt-1">Tháng này</p>
      </div>

      <!-- Card Đơn hàng -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-medium text-gray-700 mb-3">Đơn hàng</h2>
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div>
            <p class="text-gray-600">Đơn mới</p>
            <p class="font-semibold text-blue-600">12</p>
          </div>
          <div>
            <p class="text-gray-600">Đang giao</p>
            <p class="font-semibold text-yellow-600">8</p>
          </div>
          <div>
            <p class="text-gray-600">Đã hoàn tất</p>
            <p class="font-semibold text-green-600">60</p>
          </div>
          <div>
            <p class="text-gray-600">Bị hủy</p>
            <p class="font-semibold text-red-600">3</p>
          </div>
        </div>
      </div>

      <!-- Card Phí & Thuế -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-medium text-gray-700 mb-3">Phí & Thuế</h2>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">Tổng doanh thu</span>
            <span class="font-semibold">12.000.000đ</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Phí nền tảng (10%)</span>
            <span class="font-semibold text-red-600">-1.200.000đ</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Thuế VAT (8%)</span>
            <span class="font-semibold text-red-600">-960.000đ</span>
          </div>
          <div class="flex justify-between mt-2 pt-2 border-t">
            <span class="font-medium text-gray-700">Thực nhận</span>
            <span class="font-bold text-green-600">9.840.000đ</span>
          </div>
        </div>
      </div>

      <!-- Card Sản phẩm bán chạy -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-base font-medium text-gray-700 mb-3">Sản phẩm bán chạy</h2>
        <ul class="space-y-2 text-sm">
          <li class="flex justify-between">
            <span>Sữa chua Pao</span>
            <span class="font-semibold">120 đơn</span>
          </li>
          <li class="flex justify-between">
            <span>Bò một nắng</span>
            <span class="font-semibold">80 đơn</span>
          </li>
          <li class="flex justify-between">
            <span>Mít sấy</span>
            <span class="font-semibold">60 đơn</span>
          </li>
        </ul>
      </div>

      <!-- Card Đánh giá gần đây -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
        <h2 class="text-base font-medium text-gray-700 mb-3">Đánh giá mới</h2>
        <div class="space-y-4 text-sm">
          <div class="border-l-4 border-blue-500 pl-4">
            <p class="font-medium text-gray-800">Khách A</p>
            <p class="text-gray-600">Rất hài lòng với sản phẩm!</p>
          </div>
          <div class="border-l-4 border-blue-500 pl-4">
            <p class="font-medium text-gray-800">Khách B</p>
            <p class="text-gray-600">Giao hàng nhanh, chất lượng tốt.</p>
          </div>
        </div>
      </div>

      <!-- Card Biểu đồ doanh thu -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
        <h2 class="text-base font-medium text-gray-700 mb-3">Biểu đồ doanh thu</h2>
        <div class="mb-4">
          <label for="filter" class="text-sm text-gray-600 mr-2">Lọc theo:</label>
          <select id="filter" v-model="selectedFilter" class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="day">Ngày</option>
            <option value="week">Tuần</option>
            <option value="month">Tháng</option>
            <option value="year">Năm</option>
          </select>
        </div>
        <Line :data="chartData" :options="chartOptions" />
      </div>

      <!-- Card Bảng xếp hạng sản phẩm -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
        <h2 class="text-base font-medium text-gray-700 mb-3">Bảng xếp hạng sản phẩm</h2>
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-4 py-2">STT</th>
              <th class="px-4 py-2">Sản phẩm</th>
              <th class="px-4 py-2">Đơn hàng</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in topProducts" :key="index" class="border-t">
              <td class="px-4 py-2">{{ index + 1 }}</td>
              <td class="px-4 py-2">{{ item.name }}</td>
              <td class="px-4 py-2">{{ item.orders }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Card Tồn kho -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
        <h2 class="text-base font-medium text-gray-700 mb-3">Tồn kho</h2>
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-4 py-2">STT</th>
              <th class="px-4 py-2">Sản phẩm</th>
              <th class="px-4 py-2">Số lượng</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in inventory" :key="index" class="border-t">
              <td class="px-4 py-2">{{ index + 1 }}</td>
              <td class="px-4 py-2">{{ item.name }}</td>
              <td class="px-4 py-2">{{ item.quantity }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</template>

<script setup>
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement
} from 'chart.js'
import { Line } from 'vue-chartjs'
import { ref, computed } from 'vue'

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement)

definePageMeta({
  layout: 'default-seller'
})

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

const inventory = [
  { name: 'Sữa chua Pao', quantity: 120 }
]
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
</style>