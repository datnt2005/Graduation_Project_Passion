import { ref, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthHeaders } from '~/composables/useAuthHeaders' // cần import

export function useAddressForm(apiBase, user_id) {
  const provinces = ref([])
  const districts = ref([])
  const wards = ref([])

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
    const res = await axios.post(
      `${apiBase}/ghn/districts`,
      { province_id: provinceId },
      useAuthHeaders()
    )
    districts.value = res.data.data || []
  } catch (e) {
    Toast.fire({ icon: 'error', title: 'Không thể tải quận/huyện' })
  }
}

const loadWards = async (districtId = addressForm.value.district_id) => {
  addressForm.value.ward_code = ''
  wards.value = []
  if (!districtId) return
  try {
    const res = await axios.post(
      `${apiBase}/ghn/wards`,
      { district_id: districtId },
      useAuthHeaders()
    )
    wards.value = res.data.data || []
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
  }
}
