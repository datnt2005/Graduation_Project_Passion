<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a] ">
      <div class="max-w-7xl mx-auto md:pt-6 md:pb-6 flex flex-col md:flex-row gap-6">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200 md:w-64 mb-4 md:mb-0" />
      <main class="flex-1 p-0 md:p-4">
        <div class=" mx-auto">
          <h2 class="text-2xl text-center sm:text-3xl font-extrabold text-gray-900 mb-2 text-left">Kho Voucher</h2>


          <!-- Tabs (chỉ còn Tất cả) + nút sắp xếp + tìm kiếm -->
          <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-2">
            <div class="flex flex-wrap gap-2 text-sm font-medium items-center">
              <button v-for="tab in tabs" :key="tab.value" @click="selectedTab = tab.value; currentPage = 1"
                :class="['px-3 py-1 rounded-full border transition', selectedTab === tab.value ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-200 hover:bg-blue-50']">
                {{ tab.label }}
              </button>
            </div>
            <!-- Nút sắp xếp -->
            <div class="flex flex-wrap gap-2 text-sm font-medium items-center">
              <span class="text-gray-600">Sắp xếp:</span>
              <button v-for="opt in sortOptions" :key="opt.value" @click="selectedSort = opt.value"
                :class="['px-3 py-1 rounded-full border transition', selectedSort === opt.value ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-200 hover:bg-blue-50']">
                {{ opt.label }}
              </button>
            </div>
            <!-- Ô tìm kiếm -->
            <div class="flex-1 flex justify-end">
              <input v-model="searchKeyword" type="text" placeholder="Tìm kiếm theo tên hoặc mã voucher..." class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm" />
            </div>
          </div>
          <!-- Danh sách voucher -->
          <div v-if="loading" class="flex flex-col items-center justify-center bg-white border border-gray-200 p-8 rounded-lg shadow-md mt-6">
            <span class="text-gray-500 text-sm">Đang tải voucher...</span>
          </div>
          <div v-else-if="paginatedVouchers.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
              v-for="voucher in paginatedVouchers"
              :key="voucher.id"
              class="relative flex items-center bg-white rounded-lg shadow border border-gray-200 px-4 py-3 gap-4 hover:shadow-md transition min-h-[90px]"
            >
              <!-- Nút xoá ở góc trên bên phải -->
              <button
                @click="handleDeleteVoucher(voucher.id)"
                title="Xoá mã giảm giá"
                class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition z-10"
                style="font-size: 16px;"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <!-- Logo -->
              <div class="flex flex-col items-center justify-center min-w-[60px]">
                <img
                  :src="imageVoucher" alt="Voucher Logo"
                  class="w-12 h-12 object-contain mb-1"
                />
                <div v-if="voucher.seller" class="text-[10px] font-bold text-blue-700 bg-blue-100 rounded px-1 py-0.5">{{ voucher.seller.store_name }} </div>
                <div v-else class="text-[10px] font-bold text-blue-700 bg-blue-100 rounded px-1 py-0.5">PASSION VIP</div>
              </div>
              <!-- Nội dung -->
              <div class="flex-1 flex flex-col justify-between min-w-0">
                <div class="flex flex-wrap items-center gap-1 mb-1">
                  <span v-if="voucher.level" class="bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded font-semibold">{{ voucher.level }}</span>
                  <span v-if="voucher.level2" class="bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded font-semibold">{{ voucher.level2 }}</span>
                </div>
                <div class="font-semibold text-sm text-gray-900 truncate">{{ voucher.name }}</div>
                <div class="text-xs text-gray-600 truncate">{{ voucher.description }}</div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-1">
                  <span v-if="voucher.discount_type === 'percentage'">Giảm {{ formatDiscountValue(voucher.discount_value) }}% tối đa {{ formatCurrency(voucher.max_discount || 0) }}</span>
                  <span v-else>Giảm {{ formatCurrency(voucher.discount_value) }}</span>
                  <span>Đơn từ {{ formatCurrency(voucher.min_order_value) }}</span>
                </div>
                <div v-if="voucher.products.length > 0" class="text-xs text-blue-500 mt-1 w-full">Sản phẩm nhất định</div>
                <div class="text-xs text-gray-500 w-full mb-1 mt-3">HSD: {{ formatDate(voucher.end_date) }}</div>
                <!-- Nút Dùng ngay ở dưới cùng -->
                <div class="flex justify-end mt-2">
                  <button
                    @click="goToCheckout(voucher.code)"
                    class="bg-blue-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-blue-600 transition w-full sm:w-auto"
                    style="min-width: 80px;"
                  >Dùng ngay</button>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="flex flex-col items-center justify-center bg-white border border-gray-200 p-8 rounded-lg shadow-md mt-6">
            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="No vouchers" class="w-24 sm:w-32 h-24 sm:h-32 mb-4" />
            <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">Chưa có voucher</h3>
            <p class="text-gray-500 text-sm text-center">Bạn chưa có voucher nào thuộc trạng thái này.</p>
          </div>
          <!-- Pagination controls -->
          <div v-if="!loading && totalPages > 1" class="flex justify-center mt-6">
            <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&lt;</button>
            <button v-for="page in totalPages" :key="page" @click="currentPage = page" :class="['px-3 py-1 mx-1 rounded border', currentPage === page ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300']">{{ page }}</button>
            <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&gt;</button>
          </div>
        </div>
        <!-- Confirm dialog giống cart -->
        <Teleport to="body">
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="showConfirmDialog" class="fixed inset-0 z-50 overflow-y-auto">
              <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfirmDialog"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                  <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                      <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                      </div>
                      <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">{{ confirmDialogTitle }}</h3>
                        <div class="mt-2">
                          <p class="text-sm text-gray-500">{{ confirmDialogMessage }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm" @click="handleConfirmAction">Xác nhận</button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="closeConfirmDialog">Hủy</button>
                  </div>
                </div>
              </div>
            </div>
          </Transition>
        </Teleport>

      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'
import { useDiscount } from '~/composables/useDiscount'
import { useRouter } from 'vue-router'
import { useNotification } from '~/composables/useNotification'
import { useToast } from '~/composables/useToast'
import imageVoucher from '~/images/voucher.png'
import { useHead } from '#imports'

useHead({
  title: 'Kho Voucher | Quản lý mã giảm giá',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})
// Chỉ giữ lại tab 'Tất cả'
const tabs = [
  { label: 'Tất cả', value: 'all' },
]
const selectedTab = ref('all')
const searchKeyword = ref('')

// Sắp xếp
const sortOptions = [
  { label: 'Mới nhất', value: 'newest' },
  { label: 'Cũ nhất', value: 'oldest' },
  { label: 'Gần đây nhất', value: 'recent' },
  { label: 'A-Z', value: 'az' },
  { label: 'Z-A', value: 'za' },
]
const selectedSort = ref('newest')

const { discounts, saveVoucherByCode, loading, error, fetchMyVouchers, deleteUserCoupon } = useDiscount()
const { showNotification } = useNotification()
const { toast } = useToast()

const vouchers = computed(() => discounts.value)

// Lọc theo từ khoá tìm kiếm
const filteredVouchers = computed(() => {
  let list = vouchers.value
  if (searchKeyword.value.trim()) {
    const kw = searchKeyword.value.trim().toLowerCase()
    list = list.filter(v =>
      (v.name && v.name.toLowerCase().includes(kw)) ||
      (v.code && v.code.toLowerCase().includes(kw))
    )
  }
  // Sắp xếp
  switch (selectedSort.value) {
    case 'newest':
      list = [...list].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      break
    case 'oldest':
      list = [...list].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
      break
    case 'recent':
      list = [...list].sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at))
      break
    case 'az':
      list = [...list].sort((a, b) => (a.name || '').localeCompare(b.name || ''))
      break
    case 'za':
      list = [...list].sort((a, b) => (b.name || '').localeCompare(a.name || ''))
      break
  }
  return list
})

// Pagination
const pageSize = 6
const currentPage = ref(1)
const totalPages = computed(() => Math.ceil(filteredVouchers.value.length / pageSize))
const paginatedVouchers = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredVouchers.value.slice(start, start + pageSize)
})

const alertMsg = ref('')
const alertType = ref('')

const router = useRouter()

// Confirm dialog state
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const confirmAction = ref(null)

const openConfirmDialog = (title, message, action) => {
  confirmDialogTitle.value = title
  confirmDialogMessage.value = message
  confirmAction.value = action
  showConfirmDialog.value = true
}
const closeConfirmDialog = () => {
  showConfirmDialog.value = false
  confirmAction.value = null
}
const handleConfirmAction = async () => {
  if (confirmAction.value) await confirmAction.value()
  closeConfirmDialog()
}

const handleSaveVoucher = async () => {
  if (!voucherCode.value) return
  const res = await saveVoucherByCode(voucherCode.value)
  if (res.success) {
    alertMsg.value = res.message
    alertType.value = 'success'
    voucherCode.value = ''
    await fetchMyVouchers()
    currentPage.value = 1 // reset về trang đầu khi thêm mới
    toast('success', res.message || 'Lưu voucher thành công')
  } else {
    alertMsg.value = res.message
    alertType.value = 'error'
    toast('error', res.message || 'Lưu voucher thất bại')
  }
  setTimeout(() => { alertMsg.value = '' }, 3000)
}

const handleDeleteVoucher = (id) => {
  openConfirmDialog(
    'Xác nhận xoá',
    'Bạn có chắc muốn xoá mã giảm giá này?',
    async () => {
      const res = await deleteUserCoupon(id)
      if (res.success) {
        alertMsg.value = res.message
        alertType.value = 'success'
        toast('success', res.message || 'Đã xoá voucher thành công')
        await fetchMyVouchers()
      } else {
        alertMsg.value = res.message
        alertType.value = 'error'
        toast('error', res.message || 'Xoá voucher thất bại')
      }
      setTimeout(() => { alertMsg.value = '' }, 3000)
    }
  )
}

function goToCheckout(code) {
  if (!code) return
  router.push({ path: '/checkout', query: { voucher: code } })
}

onMounted(() => {
  fetchMyVouchers()
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
function formatCurrency(val) {
  if (!val) return '0₫'
  return Number(val).toLocaleString('vi-VN') + '₫'
}

const showAvailableModal = ref(false)
const availableDiscounts = ref([])
const availableLoading = ref(false)
const availableSearch = ref('')
const availablePage = ref(1)
const availablePageSize = 5

const fetchAvailableDiscounts = async () => {
  availableLoading.value = true
  try {
    const res = await fetch(`${useRuntimeConfig().public.apiBaseUrl}/discounts/all`, {
      headers: {
        'Accept': 'application/json'
      }
    })
    const data = await res.json()
    if (res.ok && data.data) {
      const savedIds = discounts.value.map(d => d.id)
      availableDiscounts.value = data.data.filter(
        d => d.status === 'active' && new Date(d.end_date) > new Date() && !savedIds.includes(d.id)
      )
    } else {
      availableDiscounts.value = []
    }
  } catch (e) {
    availableDiscounts.value = []
  } finally {
    availableLoading.value = false
  }
}

const filteredAvailableDiscounts = computed(() => {
  if (!availableSearch.value.trim()) return availableDiscounts.value
  const kw = availableSearch.value.trim().toLowerCase()
  return availableDiscounts.value.filter(v =>
    (v.name && v.name.toLowerCase().includes(kw)) ||
    (v.code && v.code.toLowerCase().includes(kw))
  )
})

const availableTotalPages = computed(() => Math.ceil(filteredAvailableDiscounts.value.length / availablePageSize))
const paginatedAvailableDiscounts = computed(() => {
  const start = (availablePage.value - 1) * availablePageSize
  return filteredAvailableDiscounts.value.slice(start, start + availablePageSize)
})

watch([availableSearch, filteredAvailableDiscounts], () => {
  availablePage.value = 1
})

const openAvailableModal = async () => {
  await fetchAvailableDiscounts()
  showAvailableModal.value = true
}
const closeAvailableModal = () => {
  showAvailableModal.value = false
}
const handleSaveAvailableVoucher = async (code) => {
  const res = await saveVoucherByCode(code)
  if (res.success) {
    alertMsg.value = res.message
    alertType.value = 'success'
    await fetchMyVouchers()
    toast('success', res.message || 'Lưu voucher thành công')
    await fetchAvailableDiscounts()
  } else {
    alertMsg.value = res.message
    alertType.value = 'error'
    toast('error', res.message || 'Lưu voucher thất bại')
  }
  setTimeout(() => { alertMsg.value = '' }, 3000)
}

function formatDiscountValue(val) {
  if (!val) return '0'
  if (Number(val) % 1 === 0) return Number(val).toString()
  return Number(val).toFixed(1)
}
</script>

<style scoped>
@media (max-width: 640px) {
  .grid {
    grid-template-columns: 1fr !important;
  }
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>