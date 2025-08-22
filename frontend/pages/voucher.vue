<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="max-w-7xl mx-auto">
      <div class="text-sm text-gray-500 px-4 py-2 rounded">
        <NuxtLink to="/" class="text-gray-400">Trang chủ</NuxtLink>
        <span class="mx-1">›</span>
        <span class="text-black font-medium">Kho Voucher</span>
      </div>
    </div>
    <div class="max-w-7xl mx-auto md:pt-6 md:pb-6 p-4">
      <div class="mx-auto">
        <h2 class="text-2xl text-center sm:text-3xl font-extrabold text-gray-900 mb-2 text-left">Kho Voucher</h2>
        <!-- Hiệu ứng cánh rơi + chip nổi tạo sinh động -->
        <div class="relative my-3 flex justify-center items-center select-none">
          <div class="absolute inset-0 pointer-events-none overflow-hidden petal-layer">
            <span
              v-for="(p, i) in petals"
              :key="'p'+i"
              class="petal"
              :style="{
                left: p.left + '%',
                animationDuration: p.duration + 's',
                animationDelay: p.delay + 's',
                transform: `scale(${p.scale})`
              }"
            />
          </div>
          <div class="inline-flex gap-2 relative z-10">
            <span class="floating-chip">Voucher Hot 🔥</span>
            <span class="floating-chip delay-1">Freeship 🚚</span>
            <span class="floating-chip delay-2">Giảm sâu 💯</span>
          </div>
        </div>
        <!-- Lớp confetti khi thao tác thành công -->
        <div v-if="showConfetti" class="confetti-layer">
          <span v-for="(c, idx) in confettiBits" :key="'c'+idx" class="confetti"
            :style="{
              left: c.left + '%',
              background: c.color,
              animationDuration: c.duration + 's',
              width: c.size + 'px', height: c.size + 'px',
              animationDelay: c.delay + 's',
              transform: `rotate(${c.rotation}deg)`
            }"
          />
        </div>
                 <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-4">
           <div class="flex-1 flex items-center bg-white rounded border border-gray-200 px-3 py-2">
             <span class="text-gray-500 text-sm mr-2 whitespace-nowrap">Mã Voucher</span>
             <input v-model="voucherCode" type="text" placeholder="Nhập mã voucher tại đây" class="flex-1 outline-none border-none bg-transparent text-sm" />
           </div>
           <button :disabled="!voucherCode || loading" @click="handleSaveVoucher" class="bg-blue-500 text-white px-4 py-2 rounded font-semibold text-sm mt-2 sm:mt-0 disabled:opacity-50 disabled:cursor-not-allowed transition">Lưu</button>
         </div>
        <div v-if="alertMsg" :class="['mb-2 px-4 py-2 rounded', alertType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">{{ alertMsg }}</div>
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
                 <!-- Danh sách tất cả voucher -->
         <div v-if="loading" class="flex flex-col items-center justify-center bg-white border border-gray-200 p-8 rounded-lg shadow-md mt-6">
           <span class="text-gray-500 text-sm">Đang tải voucher...</span>
         </div>
         <div v-else-if="allVouchers.length > 0" class="relative min-h-[260px]">
          <!-- Wavy animated background behind the list -->
          <div class="waves-bg pointer-events-none absolute inset-0 overflow-hidden">
            <div class="wave layer1"></div>
            <div class="wave layer2"></div>
            <div class="wave layer3"></div>
          </div>
          <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
              v-for="(voucher, idx) in paginatedAllVouchers"
              :key="voucher.id"
              class="voucher-anim-card relative flex items-center bg-white rounded-lg shadow border border-gray-200 px-4 py-3 gap-4 transition min-h-[90px]"
              :style="{ animationDelay: (idx * 0.06) + 's' }"
            >
              <!-- Nút xoá chỉ hiện khi voucher đã lưu -->
              <button
                v-if="isVoucherSaved(voucher.id)"
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
                <div v-if="voucher.seller && voucher.seller.store_name" class="text-[10px] font-bold text-blue-700 bg-blue-100 rounded px-1 py-0.5">{{ voucher.seller.store_name }} </div>
                <div v-else class="text-[10px] font-bold text-blue-700 bg-blue-100 rounded px-1 py-0.5">PASSION VIP</div>
              </div>
              <!-- Nội dung -->
              <div class="flex-1 flex flex-col justify-between min-w-0">
                <div class="flex flex-wrap items-center gap-1 mb-1">
                  <span v-if="voucher.level" class="bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded font-semibold">{{ voucher.level }}</span>
                  <span v-if="voucher.level2" class="bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded font-semibold">{{ voucher.level2 }}</span>
                </div>
                <div class="font-semibold text-sm text-gray-900 truncate">{{ voucher.name || 'Không có tên' }}</div>
                <div class="text-xs text-gray-600 truncate">{{ voucher.description || '' }}</div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-1">
                  <span v-if="voucher.discount_type === 'percentage'">Giảm {{ formatDiscountValue(voucher.discount_value) }}% tối đa {{ formatCurrency(voucher.max_discount || 0) }}</span>
                  <span v-else>Giảm {{ formatCurrency(voucher.discount_value) }}</span>
                  <span>Đơn từ {{ formatCurrency(voucher.min_order_value) }}</span>
                </div>
                <div v-if="voucher.products && voucher.products.length > 0" class="text-xs text-blue-500 mt-1 w-full">Sản phẩm nhất định</div>
                <div class="text-xs text-gray-500 w-full mb-1 mt-3">HSD: {{ formatDate(voucher.end_date) }}</div>
                <!-- Nút thông minh -->
                <div class="flex justify-end mt-2">
                  <button
                    v-if="!isVoucherSaved(voucher.id)"
                    @click="handleSaveAvailableVoucher(voucher.code)"
                    class="bg-blue-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-blue-600 transition w-full sm:w-auto"
                    style="min-width: 80px;"
                  >Lưu</button>
                  <button
                    v-else
                    @click="goToCheckout(voucher.code)"
                    class="bg-green-500 text-white px-3 py-1 rounded text-xs font-semibold hover:bg-green-600 transition w-full sm:w-auto"
                    style="min-width: 80px;"
                  >Dùng ngay</button>
                </div>
              </div>
            </div>
          </div>
        </div>
                 <div v-else class="flex flex-col items-center justify-center bg-white border border-gray-200 p-8 rounded-lg shadow-md mt-6">
           <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="No vouchers" class="w-24 sm:w-32 h-24 sm:h-32 mb-4" />
           <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">Chưa có voucher</h3>
           <p class="text-gray-500 text-sm text-center">Không có voucher nào có sẵn.</p>
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
      <!-- Available discounts modal -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="showAvailableModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
              <button @click="closeAvailableModal" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <h3 class="text-lg font-bold mb-4">Danh sách mã giảm giá</h3>
              <input v-model="availableSearch" type="text" placeholder="Tìm kiếm theo tên hoặc mã..." class="w-full mb-4 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm" />
              <div v-if="availableLoading" class="text-center text-gray-500 py-8">Đang tải...</div>
              <div v-else>
                                  <div v-if="filteredAvailableDiscounts.length === 0" class="text-center text-gray-500 py-8">Không còn mã giảm giá nào để lưu.</div>
                  <div v-else>
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                      <div v-for="voucher in paginatedAvailableDiscounts" :key="voucher.id" class="border rounded p-3 flex flex-col sm:flex-row sm:items-center justify-between">
                        <div>
                          <div class="font-semibold text-blue-700">{{ voucher.name }}</div>
                          <div class="text-xs text-gray-500 mb-1">Mã: <span class="font-mono">{{ voucher.code }}</span></div>
                          <div class="text-xs text-gray-500 mb-1">Hiệu lực: {{ formatDate(voucher.start_date) }} - {{ formatDate(voucher.end_date) }}</div>
                          <div class="text-xs text-gray-500">{{ voucher.description }}</div>
                          <div class="text-xs text-gray-500">
                            <span v-if="voucher.discount_type === 'percentage'">Giảm {{ formatDiscountValue(voucher.discount_value) }}%</span>
                            <span v-else>Giảm {{ formatCurrency(voucher.discount_value) }}</span>
                            <span v-if="voucher.min_order_value"> - Đơn từ {{ formatCurrency(voucher.min_order_value) }}</span>
                          </div>
                        </div>
                        <div class="flex flex-col gap-2 mt-2 sm:mt-0">
                          <button 
                            v-if="!isVoucherSaved(voucher.id)" 
                            @click="handleSaveAvailableVoucher(voucher.code)" 
                            class="px-3 py-1 bg-blue-500 text-white rounded text-xs font-semibold hover:bg-blue-600 transition"
                          >
                            Lưu
                          </button>
                          <button 
                            v-else 
                            @click="goToCheckout(voucher.code)" 
                            class="px-3 py-1 bg-green-500 text-white rounded text-xs font-semibold hover:bg-green-600 transition"
                          >
                            Dùng ngay
                          </button>
                        </div>
                      </div>
                    </div>
                  <div v-if="availableTotalPages > 1" class="flex justify-center mt-4">
                    <button @click="availablePage--" :disabled="availablePage === 1" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&lt;</button>
                    <button v-for="page in availableTotalPages" :key="page" @click="availablePage = page" :class="['px-3 py-1 mx-1 rounded border', availablePage === page ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300']">{{ page }}</button>
                    <button @click="availablePage++" :disabled="availablePage === availableTotalPages" class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 disabled:opacity-50">&gt;</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useDiscount } from '~/composables/useDiscount'
import { useRouter } from 'vue-router'
import { useNotification } from '~/composables/useNotification'
import { useToast } from '~/composables/useToast'
import imageVoucher from '~/images/voucher.png'
import { useHead } from '#imports'

useHead({
  title: 'Kho Voucher',
  meta: [
    { name: 'description', content: 'Kho Voucher, tràn ngập mã giảm giá, miễn phí vận chuyển. Nhận ngay!' }
  ]
})
// Hiệu ứng cánh rơi
const petals = Array.from({ length: 20 }).map(() => ({
  left: Math.random() * 100,
  duration: 8 + Math.random() * 8,
  delay: Math.random() * 4,
  scale: 0.6 + Math.random() * 0.8
}))

// Confetti state
const showConfetti = ref(false)
const confettiBits = ref([])
const triggerConfetti = () => {
  confettiBits.value = Array.from({ length: 30 }).map(() => ({
    left: Math.random() * 100,
    duration: 1.2 + Math.random() * 0.8,
    delay: Math.random() * 0.2,
    size: 6 + Math.floor(Math.random() * 5),
    rotation: Math.floor(Math.random() * 360),
    color: ['#FF6B6B', '#FFD93D', '#6BCB77', '#4D96FF', '#B983FF'][Math.floor(Math.random() * 5)]
  }))
  showConfetti.value = true
  setTimeout(() => (showConfetti.value = false), 1600)
}
// Chỉ giữ lại tab 'Tất cả'
const tabs = [
  { label: 'Tất cả', value: 'all' },
]
const selectedTab = ref('all')
const voucherCode = ref('')
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

const { discounts, saveVoucherByCode, loading, error, fetchMyVouchers, deleteUserCoupon, fetchSellerDiscounts } = useDiscount()
const { showNotification } = useNotification()
const { toast } = useToast()

const vouchers = computed(() => discounts.value)

// Dữ liệu seller và voucher của seller
const availableSellerDiscounts = ref([])
const verifiedSellers = ref([])
const sellersMap = ref({})

// Danh sách tất cả voucher (đã lưu + có sẵn admin + có sẵn từ seller)
const allVouchers = computed(() => {
  const savedVouchers = vouchers.value || []
  const adminAvailable = availableDiscounts.value || []
  const sellerAvailable = availableSellerDiscounts.value || []

  const allVouchersMap = new Map()

  // Ưu tiên voucher đã lưu
  savedVouchers.forEach(voucher => {
    allVouchersMap.set(voucher.id, { ...voucher, isSaved: true })
  })

  // Thêm voucher admin công khai
  adminAvailable.forEach(voucher => {
    if (!allVouchersMap.has(voucher.id)) {
      allVouchersMap.set(voucher.id, { ...voucher, isSaved: false })
    }
  })

  // Thêm voucher seller công khai
  sellerAvailable.forEach(voucher => {
    if (!allVouchersMap.has(voucher.id)) {
      allVouchersMap.set(voucher.id, { ...voucher, isSaved: false })
    }
  })

  return Array.from(allVouchersMap.values())
})

// Lọc theo từ khoá tìm kiếm
const filteredAllVouchers = computed(() => {
  let list = allVouchers.value
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

// Pagination cho tất cả voucher
const pageSize = 9
const currentPage = ref(1)
const totalPages = computed(() => Math.ceil(filteredAllVouchers.value.length / pageSize))
const paginatedAllVouchers = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredAllVouchers.value.slice(start, start + pageSize)
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
    await fetchAvailableDiscounts()
    currentPage.value = 1 // reset về trang đầu khi thêm mới
    toast('success', res.message || 'Lưu voucher thành công')
    triggerConfetti()
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
        await fetchAvailableDiscounts()
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
  router.push({ path: '/cart', query: { voucher: code } })
}

onMounted(() => {
  fetchMyVouchers()
  fetchAvailableDiscounts()
  fetchAvailableSellerDiscounts()
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
    console.log('API Response:', data) // Debug log
    
    if (res.ok && data.data) {
      // Lấy tất cả voucher có sẵn và debug
      const allDiscounts = data.data
      console.log('All discounts from API:', allDiscounts) // Debug log
      
      availableDiscounts.value = allDiscounts.filter(d => {
        const isActive = d.status === 'active'
        const isNotExpired = new Date(d.end_date) > new Date()
        console.log(`Discount ${d.code}: active=${isActive}, notExpired=${isNotExpired}, end_date=${d.end_date}`) // Debug log
        return isActive && isNotExpired
      })
      
      console.log('Filtered available discounts:', availableDiscounts.value) // Debug log
    } else {
      console.log('API error:', data) // Debug log
      availableDiscounts.value = []
    }
  } catch (e) {
    console.error('Fetch error:', e) // Debug log
    availableDiscounts.value = []
  } finally {
    availableLoading.value = false
  }
}

// Lấy danh sách seller xác thực và voucher của họ (công khai)
const fetchVerifiedSellers = async () => {
  try {
    const res = await fetch(`${useRuntimeConfig().public.apiBaseUrl}/sellers/verified`, {
      headers: { 'Accept': 'application/json' }
    })
    const data = await res.json()
    if (res.ok && data.data) {
      verifiedSellers.value = data.data
      const map = {}
      data.data.forEach((s) => { map[s.id] = s })
      sellersMap.value = map
    } else {
      verifiedSellers.value = []
      sellersMap.value = {}
    }
  } catch (e) {
    verifiedSellers.value = []
    sellersMap.value = {}
  }
}

const fetchAvailableSellerDiscounts = async () => {
  await fetchVerifiedSellers()
  if (!verifiedSellers.value.length) {
    availableSellerDiscounts.value = []
    return
  }
  try {
    const lists = await Promise.all(
      verifiedSellers.value.map(async (seller) => {
        const list = await fetchSellerDiscounts(seller.id)
        // Gắn thông tin seller để hiển thị
        return (list || []).map(d => ({
          ...d,
          seller: {
            id: seller.id,
            store_name: seller.store_name,
            store_slug: seller.store_slug,
          },
        }))
      })
    )
    // Nối và loại bỏ trùng id
    const flat = lists.flat()
    const seen = new Set()
    availableSellerDiscounts.value = flat.filter(d => {
      if (seen.has(d.id)) return false
      seen.add(d.id)
      return true
    })
  } catch (e) {
    availableSellerDiscounts.value = []
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
    await fetchAvailableDiscounts()
    toast('success', res.message || 'Lưu voucher thành công')
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

// Kiểm tra voucher đã được lưu chưa
function isVoucherSaved(voucherId) {
  const voucher = allVouchers.value.find(v => v.id === voucherId)
  return voucher ? voucher.isSaved : false
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

/* Petal falling */
@keyframes petalFall {
  0% { transform: translateY(-40px) rotate(0deg); opacity: 0 }
  10% { opacity: .95 }
  100% { transform: translateY(120vh) rotate(360deg); opacity: 0 }
}
.petal-layer { pointer-events: none; }
.petal {
  position: absolute;
  top: -20px;
  width: 16px;
  height: 12px;
  background: radial-gradient(circle at 30% 30%, #ffb3c7 0%, #ff6f91 60%, #ff4274 100%);
  border-radius: 60% 40% 60% 40%/60% 40% 60% 40%;
  filter: blur(.2px);
  animation: petalFall linear infinite;
}

/* Floating chips */
.floating-chip {
  background: #fff;
  color: #1f2937;
  border: 1px solid #e5e7eb;
  border-radius: 9999px;
  font-size: 12px;
  padding: 4px 10px;
  box-shadow: 0 1px 2px rgba(0,0,0,.06);
  animation: floatY 4s ease-in-out infinite;
}
.floating-chip.delay-1 { animation-delay: .8s }
.floating-chip.delay-2 { animation-delay: 1.6s }
@keyframes floatY {
  0%,100% { transform: translateY(0) }
  50% { transform: translateY(-6px) }
}

/* Confetti burst */
.confetti-layer {
  position: relative;
}
.confetti {
  position: absolute;
  top: -6px;
  border-radius: 2px;
  animation: confettiFall ease-out forwards;
}
@keyframes confettiFall {
  to { transform: translateY(120px); opacity: 0 }
}

/* Voucher card animation */
@keyframes voucherEntry {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.voucher-anim-card {
  animation: voucherEntry 0.5s ease-out;
}

/* Wavy animated background */
.waves-bg { filter: blur(0.8px); opacity: .6 }
.wave {
  position: absolute;
  left: -20%;
  right: -20%;
  height: 220px;
  border-radius: 100% 0 100% 0/60% 40% 60% 40%;
  transform: rotate(-2deg);
}
/* Sky blue tones */
.wave.layer1 { bottom: -10px; background: radial-gradient(100% 100% at 0% 0%, rgba(96,165,250,0.65) 0%, transparent 65%); animation: waveMove 12s linear infinite; }
.wave.layer2 { bottom: -40px; background: radial-gradient(100% 100% at 0% 0%, rgba(147,197,253,0.5) 0%, transparent 65%); animation: waveMove 18s linear infinite reverse; }
.wave.layer3 { bottom: -70px; background: radial-gradient(100% 100% at 0% 0%, rgba(219,234,254,0.35) 0%, transparent 65%); animation: waveMove 24s linear infinite; }
@keyframes waveMove {
  0% { transform: translateX(0) rotate(-2deg); }
  50% { transform: translateX(10%) rotate(-2deg); }
  100% { transform: translateX(0) rotate(-2deg); }
}
</style>