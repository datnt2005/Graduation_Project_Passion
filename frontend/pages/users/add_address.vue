<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="flex flex-col md:flex-row max-w-screen-2xl mx-auto px-4 sm:px-6 py-6 gap-6">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
        <div class="min-h-full">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-6">
            {{ isEditMode ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ mới' }}
          </h2>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <form @submit.prevent="saveAddress">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                  <input
                    type="text"
                    id="name"
                    v-model="addressForm.name"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                    placeholder="Nhập họ và tên"
                    required
                  />
                </div>
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                  <input
                    type="tel"
                    id="phone"
                    v-model="addressForm.phone"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                    placeholder="Nhập số điện thoại"
                    required
                  />
                </div>
              </div>

              <div class="mb-4">
                <label for="detail" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ cụ thể</label>
                <input
                  type="text"
                  id="detail"
                  v-model="addressForm.detail"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                  placeholder="VD: 234 Thôn 2"
                  required
                />
              </div>

              <!-- Loại địa chỉ -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Loại địa chỉ</label>
                <div class="flex flex-col sm:flex-row gap-4">
                  <label class="inline-flex items-center">
                    <input
                      type="radio"
                      class="form-radio text-blue-600"
                      value="home"
                      v-model="addressForm.address_type"
                      name="address_type"
                    />
                    <span class="ml-2 text-sm text-gray-700">Nhà riêng / Chung cư</span>
                  </label>
                  <label class="inline-flex items-center">
                    <input
                      type="radio"
                      class="form-radio text-blue-600"
                      value="company"
                      v-model="addressForm.address_type"
                      name="address_type"
                    />
                    <span class="ml-2 text-sm text-gray-700">Cơ quan / Công ty</span>
                  </label>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố</label>
                  <select
                    v-model="addressForm.province_id"
                    class="w-full border border-gray-300 rounded-md py-2 px-3"
                    required
                  >
                    <option value="">Chọn tỉnh</option>
                    <option v-for="province in provinces" :key="province.ProvinceID" :value="province.ProvinceID">
                      {{ province.ProvinceName }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                  <select
                    v-model="addressForm.district_id"
                    class="w-full border border-gray-300 rounded-md py-2 px-3"
                    required
                  >
                    <option value="">Chọn huyện</option>
                    <option v-for="district in districts" :key="district.DistrictID" :value="district.DistrictID">
                      {{ district.DistrictName }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phường/Xã</label>
                  <select
                    v-model="addressForm.ward_code"
                    class="w-full border border-gray-300 rounded-md py-2 px-3"
                    required
                  >
                    <option value="">Chọn xã</option>
                    <option v-for="ward in wards" :key="ward.WardCode" :value="ward.WardCode">
                      {{ ward.WardName }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="flex items-center mb-6">
                <input
                  id="defaultAddress"
                  type="checkbox"
                  v-model="addressForm.isDefault"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                />
                <label for="defaultAddress" class="ml-2 text-sm text-gray-900">Đặt làm địa chỉ mặc định</label>
              </div>

              <div class="flex justify-end gap-3">
                <button
                  type="button"
                  @click="goBack"
                  class="px-4 py-2 border border-gray-300 rounded-md text-sm"
                >
                  Quay lại
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
                >
                  {{ isEditMode ? 'Cập nhật' : 'Lưu địa chỉ' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { useAddressForm } from '~/composables/useAddressForm'
import { useRuntimeConfig, useRoute, useRouter } from '#app'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'
import { onMounted, computed } from 'vue'
import axios from 'axios'
import { useAuthHeaders } from '~/composables/useAuthHeaders'

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const route = useRoute()
const router = useRouter()

// Nếu có user_id thì truyền vào đây (tuỳ theo bạn lưu user thế nào)
const user_id = useCookie('user_id')?.value || null

const {
  addressForm,
  provinces,
  districts,
  wards,
  Toast,
  loadProvinces,
  loadDistricts,  // thêm dòng này
  loadWards       // thêm dòng này
} = useAddressForm(apiBase, user_id)


const isEditMode = !!route.query.id
const id = route.query.id

const loadAddressToEdit = async () => {
  try {
    const res = await axios.get(`${apiBase}/address/${id}`, useAuthHeaders())
    const data = res.data.data

    // Gán dữ liệu cơ bản
    addressForm.value = {
      user_id: data.user_id,
      name: data.name,
      phone: data.phone,
      province_id: data.province_id,
      district_id: '', // reset
      ward_code: '',
      detail: data.detail,
      address_type: data.address_type || 'home',
      isDefault: data.is_default === 1,
    }

    // ✅ Load quận/huyện theo tỉnh và gán lại district_id
    await loadDistricts(data.province_id)
    addressForm.value.district_id = data.district_id

    // ✅ Load xã/phường theo quận và gán lại ward_code
    await loadWards(data.district_id)
    addressForm.value.ward_code = data.ward_code

  } catch (e) {
    console.error(e)
    Toast.fire({ icon: 'error', title: 'Không tải được địa chỉ' })
  }
}




const saveAddress = async () => {
  const payload = {
    ...addressForm.value,
    is_default: addressForm.value.isDefault ? 1 : 0,
  }

  try {
    if (isEditMode) {
      await axios.put(`${apiBase}/address/${id}`, payload, useAuthHeaders())
      Toast.fire({ icon: 'success', title: 'Cập nhật địa chỉ thành công!' })
    } else {
      await axios.post(`${apiBase}/address`, payload, useAuthHeaders())
      Toast.fire({ icon: 'success', title: 'Thêm địa chỉ thành công!' })
    }
    router.push('/users/myaddress')
  } catch (e) {
    console.error(e)
    Toast.fire({ icon: 'error', title: 'Thất bại. Kiểm tra dữ liệu nhập.' })
  }
}

const goBack = () => {
  router.back()
}

// Hàm helper hiển thị tên tỉnh/huyện/xã (nếu cần dùng ở nơi khác như myaddress.vue)
const getProvinceName = (id) => {
  const item = provinces.value.find(p => p.ProvinceID === id)
  return item?.ProvinceName || ''
}
const getDistrictName = (id) => {
  const item = districts.value.find(d => d.DistrictID === id)
  return item?.DistrictName || ''
}
const getWardName = (code) => {
  const item = wards.value.find(w => w.WardCode === code)
  return item?.WardName || ''
}

// Đăng ký các hàm này nếu component cha cần dùng (expose)
defineExpose({
  getProvinceName,
  getDistrictName,
  getWardName
})

onMounted(async () => {
  await loadProvinces()
  if (isEditMode) await loadAddressToEdit()
})
</script>

