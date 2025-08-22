<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center overflow-auto"
      @click.self="$emit('close')">

      <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6">
        <h2 class="text-xl font-semibold mb-4">Yêu cầu trả hàng</h2>

        <!-- Thông tin đơn hàng -->
        <div class="grid grid-cols-1 md:grid-cols-2 text-sm text-gray-700 mb-6 gap-4">
          <div class="space-y-1">
            <p><b>Mã vận đơn:</b> {{ order?.shipping?.tracking_code || '-' }}</p>
            <p><b>Ngày đặt:</b> {{ formatDate(order?.created_at) || '-' }}</p>
            <p><b>Tổng tiền hàng:</b>
              {{ formatPrice(order?.final_price || 0) }}
            </p>
            <p>
              <b>Trạng thái: </b>
              <span :class="statusClass(order?.status)"> {{ statusText(order?.status) }}</span>
            </p>
          </div>
          <div class="space-y-1">
            <p><b>Khách hàng:</b> {{ order?.user?.name || '-' }}</p>
            <p><b>Email:</b> {{ order?.user?.email || '-' }}</p>
            <p><b>Điện thoại:</b> {{ order?.address?.phone || '-' }}</p>
            <p><b>Địa chỉ:</b>
              {{ order?.address?.detail || '-' }},
              {{ order?.address?.ward_name || '-' }},
              {{ order?.address?.district_name || '-' }},
              {{ order?.address?.province_name || '-' }}
            </p>
          </div>
        </div>

        <!-- Sản phẩm trong đơn -->
        <div class="border border-gray-200 rounded-md mb-6">
          <div class="px-4 py-2 text-sm font-medium bg-gray-50 border-b">Sản phẩm trong đơn</div>
          <div v-for="(item, index) in order?.order_items || []" :key="index"
            class="flex justify-between items-center gap-4 px-4 py-3 border-b last:border-none">
            <div class="flex items-center gap-3">
              <img :src="resolveImage(item.variant?.thumbnail || item.product?.thumbnail)"
                class="w-14 h-14 rounded border object-cover" />
              <div>
                <p class="font-medium text-gray-900">{{ item.product?.name || '---' }}</p>
                <p v-if="item.variant?.name" class="text-xs text-gray-500">Phân loại: {{ item.variant.name }}</p>
                <p class="text-xs text-gray-500">{{ formatPrice(item.price || 0) }} × {{ item.quantity }}</p>
              </div>
            </div>
            <div class="text-sm font-semibold text-gray-900">{{ formatPrice(item.total || 0) }}</div>
          </div>
        </div>

        <!-- Thông tin trả hàng -->
        <div class="mb-4">
          <label class="block mb-1 font-medium">Lý do trả hàng *</label>
          <select v-model="reason" class="w-full border rounded px-3 py-2 mb-4">
            <option disabled value="">-- Chọn lý do trả hàng --</option>
            <option>Nhận sai sản phẩm</option>
            <option>Sản phẩm bị lỗi/hỏng</option>
            <option>Không đúng mô tả</option>
            <option>Không còn nhu cầu</option>
            <option>Khác</option>
          </select>

          <label class="block mb-1 font-medium">Lý do bổ sung (tùy chọn)</label>
          <textarea v-model="additionalReason" rows="3" class="w-full border rounded px-3 py-2 mb-4"
            placeholder="Nhập lý do bổ sung..."></textarea>

          <!-- Upload ảnh minh họa -->
          <label class="block mb-1 font-medium">Ảnh minh họa (tùy chọn)</label>
          <label for="image-upload"
            class="border border-dashed rounded-md px-6 py-8 text-center text-sm text-gray-500 hover:border-blue-500 cursor-pointer transition block">
            <div class="flex flex-col items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-2 text-gray-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4-4m0 0l-4 4m4-4v12" />
              </svg>
              <span>Nhấp để tải lên hoặc kéo thả ảnh vào đây</span>
              <span class="text-xs text-gray-400 mt-1">PNG, JPG, GIF tối đa 10MB</span>
            </div>
          </label>
          <input id="image-upload" type="file" multiple accept="image/*" @change="handleFileUpload" class="hidden" />

          <div class="mt-4 flex flex-wrap gap-2">
            <div v-for="(img, i) in previewImages" :key="i" class="relative group">
              <img :src="img" class="w-20 h-20 object-cover rounded border" />
              <button type="button" @click="removeImage(i)"
                class="absolute top-[-6px] right-[-6px] bg-white border border-gray-300 text-gray-600 rounded-full w-5 h-5 text-xs flex items-center justify-center hover:bg-red-500 hover:text-white">
                ✕
              </button>
            </div>
          </div>

        </div>

        <!-- Trạng thái đổi trả -->
        <div class="mb-4 space-y-1 text-sm">
          <p v-if="isReturnExpired" class="text-red-600">Đã quá thời gian đổi trả (14 ngày kể từ ngày đặt hàng).</p>
          <p v-if="hasSubmittedRequest && requestStatus === 'pending'" class="text-red-600">
            Bạn đã gửi yêu cầu. Vui lòng chờ phản hồi từ người bán.
          </p>
          <p v-if="hasSubmittedRequest && requestStatus === 'approved'" class="text-green-600">
            Yêu cầu của bạn đã được <b>chấp thuận</b>.
          </p>
          <p v-if="hasSubmittedRequest && requestStatus === 'rejected'" class="text-orange-600">
            Yêu cầu của bạn đã bị <b>từ chối</b>.
          </p>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-2">
          <button @click="$emit('close')" class="px-4 py-2 bg-gray-200 rounded">Hủy</button>
          <template v-if="!hasSubmittedRequest && !isReturnExpired">
            <button @click="submit" :disabled="isLoading"
              class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 flex items-center gap-2">
              <span v-if="isLoading" class="animate-spin">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
              </span>
              <span>Gửi yêu cầu</span>
            </button>
          </template>
          <template v-else>
            <button disabled class="px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed">Không thể gửi</button>
          </template>
        </div>
      </div>
    </div>
  </Teleport>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue'
const props = defineProps({ order: Object })
const emit = defineEmits(['close'])
import Swal from 'sweetalert2'

const reason = ref('')
const additionalReason = ref('')
const type = ref('return')
const isLoading = ref(false)
const selectedItemId = ref(props.order?.order_items?.[0]?.id || null)
const imageFiles = ref([])
const previewImages = ref([])
const hasSubmittedRequest = ref(false)
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const requestStatus = ref(null)

const createdDate = computed(() => {
  const created = props.order?.created_at
  if (!created) return null
  const d = new Date(created.split(' ')[0])
  d.setHours(0, 0, 0, 0)
  return d
})
function removeImage(index) {
  previewImages.value.splice(index, 1)
  imageFiles.value.splice(index, 1)
}

const remainingDays = computed(() => {
  if (!createdDate.value) return 0
  const deadline = new Date(createdDate.value)
  deadline.setDate(deadline.getDate() + 14)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const diff = (deadline.getTime() - today.getTime()) / (1000 * 60 * 60 * 24)
  return Math.max(0, Math.floor(diff))
})


onMounted(() => {
  checkIfAlreadySubmitted()
})

async function checkIfAlreadySubmitted() {
  if (!selectedItemId.value) return
  try {
    const data = await secureFetch(`${apiBase}/return-requests/check/${selectedItemId.value}`)
    console.log('Raw response data from secureFetch (before return):', data)
    hasSubmittedRequest.value = data?.exists === true
    requestStatus.value = data?.status || null
  } catch (e) {
    console.error('Lỗi khi kiểm tra:', e)
  }
}

function handleFileUpload(event) {
  const files = Array.from(event.target.files || [])
  imageFiles.value = files
  previewImages.value = files.map(file => URL.createObjectURL(file))
}

async function submit() {
  if (!selectedItemId.value) return toast('error', 'Vui lòng chọn sản phẩm muốn trả!')
  if (!reason.value.trim()) return toast('error', 'Vui lòng chọn lý do trả hàng!')
  isLoading.value = true
  try {
    const form = new FormData()
    form.append('order_item_id', selectedItemId.value)
    form.append('reason', reason.value)
    form.append('additional_reason', additionalReason.value || '')
    form.append('type', type.value)
    imageFiles.value.forEach(file => form.append('images[]', file))

    const res = await fetch(`${apiBase}/returns`, {
      method: 'POST',
      body: form,
      headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` }
    })

    const data = await res.json()

    if (!res.ok) {
      // Lấy lỗi từ backend
      throw new Error(data?.message || 'Có lỗi xảy ra')
    }

    if (data?.success) {
      hasSubmittedRequest.value = true
      requestStatus.value = 'pending'
      toast('success', 'Yêu cầu trả hàng đã được gửi thành công!')
      emit('close')
    } else {
      toast('error', data?.message || 'Lỗi khi gửi yêu cầu!')
    }
  } catch (e) {
    console.error('Lỗi khi gửi yêu cầu:', e)
    toast('error', e.message || 'Lỗi khi gửi yêu cầu! Vui lòng thử lại sau.')
  } finally {
    isLoading.value = false
  }
}

function toast(icon = 'info', title = 'Thông báo') {
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
    didOpen: (el) => {
      el.addEventListener('mouseenter', () => Swal.stopTimer())
      el.addEventListener('mouseleave', () => Swal.resumeTimer())
    }
  })
}

function formatDate(date) {
  if (!date) return '-'
  return date.split(' ')[0]
}

function formatPrice(price) {
  const number = typeof price === 'string' ? Number.parseFloat(price.replace(/,/g, '')) : price
  return isNaN(number)
    ? '0 ₫'
    : new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(Math.round(number))
}

function resolveImage(path) {
  if (!path) return 'https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/products/default.png'
  return `https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/${path}`
}

function statusText(status) {
  switch (status) {
    case 'pending': return 'Chờ xử lý'
    case 'processing': return 'Đã xử lý'
    case 'shipped': return 'Đang giao'
    case 'delivered': return 'Đã giao'
    case 'cancelled': return 'Đã huỷ'
    default: return status
  }
}

function statusClass(status) {
  switch (status) {
    case 'delivered': return 'text-green-600'
    case 'cancelled': return 'text-red-500'
    default: return 'text-gray-600'
  }
}
</script>