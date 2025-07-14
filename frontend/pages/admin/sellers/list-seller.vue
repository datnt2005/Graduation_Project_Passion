<template>
  <div class="bg-gray-100 text-gray-700 font-sans">
    <div class="max-w-full overflow-x-auto">

      <!-- Header -->
      <div class="bg-white px-4 py-4 flex items-center justify-between border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Danh sách Seller</h1>

      </div>

      <!-- Filter -->
      <div class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700">
        <div class="flex items-center gap-2">
          <span class="font-bold">Tất cả</span>
          <span>({{ sellers.length }})</span>
        </div>
        <div class="ml-auto flex flex-wrap gap-2 items-center">
          <div class="relative">
            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm tên cửa hàng, email, sđt..."
              class="pl-8 pr-3 py-1.5 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-64" />
            <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Lọc trạng thái -->
      <div
        class="bg-gray-200 px-4 py-3 flex flex-wrap items-center gap-3 text-sm text-gray-700 border-t border-gray-300">
        <select v-model="filterVerifyStatus"
          class="rounded-md border border-gray-300 py-1.5 pl-3 pr-8 text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Lọc theo trạng thái</option>
          <option value="pending">Chờ xác minh</option>
          <option value="verified">Đã xác minh</option>
          <option value="rejected">Đã từ chối</option>
        </select>
      </div>

      <!-- Bảng -->
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
            <tr v-for="(seller, idx) in filteredSellers" :key="seller.id" class="border-b group hover:bg-[#f5f6fa]">
              <td class="py-3 px-4 text-gray-600 font-semibold">{{ idx + 1 }}</td>
              <td class="py-3 px-4">{{ seller.store_name || '-' }}</td>
              <td class="py-3 px-4">{{ seller.user?.email || '-' }}</td>
              <td class="py-3 px-4">{{ seller.phone_number || '-' }}</td>
              <td class="py-3 px-4 text-center">
                <span class="inline-block px-3 py-1 text-xs rounded-full font-medium" :class="{
                  'bg-green-100 text-green-700': seller.verification_status === 'verified',
                  'bg-yellow-100 text-yellow-700': seller.verification_status === 'pending',
                  'bg-red-100 text-red-700': seller.verification_status === 'rejected'
                }">
                  {{ getVerifyText(seller.verification_status) }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <button @click="openDetail(seller)" class="text-blue-600 hover:underline text-sm font-medium">
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

      <!-- Modal chi tiết -->
      <div v-if="detailModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 font-sans backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl relative animate-fadeIn p-6 md:p-8 overflow-y-auto max-h-screen">

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
                @click="tab = 'info'">👤 Thông tin cơ bản</button>
              <button class="flex-1 py-2 text-sm font-medium text-center transition-all"
                :class="tab === 'verify' ? 'bg-white text-[#1564ff] shadow font-semibold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                @click="tab = 'verify'">📄 Giấy tờ & Xác minh</button>
            </div>
          </div>

          <!-- Tab info -->
          <div v-if="tab === 'info'" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="flex flex-col items-center border rounded-lg p-4">
              <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center text-xl font-bold">
                {{ getInitials(currentDetail.user?.name) }}
              </div>
              <div class="mt-3 text-lg font-semibold text-gray-800">{{ currentDetail.user?.name || '-' }}</div>
              <div class="mt-1 text-sm">
                <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="currentDetail.user?.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'">
                  {{ currentDetail.user?.status === 'active' ? 'Đang hoạt động' : 'Không hoạt động' }}
                </span>
              </div>
              <div class="mt-2 text-sm text-gray-500">
                🏪 {{ currentDetail.store_name || '-' }}
              </div>
            </div>

            <!-- Chi tiết -->
            <div
              class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5 border rounded-lg p-4 text-sm min-h-[420px]">
              <div><strong>CCCD:</strong> {{ currentDetail.identity_card_number || '-' }}</div>
              <div><strong>Ngày sinh:</strong> {{ currentDetail.date_of_birth || '-' }}</div>
              <div><strong>Số điện thoại:</strong> {{ currentDetail.phone_number || '-' }}</div>
              <div><strong>Email:</strong> {{ currentDetail.user?.email || '-' }}</div>
              <div><strong>Địa chỉ:</strong> {{ currentDetail.personal_address || '-' }}</div>
              <div><strong>Giới thiệu:</strong> {{ currentDetail.bio || '-' }}</div>
              <div><strong>Mã số thuế:</strong> {{ currentDetail.tax_code || '-' }}</div>
              <div><strong>Tên doanh nghiệp:</strong> {{ currentDetail.business_name || '-' }}</div>
              <div><strong>Email doanh nghiệp:</strong> {{ currentDetail.business_email || '-' }}</div>
              <div><strong>Địa chỉ lấy hàng:</strong> {{ currentDetail.pickup_address || '-' }}</div>
              <div><strong>Giao hàng:</strong>
                {{ currentDetail.shipping_options?.express === 'true' ? 'Giao hàng nhanh' : 'Không có thông tin' }}
              </div>
            </div>
          </div>

          <!-- Tab giấy tờ -->
          <div v-else-if="tab === 'verify'" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="border rounded-lg p-4">
              <div class="font-semibold text-gray-700 mb-2">Ảnh CCCD mặt trước</div>
              <div v-if="currentDetail.id_card_front_url"
                class="aspect-square border border-gray-300 rounded-lg flex items-center justify-center cursor-pointer overflow-hidden"
                @click="enlargeImage(currentDetail.id_card_front_url)">
                <img :src="getDocUrl(currentDetail.id_card_front_url)" class="object-contain max-w-full max-h-full" />
              </div>
              <div v-else
                class="aspect-square border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-400 text-5xl">
                📷
              </div>
            </div>

            <div class="border rounded-lg p-4">
              <div class="font-semibold text-gray-700 mb-2">Ảnh CCCD mặt sau</div>
              <div v-if="currentDetail.id_card_back_url"
                class="aspect-square border border-gray-300 rounded-lg flex items-center justify-center cursor-pointer overflow-hidden"
                @click="enlargeImage(currentDetail.id_card_back_url)">
                <img :src="getDocUrl(currentDetail.id_card_back_url)" class="object-contain max-w-full max-h-full" />
              </div>
              <div v-else
                class="aspect-square border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-400 text-5xl">
                📷
              </div>
            </div>

            <div class="md:col-span-2 border rounded-lg p-4 flex flex-col justify-between">
              <div>
                <div class="font-semibold text-gray-700 mb-2">Trạng thái xác minh</div>
                <div class="mb-3">
                  <span v-if="currentDetail.verification_status === 'verified'"
                    class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">✅ Đã
                    xác minh</span>
                  <span v-else-if="currentDetail.verification_status === 'rejected'"
                    class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">❌ Đã từ
                    chối</span>
                  <span v-else
                    class="inline-block bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">⏳
                    Chờ xác minh</span>
                </div>

                <div v-if="currentDetail.verification_status === 'rejected'"
                  class="bg-red-50 text-red-700 text-sm border border-red-200 rounded p-3">
                  Seller này đã bị từ chối
                </div>
                <div v-else-if="currentDetail.verification_status !== 'verified'"
                  class="bg-blue-50 text-blue-700 text-sm border border-blue-200 rounded p-3">
                  Seller đang chờ xác minh. Vui lòng kiểm tra thông tin kỹ trước khi phê duyệt.
                </div>
              </div>

              <div class="flex gap-3 mt-6"
                v-if="currentDetail.verification_status !== 'verified' && currentDetail.verification_status !== 'rejected'">
                <button @click="approveSeller(currentDetail.id)" :disabled="loadingApprove"
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
          <!-- Modal từ chối -->
          <div v-if="rejectModal"
            class="fixed inset-0 z-60 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 animate-fadeIn">
              <h2 class="text-lg font-semibold text-red-600 mb-2">Từ chối seller</h2>
              <p class="text-sm text-gray-600 mb-4">Vui lòng nhập lý do từ chối xác minh seller này.</p>
              <textarea v-model="rejectReason" rows="4" placeholder="Nhập lý do..."
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>

              <div class="mt-4 flex justify-end gap-2">
                <button @click="closeReject"
                  class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm text-gray-800 font-semibold">
                  Hủy
                </button>
                <button @click="submitReject" :disabled="loadingReject"
                  class="px-4 py-2 rounded bg-red-500 hover:bg-red-600 text-sm text-white font-semibold"
                  :class="{ 'opacity-60 cursor-not-allowed': loadingReject }">
                  {{ loadingReject ? 'Đang xử lý...' : 'Xác nhận từ chối' }}
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>



      <!-- end modal -->
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
import { secureAxios } from '@/utils/secureAxios'

import { useNotification } from '~/composables/useNotification'
const { showNotification } = useNotification()

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
const filterVerifyStatus = ref('')
const rejectSeller = ref(null)



const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const mediaBaseUrl = config.public.mediaBaseUrl;

//  lấy link ảnh

// Lấy danh sách sellers
const fetchSellers = async () => {
  loading.value = true
  try {
    const res = await secureAxios(`${API}/admin/sellers`, {
      method: 'GET'
    }, ['admin'])
    sellers.value = res.data || []
  } catch (error) {
    console.error(error)
    sellers.value = []
    showNotification('Không thể tải danh sách seller. Vui lòng thử lại sau!', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(fetchSellers)



const filteredSellers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  const status = filterVerifyStatus.value

  return sellers.value.filter(seller => {
    const matchSearch =
      !q ||
      (seller.store_name && seller.store_name.toLowerCase().includes(q)) ||
      (seller.user?.email && seller.user.email.toLowerCase().includes(q)) ||
      (seller.phone_number && seller.phone_number.toLowerCase().includes(q))

    const matchStatus =
      !status || seller.verification_status === status

    return matchSearch && matchStatus
  })
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
const getVerifyText = (status) => {
  if (status === 'verified') return 'Đã xác minh'
  if (status === 'rejected') return 'Đã từ chối'
  return 'Chờ xác minh'
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
    showNotification('Seller đã được duyệt!', 'success')
  } catch (e) {
    showNotification('Lỗi khi duyệt seller! Vui lòng thử lại sau!', 'error')
  } finally {
    loadingApprove.value = false
  }
}
// Từ chối seller
const openReject = (seller) => {
  rejectSeller.value = seller
  rejectSellerId.value = seller.id
  rejectModal.value = true
}

const closeReject = () => {
  rejectModal.value = false
  rejectSellerId.value = null
  rejectReason.value = ''
}


const submitReject = async () => {
  if (!rejectReason.value.trim()) {
    showNotification('error', 'Vui lòng nhập lý do từ chối!')
    return
  }

  loadingReject.value = true
  try {
    await secureAxios(`${API}/admin/sellers/${rejectSellerId.value}/reject`, {
      method: 'POST',
      data: {
        reason: rejectReason.value.trim()
      }
    }, ['admin'])
    await fetchSellers()
    rejectModal.value = false
    detailModal.value = false
    showNotification('Seller đã bị từ chối!', 'success')
  } catch (e) {
    showNotification('Lỗi khi duyệt seller! Vui lòng thử lại sau!', 'error')
  } finally {
    loadingReject.value = false
  }
}
const getDocUrl = (url) => {
  if (!url) return ''
  return url.startsWith('http') ? url : `${mediaBaseUrl}${url}`
}
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