<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Khách hàng của tôi</h1>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả khách hàng</span>
          <span>({{ filteredCustomers.length }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <select v-model="filterType" @change="fetchCustomers" class="py-1.5 px-3 rounded-md border border-gray-300 w-full sm:w-auto">
            <option value="all">Tất cả</option>
            <option value="ordered">Đã mua</option>
            <option value="follow_only">Theo dõi</option>
            <option value="reviewed">Đã đánh giá</option>
            <option value="messaged">Đã nhắn tin</option>
          </select>

          <div class="relative w-full sm:w-64">
            <input v-model="searchQuery" type="text" placeholder="Tìm tên, email..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-full" />
            <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-300 text-sm">
          <thead class="bg-white border-b border-gray-300">
            <tr>
              <th class="border px-3 py-2 text-left hidden sm:table-cell">Tên khách</th>
              <th class="border px-3 py-2 text-left hidden md:table-cell">Email</th>
              <th class="border px-3 py-2 text-left hidden lg:table-cell">Số đơn</th>
              <th class="border px-3 py-2 text-left hidden xl:table-cell">Tổng chi tiêu</th>
              <th class="border px-3 py-2 text-left hidden xl:table-cell">Ngày mua gần nhất</th>
              <th class="border px-3 py-2 text-left hidden lg:table-cell">Loại</th>
              <th class="border px-3 py-2 text-left">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loading -->
            <template v-if="isLoading">
              <tr v-for="n in 5" :key="'skeleton-' + n" class="animate-pulse-fast border-b">
                <td class="px-3 py-2 hidden sm:table-cell">
                  <div class="h-4 shadow-sm w-32 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2 hidden md:table-cell">
                  <div class="h-4 shadow-sm w-44 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2 hidden lg:table-cell">
                  <div class="h-4 shadow-sm w-12 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2 hidden xl:table-cell">
                  <div class="h-4 shadow-sm w-20 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2 hidden xl:table-cell">
                  <div class="h-4 shadow-sm w-28 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2 hidden lg:table-cell">
                  <div class="h-4 shadow-sm w-20 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2">
                  <div class="h-4 shadow-sm w-10 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2">
                  <div class="h-4 shadow-sm w-10 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2">
                  <div class="h-4 shadow-sm w-10 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2">
                  <div class="h-4 shadow-sm w-10 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2">
                  <div class="h-4 shadow-sm w-10 bg-gray-200 rounded"></div>
                </td>
                <td class="px-3 py-2">
                  <div class="h-4 shadow-sm w-10 bg-gray-200 rounded"></div>
                </td>
                
              </tr>
            </template>

            <!-- Không có dữ liệu -->
            <tr v-else-if="paginatedCustomers.length === 0">
              <td :colspan="isMobile ? 1 : 7" class="text-center py-6 text-gray-500 italic">Không có khách hàng nào.</td>
            </tr>

            <!-- Có dữ liệu -->
            <tr v-for="customer in paginatedCustomers" :key="customer.id" class="border-b hover:bg-gray-50">
              <td class="px-3 py-2 hidden sm:table-cell">{{ customer.name }}</td>
              <td class="px-3 py-2 hidden md:table-cell">{{ customer.email }}</td>
              <td class="px-3 py-2 hidden lg:table-cell">
                <span v-if="customer.order_count !== null" class="text-gray-800">{{ customer.order_count }}</span>
                <span v-else class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-500">không có</span>
              </td>
              <td class="px-3 py-2 hidden xl:table-cell">
                <span v-if="customer.total_spent" class="text-gray-800">
                  {{ formatCurrency(customer.total_spent) }}
                </span>
                <span v-else class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-500">không có</span>
              </td>
              <td class="px-3 py-2 hidden xl:table-cell">
                <span v-if="customer.last_order_date" class="text-gray-800">{{ customer.last_order_date }}</span>
                <span v-else class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-500">không có</span>
              </td>
              <td class="px-3 py-2 hidden lg:table-cell">
                <span class="inline-block px-2 py-0.5 text-xs rounded font-medium" :class="{
                  'bg-blue-100 text-blue-700': customer.type === 'ordered',
                  'bg-yellow-100 text-yellow-800': customer.type === 'follow_only',
                  'bg-green-100 text-green-700': customer.type === 'reviewed',
                  'bg-purple-100 text-purple-700': customer.type === 'messaged',
                  'bg-gray-100 text-gray-500': !customer.type
                }">
                  {{
                    customer.type === 'ordered' ? 'Đã mua' :
                      customer.type === 'follow_only' ? 'Theo dõi' :
                        customer.type === 'reviewed' ? 'Đánh giá' :
                          customer.type === 'messaged' ? 'Nhắn tin' :
                            'không có'
                  }}
                </span>
              </td>
              <td class="px-3 py-2">
                <button @click="viewCustomer(customer.id)" class="text-blue-600 hover:underline">Xem</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button @click="prevPage" :disabled="currentPage === 1"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Trước
          </button>
          <button @click="nextPage" :disabled="currentPage === totalPages"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Sau
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Hiển thị
              <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
              đến
              <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredCustomers.length) }}</span>
              của
              <span class="font-medium">{{ filteredCustomers.length }}</span>
              kết quả
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <button @click="prevPage" :disabled="currentPage === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
                </svg>
              </button>
              <button v-for="page in visiblePages" :key="page"
                @click="goToPage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium',
                  currentPage === page ? 'bg-blue-50 text-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50'
                ]">
                {{ page }}
              </button>
              <button @click="nextPage" :disabled="currentPage === totalPages"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clip-rule="evenodd" />
                </svg>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
      <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6 relative animate-fadeIn">
        <button @click="closeModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 text-2xl">×</button>

        <div class="flex flex-col items-center text-center mb-6">
          <img
            :src="selectedCustomer?.avatar?.startsWith('http') 
              ? selectedCustomer.avatar 
              : `https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/${selectedCustomer.avatar}`"
            class="w-20 h-20 rounded-full bg-gray-100 object-cover border mb-3"
            alt="avatar" />
          <h2 class="text-lg font-semibold text-gray-800">{{ selectedCustomer?.name }}</h2>
          <p class="text-sm text-gray-500">{{ selectedCustomer?.email }}</p>

          <span
            class="mt-2 text-xs font-medium px-3 py-0.5 rounded-full"
            :class="{
              'bg-blue-100 text-blue-700': selectedCustomer?.type === 'ordered',
              'bg-yellow-100 text-yellow-800': selectedCustomer?.type === 'follow_only',
              'bg-green-100 text-green-700': selectedCustomer?.type === 'reviewed',
              'bg-purple-100 text-purple-700': selectedCustomer?.type === 'messaged'
            }"
          >
            {{
              selectedCustomer?.type === 'ordered' ? 'Đã mua' :
              selectedCustomer?.type === 'follow_only' ? 'Theo dõi' :
              selectedCustomer?.type === 'reviewed' ? 'Đánh giá' :
              selectedCustomer?.type === 'messaged' ? 'Nhắn tin' :
              'Không rõ'
            }}
          </span>
        </div>

        <div class="space-y-3 text-sm text-gray-700">
          <div class="flex justify-between items-center bg-gray-50 rounded-md px-4 py-2">
            <span class="flex items-center gap-2">
              <i class="fas fa-receipt text-gray-400"></i>
              Số đơn hàng
            </span>
            <span class="font-semibold">{{ selectedCustomer?.order_count ?? 'Không có' }}</span>
          </div>

          <div class="flex justify-between items-center bg-gray-50 rounded-md px-4 py-2">
            <span class="flex items-center gap-2">
              <i class="fas fa-dollar-sign text-gray-400"></i>
              Tổng chi tiêu
            </span>
            <span class="font-semibold">
              {{ selectedCustomer?.total_spent ? formatCurrency(selectedCustomer.total_spent) : 'Không có' }}
            </span>
          </div>

          <div class="flex justify-between items-center bg-gray-50 rounded-md px-4 py-2">
            <span class="flex items-center gap-2">
              <i class="fas fa-calendar-alt text-gray-400"></i>
              Mua gần nhất
            </span>
            <span class="font-semibold">{{ selectedCustomer?.last_order_date || 'Không có' }}</span>
          </div>
        </div>

        <div class="flex justify-between gap-3 mt-6">
          <button @click="closeModal"
            class="w-full py-2 rounded-md border text-gray-700 hover:bg-gray-100 transition font-medium">Đóng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { secureAxios } from '@/utils/secureAxios'

const customers = ref([])
const allCustomers = ref([])
const searchQuery = ref('')
const filterType = ref('all')
const selectedCustomer = ref(null)
const showModal = ref(false)
const isLoading = ref(false)
const currentPage = ref(1)
const itemsPerPage = ref(10)

const isMobile = computed(() => window.innerWidth < 640)

const totalPages = computed(() => Math.ceil(filteredCustomers.value.length / itemsPerPage.value))

const visiblePages = computed(() => {
  const pages = []
  const maxPages = 5
  let startPage = Math.max(1, currentPage.value - Math.floor(maxPages / 2))
  let endPage = Math.min(totalPages.value, startPage + maxPages - 1)

  if (endPage - startPage + 1 < maxPages) {
    startPage = Math.max(1, endPage - maxPages + 1)
  }

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i)
  }
  return pages
})

const paginatedCustomers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredCustomers.value.slice(start, end)
})

const goToPage = (page) => {
  currentPage.value = page
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const openModal = (customer) => {
  selectedCustomer.value = customer
  showModal.value = true
}

const closeModal = () => {
  selectedCustomer.value = null
  showModal.value = false
}

const fetchCustomers = async () => {
  isLoading.value = true
  try {
    if (filterType.value === 'all') {
      const types = ['ordered', 'follow_only', 'reviewed', 'messaged']
      const promises = types.map(type =>
        secureAxios(`http://localhost:8000/api/seller/customers?type=${type}`, {}, ['seller'])
          .then(res => res.data)
          .catch(err => {
            console.warn(`Bỏ qua ${type} vì lỗi`, err)
            return []
          })
      )
      const results = await Promise.all(promises)
      customers.value = results.flat()
    } else {
      const res = await secureAxios(
        `http://localhost:8000/api/seller/customers?type=${filterType.value}`,
        {},
        ['seller']
      )
      customers.value = res.data
    }
    currentPage.value = 1 // Reset to first page when fetching new data
  } catch (err) {
    console.error('Lỗi khi load danh sách khách:', err)
  } finally {
    isLoading.value = false
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value)
}

const filteredCustomers = computed(() => {
  return customers.value.filter((c) => {
    const search = searchQuery.value.toLowerCase()
    return (
      c.name.toLowerCase().includes(search) ||
      c.email.toLowerCase().includes(search)
    )
  })
})

const viewCustomer = (id) => {
  const customer = customers.value.find(c => c.id === id)
  if (customer) openModal(customer)
}

onMounted(fetchCustomers)

definePageMeta({ layout: 'default-seller' })
</script>

<style scoped>
@keyframes pulse-fast {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.4;
  }
}

.animate-pulse-fast {
  animation: pulse-fast 0.4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
  animation: fadeIn 0.34s ease-out;
}

/* Responsive table styles */
table {
  width: 100%;
}

th, td {
  @apply px-3 py-2 text-sm;
}

@media (max-width: 640px) {
  table {
    display: block;
  }
  
  thead {
    display: none;
  }
  
  tr {
    @apply block mb-4 border-b;
  }
  
  td {
    @apply block text-right relative pl-20;
  }
  
  td:before {
    @apply absolute left-3 font-medium text-gray-600;
    content: attr(data-label);
  }
  
  td:first-child:before { content: 'Tên khách'; }
  td:nth-child(2):before { content: 'Email'; }
  td:nth-child(3):before { content: 'Số đơn'; }
  td:nth-child(4):before { content: 'Tổng chi tiêu'; }
  td:nth-child(5):before { content: 'Ngày mua gần nhất'; }
  td:nth-child(6):before { content: 'Loại'; }
  td:nth-child(7):before { content: 'Thao tác'; }
}
</style>