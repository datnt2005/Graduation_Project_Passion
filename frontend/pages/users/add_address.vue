<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="flex flex-col md:flex-row max-w-7xl mx-auto py-6 gap-6">
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
                  <input type="text" id="name" v-model="addressForm.name"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                    placeholder="Nhập họ và tên"/>
                </div>
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                  <input type="tel" id="phone" v-model="addressForm.phone"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                    placeholder="Nhập số điện thoại"/>
                </div>
              </div>

              <div class="mb-4">
                <label for="detail" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ cụ thể</label>
                <input type="text" id="detail" v-model="addressForm.detail"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                  placeholder="VD: 234 Thôn 2" />
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Loại địa chỉ</label>
                <div class="flex flex-col sm:flex-row gap-4">
                  <label class="inline-flex items-center">
                    <input type="radio" class="form-radio text-blue-600" value="home" v-model="addressForm.address_type" name="address_type" />
                    <span class="ml-2 text-sm text-gray-700">Nhà riêng / Chung cư</span>
                  </label>
                  <label class="inline-flex items-center">
                    <input type="radio" class="form-radio text-blue-600" value="company" v-model="addressForm.address_type" name="address_type" />
                    <span class="ml-2 text-sm text-gray-700">Cơ quan / Công ty</span>
                  </label>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố</label>
                  <select v-model="addressForm.province_id" class="w-full border border-gray-300 rounded-md py-2 px-3" @change="loadDistricts(addressForm.province_id)">
                    <option value="">Chọn tỉnh</option>
                    <option v-for="province in provinces" :key="province.ProvinceID" :value="province.ProvinceID">
                      {{ province.ProvinceName }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                  <select v-model="addressForm.district_id" class="w-full border border-gray-300 rounded-md py-2 px-3"  @change="loadWards(addressForm.district_id)">
                    <option value="">Chọn huyện</option>
                    <option v-for="district in districts" :key="district.DistrictID" :value="district.DistrictID">
                      {{ district.DistrictName }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phường/Xã</label>
                  <select v-model="addressForm.ward_code" class="w-full border border-gray-300 rounded-md py-2 px-3" >
                    <option value="">Chọn xã</option>
                    <option v-for="ward in wards" :key="ward.WardCode" :value="ward.WardCode">
                      {{ ward.WardName }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="flex items-center mb-6">
                <input id="defaultAddress" type="checkbox" v-model="addressForm.isDefault" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                <label for="defaultAddress" class="ml-2 text-sm text-gray-900">Đặt làm địa chỉ mặc định</label>
              </div>

              <div class="flex justify-end gap-3">
                <button type="button" @click="goBack" class="px-4 py-2 border border-gray-300 rounded-md text-sm">Quay lại</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
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
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useAuthHeaders } from '~/composables/useAuthHeaders'
import { useToast } from '~/composables/useToast'

const errors = ref({})
const { showSuccess, showError } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const route = useRoute()
const router = useRouter()
const user_id = useCookie('user_id')?.value || null

const {
  addressForm,
  provinces,
  districts,
  wards,
  loadProvinces,
  loadDistricts,
  loadWards
} = useAddressForm(apiBase, user_id)

const isEditMode = !!route.query.id
const id = route.query.id

const loadAddressToEdit = async () => {
  try {
    const query = route.query
    if (query.province_id && query.district_id && query.ward_code) {
      const provinceId = +query.province_id
      const districtId = +query.district_id

      addressForm.value = {
        user_id: user_id,
        name: query.name,
        phone: query.phone,
        detail: query.detail,
        province_id: provinceId,
        district_id: districtId,
        ward_code: query.ward_code,
        address_type: query.address_type || 'home',
        isDefault: +query.is_default === 1
      }

      await loadDistricts(provinceId)
      await loadWards(districtId)
    } else {
      const res = await axios.get(`${apiBase}/address/${id}`, useAuthHeaders())
      const data = res.data.data

      addressForm.value = {
        user_id: data.user_id,
        name: data.name,
        phone: data.phone,
        detail: data.detail,
        province_id: data.province_id,
        district_id: data.district_id,
        ward_code: data.ward_code,
        address_type: data.address_type || 'home',
        isDefault: data.is_default === 1
      }

      await loadDistricts(data.province_id)
      await loadWards(data.district_id)
    }
  } catch (err) {
    showError('Không tải được địa chỉ để chỉnh sửa.')
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
      showSuccess('Cập nhật địa chỉ thành công!')
    } else {
      await axios.post(`${apiBase}/address`, payload, useAuthHeaders())
      showSuccess('Thêm địa chỉ thành công!')
    }
    router.push('/users/myaddress')
  } catch (e) {
    if (e.response && e.response.status === 401) {
      showError('Bạn cần đăng nhập để thêm địa chỉ.')
      return
    }
    const data = e.response?.data
    if (data?.errors) {
      errors.value = data.errors
      const firstError = Object.values(data.errors)[0]?.[0]
      showError(firstError || 'Dữ liệu không hợp lệ.')
    } else {
      showError(data?.message || 'Có lỗi xảy ra. Vui lòng thử lại.')
    }
  }
}

const goBack = () => {
  router.back()
}

onMounted(async () => {
  await loadProvinces()
  if (isEditMode) {
    await loadAddressToEdit()
  }
})
</script>
