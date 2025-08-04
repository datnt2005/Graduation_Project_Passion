<template>
  <div class="flex min-h-screen bg-gray-100 justify-center py-8">
    <main class="container mx-auto px-4 sm:px-6 lg:px-8 bg-white shadow-md rounded-lg mt-4 py-6">
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
              <select v-model="form.province_id" @change="onProvinceChange"
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
              <select v-model="form.district_id" @change="onDistrictChange"
                class="block w-2/3 border border-gray-300 rounded-md shadow-sm p-2" :disabled="!form.province_id">
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
                class="block w-2/3 border border-gray-300 rounded-md shadow-sm p-2" :disabled="!form.district_id">
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
                  placeholder="VD: 01 Cù Chính Lan, Phường Hiệp Thành"></textarea>
                <p class="text-sm text-gray-500 mt-1">Vui lòng nhập địa chỉ cụ thể để giao hàng nhanh hơn.</p>
              </div>
            </div>

            <!-- Loại địa chỉ -->
            <div class="flex items-start">
              <label class="block text-sm font-medium text-gray-700 w-1/3 pt-2">Loại địa chỉ</label>
              <div class="w-2/3 mt-1 flex flex-col space-y-2">
                <label class="inline-flex items-center">
                  <input type="radio" v-model="form.address_type" name="address_type" value="home"
                    class="form-radio text-blue-600" />
                  <span class="ml-2">Nhà riêng / Chung cư</span>
                </label>
                <label class="inline-flex items-center">
                  <input type="radio" v-model="form.address_type" name="address_type" value="company"
                    class="form-radio text-blue-600" />
                  <span class="ml-2">Cơ quan / Công ty</span>
                </label>
              </div>
            </div>

            <!-- Địa chỉ mặc định -->
            <div class="flex items-center">
              <label class="inline-flex items-center">
                <input type="checkbox" v-model="form.is_default" class="form-checkbox text-blue-600" />
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
import { ref, onMounted, computed, reactive, watch } from 'vue';
import { useHead, useRuntimeConfig } from '#app';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useAuthHeaders } from '~/composables/useAuthHeaders';
import { useToast } from '~/composables/useToast';

useHead({
  title: 'Địa chỉ giao hàng | Thanh toán',
  meta: [
    { name: 'description', content: 'Chọn hoặc thêm địa chỉ giao hàng để hoàn tất đơn hàng của bạn.' },
    { name: 'robots', content: 'noindex, nofollow' },
    { property: 'og:title', content: 'Địa chỉ giao hàng - Thanh toán' },
    { property: 'og:description', content: 'Quản lý địa chỉ giao hàng để nhận hàng nhanh chóng.' }
  ]
});

const { showSuccess, showError } = useToast();
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;

const showNewAddressForm = ref(false);
const shippingFee = ref(0);
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const addresses = ref([]);
const editAddress = ref(null);
const loading = ref(true);
const loadingEdit = ref(false);

const form = reactive({
  name: '',
  phone: '',
  province_id: '',
  district_id: '',
  ward_code: '',
  detail: '',
  address_type: 'home',
  is_default: false
});

const provinceMap = computed(() => new Map(provinces.value.map(p => [p.ProvinceID, p.ProvinceName])));
const districtMap = computed(() => new Map(districts.value.map(d => [d.DistrictID, d.DistrictName])));
const wardMap = computed(() => new Map(wards.value.map(w => [`${w.WardCode}-${w.DistrictID}`, w.WardName])));

const isFormValid = computed(() => {
  return (
    form.name.trim() &&
    form.phone.trim() &&
    form.province_id &&
    form.district_id &&
    form.ward_code &&
    form.detail.trim()
  );
});

const getProvinceName = (id) => {
  if (!id || !provinces.value.length) return '';
  return provinceMap.value.get(id) || 'Đang tải...';
};

const getDistrictName = (id) => {
  if (!id || !districts.value.length) return '';
  return districtMap.value.get(id) || 'Đang tải...';
};

const getWardName = (code, did) => {
  if (!code || !did || !wards.value.length) return '';
  return wardMap.value.get(`${code}-${did}`) || 'Đang tải...';
};

const isCacheValid = (key, ttl = 24 * 60 * 60 * 1000) => {
  const cached = localStorage.getItem(key);
  if (!cached) return false;
  const { data, timestamp } = JSON.parse(cached);
  return Date.now() - timestamp < ttl;
};

const loadProvinces = async () => {
  const cacheKey = 'ghn_provinces';
  if (isCacheValid(cacheKey)) {
    provinces.value = JSON.parse(localStorage.getItem(cacheKey)).data;
    return;
  }
  try {
    const res = await axios.get(`${apiBase}/ghn/provinces`);
    provinces.value = res.data.data || [];
    localStorage.setItem(cacheKey, JSON.stringify({ data: provinces.value, timestamp: Date.now() }));
  } catch (error) {
    showError('Không tải được danh sách tỉnh.');
    console.error('Error loading provinces:', error);
  }
};

const loadDistricts = async (provinceId) => {
  if (!provinceId) return;
  const cacheKey = `ghn_districts_${provinceId}`;
  if (isCacheValid(cacheKey)) {
    districts.value = [...districts.value, ...JSON.parse(localStorage.getItem(cacheKey)).data];
    return;
  }
  try {
    const res = await axios.post(`${apiBase}/ghn/districts`, { province_id: provinceId });
    const newDistricts = res.data.data || [];
    districts.value = [...districts.value, ...newDistricts];
    localStorage.setItem(cacheKey, JSON.stringify({ data: newDistricts, timestamp: Date.now() }));
  } catch (error) {
    showError('Không tải được danh sách quận.');
    console.error('Error loading districts:', error);
  }
};

const loadWards = async (districtId) => {
  if (!districtId) return;
  const cacheKey = `ghn_wards_${districtId}`;
  if (isCacheValid(cacheKey)) {
    wards.value = [...wards.value, ...JSON.parse(localStorage.getItem(cacheKey)).data];
    return;
  }
  try {
    const res = await axios.post(`${apiBase}/ghn/wards`, { district_id: districtId });
    const newWards = res.data.data || [];
    wards.value = [...wards.value, ...newWards];
    localStorage.setItem(cacheKey, JSON.stringify({ data: newWards, timestamp: Date.now() }));
  } catch (error) {
    showError('Không tải được danh sách phường.');
    console.error('Error loading wards:', error);
  }
};

const loadAddresses = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`${apiBase}/address`, useAuthHeaders());
    addresses.value = res.data.data || [];
    const uniqueProvinceIds = [...new Set(addresses.value.map(addr => addr.province_id))].filter(Boolean);
    const uniqueDistrictIds = [...new Set(addresses.value.map(addr => addr.district_id))].filter(Boolean);
    await Promise.allSettled([
      ...uniqueProvinceIds.map(pid => loadDistricts(pid)),
      ...uniqueDistrictIds.map(did => loadWards(did))
    ]);
  } catch (error) {
    showError('Không thể tải địa chỉ.');
    console.error('Error loading addresses:', error);
  } finally {
    loading.value = false;
  }
};

const calculateShippingFee = async () => {
  const { province_id, district_id, ward_code } = form;
  if (!province_id || !district_id || !ward_code) {
    shippingFee.value = 0;
    return;
  }

};

const startEditAddress = async (addr) => {
  console.time('startEditAddress');
  loadingEdit.value = true;
  editAddress.value = addr;
  Object.assign(form, {
    name: addr.name,
    phone: addr.phone,
    province_id: addr.province_id,
    district_id: addr.district_id,
    ward_code: addr.ward_code,
    detail: addr.detail,
    address_type: addr.address_type,
    is_default: addr.is_default === 1
  });
  showNewAddressForm.value = true;

  try {
    if (!provinces.value.length) {
      await loadProvinces();
    }
    if (addr.province_id) {
      await loadDistricts(addr.province_id);
    }
    if (addr.district_id) {
      await loadWards(addr.district_id);
    }
  } finally {
    loadingEdit.value = false;
    console.timeEnd('startEditAddress');
  }
};

const submitForm = async () => {
  if (!isFormValid.value) {
    showError('Vui lòng điền đầy đủ thông tin.')
    return
  }

  const payload = {
    name: form.name.trim(),
    phone: form.phone.trim(),
    province_id: form.province_id,
    district_id: form.district_id,
    ward_code: form.ward_code,
    detail: form.detail.trim(),
    address_type: form.address_type,
    is_default: form.is_default ? 1 : 0
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
    // Reload trang để làm mới dữ liệu
    window.location.reload()
  } catch (error) {
    showError(error.response?.data?.message || 'Lỗi khi lưu địa chỉ.')
    console.error('Error submitting form:', error)
  } finally {
    resetForm()
    showNewAddressForm.value = false
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
  } catch (error) {
    showError('Không thể xóa địa chỉ.')
    console.error('Error deleting address:', error)
  }
}



const toggleNewAddressForm = () => {
  showNewAddressForm.value = !showNewAddressForm.value
  if (!showNewAddressForm.value) resetForm()
}

const resetForm = () => {
  Object.assign(form, {
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

const onProvinceChange = async () => {
  form.district_id = ''
  form.ward_code = ''
  districts.value = []
  wards.value = []
  if (form.province_id) {
    console.log('Selected province_id:', form.province_id)
    await loadDistricts(form.province_id)
  }
}

const onDistrictChange = async () => {
  form.ward_code = ''
  wards.value = []
  if (form.district_id) {
    console.log('Selected district_id:', form.district_id)
    await loadWards(form.district_id)
  }
}

onMounted(async () => {
  await Promise.all([
    loadProvinces(),
    loadAddresses()
  ])
})

watch([() => form.province_id, () => form.district_id, () => form.ward_code], async ([province_id, district_id, ward_code]) => {
  if (province_id && district_id && ward_code) {
    await calculateShippingFee();
  } else {
    shippingFee.value = 0;
  }
});

watchEffect(() => {
  if (form.ward_code) calculateShippingFee()
})
</script>

<style>
body {
  margin: 0;
}
</style>