<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a] min-h-screen">
    <div class="flex flex-col md:flex-row max-w-screen-2xl mx-auto px-4 sm:px-6 py-6 gap-6">
      <!-- Sidebar -->
      <div class="w-full md:w-auto md:min-w-[250px]">
        <SidebarProfile class="w-full border border-gray-200 rounded-md bg-white" />
      </div>

      <!-- Main content -->
      <main class="flex-1 w-full">
        <!-- Title -->
        <div class="text-center mb-6">
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Đơn hàng của tôi</h2>
          <p class="text-sm text-gray-500">Theo dõi và quản lý đơn hàng bạn đã đặt</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6 flex flex-wrap justify-center gap-2">
          <button v-for="tab in tabs" :key="tab.value" @click="selectedTab = tab.value" :class="[
            'px-4 py-2 text-sm rounded-full border flex items-center gap-1 transition',
            selectedTab === tab.value
              ? 'bg-blue-600 text-white border-blue-600'
              : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50'
          ]">
            {{ tab.label }}
            <span v-if="tab.count !== undefined"
              class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-blue-600 bg-blue-100 rounded-full">
              {{ tab.count }}
            </span>
          </button>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex justify-center py-10">
          <div class="animate-spin w-8 h-8 border-t-2 border-blue-500 rounded-full"></div>
        </div>

        <!-- Orders list -->
        <div v-else>
          <!-- Desktop table -->
          <div v-if="filteredOrders.length > 0"
            class="hidden md:block bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase text-left">
                <tr>
                  <th class="px-4 py-3">STT</th>
                  <th class="px-4 py-3">Mã đơn</th>
                  <th class="px-4 py-3">Khách hàng</th>
                  <th class="px-4 py-3">SĐT</th>
                  <th class="px-4 py-3">Trạng thái</th>
                  <th class="px-4 py-3 text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(order, index) in filteredOrders" :key="order.id" class="hover:bg-gray-50 border-t">
                  <td class="px-4 py-3">{{ index + 1 }}</td>
                  <td class="px-4 py-3 font-semibold text-blue-600">#ORD{{ String(order.id).padStart(3, '0') }}</td>
                  <td class="px-4 py-3 text-gray-800">
                    {{ order.user?.name || '---' }}
                  </td>
                  <td class="px-4 py-3">{{ order.address?.phone || '-' }}</td>
                  <td class="px-4 py-3">
                    <span :class="statusClass(order.status)"
                      class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap">
                      {{ statusText(order.status) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <div class="flex flex-col sm:flex-row justify-center gap-2">
                      <button class="text-xs px-3 py-1 text-blue-600 hover:bg-blue-50 flex items-center gap-1"
                        @click="viewOrder(order.id)">
                        <i class="fas fa-eye"></i> Chi tiết
                      </button>
                      <button v-if="order.can_delete"
                        class="text-xs px-3 py-1 text-red-500 hover:bg-red-50 flex items-center gap-1"
                        @click="confirmCancel(order.id)">
                        <i class="fas fa-times-circle"></i> Hủy
                      </button>
                      <button v-if="order.status === 'cancelled'" @click="reorderToCart(order)"
                        class="text-xs px-3 py-1 text-orange-600 hover:bg-orange-50 flex items-center gap-1">
                        <i class="fas fa-undo-alt"></i> Mua lại
                      </button>

                     <div v-if="order.status === 'delivered'" class="flex gap-2">
  <!-- Nút In hóa đơn -->
  <button @click="printOrder(order.id)"
    class="text-xs px-3 py-1 text-gray-600 hover:bg-gray-50 flex items-center gap-1">
    <i class="fas fa-print"></i> In hóa đơn
  </button>

  <!-- Nút Tải PDF -->
  <button @click="downloadPDF(order.id)"
    class="text-xs px-3 py-1 text-blue-600 hover:bg-blue-50 flex items-center gap-1">
    <i class="fas fa-file-pdf"></i> Tải PDF
  </button>
</div>

                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile card view -->
          <div class="md:hidden space-y-4">
            <div v-for="(order, index) in filteredOrders" :key="order.id"
              class="bg-white rounded-lg shadow border border-gray-200 p-4">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500 font-medium">#ORD{{ String(order.id).padStart(3, '0') }}</span>
                <span :class="statusClass(order.status)"
                  class="px-2 py-1 text-xs rounded-full font-medium whitespace-nowrap">
                  {{ statusText(order.status) }}
                </span>
              </div>
              <div class="text-sm text-gray-800 space-y-1">
                <div><strong>Khách:</strong> {{ order.user?.name || '-' }}</div>

                <div><strong>Điện thoại:</strong> {{ order.address?.phone || '-' }}</div>

                <div>
                  <strong>Tổng tiền: </strong>
                  <span class="text-green-600 font-medium">
                    {{ formatPrice(parseFloat(order.final_price) * 1000) }}
                  </span>
                </div>

              </div>
              <div class="mt-3 flex flex-wrap gap-2">
                <button class="text-xs px-3 py-1 text-blue-600 hover:bg-blue-50 flex items-center gap-1"
                  @click="viewOrder(order.id)">
                  <i class="fas fa-eye"></i> Chi tiết
                </button>
                <button v-if="order.can_delete"
                  class="text-xs px-3 py-1 text-red-500 hover:bg-red-50 flex items-center gap-1"
                  @click="confirmCancel(order.id)">
                  <i class="fas fa-times-circle"></i> Hủy
                </button>

                <button v-if="order.status === 'cancelled'" @click="reorderToCart(order)"
                  class="text-xs px-3 py-1 text-orange-600 hover:bg-orange-50 flex items-center gap-1">
                  <i class="fas fa-undo-alt"></i> Mua lại
                </button>


              <div v-if="order.status === 'delivered'" class="flex gap-2">
  <!-- Nút In hóa đơn -->
  <button @click="printOrder(order.id)"
    class="text-xs px-3 py-1 text-gray-600 hover:bg-gray-50 flex items-center gap-1">
    <i class="fas fa-print"></i> In hóa đơn
  </button>

  <!-- Nút Tải PDF -->
  <button @click="downloadPDF(order.id)"
    class="text-xs px-3 py-1 text-blue-600 hover:bg-blue-50 flex items-center gap-1">
    <i class="fas fa-file-pdf"></i> Tải PDF
  </button>
</div>

              </div>
            </div>
          </div>

          <!-- No orders -->
          <div v-if="filteredOrders.length === 0" class="text-center text-gray-600 mt-10">
            Không có đơn hàng nào phù hợp.
          </div>
        </div>

        <!-- Order details modal -->
        <Teleport to="body">
          <div v-if="isDetailOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-auto p-6 relative">

              <!-- Nút đóng -->
              <button @click="isDetailOpen = false"
                class="absolute top-4 right-4 text-gray-400 hover:text-black text-lg">
                ✕
              </button>

              <!-- Tiêu đề -->
              <h2 class="text-xl font-semibold mb-6 text-gray-900">Chi tiết đơn hàng</h2>

              <!-- Thông tin -->
              <div class="flex flex-col md:flex-row gap-4 mb-6 items-stretch text-sm text-gray-700">
                <!-- Box 1: Thông tin đơn hàng -->
                <div class="flex-1 border border-gray-200 rounded-lg p-4 space-y-1 flex flex-col justify-between">
                  <div class="flex items-center gap-2 text-gray-500 mb-1">
                    <span class="font-medium text-gray-900">Thông tin đơn hàng</span>
                  </div>
                  <p class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Mã đơn:</span>
                    <span class="text-black">#ORD{{ String(selectedOrder?.id).padStart(3, '0') }}</span>
                  </p>
                  <p class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Ngày đặt:</span>
                    <span class="text-black">{{ formatDate(selectedOrder?.created_at) }}</span>
                  </p>

                  <p class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Trạng thái:</span>
                    <span :class="statusClass(selectedOrder?.status)" class="text-xs px-2 py-1 rounded-full">
                      {{ statusText(selectedOrder?.status) }}
                    </span>
                  </p>

                  <p class="flex gap-1 pb-2">
                    <span class="min-w-[90px] text-gray-500">Tổng tiền:</span>
                    <span class="text-black">{{ formatPrice(selectedOrder?.final_price) }}</span>
                  </p>

                </div>

                <!-- Box 2: Thông tin khách hàng -->
                <div class="flex-1 border border-gray-200 rounded-lg p-4 flex flex-col space-y-2 text-sm text-gray-700">
                  <!-- Tiêu đề -->
                  <div class="flex items-center gap-2 text-gray-500">
                    <span class="font-medium text-gray-900">Thông tin khách hàng</span>
                  </div>

                  <!-- Họ tên -->
                  <div class="flex items-center gap-2">
                    <i class="fas fa-user text-gray-400 w-4 h-4"></i>
                    <span class="text-black">{{ selectedOrder?.user?.name || '-' }}</span>
                  </div>

                  <!-- Điện thoại -->
                  <div class="flex items-center gap-2">
                    <i class="fas fa-phone-alt text-gray-400 w-4 h-4"></i>
                    <span class="text-black">{{ selectedOrder?.address?.phone || '-' }}</span>
                  </div>

                  <!-- Địa chỉ -->
                  <div class="flex items-start gap-2">
                    <i class="fas fa-map-marker-alt text-gray-400 w-4 h-4"></i>
                    <span class="text-black">
                      {{ selectedOrder?.address?.detail || '-' }},
                      {{ getWardName(selectedOrder?.address?.ward_code, selectedOrder?.address?.district_id) || '-' }},
                      {{ getDistrictName(selectedOrder?.address?.district_id) || '-' }},
                      {{ getProvinceName(selectedOrder?.address?.province_id) || '-' }}
                    </span>
                  </div>
                </div>

              </div>

              <!-- Danh sách sản phẩm -->
              <div class="border border-gray-200 rounded-lg mb-6">
                <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Sản phẩm đã đặt</div>
                <div v-for="item in selectedOrder?.items || []" :key="item.product_name + item.variant"
                  class="flex items-start justify-between p-4 border-b last:border-0">
                  <div class="flex gap-3">

                    <img
                      :src="item.productVariant?.thumbnail ? `${mediaBaseUrl}${item.productVariant.thumbnail}` : `${mediaBaseUrl}products/images/product_default.jpg`"
                      :alt="item.productVariant?.product?.name || 'Ảnh sản phẩm'"
                      class="w-12 h-12 object-cover rounded-md border" width="60" />

                    <div class="space-y-1">
                      <p class="text-gray-800">{{ item.product_name || '-' }}</p>
                      <p class="text-xs text-gray-500">Phân loại: {{ item.variant || '---' }}</p>
                      <p class="text-xs text-gray-500">{{ formatPrice(item.price) }} × {{ item.quantity || 0 }}</p>
                    </div>
                  </div>
                  <div class="text-right text-gray-900 font-semibold whitespace-nowrap">
                    {{ formatPrice(item.total) }}
                  </div>
                </div>
              </div>

              <!-- Thông tin thanh toán -->
              <div v-if="selectedOrder?.payments?.length" class="border border-gray-200 rounded-lg">
                <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Thông tin thanh toán</div>
                <div v-for="payment in selectedOrder.payments" :key="payment.created_at"
                  class="px-4 py-3 text-sm text-gray-700 space-y-1">
                  <p>Phương thức: <span class="text-black">{{ payment.method || '-' }}</span></p>
                  <p>Số tiền: <span class="text-black">{{ formatPrice(payment.amount) }}</span></p>
                  <p v-if="payment && payment.status">
                    Trạng thái: <span class="text-black">{{ statusText(payment.status) }}</span>
                  </p>

                </div>
              </div>

            </div>
          </div>
        </Teleport>


      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'
import Swal from 'sweetalert2'
import { useCartStore } from '~/stores/cart'
import { useRouter } from 'vue-router'


// State
const orders = ref([])
const isLoading = ref(true)
const selectedTab = ref('all')
const user = ref(null)
const selectedOrder = ref(null)
const isDetailOpen = ref(false)
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const cartStore = useCartStore()
const router = useRouter()
const config = useRuntimeConfig()
const mediaBaseUrl = config.public.mediaBaseUrl
const apiBase = config.public.apiBaseUrl

// Tabs configuration
const tabs = ref([
  { label: 'Tất cả', value: 'all', count: 0 },
  { label: 'Chờ xử lý', value: 'pending', count: 0 },
  { label: 'Đang xử lý', value: 'processing', count: 0 },
  { label: 'Đã gửi hàng', value: 'shipped', count: 0 },
  { label: 'Đã giao hàng', value: 'delivered', count: 0 },
  { label: 'Đã huỷ', value: 'cancelled', count: 0 }
])

// Status mapping
const statusText = (status) => ({
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  shipped: 'Đã gửi hàng',
  delivered: 'Đã giao hàng',
  cancelled: 'Đã huỷ'
}[status] || 'Không xác định')

const statusClass = (status) => ({
  pending: 'bg-yellow-100 text-yellow-700',
  processing: 'bg-indigo-100 text-indigo-700',
  shipped: 'bg-blue-100 text-blue-700',
  delivered: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700'
}[status] || 'bg-gray-100 text-gray-700')

// Utility functions
const formatPrice = (price) => {
  const number = typeof price === 'string' ? parseFloat(price) : price
  if (isNaN(number)) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(number)
}
  const toast = (icon, title) => {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      width: '350px',
      padding: '10px 20px',
      customClass: { popup: 'text-sm rounded-md shadow-md' },
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
      didOpen: (toastEl) => {
        toastEl.addEventListener('mouseenter', () => Swal.stopTimer())
        toastEl.addEventListener('mouseleave', () => Swal.resumeTimer())
      }
    })
  }

const reorderToCart = async (order) => {
  const token = localStorage.getItem('access_token')
  const item = order.order_items[0]

  if (!item?.product?.id || !item?.quantity) {
    
    return
  }

  const availableStock = item.variant?.stock ?? item.product?.stock ?? 0
  if (item.quantity > availableStock) {
    toast('error',' Hết hàng vui lòng quay lại sau!')
    return
  }

  try {
    await axios.post(`${apiBase}/cart/add`, {
      product_id: item.product.id,
      product_variant_id: item.variant?.id || undefined,
      quantity: item.quantity,
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    router.push('/cart')
  } catch (err) {
    toast('error', 'Không thể thêm sản phẩm vào giỏ hàng!')
  }
}




const formatDate = (date) => {
  if (!date) return '-'
  try {
    const [day, month, rest] = date.split('/')
    const [year, time] = rest.split(' ')
    const isoString = `${year}-${month}-${day}T${time}:00`
    const parsedDate = new Date(isoString)

    if (isNaN(parsedDate)) return '-'

    return parsedDate.toLocaleString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return '-'
  }
}



// Address lookup functions
const getProvinceName = (provinceId) => {
  if (!provinceId) return '-'
  const province = provinces.value.find(p => p.ProvinceID === provinceId)
  return province?.ProvinceName || '-'
}

const getDistrictName = (districtId) => {
  if (!districtId) return '-'
  const district = districts.value.find(d => d.DistrictID === districtId)
  return district?.DistrictName || '-'
}

const getWardName = (wardCode, districtId) => {
  if (!wardCode || !districtId) return '-'
  const ward = wards.value.find(w => w.WardCode === wardCode && w.DistrictID === districtId)
  return ward?.WardName || '-'
}

// API calls  
const fetchOrders = async () => {
  isLoading.value = true
  try {
    const token = localStorage.getItem('access_token')
    if (!token) throw new Error('Chưa đăng nhập')

    // Fetch user info
  
    const userRes = await axios.get(`${apiBase}/me`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    user.value = userRes.data

    // Fetch orders
    const { data } = await axios.get(`${apiBase}/user/orders`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    orders.value = Array.isArray(data.data) ? data.data : []
    updateTabCounts()

 
  } catch (err) {
    toast('error', 'Không thể tải đơn hàng!')
  } finally {
    isLoading.value = false
  }
}

const viewOrder = async (id) => {
  try {
    const token = localStorage.getItem('access_token')
    const { data } = await axios.get(`${apiBase}/user/orders/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    selectedOrder.value = data
    isDetailOpen.value = true
 
  } catch (err) {
    toast('error', 'Không thể tải thông tin đơn hàng!')
  }
}

const loadProvinces = async () => {
  try {
    const res = await axios.get(`${apiBase}/ghn/provinces`)
    provinces.value = Array.isArray(res.data.data) ? res.data.data : []
   } catch (err) {
    toast('error', 'Lỗi khi tải danh sách tỉnh thành!')
  }
}

const loadDistricts = async () => {
  try {
    const provinceIds = [...new Set(orders.value.map(o => o.address?.province_id).filter(id => id))]
    for (const provinceId of provinceIds) {
      const res = await axios.post(`${apiBase}/ghn/districts`, { province_id: provinceId })
      if (Array.isArray(res.data.data)) {
        districts.value.push(...res.data.data.filter(d => !districts.value.some(existing => existing.DistrictID === d.DistrictID)))
      }
    }
  } catch (err) {
    toast('error', 'Lỗi khi tải danh sách quận huyện!')
  }
}

const loadWards = async () => {
  try {
    const districtIds = [...new Set(orders.value.map(o => o.address?.district_id).filter(id => id))]
    for (const districtId of districtIds) {
      const res = await axios.post(`${apiBase}/ghn/wards`, { district_id: districtId })
      if (Array.isArray(res.data.data)) {
        wards.value.push(...res.data.data.filter(w => !wards.value.some(existing => existing.WardCode === w.WardCode)))
      }
    }
     
  } catch (err) {
    toast('error', 'Lỗi khi tải danh sách phường xã!')
  }
}

const updateTabCounts = () => {
  const counts = { all: orders.value.length }
  orders.value.forEach(o => {
    counts[o.status] = (counts[o.status] || 0) + 1
  })
  tabs.value = tabs.value.map(tab => ({
    ...tab,
    count: counts[tab.value] || 0
  }))
}

const confirmCancel = (orderId) => {
  Swal.fire({
    title: 'Hủy đơn hàng?',
    text: 'Bạn chắc chắn muốn hủy đơn này chứ?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#1BA0E2',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'Hủy',
    cancelButtonText: 'Đóng',
    customClass: {
      popup: 'rounded-md shadow-sm',
      title: 'text-base font-semibold',
      htmlContainer: 'text-sm',
      confirmButton: 'text-sm px-4 py-2',
      cancelButton: 'text-sm px-4 py-2'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      cancelOrder(orderId)
    }
  })
}

const cancelOrder = async (id) => {
  try {
    const token = localStorage.getItem('access_token')
    await axios.post(`${apiBase}/user/orders/${id}/cancel`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })
    Swal.fire({
      icon: 'success',
      title: 'Thành công',
      text: 'Đơn hàng đã được hủy!',
      confirmButtonColor: '#1BA0E2'
    })
    await fetchOrders()
  } catch (err) {
   
    toast('error', 'Không thể hủy đơn hàng!')
  }
}
 
// print
const printOrder = (orderId) => {
  window.open(`/users/print-order/${orderId}`, '_blank')
}

// print pdf
const downloadPDF = async (orderId) => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`${apiBase}/user/orders/${orderId}`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const data = res.data
    const content = `
      <div style="font-family:sans-serif; font-size:13px;">
        <h2 style="text-align:center">HÓA ĐƠN MUA HÀNG - #ORD${String(data.id).padStart(3, '0')}</h2>
        <p><strong>Khách hàng:</strong> ${data.user.name}</p>
        <p><strong>Email:</strong> ${data.user.email}</p>
        <p><strong>Địa chỉ:</strong> ${data.address.detail}</p>
        <p><strong>SĐT:</strong> ${data.address.phone}</p>
        <hr />
        <table style="width:100%; border-collapse: collapse;" border="1">
          <thead><tr>
            <th style="padding:4px;">Sản phẩm</th>
            <th style="padding:4px;">SL</th>
            <th style="padding:4px;">Đơn giá</th>
            <th style="padding:4px;">Thành tiền</th>
          </tr></thead>
          <tbody>
            ${data.items.map(item => `
              <tr>
                <td style="padding:4px;">${item.product_name}</td>
                <td style="padding:4px; text-align:center;">${item.quantity}</td>
                <td style="padding:4px; text-align:right;">${formatPrice(item.price)}</td>
                <td style="padding:4px; text-align:right;">${formatPrice(item.total)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
        <p style="text-align:right; font-weight:bold; margin-top:10px;">Tổng cộng: ${formatPrice(data.final_price)}</p>
      </div>
    `

    // ✅ Import html2pdf tại đây, chỉ chạy ở client
    const html2pdf = (await import('html2pdf.js')).default

    const opt = {
      margin: 0.5,
      filename: `HoaDon_${data.user.name.replace(/\s+/g, '_')}_ORD${String(data.id).padStart(3, '0')}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }

    html2pdf().from(content).set(opt).save()
  } catch (err) {
    toast('error', 'Không thể tải hóa đơn PDF!')
  }
}


// Computed
const filteredOrders = computed(() => {
  if (selectedTab.value === 'all') return orders.value

  return orders.value.filter(o => o.status === selectedTab.value)

})

// Lifecycle
onMounted(async () => {
  await fetchOrders()
  if (orders.value.length) {
    await Promise.all([
      loadProvinces(),
      loadDistricts(),
      loadWards(),
    ])
  }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.2s ease;
  transform-origin: top right;
}

.fade-enter-from {
  opacity: 0;
  transform: scale(0.95);
}

.fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
