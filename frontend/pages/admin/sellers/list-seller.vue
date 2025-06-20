<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">
      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Danh sách Seller</h1>
        <button
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
          Xuất dữ liệu
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ sellers.length }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm tên cửa hàng, email, sđt..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-full sm:w-64 bg-[#f5f6fa]" />
            <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-xl shadow border">
        <table class="min-w-full w-full bg-white text-sm">
          <thead>
            <tr class="bg-[#f5f6fa] text-gray-600 text-xs font-semibold uppercase border-b border-gray-200">
              <th class="py-3 px-4 text-left">#</th>
              <th class="py-3 px-4 text-left">Tên cửa hàng</th>
              <th class="py-3 px-4 text-left">Email</th>
              <th class="py-3 px-4 text-left">SĐT</th>
              <th class="py-3 px-4 text-center">Trạng thái</th>
              <th class="py-3 px-4 text-center">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(user, idx) in filteredSellers" :key="user.id" class="border-b group hover:bg-[#f5f6fa]">
              <td class="py-3 px-4 text-gray-800 text-sm font-normal">{{ idx + 1 }}</td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <span class="w-9 h-9 rounded-full flex items-center justify-center font-semibold text-base"
                    :style="`background:${getColor(user.seller?.store_name)}`">
                    {{ getInitials(user.seller?.store_name) }}
                  </span>
                  <div>
                    <div class="font-semibold text-gray-900">{{ user.seller?.store_name || '-' }}</div>

                    <template v-if="user.seller?.verification_status === 'verified'">
                      <span
                        class="inline-block mt-1 px-2 py-0.5 text-xs rounded bg-green-100 text-green-700 font-semibold">
                        Đã xác minh
                      </span>
                    </template>

                    <template v-else-if="user.seller?.verification_status === 'rejected'">
                      <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded bg-red-100 text-red-700 font-semibold">
                        Đã bị từ chối
                      </span>
                    </template>

                    <template v-else>
                      <span
                        class="inline-block mt-1 px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-700 font-semibold">
                        Chờ xác minh
                      </span>
                    </template>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-gray-800 text-sm font-normal">{{ user.email }}</td>
              <td class="py-3 px-4 text-gray-800 text-sm font-normal">{{ user.seller?.phone_number || '-' }}</td>
              <td class="py-3 px-4 text-center">
                <span
                  class="inline-block px-3 py-1 text-xs rounded-full font-medium bg-[#f5f6fa] text-gray-700 border border-gray-200"
                  :class="user.status === 'active' ? '' : 'text-gray-400'">{{ user.status === 'active' ? 'Hoạt động' :
                    'Không hoạt động' }}</span>
              </td>
              <td class="py-3 px-4 text-center">
                <button @click="openDetail(user)"
                  class="inline-flex items-center gap-2 px-4 py-1.5 rounded-lg border border-[#1564ff] text-[#1564ff] font-semibold text-sm bg-white hover:bg-[#eaf2ff] shadow transition"
                  style="box-shadow: 0 1px 3px 0 rgba(21,100,255,0.08);">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" />
                    <circle cx="12" cy="12" r="3" stroke="currentColor" />
                  </svg>
                  Xem chi tiết
                </button>
              </td>

            </tr>
            <tr v-if="filteredSellers.length === 0">
              <td colspan="6" class="text-center py-6 text-gray-400">Không có seller nào!</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal xem chi tiết Seller với tab -->
      <!-- Modal Chi tiết Seller -->
      <!-- Modal Chi tiết Seller với giao diện mới giống ảnh Daddy gửi -->
      <div v-if="detailModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 font-sans backdrop-blur-sm">
        <div
          class="bg-white rounded-xl shadow-xl w-full max-w-3xl relative animate-fadeIn p-6 md:p-8 transition-all duration-300">

          <!-- Header -->
          <div class="border-b border-gray-200 pb-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-2xl font-bold text-[#1564ff]">Chi tiết Seller</h3>
                <p class="text-gray-500 text-sm mt-1">Xem thông tin chi tiết & xác minh seller</p>
              </div>
              <button @click="closeDetail"
                class="text-gray-400 hover:text-black text-xl transition-colors duration-200">✕</button>
            </div>

            <!-- Tabs -->
            <div class="flex border rounded-lg overflow-hidden mt-5">
              <button class="flex-1 py-2 text-sm font-medium text-center transition-all"
                :class="tab === 'info' ? 'bg-white text-[#1564ff] shadow font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                @click="tab = 'info'">
                <i class="mr-1">👤</i> Thông tin cơ bản
              </button>
              <button class="flex-1 py-2 text-sm font-medium text-center transition-all"
                :class="tab === 'verify' ? 'bg-white text-[#1564ff] shadow font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                @click="tab = 'verify'">
                <i class="mr-1">📄</i> Giấy tờ & Xác minh
              </button>
            </div>
          </div>

          <!-- Tab: Thông tin -->
          <div v-if="tab === 'info'" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <!-- Avatar -->
            <div class="flex flex-col items-center border rounded-lg p-4">
              <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center text-xl font-bold">
                {{ getInitials(currentDetail.name) }}
              </div>
              <div class="mt-3 text-lg font-semibold text-gray-800">{{ currentDetail.name || '-' }}</div>
              <div class="mt-1 text-sm">
                <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="currentDetail.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'">
                  {{ currentDetail.status === 'active' ? 'Đang hoạt động' : 'Không hoạt động' }}
                </span>
              </div>
              <div class="mt-2 text-sm text-gray-500">
                <i class="mr-1">🏪</i>{{ currentDetail.seller?.store_name || '-' }}
              </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 border rounded-lg p-4">
              <div class="text-sm"><strong>CCCD:</strong> {{ currentDetail.seller?.identity_card_number || '-' }}</div>
              <div class="text-sm"><strong>Ngày sinh:</strong> {{ currentDetail.seller?.date_of_birth || '-' }}</div>
              <div class="text-sm"><strong>Số điện thoại:</strong> {{ currentDetail.seller?.phone_number || '-' }}</div>
              <div class="text-sm"><strong>Email:</strong> {{ currentDetail.email || '-' }}</div>
              <div class="text-sm sm:col-span-2"><strong>Địa chỉ:</strong> {{ currentDetail.seller?.personal_address ||
                '-' }}
              </div>
              <div class="text-sm sm:col-span-2"><strong>Giới thiệu:</strong> {{ currentDetail.seller?.bio || '-' }}
              </div>
            </div>
          </div>

          <!-- Tab: Giấy tờ & Xác minh -->
          <div v-else-if="tab === 'verify'" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Ảnh giấy tờ -->
            <div class="border rounded-lg p-4">
              <div class="font-semibold text-gray-700 mb-2">Ảnh CCCD/Giấy tờ</div>
              <div v-if="currentDetail.seller?.document"
                class="aspect-square border border-gray-300 rounded-lg flex items-center justify-center cursor-pointer overflow-hidden"
                @click="enlargeImage(currentDetail.seller.document)">
                <img :src="getDocUrl(currentDetail.seller.document)" class="object-contain max-w-full max-h-full" />
              </div>
              <div v-else
                class="aspect-square border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-400 text-5xl">
                <i>📷</i>
              </div>
            </div>

            <!-- Trạng thái xác minh -->
            <div class="border rounded-lg p-4 flex flex-col justify-between">
              <div>
                <div class="font-semibold text-gray-700 mb-2">Trạng thái xác minh</div>
                <div class="mb-3">
                  <span v-if="currentDetail.seller?.verification_status === 'verified'"
                    class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                    ✅ Đã xác minh
                  </span>
                  <span v-else-if="currentDetail.seller?.verification_status === 'rejected'"
                    class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">
                    ❌ Đã bị từ chối
                  </span>
                  <span v-else
                    class="inline-block bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">
                    ⏳ Chờ xác minh
                  </span>
                </div>

                <div v-if="currentDetail.seller?.verification_status === 'rejected'"
                  class="bg-red-50 text-red-700 text-sm border border-red-200 rounded p-3">
                  Seller này đã bị từ chối xác minh. Vui lòng xem xét lý do và liên hệ lại nếu cần.
                </div>
                <div v-else-if="currentDetail.seller?.verification_status !== 'verified'"
                  class="bg-blue-50 text-blue-700 text-sm border border-blue-200 rounded p-3">
                  Seller này đang chờ được xác minh. Vui lòng kiểm tra thông tin và giấy tờ trước khi phê duyệt.
                </div>
              </div>

              <!-- Buttons -->
              <div class="flex gap-3 mt-6"
                v-if="currentDetail.seller?.verification_status !== 'verified' && currentDetail.seller?.verification_status !== 'rejected'">
                <button @click="approveSeller(currentDetail.seller.id)" :disabled="loadingApprove"
                  class="flex-1 py-2 rounded bg-blue-700 hover:bg-blue-900 text-white font-semibold text-sm transition"
                  :class="{ 'opacity-60 cursor-not-allowed': loadingApprove }">
                  {{ loadingApprove ? 'Đang duyệt...' : 'Duyệt seller' }}
                </button>

                <button @click="openReject(currentDetail)"
                  class="flex-1 py-2 rounded bg-red-400 hover:bg-red-600 text-white font-semibold text-sm transition">
                  Từ chối
                </button>
              </div>
            </div>
          </div>

          <!-- Modal ảnh -->
          <div v-if="imagePreview" class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-70"
            @click="imagePreview = null">
            <img :src="imagePreview" alt="Preview"
              class="max-h-[90vh] max-w-[90vw] rounded-xl shadow-lg border-4 border-white" />
          </div>

          <!-- Modal từ chối -->
          <div v-if="rejectModal" class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 relative animate-fadeIn">
              <button class="absolute top-3 right-3 text-gray-400 hover:text-black" @click="closeReject">✕</button>
              <h3 class="text-lg font-bold mb-4 text-red-700">Nhập lý do từ chối</h3>
              <textarea v-model="rejectReason" rows="4"
                class="w-full border border-gray-300 rounded px-3 py-2 mb-3 focus:ring-2 focus:ring-red-500 focus:outline-none"
                placeholder="Nhập lý do từ chối seller này..."></textarea>
              <div class="flex justify-end gap-2">
                <button @click="closeReject" class="px-4 py-1 rounded bg-gray-200 hover:bg-gray-300">Hủy</button>
                <button @click="submitReject" :disabled="!rejectReason.trim() || loadingReject"
                  class="px-4 py-1 rounded bg-red-600 hover:bg-red-700 text-white font-semibold"
                  :class="{ 'opacity-60 cursor-not-allowed': !rejectReason.trim() || loadingReject }">
                  {{ loadingReject ? 'Đang từ chối...' : 'Xác nhận' }}
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
      <!-- end Modal -->
    </div>
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'
import axios from 'axios'
definePageMeta({
  layout: 'default-admin'
});
// State
const sellers = ref([])
const loading = ref(true)
const searchQuery = ref('')
const detailModal = ref(false)
const currentDetail = ref(null)
const tab = ref('info')
const rejectModal = ref(false)
const rejectSellerId = ref(null)
const rejectReason = ref('')
const imagePreview = ref(null)
const loadingApprove = ref(false);
const loadingReject = ref(false);
const getSellerId = (user) => user?.seller?.id

const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

// toast
const toast = (type = 'success', message = '', timer = 2000) => {
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    icon: type,
    title: message,
    showConfirmButton: false,
    timer: timer,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  })
}
// Lấy danh sách sellers
const fetchSellers = async () => {
  loading.value = true
  try {
    const res = await axios.get(`${API}/admin/sellers`)
    sellers.value = res.data || []
  } catch {
    sellers.value = []
    toast('error', 'Không thể tải danh sách seller. Vui lòng thử lại sau!')
  } finally {
    loading.value = false
  }
}
onMounted(fetchSellers)

// Tìm kiếm theo tên cửa hàng/email/sđt
const filteredSellers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return sellers.value
  return sellers.value.filter(user =>
    (user.seller?.store_name && user.seller.store_name.toLowerCase().includes(q)) ||
    (user.email && user.email.toLowerCase().includes(q)) ||
    (user.phone && user.phone.toLowerCase().includes(q))
  )
})

// Modal chi tiết
const openDetail = (user) => {
  currentDetail.value = user
  detailModal.value = true
  tab.value = 'info'
}
const closeDetail = () => {
  detailModal.value = false
  currentDetail.value = null
  tab.value = 'info'
  imagePreview.value = null
  rejectModal.value = false
}

// Helper avatar chữ
const getInitials = (str) => {
  if (!str) return '--'
  return str
    .split(' ')
    .map((w) => w[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
}
// Helper avatar màu random ổn định
const colors = ['#FFD6D6', '#FFE9B5', '#C6EEFF', '#D8D1FF', '#FFD8F4', '#E3FFCB', '#FAD6FF', '#A4C9FF', '#E4FFF4']
const getColor = (name) => {
  let hash = 0
  for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colors[Math.abs(hash) % colors.length]
}

// Duyệt seller
const approveSeller = async (sellerId) => {
  const result = await Swal.fire({
    title: '<strong>Duyệt seller?</strong>',
    html: `
      <div class="text-sm text-gray-600">Hành động này sẽ xác minh và kích hoạt seller.</div>
    `,
    icon: 'info',
    showCancelButton: true,
    focusConfirm: false,
    confirmButtonText: 'Duyệt ngay',
    cancelButtonText: 'Huỷ',
    buttonsStyling: false,
    customClass: {
      popup: 'rounded-xl shadow-lg',
      confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded mr-2',
      cancelButton: 'bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded',
      title: 'text-lg font-bold text-gray-800',
      htmlContainer: 'mt-1',
    }
  })
  if (!result.isConfirmed) return
  loadingApprove.value = true
  try {
    loading.value = true
await axios.post(`${API}/admin/sellers/${sellerId}/verify`)
    await fetchSellers()
    detailModal.value = false
    toast('success', 'Seller đã được duyệt!')
  } catch (e) {
    toast('error', 'Lỗi khi duyệt seller! Vui lòng thử lại sau!')
  } finally {
    loadingApprove.value = false
  }
}
// Từ chối seller
const openReject = (user) => {
  rejectSellerId.value = user.seller?.id
  rejectReason.value = ''
  rejectModal.value = true
}
const closeReject = () => {
  rejectModal.value = false
  rejectSellerId.value = null
  rejectReason.value = ''
}
const submitReject = async () => {
  if (!rejectReason.value.trim()) {
    toast('error', 'Vui lòng nhập lý do từ chối!')
    return
  }

  loadingReject.value = true
  try {
    await axios.post(`${API}/admin/sellers/${rejectSellerId.value}/reject`, {
      reason: rejectReason.value.trim()
    })
    await fetchSellers()
    rejectModal.value = false
    detailModal.value = false
    toast('success', 'Seller đã bị từ chối!')
  } catch (e) {
    toast('error', 'Lỗi khi từ chối seller! Vui lòng thử lại sau!')
  } finally {
    loadingReject.value = false
  }
}

const getDocUrl = (url) => url?.startsWith('http') ? url : '/' + url
const enlargeImage = (imgUrl) => {
  if (imgUrl) imagePreview.value = getDocUrl(imgUrl)
}
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-in-out;
}

table {
  width: 100%;
  table-layout: auto;
}

@media (max-width: 640px) {
  table {
    font-size: 0.75rem;
  }

  th,
  td {
    padding: 0.5rem;
  }

  .w-64 {
    width: 100%;
  }

  .text-xl {
    font-size: 1rem;
  }

  .text-base {
    font-size: 0.875rem;
  }

  .w-24.h-24 {
    width: 4rem;
    height: 4rem;
    font-size: 1.5rem;
  }
}

@media (max-width: 768px) {
  .flex-col.md\\:flex-row {
    flex-direction: column;
  }

  .grid-cols-1.sm\\:grid-cols-2 {
    grid-template-columns: 1fr;
  }

  .flex-1.px-5.py-2\\.5 {
    font-size: 0.875rem;
  }
}

@media (max-width: 480px) {
  .max-w-lg {
    width: 90vw;
  }

  .px-8 {
    padding-left: 1rem;
    padding-right: 1rem;
    /* Reduced padding */
  }

  .py-8 {
    padding-top: 1rem;
    padding-bottom: 1rem;
  }
}
</style>