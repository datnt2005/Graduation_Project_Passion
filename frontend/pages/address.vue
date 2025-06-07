<template>
  <div class="min-h-screen bg-gray-100 pb-8">
    <main class="container mx-auto px-4 sm:px-6 lg:px-8 bg-white shadow-md rounded-lg mt-4 py-6 **max-w-2xl**">
      <h2 class="text-xl font-bold mb-4">2. Địa chỉ giao hàng</h2>
      <p class="mb-4 text-gray-600">Chọn địa chỉ giao hàng có sẵn dưới đây:</p>

      <div v-for="address in addresses" :key="address.id" class="border border-green-500 p-4 rounded-lg mb-6 relative">
        <span v-if="address.is_default"
          class="absolute top-0 right-0 bg-green-500 text-white text-xs px-2 py-1 rounded-bl-lg">Mặc định</span>
        <h3 class="font-semibold text-lg mb-2">{{ address.name }}</h3>
        <p>Địa chỉ: {{ address.detail }},
          {{ getWardName(address.ward_code, address.district_id, address.province_id) }},
          {{ getDistrictName(address.district_id, address.province_id) }},
          {{ getProvinceName(address.province_id) }}</p>
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
                Giao đến địa chỉ này
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
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const showNewAddressForm = ref(false)
const shippingFee = ref(0)
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const editAddress = ref(null)


const form = ref({
  user_id: 3,
  name: '',
  phone: '',
  province_id: '',
  district_id: '',
  ward_code: '',
  detail: '',
  address_type: 'home',
  isDefault: false,
})



// Tải danh sách tỉnh
const loadProvinces = async () => {
  const res = await axios.get('http://127.0.0.1:8000/api/ghn/provinces')
  provinces.value = res.data.data || []
}

// Tải danh sách quận
const loadDistricts = async () => {
  form.value.district_id = ''
  form.value.ward_code = ''
  districts.value = []
  wards.value = []
  if (!form.value.province_id) return

  const res = await axios.post('http://127.0.0.1:8000/api/ghn/districts', {
    province_id: form.value.province_id,
  })
  districts.value = res.data.data || []
}

// Tải danh sách phường
const loadWards = async () => {
  form.value.ward_code = ''
  wards.value = []
  if (!form.value.district_id) return

  const res = await axios.post('http://127.0.0.1:8000/api/ghn/wards', {
    district_id: form.value.district_id,
  })
  wards.value = res.data.data || []
}


// Tính phí vận chuyển
const calculateShippingFee = async () => {
  if (!form.value.ward_code) return

  const res = await axios.post('/api/shipping/calculate-fee', {
    province_id: form.value.province_id,
    district_id: form.value.district_id,
    ward_code: form.value.ward_code,
  })
  shippingFee.value = res.data.fee || 0
}


// Submit form
const submitForm = async () => {
  try {
    const payload = {
      user_id: 3,
      name: form.value.name,
      phone: form.value.phone,
      province_id: form.value.province_id,
      district_id: form.value.district_id,
      ward_code: form.value.ward_code,
      detail: form.value.detail,
      address_type: form.value.address_type,
      is_default: form.value.isDefault ? 1 : 0,
    }

    if (editAddress.value) {
      // 👉 SỬA
      await axios.put(`http://127.0.0.1:8000/api/address/${editAddress.value.id}`, payload)
      alert('Cập nhật địa chỉ thành công!')
    } else {
      // 👉 TẠO MỚI
      await axios.post(`http://127.0.0.1:8000/api/address`, payload)
      alert('Thêm địa chỉ thành công!')
    }

    showNewAddressForm.value = false
    editAddress.value = null
    await loadAddresses()
  } catch (error) {
    if (error.response?.data?.errors) {
      alert(Object.values(error.response.data.errors).join('\n'))
    } else {
      console.error('Lỗi:', error)
    }
  }
}


// Hàm lấy tên tỉnh, quận, xã theo id/code
const getProvinceName = (province_id) => {
  const p = provinces.value.find(item => item.ProvinceID == province_id)
  return p ? p.ProvinceName : ''
}
const getDistrictName = (district_id, province_id) => {
  const d = districts.value.find(item =>
    item.DistrictID == district_id && item.ProvinceID == province_id
  )
  return d ? d.DistrictName : ''
}

const getWardName = (ward_code, district_id, province_id) => {
  const w = wards.value.find(item =>
    item.WardCode == ward_code &&
    item.DistrictID == district_id &&
    item.ProvinceID == province_id
  )
  return w ? w.WardName : ''
}


// Mảng địa chỉ
const addresses = ref([])

// Load địa chỉ
const loadAddresses = async () => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/address?user_id=3`)
    addresses.value = res.data.data || []

    // Sau khi load addresses, load districts và wards đầy đủ để hiển thị tên
    const provinceIds = [...new Set(addresses.value.map(a => a.province_id))]
    const districtIds = [...new Set(addresses.value.map(a => a.district_id))]

    // Tải districts theo tỉnh đã có
    for (const pid of provinceIds) {
      const resDistricts = await axios.post('http://127.0.0.1:8000/api/ghn/districts', { province_id: pid })
      districts.value.push(...resDistricts.data.data)
    }

    // Tải wards theo từng district
    for (const did of districtIds) {
      const resWards = await axios.post('http://127.0.0.1:8000/api/ghn/wards', { district_id: did })
      wards.value.push(...resWards.data.data)
    }
  } catch (err) {
    console.error('Lỗi tải địa chỉ:', err)
  }
}

const deleteAddress = async (id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa địa chỉ này không?')) return

  try {
    // Gửi user_id cùng request để xác thực quyền xóa
    await axios.delete(`http://127.0.0.1:8000/api/address/${id}`, {
      data: { user_id: 3 } // Thay số 3 bằng userId hiện tại nếu có
    })
    alert('Xóa địa chỉ thành công!')
    await loadAddresses()  // Reload danh sách địa chỉ sau khi xóa
  } catch (error) {
    if (error.response) {
      alert(error.response.data.message || 'Xảy ra lỗi khi xóa địa chỉ.')
    } else {
      alert('Lỗi mạng hoặc không thể kết nối đến server.')
    }
  }
}

const resetForm = () => {
  form.value = {
    user_id: 3,
    name: '',
    phone: '',
    province_id: '',
    district_id: '',
    ward_code: '',
    detail: '',
    address_type: 'home',
    isDefault: false,
  }
  editAddress.value = null
}

// Bắt đầu sửa địa chỉ
const startEditAddress = async (address) => {
  showNewAddressForm.value = true
  editAddress.value = address

  form.value = {
    user_id: address.user_id,
    name: address.name,
    phone: address.phone,
    province_id: address.province_id,
    district_id: address.district_id,
    ward_code: address.ward_code,
    detail: address.detail,
    address_type: address.address_type,
    isDefault: address.is_default == 1 ? true : false,
  }

  // Gọi thủ công các hàm load quận/huyện và phường/xã nếu cần
  await loadDistricts()
  await loadWards()
}




// Toggle form
const toggleNewAddressForm = () => {
  showNewAddressForm.value = !showNewAddressForm.value
}

// Load dữ liệu ban đầu
onMounted(() => {
  loadProvinces()
  loadAddresses()
})

// Watch tự động
watch(() => form.value.province_id, loadDistricts)
watch(() => form.value.district_id, loadWards)
watch(() => form.value.ward_code, calculateShippingFee)


</script>




<style>
/* Bạn có thể thêm các style tùy chỉnh nếu cần */
body {
  margin: 0;
  /* Đảm bảo không có margin mặc định từ body */
}
</style>