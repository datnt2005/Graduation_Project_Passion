<template>
  <div class="overflow-x-auto">
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 min-w-full">
    <!-- Thẻ thống kê -->
    <div class="bg-white p-4 rounded shadow text-center">
      <h3 class="text-gray-500 text-sm">Tổng Người Dùng</h3>
      <p class="text-xl font-bold">2,500</p>
      <p class="text-green-500 text-xs">+4% so với tuần trước</p>
    </div>

    <div class="bg-white p-4 rounded shadow text-center">
      <h3 class="text-gray-500 text-sm">Tổng đơn hàng</h3>
      <p class="text-xl font-bold">123.50 Đơn</p>
      <p class="text-green-500 text-xs">+7% so với tuần trước</p>
    </div>

    <div class="bg-white p-4 rounded shadow text-center">
      <h3 class="text-gray-500 text-sm">Tổng kênh bán hàng</h3>
      <p class="text-xl font-bold">325</p>
      <p class="text-green-500 text-xs">+34% so với tuần trước</p>
    </div>

    <div class="bg-white p-4 rounded shadow text-center">
      <h3 class="text-gray-500 text-sm">Doanh Thu Từ Người bán</h3>
      <p class="text-xl font-bold text-green-600">2,500</p>
      <p class="text-green-500 text-xs">+34% so với tuần trước</p>
    </div>

    <div class="bg-white p-4 rounded shadow text-center">
      <h3 class="text-gray-500 text-sm">Tổng Doanh Thu</h3>
      <p class="text-xl font-bold text-red-600">4,567</p>
      <p class="text-red-500 text-xs">-18% so với tuần trước</p>
    </div>

    <div class="bg-white p-4 rounded shadow text-center">
      <h3 class="text-gray-500 text-sm">Tổng Thu Thập</h3>
      <p class="text-xl font-bold">2,315</p>
      <p class="text-green-500 text-xs">+8% so với tuần trước</p>
    </div>
  </div>
</div>

      <!-- Biểu đồ doanh thu dạng cột -->
  <div class="bg-white p-4 sm:p-6 rounded shadow mt-6 w-full overflow-x-auto">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
    <h2 class="text-lg sm:text-xl font-semibold text-gray-700">Biểu đồ doanh thu</h2>
    <div class="mt-2 md:mt-0 flex flex-wrap items-center gap-2">
      <label for="filter" class="text-sm text-gray-600">Lọc theo:</label>
      <select
        id="filter"
        v-model="selectedFilter"
        class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:ring-blue-200"
      >
        <option value="day">Ngày</option>
        <option value="week">Tuần</option>
        <option value="month">Tháng</option>
        <option value="year">Năm</option>
      </select>
    </div>
  </div>

  <div class="h-[300px] sm:h-[400px] min-w-[600px]">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
  </div>

<!-- Bảng xếp hạng sản phẩm -->
<div class="bg-white p-4 sm:p-6 rounded shadow mt-6 w-full overflow-x-auto">
  <h2 class="text-lg sm:text-xl font-semibold text-gray-700">Danh sách tồn kho</h2>

  <!-- Bộ lọc -->
  <div class="flex flex-wrap gap-3 mt-4">
    <input v-model="filters.keyword" type="text" placeholder="Tìm theo tên hoặc mã SP"
      class="border p-2 rounded flex-1 min-w-[150px] sm:min-w-[200px]">

    <select v-model="filters.category"
      class="border p-2 rounded flex-1 min-w-[150px] sm:min-w-[180px]">
      <option value="">Tất cả danh mục</option>
      <option value="Đồ gia dụng">Đồ gia dụng</option>
      <option value="Thực phẩm">Thực phẩm</option>
    </select>

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
  </div>

  <!-- Bảng dữ liệu -->
  <div class="overflow-x-auto mt-4">
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
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.code }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.name }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.category }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.variant }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.costPrice }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.sellPrice }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ item.status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

</template>


<script setup>
// imports
import { ref, computed } from 'vue'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js'
import { Bar } from 'vue-chartjs'
// Đăng ký Chart.js components
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)
// Lọc theo tháng (có thể mở rộng sau này gọi API)
const selectedFilter = ref('month')
// Dữ liệu mẫu
const allData = {
  day: {
    labels: ['01', '02', '03', '04', '05', '06', '07'],
    revenue: [20, 25, 18, 30, 22, 27, 19],
    profit: [2, -1, 1, 5, -2, 3, 1],
  },
  week: {
    labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
    revenue: [100, 120, 90, 150],
    profit: [20, -10, 30, 50],
  },
  month: {
    labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4'],
    revenue: [120, 150, 170, 200],
    profit: [20, -10, 30, 50],
  },
  year: {
    labels: ['2021', '2022', '2023', '2024'],
    revenue: [1200, 1350, 1600, 1800],
    profit: [200, -100, 300, 400],
  },
};
// Tạo chart data phản ứng với selectedFilter
const chartData = computed(() => {
  const current = allData[selectedFilter.value]
  return {
    labels: current.labels,
    datasets: [
      {
        label: 'Doanh thu',
        data: current.revenue,
        backgroundColor: '#3b82f6',
        borderRadius: 6,
        barThickness: 30,
      },
      {
        label: 'Lời / Lỗ',
        data: current.profit,
        backgroundColor: current.profit.map((v) => (v >= 0 ? '#10b981' : '#ef4444')),
        borderRadius: 6,
        barThickness: 30,
      },
    ],
  }
});

// Cấu hình biểu đồ
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
    },
    tooltip: {
      callbacks: {
        label: (context) => {
          return `${context.dataset.label}: ${context.parsed.y} triệu`
        },
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value) => `${value} triệu`,
      },
    },
  },
}

// tạo dữ liệu tồn kho 
const dataStock = ref([
  { id: 1, code: '#001', name:'Nồi cơm điện nhật', category: 'Đồ gia dụng', quantity: 20, variant: 'màu trắng', costPrice: '120.000 đ', sellPrice: '160.000 đ', status: 'Còn hàng' },
  { id: 2, code: '#002', name:'Bánh tráng', category: 'Thực phẩm', quantity: 10, variant: '500g', costPrice: '70.000 đ', sellPrice: '120.000 đ', status: 'Hết hàng' },
  { id: 3, code: '#003', name:'Máy xay', category: 'Đồ gia dụng', quantity: 25, variant: 'xám bạc', costPrice: '250.000 đ', sellPrice: '300.000 đ', status: 'Còn hàng' },
]);

const filters = ref({
  keyword: '',
  category: '',
  status: '',
  maxQuantity: '',
  minPrice: '',
  maxPrice: '',
});

const filteredData = computed(() => {
  return dataStock.value.filter(item => {
    const keyword = filters.value.keyword.toLowerCase();

    const matchKeyword =
      item.code.toLowerCase().includes(keyword) ||
      item.name.toLowerCase().includes(keyword);

    const matchCategory =
      !filters.value.category || item.category === filters.value.category;

    const matchStatus =
      !filters.value.status || item.status === filters.value.status;

    const matchQuantity =
      !filters.value.maxQuantity || item.quantity <= Number(filters.value.maxQuantity);

    const matchMinPrice =
      !filters.value.minPrice || item.sellPrice >= Number(filters.value.minPrice);

    const matchMaxPrice =
      !filters.value.maxPrice || item.sellPrice <= Number(filters.value.maxPrice);

    return matchKeyword && matchCategory && matchStatus && matchQuantity && matchMinPrice && matchMaxPrice;
  });
});


definePageMeta({
  layout: 'default-admin'
})
</script>

