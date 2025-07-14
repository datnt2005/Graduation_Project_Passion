<template>
  <div class="flex min-h-screen bg-gray-100 justify-center py-8">
    <main class="container mx-auto px-4 sm:px-6 lg:px-8 bg-white shadow-md rounded-lg mt-4 py-6 **max-w-2xl**">
      <h2 class="text-xl font-bold mb-4">2. Địa chỉ giao hàng</h2>
      <p class="mb-4 text-gray-600">Chọn địa chỉ giao hàng có sẵn dưới đây:</p>

      <div v-for="address in addresses" :key="address.id" class="border border-green-500 p-4 rounded-lg mb-6 relative">
        <span v-if="address.is_default"
          class="absolute top-0 right-0 bg-green-500 text-white text-xs px-2 py-1 rounded-bl-lg">Mặc định</span>
        <h3 class="font-semibold text-lg mb-2">{{ address.name }}</h3>
        <p>
          Địa chỉ: {{ address.detail }},
          {{ getWardName(address.ward_code, address.district_id) }},
          {{ getDistrictName(address.district_id) }},
          {{ getProvinceName(address.province_id) }}
        </p>

        <p>Điện thoại: {{ address.phone }}</p>
        <p>Loại địa chi: {{ address.address_type }}</p>
        <div class="mt-4 flex space-x-2">
          <NuxtLink :to="{ path: '/checkout', query: { address_id: address.id } }">
            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
              Giao đến địa chỉ này
            </button>
          </NuxtLink>
          <button @click="startEditAddress(address)" class="border border-gray-300 px-4 py-2 rounded hover:bg-gray-100">
            Sửa
          </button>
          <button @click="deleteAddress(address.id)"
            class="border border-red-300 px-4 py-2 rounded hover:bg-red-100 text-red-500">
            Xóa
          </button>
        </div>
      </div>

      <div class="mb-6">
        <p class="mb-4">
          Bạn muốn giao hàng đến địa chỉ khác?
          <a href="#" @click.prevent="toggleNewAddressForm" class="text-blue-500 hover:underline">
            {{ showNewAddressForm ? 'Ẩn form thêm địa chỉ' : 'Thêm địa chỉ giao hàng mới' }}
          </a>
        </p>

        <div v-if="showNewAddressForm" class="mt-4 px-4">
          <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6 space-y-4">

            <!-- Họ tên -->
            <div class="flex items-center">
              <label for="name" class="block text-sm font-medium text-gray-700 w-1/3">Họ tên</label>
              <input type="text" id="name" v-model="form.name"
                class="block w-2/3 border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: Nguyễn Văn A" />
            </div>

            <!-- SĐT -->
            <div class="flex items-center">
              <label for="phone" class="block text-sm font-medium text-gray-700 w-1/3">Điện thoại di động</label>
              <input type="text" id="phone" v-model="form.phone"
                class="block w-2/3 border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: 0987654321" />
            </div>

            <!-- Tỉnh -->
            <div class="flex items-center">
              <label class="block text-sm font-medium text-gray-700 w-1/3">Tỉnh/Thành phố</label>
              <select v-model="form.province_id" @change="loadDistricts()"
                class="block w-2/3 border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">-- Chọn tỉnh --</option>
                <option v-for="item in provinces" :key="item.ProvinceID" :value="item.ProvinceID">
                  {{ item.ProvinceName }}
                </option>
              </select>
            </div>

            <!-- Quận -->
            <div class="flex items-center">
              <label class="block text-sm font-medium text-gray-700 w-1/3">Quận/Huyện</label>
              <select v-model="form.district_id" @change="loadWards"
                class="block w-2/3 border border-gray-300 rounded-md shadow-sm p-2" :disabled="!districts.length">
                <option value="">-- Chọn quận --</option>
                <option v-for="item in districts" :key="item.DistrictID" :value="item.DistrictID">
                  {{ item.DistrictName }}
                </option>
              </select>
            </div>

            <!-- Phường -->
            <div class="flex items-center">
              <label class="block text-sm font-medium text-gray-700 w-1/3">Phường/Xã</label>
              <select v-model="form.ward_code" @change="calculateShippingFee"
                class="block w-2/3 border border-gray-300 rounded-md shadow-sm p-2" :disabled="!wards.length">
                <option value="">-- Chọn phường --</option>
                <option v-for="item in wards" :key="item.WardCode" :value="item.WardCode">
                  {{ item.WardName }}
                </option>
              </select>
            </div>

            <!-- Địa chỉ cụ thể -->
            <div class="flex items-start">
              <label for="addressDetail" class="block text-sm font-medium text-gray-700 w-1/3 pt-2">Địa chỉ</label>
              <div class="w-2/3">
                <textarea id="addressDetail" v-model="form.detail" rows="3"
                  class="block w-full border border-gray-300 rounded-md shadow-sm p-2"
                  placeholder="VD: 52 đường Trần Hưng Đạo, phường 1"></textarea>
                <p class="text-sm text-gray-500 mt-1">Vui lòng nhập địa chỉ cụ thể để giao hàng nhanh hơn.</p>
              </div>
            </div>

            <!-- Loại địa chỉ -->
            <div class="flex items-start">
              <label class="block text-sm font-medium text-gray-700 w-1/3 pt-2">Loại địa chỉ</label>
              <div class="w-2/3 mt-1 flex flex-col space-y-2">
                <label class="inline-flex items-center">
                  <input type="radio" v-model="form.addressType" name="address_type" value="home"
                    class="form-radio text-blue-600" />
                  <span class="ml-2">Nhà riêng / Chung cư</span>
                </label>
                <label class="inline-flex items-center">
                  <input type="radio" v-model="form.addressType" name="address_type" value="company"
                    class="form-radio text-blue-600" />
                  <span class="ml-2">Cơ quan / Công ty</span>
                </label>
              </div>
            </div>

            <!-- Địa chỉ mặc định -->
            <div class="flex items-center">
              <label class="inline-flex items-center">
                <input type="checkbox" v-model="form.isDefault" class="form-checkbox text-blue-600" />
                <span class="ml-2">Sử dụng địa chỉ này làm mặc định</span>
              </label>
            </div>

            <!-- Nút -->
            <div class="flex items-center justify-end space-x-4 pt-4">
              <button @click="cancel" class="border border-gray-300 px-6 py-2 rounded hover:bg-gray-100">
                Hủy bỏ
              </button>
              <button @click="submitForm" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                {{ editAddress ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ' }}
              </button>
            </div>

            <!-- Phí giao hàng -->
            <div v-if="shippingFee > 0" class="text-green-600 font-semibold text-sm text-right mt-2">
              Phí giao hàng: {{ shippingFee.toLocaleString() }} đ
            </div>

          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, watchEffect } from 'vue'
import { useHead, useRuntimeConfig } from '#app'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthHeaders } from '~/composables/useAuthHeaders'
import { useToast } from '~/composables/useToast'

useHead({
  title: 'Địa chỉ giao hàng | Thanh toán',
  meta: [
    { name: 'description', content: 'Chọn hoặc thêm địa chỉ giao hàng để hoàn tất đơn hàng của bạn.' },
    { name: 'robots', content: 'noindex, nofollow' }, // Trang checkout không cần lập chỉ mục
    { property: 'og:title', content: 'Địa chỉ giao hàng - Thanh toán' },
    { property: 'og:description', content: 'Quản lý địa chỉ giao hàng để nhận hàng nhanh chóng.' }
  ]
})

const { showSuccess, showError } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const showNewAddressForm = ref(false)
const shippingFee = ref(0)
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const addresses = ref([])
const editAddress = ref(null)
const loading = ref(true)
const resolved = reactive({})

const form = ref({
  name: '',
  phone: '',
  province_id: '',
  district_id: '',
  ward_code: '',
  detail: '',
  address_type: 'home',
  is_default: false
})

const provinceMap = computed(() => new Map(provinces.value.map(p => [p.ProvinceID, p.ProvinceName])))
const districtMap = computed(() => new Map(districts.value.map(d => [d.DistrictID, d.DistrictName])))
const wardMap = computed(() => new Map(wards.value.map(w => [`${w.WardCode}-${w.DistrictID}`, w.WardName])))

const isFormValid = computed(() => {
  return (
    form.value.name.trim() &&
    form.value.phone.trim() &&
    form.value.province_id &&
    form.value.district_id &&
    form.value.ward_code &&
    form.value.detail.trim()
  )
})

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

const getProvinceName = (id) => id ? provinceMap.value.get(id) || 'Đang tải...' : ''
const getDistrictName = (id) => id ? districtMap.value.get(id) || 'Đang tải...' : ''
const getWardName = (code, did) => (code && did) ? wardMap.value.get(`${code}-${did}`) || 'Đang tải...' : ''

const loadProvinces = async () => {
  const cacheKey = 'ghn_provinces'
  const cache = localStorage.getItem(cacheKey)
  if (cache) {
    provinces.value = JSON.parse(cache)
    return
  }
  try {
    const res = await axios.get(`${apiBase}/ghn/provinces`)
    provinces.value = res.data.data || []
    localStorage.setItem(cacheKey, JSON.stringify(provinces.value))
  } catch {
    showError('Không tải được danh sách tỉnh.')
  }
}

const loadDistrictsAppend = async (provinceId) => {
  if (!provinceId) return
  const cacheKey = `ghn_districts_${provinceId}`
  const cache = localStorage.getItem(cacheKey)
  if (cache) {
    const parsed = JSON.parse(cache)
    const ids = parsed.map(d => d.DistrictID)
    if (!districts.value.some(d => ids.includes(d.DistrictID))) {
      districts.value.push(...parsed)
    }
    return
  }
  try {
    const res = await axios.post(`${apiBase}/ghn/districts`, { province_id: provinceId })
    const data = res.data.data || []
    localStorage.setItem(cacheKey, JSON.stringify(data))
    districts.value.push(...data.filter(d => !districts.value.some(existing => existing.DistrictID === d.DistrictID)))
  } catch {
    showError('Không tải được danh sách quận.')
  }
}

const loadWardsAppend = async (districtId) => {
  if (!districtId) return
  const cacheKey = `ghn_wards_${districtId}`
  const cache = localStorage.getItem(cacheKey)
  if (cache) {
    const parsed = JSON.parse(cache)
    const codes = parsed.map(w => w.WardCode)
    if (!wards.value.some(w => codes.includes(w.WardCode))) {
      wards.value.push(...parsed)
    }
    return
  }
  try {
    const res = await axios.post(`${apiBase}/ghn/wards`, { district_id: districtId })
    const data = res.data.data || []
    localStorage.setItem(cacheKey, JSON.stringify(data))
    wards.value.push(...data.filter(w => !wards.value.some(existing => existing.WardCode === w.WardCode)))
  } catch {
    showError('Không tải được danh sách phường.')
  }
}

const resolveAddressText = async (address) => {
  await Promise.all([
    loadDistrictsAppend(address.province_id),
    loadWardsAppend(address.district_id)
  ])
  const ward = getWardName(address.ward_code, address.district_id)
  const district = getDistrictName(address.district_id)
  const province = getProvinceName(address.province_id)
  return `${address.detail}, ${ward}, ${district}, ${province}`
}

const loadAddresses = async () => {
  loading.value = true
  try {
    const res = await axios.get(`${apiBase}/address`, useAuthHeaders())
    addresses.value = res.data.data || []
    await Promise.all(addresses.value.map(addr => resolveAddressText(addr).then(text => {
      resolved[addr.id] = text
    })))
  } catch {
    showError('Không thể tải địa chỉ.')
  } finally {
    loading.value = false
  }
}

const calculateShippingFee = async () => {
  const { province_id, district_id, ward_code } = form.value
  if (!province_id || !district_id || !ward_code) {
    shippingFee.value = 0
    return
  }
  try {
    const res = await axios.post(`${apiBase}/shipping/calculate-fee`, { province_id, district_id, ward_code })
    shippingFee.value = res.data.fee || 0
  } catch {
    shippingFee.value = 0
    showError('Không thể tính phí giao hàng.')
  }
}

const submitForm = async () => {
  if (!isFormValid.value) {
    showError('Vui lòng điền đầy đủ thông tin.')
    return
  }

  const payload = {
    name: form.value.name.trim(),
    phone: form.value.phone.trim(),
    province_id: form.value.province_id,
    district_id: form.value.district_id,
    ward_code: form.value.ward_code,
    detail: form.value.detail.trim(),
    address_type: form.value.address_type,
    is_default: form.value.is_default ? 1 : 0
  }

  const confirm = await Swal.fire({
    title: editAddress.value ? 'Cập nhật địa chỉ?' : 'Thêm địa chỉ mới?',
    text: `Bạn có chắc chắn muốn ${editAddress.value ? 'cập nhật' : 'thêm'} địa chỉ này?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: editAddress.value ? 'Cập nhật' : 'Thêm',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  try {
    if (editAddress.value) {
      await axios.put(`${apiBase}/address/${editAddress.value.id}`, payload, useAuthHeaders())
      showSuccess('Cập nhật địa chỉ thành công!')
    } else {
      await axios.post(`${apiBase}/address`, payload, useAuthHeaders())
      showSuccess('Thêm địa chỉ thành công!')
    }
    await loadAddresses()
    resetForm()
    showNewAddressForm.value = false
  } catch (e) {
    showError(e.response?.data?.message || 'Lỗi khi lưu địa chỉ.')
  }
}

const deleteAddress = async (id) => {
  const confirm = await Swal.fire({
    title: 'Xóa địa chỉ?',
    text: 'Bạn có chắc chắn muốn xóa địa chỉ này?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280'
  })

  if (!confirm.isConfirmed) return

  try {
    await axios.delete(`${apiBase}/address/${id}`, useAuthHeaders())
    showSuccess('Xóa địa chỉ thành công!')
    await loadAddresses()
  } catch {
    showError('Không thể xóa địa chỉ.')
  }
}

const startEditAddress = async (addr) => {
  editAddress.value = addr
  Object.assign(form.value, {
    name: addr.name,
    phone: addr.phone,
    province_id: addr.province_id,
    district_id: addr.district_id,
    ward_code: addr.ward_code,
    detail: addr.detail,
    address_type: addr.address_type,
    is_default: addr.is_default === 1
  })
  showNewAddressForm.value = true
  await Promise.all([
    loadDistrictsAppend(form.value.province_id),
    loadWardsAppend(form.value.district_id)
  ])
}

const toggleNewAddressForm = () => {
  showNewAddressForm.value = !showNewAddressForm.value
  if (!showNewAddressForm.value) resetForm()
}

const resetForm = () => {
  Object.assign(form.value, {
    name: '',
    phone: '',
    province_id: '',
    district_id: '',
    ward_code: '',
    detail: '',
    address_type: 'home',
    is_default: false
  })
  editAddress.value = null
  shippingFee.value = 0
  districts.value = []
  wards.value = []
}

onMounted(async () => {
  await Promise.all([
    loadProvinces(),
    loadAddresses()
  ])
})

watchEffect(() => {
  if (form.value.province_id) loadDistrictsAppend(form.value.province_id)
})

watchEffect(() => {
  if (form.value.district_id) loadWardsAppend(form.value.district_id)
})

watchEffect(() => {
  if (form.value.ward_code) calculateShippingFee()
})
</script>






<style>
/* Bạn có thể thêm các style tùy chỉnh nếu cần */
body {
  margin: 0;
  /* Đảm bảo không có margin mặc định từ body */
}
</style>