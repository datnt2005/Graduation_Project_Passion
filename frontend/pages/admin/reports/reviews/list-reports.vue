<template>
    <div class="bg-gray-100 text-gray-700 font-sans min-h-screen">
        <div class="max-w-full overflow-x-auto">
            <!-- Header -->
            <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
                <h1 class="text-xl font-semibold text-gray-800">Quản lý đánh giá bị báo cáo</h1>
            </div>
            <!-- Bộ lọc nâng cao -->
            <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 mb-4 rounded">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-600">Sắp xếp:</label>
                    <select v-model="sortOrder" @change="applyFilters"
                        class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="desc">Mới nhất</option>
                        <option value="asc">Cũ nhất</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-600">Trạng thái:</label>
                    <select v-model="filterStatus" @change="applyFilters"
                        class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tất cả</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="resolved">Đã ẩn</option>
                        <option value="rejected">Đã bỏ qua</option>
                    </select>
                </div>
            </div>


            <!-- Bảng báo cáo -->
            <table class="min-w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-white border-b border-gray-300">
                    <tr>
                        <th class="border px-3 py-2 font-semibold text-left">#</th>
                        <th class="border px-3 py-2 font-semibold text-left">Sản phẩm</th>
                        <th class="border px-3 py-2 font-semibold text-left">Người đánh giá</th>
                        <th class="border px-3 py-2 font-semibold text-left">Nội dung</th>
                        <th class="border px-3 py-2 font-semibold text-left">Lý do báo cáo</th>
                        <th class="border px-3 py-2 font-semibold text-left">Người báo cáo</th>
                        <th class="border px-3 py-2 font-semibold text-left">Trạng thái</th>
                        <th class="border px-3 py-2 font-semibold text-left">Ngày báo cáo</th>
                        <th class="border px-3 py-2 font-semibold text-left">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in reports" :key="item.report_id"
                        :class="{ 'bg-gray-50': index % 2 === 0 }">

                        <td class="border px-3 py-2">{{ index + 1 }}</td>
                        <td class="border px-3 py-2">{{ item.review.product_name }}</td>
                        <td class="border px-3 py-2">{{ item.review.user_name }}</td>
                        <td class="border px-3 py-2 truncate max-w-[200px]">{{ item.review.content }}</td>
                        <td class="border px-3 py-2">{{ reasonLabel(item.reason) }}</td>
                        <td class="border px-3 py-2">{{ item.reporter }}</td>
                        <td class="border px-3 py-2">
                            <span :class="badgeClass(item.status)">
                                {{ statusText(item.status) }}
                            </span>
                        </td>
                        <td class="border px-3 py-2">{{ formatDate(item.reported_at) }}</td>
                        <td class="border px-3 py-2 relative">
                            <button @click.stop="toggleDropdown($event, item.report_id)"
                                class="p-1 text-gray-600 hover:text-black focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="reports.length === 0">
                        <td colspan="8" class="text-center py-4 text-gray-500">Không có báo cáo nào</td>
                    </tr>
                </tbody>
            </table>

            <Teleport to="body">
                <Transition enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                    <div v-if="activeDropdown !== null" class="fixed inset-0 z-50" @click="closeDropdown">
                        <div class="absolute bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 origin-top-right w-40"
                            :style="dropdownPosition" @click.stop>
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
import { useRuntimeConfig } from '#app'
import { Eye, Check, X } from 'lucide-vue-next'
import { secureAxios } from '@/utils/secureAxios'
import { useToast } from '~/composables/useToast'
import Swal from 'sweetalert2'

const { toast } = useToast()


definePageMeta({ layout: 'default-admin' })


//  Khai báo biến
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const reports = ref([])
const allReports = ref([])
const loading = ref(true)
const sortOrder = ref('desc')
const filterStatus = ref('')

// Dropdown thao tác
const activeDropdown = ref(null)
const dropdownPosition = ref({ top: '0px', left: '0px' })


//  Lý do báo cáo & hàm hỗ trợ
const reasons = [
  { value: 'offensive', label: 'Đánh giá thô tục phản cảm' },
  { value: 'image', label: 'Chứa hình ảnh phản cảm, khỏa thân, khiêu dâm' },
  { value: 'duplicate', label: 'Đánh giá trùng lặp (thông tin rác)' },
  { value: 'personal', label: 'Chứa thông tin cá nhân' },
  { value: 'ads', label: 'Quảng cáo trái phép' },
  { value: 'wrong', label: 'Đánh giá không chính xác / gây hiểu lầm' },
  { value: 'other', label: 'Vi phạm khác' }
]

const reasonLabel = (code) => {
  const found = reasons.find(r => r.value === code)
  return found ? found.label : code
}


//  Tải và lọc dữ liệu
const fetchReports = async () => {
  loading.value = true
  try {
    const res = await secureAxios(`${apiBase}/admin/reports/reviews`, {}, ['admin'])
    allReports.value = res.data.data
    applyFilters()
  } catch (error) {
    toast('error', 'Lỗi khi tải danh sách báo cáo')
    console.error('Lỗi khi tải danh sách báo cáo:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  let filtered = [...allReports.value]

  if (filterStatus.value) {
    filtered = filtered.filter(r => r.status === filterStatus.value)
  }

  filtered.sort((a, b) => {
    const dateA = new Date(a.reported_at)
    const dateB = new Date(b.reported_at)
    return sortOrder.value === 'asc' ? dateA - dateB : dateB - dateA
  })

  reports.value = filtered
}


//  Cập nhật trạng thái
const updateStatus = async (id, status) => {
  const statusLabels = {
    resolved: 'ẩn đánh giá',
    dismissed: 'bỏ qua báo cáo',
  }

  const confirmText = statusLabels[status] || 'cập nhật trạng thái'

  const result = await Swal.fire({
    title: `Xác nhận ${confirmText}?`,
    text: `Bạn có chắc chắn muốn ${confirmText} này không?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy'
  })

  if (!result.isConfirmed) return

  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.put(`${apiBase}/admin/reports/reviews/${id}/status`, { status }, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })

    toast('success', `Đã ${statusLabels[status] || 'cập nhật'} thành công`)
    await fetchReports()
  } catch (err) {
    const message = err?.response?.data?.message || 'Lỗi khi cập nhật trạng thái'
    toast('error', message)
    console.error('Lỗi khi cập nhật trạng thái:', err)
  }
}

//  Dropdown thao tác
function toggleDropdown(event, id) {
  if (activeDropdown.value === id) {
    activeDropdown.value = null
    return
  }

  const rect = event.currentTarget.getBoundingClientRect()
  dropdownPosition.value = {
    top: `${rect.bottom + window.scrollY}px`,
    left: `${rect.left + window.scrollX - 160}px`
  }
  activeDropdown.value = id
}

function closeDropdown() {
  activeDropdown.value = null
}

function viewReport(id) {
  navigateTo(`/admin/reports/reviews/view/${id}`)
}


//  Helpers
const statusText = (status) => {
  switch (status) {
    case 'pending': return 'Chờ xử lý'
    case 'resolved': return 'Đã ẩn'
    case 'dismissed': return 'Đã bỏ qua'
    default: return 'Không rõ'
  }
}

const badgeClass = (status) => {
  switch (status) {
    case 'pending':
      return 'inline-block px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded'
    case 'resolved':
      return 'inline-block px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded'
    case 'dismissed':
      return 'inline-block px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded'
    default:
      return 'inline-block px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded'
  }
}

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleString('vi-VN')
}


//  Khởi tạo
onMounted(fetchReports)
</script>



<style scoped>
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>