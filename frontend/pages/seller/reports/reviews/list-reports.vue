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
            <td class="border px-3 py-2 relative">
              <button @click.stop="toggleDropdown($event, item.report_id)" class="p-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <Teleport to="body">
        <Transition enter-active-class="transition" leave-active-class="transition">
          <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
            <div class="absolute bg-white rounded shadow-lg ring-1 ring-black w-40 z-50" :style="dropdownPosition"
              @click.stop>
              <div class="py-1">
                <button @click="viewReport(activeDropdown); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <Eye class="w-4 h-4 mr-2" /> Xem
                </button>
                <button @click="updateStatus(activeDropdown, 'resolved'); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                  <Check class="w-4 h-4 mr-2" /> Ẩn
                </button>
                <button @click="updateStatus(activeDropdown, 'dismissed'); closeDropdown()"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                  <X class="w-4 h-4 mr-2" /> Bỏ qua
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useRuntimeConfig } from '#app'
import { Eye, Check, X } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'
const { toast } = useToast()

definePageMeta({ layout: 'default-seller' })

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const reports = ref([])
const allReports = ref([])
const sortOrder = ref('desc')
const filterStatus = ref('')

const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px' })

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

const statusText = (s) => {
  return s === 'pending' ? 'Chờ xử lý' : s === 'resolved' ? 'Đã ẩn' : 'Đã bỏ qua'
}

const badgeClass = (s) => {
  return s === 'pending'
    ? 'px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded'
    : s === 'resolved'
      ? 'px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded'
      : 'px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded'
}

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

const updateStatus = async (id, status) => {
  const label = status === 'resolved' ? 'ẩn đánh giá' : 'bỏ qua báo cáo'
  const confirm = await Swal.fire({
    title: `Xác nhận ${label}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy'
  })
  if (!confirm.isConfirmed) return

  try {
    await axios.put(`${apiBase}/seller/reports/reviews/${id}/status`, { status }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` }
    })
    toast('success', 'Cập nhật thành công')
    fetchReports()
  } catch (err) {
    toast('error', 'Không thể cập nhật trạng thái')
  }
}

const toggleDropdown = (e, id) => {
  if (activeDropdown.value === id) return activeDropdown.value = null
  const rect = e.currentTarget.getBoundingClientRect()
  dropdownPosition.value = {
    top: `${rect.bottom + window.scrollY}px`,
    left: `${rect.left + window.scrollX - 160}px`
  }
  activeDropdown.value = id
}

const closeDropdown = () => (activeDropdown.value = null)
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
