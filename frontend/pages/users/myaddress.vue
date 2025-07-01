<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="flex flex-col md:flex-row max-w-screen-2xl mx-auto px-4 sm:px-6 py-6 gap-6">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-4 sm:p-6">
        <div class="min-h-full">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-4">
            Số địa chỉ
          </h2>

          <!-- Nút thêm địa chỉ -->
          <div class="bg-white rounded-lg shadow-sm border border-dashed border-gray-400 p-3 sm:p-4 mb-6">
            <NuxtLink to="/users/add_address"
              class="flex items-center justify-center w-full py-2 px-3 text-blue-600 hover:text-blue-700 transition-colors duration-200"
              aria-label="Thêm địa chỉ mới">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                  clip-rule="evenodd" />
              </svg>
              Thêm địa chỉ mới
            </NuxtLink>
          </div>

          <!-- Danh sách địa chỉ -->
          <div v-for="address in addresses" :key="address.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-4">
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-semibold text-gray-900">
                {{ address.name }}
                <span v-if="address.is_default"
                  class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                  Địa chỉ mặc định
                </span>
              </h3>
              <div class="flex gap-4 text-sm font-medium">
                <NuxtLink :to="{
                  path: '/users/add_address',
                  query: {
                    id: address.id,
                    name: address.name,
                    phone: address.phone,
                    detail: address.detail,
                    province_id: address.province_id,
                    district_id: address.district_id,
                    ward_code: address.ward_code,
                    address_type: address.address_type,
                    is_default: address.is_default
                  }
                }" class="text-blue-600 hover:text-blue-800">
                  Chỉnh sửa
                </NuxtLink>

                <button class="text-red-500 hover:text-red-700" @click="deleteAddress(address.id)"
                  aria-label="Xoá địa chỉ">
                  Xoá
                </button>
              </div>
            </div>
            <p class="text-gray-700 text-sm mb-1">
              Địa chỉ:
              {{ address.detail }},
              {{ getWardName(address.ward_code, address.district_id) }},
              {{ getDistrictName(address.district_id) }},
              {{ getProvinceName(address.province_id) }}
            </p>
            <p class="text-gray-700 text-sm">Điện thoại: {{ address.phone }}</p>
          </div>

          <div v-if="addresses.length === 0" class="text-center text-gray-600 mt-8">
            Bạn chưa có địa chỉ nào. Hãy thêm một địa chỉ mới!
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'
import { ref, onMounted } from 'vue'
import { useRuntimeConfig } from '#app'
import axios from 'axios'
import { useAuthHeaders } from '~/composables/useAuthHeaders'
import { useAddressForm } from '~/composables/useAddressForm'
import { useToast } from '~/composables/useToast'
import Swal from 'sweetalert2'


const { showSuccess, showError } = useToast()

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const {
  provinces,
  districts,
  wards,
  loadProvinces,
  getAuthHeaders,
  addressForm,
} = useAddressForm(apiBase)

const addresses = ref([])

const loadAddresses = async () => {
  try {
    const res = await axios.get(`${apiBase}/address`, useAuthHeaders())
    addresses.value = res.data.data || []

    const provinceIds = [...new Set(addresses.value.map(a => a.province_id))]
    const districtIds = [...new Set(addresses.value.map(a => a.district_id))]

    for (const pid of provinceIds) {
      const resDistricts = await axios.post(`${apiBase}/ghn/districts`, {
        province_id: pid
      }, useAuthHeaders())

      if (Array.isArray(resDistricts.data.data)) {
        const newDistricts = resDistricts.data.data.filter(
          d => !districts.value.find(existing => existing.DistrictID === d.DistrictID)
        )
        districts.value.push(...newDistricts)
      }
    }

    for (const did of districtIds) {
      const resWards = await axios.post(`${apiBase}/ghn/wards`, {
        district_id: did
      }, useAuthHeaders())

      if (Array.isArray(resWards.data.data)) {
        const newWards = resWards.data.data.filter(
          w => !wards.value.find(existing => existing.WardCode === w.WardCode)
        )
        wards.value.push(...newWards)
      }
    }
  } catch (e) {
    showError('Không thể tải địa chỉ')
  }
}

const getProvinceName = (province_id) => {
  const p = provinces.value.find(p => p.ProvinceID == province_id)
  return p ? p.ProvinceName : ''
}

const getDistrictName = (district_id) => {
  const d = districts.value.find(d => d.DistrictID == district_id)
  return d ? d.DistrictName : ''
}

const getWardName = (ward_code, district_id) => {
  const w = wards.value.find(w => w.WardCode == ward_code && w.DistrictID == district_id)
  return w ? w.WardName : ''
}

const deleteAddress = async (id) => {
  const confirm = await Swal.fire({
    title: 'Xoá địa chỉ?',
    text: 'Bạn có chắc chắn muốn xoá địa chỉ này?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xoá',
    cancelButtonText: 'Huỷ',
  })

  if (!confirm.isConfirmed) return

  try {
    await axios.delete(`${apiBase}/address/${id}`, useAuthHeaders())
    showSuccess('Đã xoá thành công')
    await loadAddresses()
  } catch (e) {
    showError('Không thể xoá địa chỉ')
  }
}

onMounted(() => {
  loadAddresses()
  loadProvinces()
})
</script>




<style scoped>
@media (max-width: 640px) {

  button,
  a {
    font-size: 0.75rem;
    padding: 0.5rem 1rem;
  }
}
</style>
