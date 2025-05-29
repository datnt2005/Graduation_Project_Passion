<template>
  <div class="bg-gray-100 text-gray-800 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow p-4 flex justify-between items-center">
      <h1 class="text-xl font-bold">Dashboard Người Bán</h1>
      <span class="text-sm text-gray-600">Xin chào, Pao Núi</span>
    </header>

    <!-- Main Content -->
    <main class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Tổng Quan Doanh Thu -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-2">Tổng quan doanh thu</h2>
        <p class="text-2xl font-bold text-green-600">12.000.000đ</p>
        <p class="text-sm text-gray-500">Tháng này</p>
      </div>

      <!-- Đơn hàng -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-2">Đơn hàng</h2>
        <ul class="text-sm">
          <li>Đơn mới: <strong class="text-blue-600">12</strong></li>
          <li>Đang giao: <strong class="text-yellow-600">8</strong></li>
          <li>Đã hoàn tất: <strong class="text-green-600">60</strong></li>
          <li>Bị hủy: <strong class="text-red-600">3</strong></li>
        </ul>
      </div>

      <!-- Phí & Thuế bị trừ -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-2">Phí & Thuế</h2>
        <ul class="text-sm">
          <li>Tổng doanh thu: <strong>12.000.000đ</strong></li>
          <li>Phí nền tảng (10%): <strong>-1.200.000đ</strong></li>
          <li>Thuế VAT (8%): <strong>-960.000đ</strong></li>
          <li class="text-green-700 font-bold mt-2">Thực nhận: 9.840.000đ</li>
        </ul>
      </div>

      <!-- Sản phẩm bán chạy -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-2">Sản phẩm bán chạy</h2>
        <ul class="text-sm list-disc pl-4">
          <li>Sữa chua Pao – 120 đơn</li>
          <li>Bò một nắng – 80 đơn</li>
          <li>Mít sấy – 60 đơn</li>
        </ul>
      </div>

      <!-- Đánh giá gần đây -->
      <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-2">
        <h2 class="text-lg font-semibold mb-2">Đánh giá mới</h2>
        <div class="space-y-2 text-sm">
          <div class="border-b pb-2">
            <p class="font-medium">Khách A:</p>
            <p>Rất hài lòng với sản phẩm!</p>
          </div>
          <div class="border-b pb-2">
            <p class="font-medium">Khách B:</p>
            <p>Giao hàng nhanh, chất lượng tốt.</p>
          </div>
        </div>
      </div>

      <!-- Biểu đồ doanh thu -->
      <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-2 ">
        <h2 class="text-lg font-semibold mb-4">Biểu đồ doanh thu</h2>
        <div class="mb-4">
          <label for="filter" class="mr-2">Lọc theo:</label>
          <select id="filter" v-model="selectedFilter" class="border rounded px-2 py-1">
            <option value="day">Ngày</option>
            <option value="week">Tuần</option>
            <option value="month">Tháng</option>
            <option value="year">Năm</option>
          </select>
        </div>
        <Line :data="chartData" :options="chartOptions" />
      </div>

      <!-- Bảng xếp hạng sản phẩm -->
      <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-2">
        <h2 class="text-lg font-semibold mb-2">Bảng xếp hạng sản phẩm</h2>
        <table class="w-full text-sm text-left border border-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="border px-4 py-2">STT</th>
              <th class="border px-4 py-2">Sản phẩm</th>
              <th class="border px-4 py-2">Đơn hàng</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="border px-4 py-2">1</td>
              <td class="border px-4 py-2">Sữa chua Pao</td>
              <td class="border px-4 py-2">120</td>
            </tr>
            <tr>
              <td class="border px-4 py-2">2</td>
              <td class="border px-4 py-2">Bò một nắng</td>
              <td class="border px-4 py-2">80</td>
            </tr>
            <tr>
              <td class="border px-4 py-2">3</td>
              <td class="border px-4 py-2">Mít sấy</td>
              <td class="border px-4 py-2">60</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- // tồn kho  -->

      <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-2">
        <h2 class="text-lg font-semibold mb-2">Tốn kho</h2>
        <table class="w-full text-sm text-left border border-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="border px-4 py-2">STT</th>
              <th class="border px-4 py-2">Sản phẩm</th>
              <th class="border px-4 py-2">Số lượng</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="border px-4 py-2">1</td>
              <td class="border px-4 py-2">Sữa chua Pao</td>
              <td class="border px-4 py-2">120</td>
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
      borderColor: 'rgb(75, 192, 192)',
      backgroundColor: 'rgba(75, 192, 192, 0.2)'
    }
  ]
}))

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { position: 'top' },
    title: { display: true, text: 'Biểu đồ doanh thu' }
  }
}
</script>
