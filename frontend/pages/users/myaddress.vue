<template>
  <div class="bg-[#f5f7fa] font-sans text-[#1a1a1a]">
    <div class="max-w-7xl mx-auto md:pt-6 md:pb-6 flex flex-col md:flex-row gap-6">
      <SidebarProfile class="flex-shrink-0 border-r border-gray-200" />

      <main class="flex-1 p-4 sm:p-6">
        <div class="min-h-full">
          <h1 class="text-2xl sm:text-3xl text-center font-extrabold text-gray-900 mb-4">
            Sổ địa chỉ giao hàng
          </h1>

          <!-- Nút thêm địa chỉ -->
          <div class="bg-white rounded-lg shadow-sm border border-dashed border-gray-400 p-3 sm:p-4 mb-6">
            <NuxtLink
              to="/users/add_address"
              class="flex items-center justify-center w-full py-2 px-3 text-blue-600 hover:text-blue-700 transition-colors duration-200"
              aria-label="Thêm địa chỉ mới"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                  clip-rule="evenodd"
                />
              </svg>
              Thêm địa chỉ mới
            </NuxtLink>
          </div>

          <!-- Danh sách địa chỉ -->
          <div v-if="loading">
            <div
              class="bg-white rounded-lg shadow-sm border p-4 mb-4 animate-pulse h-24"
              v-for="i in 3"
              :key="i"
            />
          </div>

          <div v-else>
            <div
              v-for="address in addresses"
              :key="address.id"
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-4"
            >
              <div class="flex justify-between items-start mb-2">
                <h2 class="text-lg font-semibold text-gray-900">
                  {{ address.name }}
                  <span
                    v-if="address.is_default"
                    class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"
                  >
                    Mặc định
                  </span>
                </h2>
                <div class="flex gap-4 text-sm font-medium">
                  <NuxtLink
                    :to="{ path: '/users/add_address', query: { ...address } }"
                    class="text-blue-600 hover:text-blue-800"
                    :aria-label="`Chỉnh sửa địa chỉ ${address.name}`"
                  >
                    Chỉnh sửa
                  </NuxtLink>
                  <button
                    class="text-red-500 hover:text-red-700"
                    @click="deleteAddress(address.id)"
                    :aria-label="`Xoá địa chỉ ${address.name}`"
                  >
                    Xoá
                  </button>
                </div>
              </div>
              <p class="text-gray-700 text-sm mb-1">
                Địa chỉ: {{ resolved[address.id] || 'Đang tải...' }}
              </p>
              <p class="text-gray-700 text-sm">Số điện thoại: {{ address.phone }}</p>
            </div>

            <div v-if="addresses.length === 0" class="text-center text-gray-600 mt-8">
              Bạn chưa có địa chỉ nào. Hãy thêm một địa chỉ mới!
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, watchEffect } from 'vue'
import { useRuntimeConfig, useHead } from '#app'
import SidebarProfile from '~/components/shared/layouts/Sidebar-profile.vue'
import { useAuthHeaders } from '~/composables/useAuthHeaders'
import { useToast } from '~/composables/useToast'
import Swal from 'sweetalert2'
import axios from 'axios'

useHead({
  title: 'Sổ địa chỉ | Tài khoản của bạn',
  meta: [
    { name: 'description', content: 'Trang quản lý địa chỉ giao hàng của bạn trên hệ thống.' },
]
})


const { showSuccess, showError } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

const addresses = ref([])
const loading = ref(true)

const provinces = ref([])
const districts = ref([])
const wards = ref([])

const resolved = reactive({})

const loadProvinces = async () => {
  const cache = localStorage.getItem('ghn_provinces')
  if (cache) {
    provinces.value = JSON.parse(cache)
  } else {
    const res = await axios.get(`${apiBase}/ghn/provinces`)
    provinces.value = res.data.data || []
    localStorage.setItem('ghn_provinces', JSON.stringify(provinces.value))
  }
}

const loadDistrictsAppend = async (provinceId) => {
  const key = `ghn_districts_${provinceId}`
  const cache = localStorage.getItem(key)
  if (cache) {
    const parsed = JSON.parse(cache)
    const ids = parsed.map(d => d.DistrictID)
    if (!districts.value.some(d => ids.includes(d.DistrictID))) {
      districts.value.push(...parsed)
    }
  } else {
    const res = await axios.post(`${apiBase}/ghn/districts`, { province_id: provinceId })
    const data = res.data.data || []
    localStorage.setItem(key, JSON.stringify(data))
    districts.value.push(...data)
  }
}

const loadWardsAppend = async (districtId) => {
  const key = `ghn_wards_${districtId}`
  const cache = localStorage.getItem(key)
  if (cache) {
    const parsed = JSON.parse(cache)
    const codes = parsed.map(w => w.WardCode)
    if (!wards.value.some(w => codes.includes(w.WardCode))) {
      wards.value.push(...parsed)
    }
  } else {
    const res = await axios.post(`${apiBase}/ghn/wards`, { district_id: districtId })
    const data = res.data.data || []
    localStorage.setItem(key, JSON.stringify(data))
    wards.value.push(...data)
  }
}

const provinceMap = computed(() => new Map(provinces.value.map(p => [p.ProvinceID, p.ProvinceName])))
const districtMap = computed(() => new Map(districts.value.map(d => [d.DistrictID, d.DistrictName])))
const wardMap = computed(() => new Map(wards.value.map(w => [`${w.WardCode}-${w.DistrictID}`, w.WardName])))

const getProvinceName = (id) => id ? provinceMap.value.get(id) || '' : ''
const getDistrictName = (id) => id ? districtMap.value.get(id) || '' : ''
const getWardName = (code, did) => (code && did) ? wardMap.value.get(`${code}-${did}`) || '' : ''

const resolveAddressText = async (address) => {
  await loadDistrictsAppend(address.province_id)
  await loadWardsAppend(address.district_id)

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

    for (const addr of addresses.value) {
      resolveAddressText(addr).then(text => {
        resolved[addr.id] = text
      })
    }
  } catch (e) {
    showError('Không thể tải địa chỉ')
  } finally {
    loading.value = false
  }
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

onMounted(async () => {
  await loadProvinces()
  await loadAddresses()
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
