import { ref, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthHeaders } from '~/composables/useAuthHeaders'

export function useAddressForm(apiBase, user_id = null) {
  const provinces = ref([])
  const districts = ref([])
  const wards = ref([])

  const loadedDistricts = ref(new Set())
  const loadedWards = ref(new Set())

  const addressForm = ref({
    user_id,
    name: '',
    phone: '',
    province_id: '',
    district_id: '',
    ward_code: '',
    detail: '',
    address_type: 'home',
    isDefault: false,
  })

  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
  })

  const loadProvinces = async () => {
    if (provinces.value.length > 0) return
    try {
      const res = await axios.get(`${apiBase}/ghn/provinces`, useAuthHeaders())
      provinces.value = res.data.data || []
    } catch (e) {
      Toast.fire({ icon: 'error', title: 'Không thể tải tỉnh/thành' })
    }
  }

  const loadDistricts = async (provinceId = addressForm.value.province_id) => {
    addressForm.value.district_id = ''
    addressForm.value.ward_code = ''
    districts.value = []
    wards.value = []

    if (!provinceId) return

    try {
      const res = await axios.post(`${apiBase}/ghn/districts`, { province_id: provinceId }, useAuthHeaders())
      districts.value = res.data.data || []
      loadedDistricts.value.add(provinceId)
    } catch (e) {
      Toast.fire({ icon: 'error', title: 'Không thể tải quận/huyện' })
    }
  }

  const loadWards = async (districtId = addressForm.value.district_id) => {
    addressForm.value.ward_code = ''
    wards.value = []

    if (!districtId) return

    try {
      const res = await axios.post(`${apiBase}/ghn/wards`, { district_id: districtId }, useAuthHeaders())
      wards.value = res.data.data || []
      loadedWards.value.add(districtId)
    } catch (e) {
      Toast.fire({ icon: 'error', title: 'Không thể tải phường/xã' })
    }
  }

  // 🆕 Dành cho việc append nhiều địa chỉ khi load danh sách
  const loadDistrictsAppend = async (provinceId) => {
    if (!provinceId || loadedDistricts.value.has(provinceId)) return
    try {
      const res = await axios.post(`${apiBase}/ghn/districts`, { province_id: provinceId }, useAuthHeaders())
      const newItems = res.data.data?.filter(d => !districts.value.some(e => e.DistrictID === d.DistrictID)) || []
      districts.value.push(...newItems)
      loadedDistricts.value.add(provinceId)
    } catch (e) {
      Toast.fire({ icon: 'error', title: 'Không thể tải quận/huyện' })
    }
  }

  const loadWardsAppend = async (districtId) => {
    if (!districtId || loadedWards.value.has(districtId)) return
    try {
      const res = await axios.post(`${apiBase}/ghn/wards`, { district_id: districtId }, useAuthHeaders())
      const newItems = res.data.data?.filter(w => !wards.value.some(e => e.WardCode === w.WardCode)) || []
      wards.value.push(...newItems)
      loadedWards.value.add(districtId)
    } catch (e) {
      Toast.fire({ icon: 'error', title: 'Không thể tải phường/xã' })
    }
  }

  watch(() => addressForm.value.province_id, loadDistricts)
  watch(() => addressForm.value.district_id, loadWards)

  return {
    addressForm,
    provinces,
    districts,
    wards,
    Toast,
    loadProvinces,
    loadDistricts,
    loadWards,
    loadDistrictsAppend,
    loadWardsAppend,
  }
}
