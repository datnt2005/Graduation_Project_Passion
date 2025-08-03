<template>
  <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
    <div class="max-w-full overflow-x-auto">
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Báo cáo đánh giá sản phẩm của bạn</h1>
      </div>

      <!-- Bộ lọc -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 mb-4 rounded">
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Sắp xếp:</label>
          <select v-model="sortOrder" @change="applyFilters" class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8">
            <option value="desc">Mới nhất</option>
            <option value="asc">Cũ nhất</option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-600">Trạng thái:</label>
          <select v-model="filterStatus" @change="applyFilters"
            class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8">
            <option value="">Tất cả</option>
            <option value="pending">Chờ xử lý</option>
            <option value="resolved">Đã ẩn</option>
            <option value="dismissed">Đã bỏ qua</option>
          </select>
        </div>
      </div>

      <!-- Bảng dữ liệu -->
      <table class="min-w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-white border-b border-gray-300">
          <tr>
            <th class="border px-3 py-2">#</th>
            <th class="border px-3 py-2">Sản phẩm</th>
            <th class="border px-3 py-2">Người đánh giá</th>
            <th class="border px-3 py-2">Nội dung</th>
            <th class="border px-3 py-2">Lý do</th>
            <th class="border px-3 py-2">Người báo cáo</th>
            <th class="border px-3 py-2">Trạng thái</th>
            <th class="border px-3 py-2">Ngày</th>
            <th class="border px-3 py-2">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in reports" :key="item.report_id" :class="{ 'bg-gray-50': index % 2 === 0 }">
            <td class="border px-3 py-2">{{ index + 1 }}</td>
            <td class="border px-3 py-2">{{ item.review.product_name }}</td>
            <td class="border px-3 py-2">{{ item.review.user_name }}</td>
            <td class="border px-3 py-2 truncate max-w-[200px]">{{ item.review.content }}</td>
            <td class="border px-3 py-2">{{ reasonLabel(item.reason) }}</td>
            <td class="border px-3 py-2">{{ item.reporter }}</td>
            <td class="border px-3 py-2"><span :class="badgeClass(item.status)">{{ statusText(item.status) }}</span>
            </td>
            <td class="border px-3 py-2">{{ formatDate(item.reported_at) }}</td>
            <td class="border px-3 py-2">
              <button @click="viewReport(item.report_id)" class="flex items-center gap-1 text-blue-600 hover:underline">
                <Eye class="w-4 h-4" /> Xem
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRuntimeConfig } from '#app'
import { useToast } from '@/composables/useToast'
import { Eye } from 'lucide-vue-next'

const { toast } = useToast()
definePageMeta({ layout: 'default-seller' })

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const reports = ref([])
const allReports = ref([])
const sortOrder = ref('desc')
const filterStatus = ref('')

const reasons = [
  { value: 'offensive', label: 'Thô tục' },
  { value: 'image', label: 'Hình ảnh phản cảm' },
  { value: 'duplicate', label: 'Trùng lặp' },
  { value: 'personal', label: 'Thông tin cá nhân' },
  { value: 'ads', label: 'Quảng cáo' },
  { value: 'wrong', label: 'Thông tin sai lệch' },
  { value: 'other', label: 'Khác' },
]

const reasonLabel = code => reasons.find(r => r.value === code)?.label || code

function formatDate(d) {
  return new Date(d).toLocaleString('vi-VN')
}

const statusText = s =>
  s === 'pending' ? 'Chờ xử lý' : s === 'resolved' ? 'Đã ẩn' : 'Đã bỏ qua'

const badgeClass = s =>
  s === 'pending'
    ? 'px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded'
    : s === 'resolved'
      ? 'px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded'
      : 'px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded'

const fetchReports = async () => {
  try {
    const res = await axios.get(`${apiBase}/seller/reports/reviews`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` }
    })
    allReports.value = res.data.data
    applyFilters()
  } catch (err) {
    toast('error', 'Lỗi khi tải báo cáo')
  }
}

const applyFilters = () => {
  let filtered = [...allReports.value]
  if (filterStatus.value) {
    filtered = filtered.filter(r => r.status === filterStatus.value)
  }
  filtered.sort((a, b) => {
    const da = new Date(a.reported_at)
    const db = new Date(b.reported_at)
    return sortOrder.value === 'asc' ? da - db : db - da
  })
  reports.value = filtered
}

const viewReport = id => navigateTo(`/seller/reports/reviews/view/${id}`)

onMounted(fetchReports)
</script>

<style scoped>
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
