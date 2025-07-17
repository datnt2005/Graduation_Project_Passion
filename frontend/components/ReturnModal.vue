<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6">
        <h2 class="text-xl font-semibold mb-4">Yêu cầu trả hàng</h2>

        <!-- Thông tin đơn hàng -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
          <div class="space-y-1">
            <p><b>Mã vận đơn:</b> {{ order?.shipping?.tracking_code || '-' }}</p>
            <p><b>Ngày đặt:</b> {{ formatDate(order?.created_at) || '-' }}</p>
            <p>
              <b>Trạng thái:</b>
              <span :class="statusClass(order?.status)">
                {{ statusText(order?.status) }}
              </span>
            </p>
          </div>

          <div class="space-y-1">
            <p><b>Khách:</b> {{ order?.user?.name || '-' }}</p>
            <p><b>Email:</b> {{ order?.user?.email || '-' }}</p>
            <p><b>Điện thoại:</b> {{ order?.address?.phone || '-' }}</p>
            <p>
              <b>Địa chỉ:</b>
              {{ order?.address?.detail || '-' }},
              {{ order?.address?.ward_name || '-' }},
              {{ order?.address?.district_name || '-' }},
              {{ order?.address?.province_name || '-' }}
            </p>
          </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="border border-gray-200 rounded-md mb-6">
          <div class="border-b px-4 py-2 font-medium text-sm bg-gray-50 text-gray-800">Sản phẩm trong đơn</div>
          <div v-for="(item, index) in order?.order_items || []" :key="index"
            class="flex items-center justify-between px-4 py-3 border-b last:border-0">
            <div class="flex items-center gap-3">
              <img :src="resolveImage(item.variant?.thumbnail || item.product?.thumbnail)" alt="Sản phẩm"
                class="w-12 h-12 object-cover rounded border" />
              <div>
                <p class="text-gray-800 font-medium">{{ item.product?.name || '---' }}</p>
                <p class="text-xs text-gray-500" v-if="item.variant && item.variant.name">Phân loại: {{
                  item.variant.name }}</p>
                <p class="text-xs text-gray-500">{{ formatPrice(item.price || 0) }} × {{ item.quantity }}</p>
              </div>
            </div>
            <div class="text-right text-sm font-semibold text-gray-900">{{ formatPrice(item.total || 0) }}</div>
          </div>
        </div>

        <!-- Lý do trả hàng -->
        <label class="block mb-1 font-medium">Lý do trả hàng</label>
        <textarea v-model="reason" class="w-full border rounded px-3 py-2 mb-4" rows="4"
          placeholder="Nhập lý do trả hàng..." />

          <div class="mb-4">
        <label class="block mb-1 font-medium">Ảnh minh họa (nếu có)</label>
        <input type="file" multiple accept="image/*" @change="handleFileUpload"
          class="block w-full border rounded px-3 py-2 text-sm" />
        <div class="mt-2 flex flex-wrap gap-2">
          <img v-for="(img, i) in previewImages" :key="i" :src="img" class="w-20 h-20 object-cover rounded border" />
        </div>
      </div>

        <div class="flex justify-end gap-2">
          <button @click="$emit('close')" class="px-4 py-2 bg-gray-200 rounded">Hủy</button>
          <button @click="submit" :disabled="isLoading"
            class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">
            Gửi yêu cầu
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  order: Object
})

const emit = defineEmits(['close'])

const reason = ref('')
const type = ref('return') 
const isLoading = ref(false)
const selectedItemId = ref(props.order?.order_items?.[0]?.id || null)

const imageFiles = ref([])
const previewImages = ref([])

function formatDate(date) {
  if (!date) return '-'
  return date.split(' ')[0]
}

function formatPrice(price) {
  const number = typeof price === 'string'
    ? Number.parseFloat(price.replace(/,/g, ''))
    : price
  return isNaN(number)
    ? '0 ₫'
    : new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(Math.round(number))
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

function resolveImage(path) {
  if (!path) return 'https://via.placeholder.com/150'
  return `https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/${path}`
}

function handleFileUpload(event) {
  const files = Array.from(event.target.files || [])
  imageFiles.value = files
  previewImages.value = files.map(file => URL.createObjectURL(file))
}

async function submit() {
  if (!selectedItemId.value) {
    alert('Vui lòng chọn sản phẩm muốn trả!')
    return
  }

  if (!reason.value.trim()) {
    alert('Vui lòng nhập lý do trả hàng!')
    return
  }

  isLoading.value = true

  try {
    const form = new FormData()
    form.append('order_item_id', selectedItemId.value)
    form.append('reason', reason.value)
    form.append('type', type.value)

    imageFiles.value.forEach((file) => {
      form.append('images[]', file)
    })

    await secureFetch('http://localhost:8000/api/returns', {
      method: 'POST',
      body: form,
    })

    alert('Yêu cầu trả hàng đã được gửi!')
    emit('close')
  } catch (err) {
    console.error(err)
    alert('Có lỗi xảy ra khi gửi yêu cầu!')
  } finally {
    isLoading.value = false
  }
}

</script>
