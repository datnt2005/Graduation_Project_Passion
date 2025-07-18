<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-white relative">
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white">
      <RegisterSteps :currentStep="0" />
    </div>
    <div class="hidden lg:flex items-center justify-center">
      <img src="/images/SellerCenter2.png" alt="Đăng ký bán hàng" class="max-h-[500px] rounded-xl shadow-md" />
    </div>
    <div class="flex items-center justify-center px-8 py-20">
      <div class="w-full max-w-xl">
        <div class="mb-8 mt-6">
          <h1 class="text-2xl font-bold">Tạo tài khoản bán hàng</h1>
          <p class="text-gray-600">Tham gia để tiếp cận hàng triệu khách hàng</p>
        </div>

        <form @submit.prevent="submitStep1" class="space-y-5">
          <div>
            <label class="block mb-1 font-medium">Tên cửa hàng *</label>
            <input v-model="form.store_name" type="text" class="input" required />
            <p v-if="errors.store_name" class="text-red-500 text-sm mt-1">{{ errors.store_name[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 font-medium">Số điện thoại *</label>
            <input v-model="form.phone_number" type="text" class="input" required />
            <p v-if="errors.phone_number" class="text-red-500 text-sm mt-1">{{ errors.phone_number[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 font-medium">Tỉnh/Thành phố *</label>
            <select v-model="form.province_id" class="input" @change="loadDistricts" required>
              <option value="" disabled>Chọn tỉnh/thành phố</option>
              <option v-for="province in provinces" :key="province.ProvinceID" :value="province.ProvinceID">
                {{ province.ProvinceName }}
              </option>
            </select>
            <p v-if="errors.province_id" class="text-red-500 text-sm mt-1">{{ errors.province_id[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 font-medium">Quận/Huyện *</label>
            <select v-model="form.district_id" class="input" @change="loadWards" required :disabled="!form.province_id || loadingDistricts">
              <option value="" disabled>Chọn quận/huyện</option>
              <option v-for="district in districts" :key="district.DistrictID" :value="district.DistrictID">
                {{ district.DistrictName }}
              </option>
            </select>
            <p v-if="errors.district_id" class="text-red-500 text-sm mt-1">{{ errors.district_id[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 font-medium">Phường/Xã *</label>
            <select v-model="form.ward_id" class="input" required :disabled="!form.district_id || loadingWards">
              <option value="" disabled>Chọn phường/xã</option>
              <option v-for="ward in wards" :key="ward.WardCode" :value="ward.WardCode">
                {{ ward.WardName }}
              </option>
            </select>
            <p v-if="errors.ward_id" class="text-red-500 text-sm mt-1">{{ errors.ward_id[0] }}</p>
          </div>

          <div>
            <label class="block mb-1 font-medium">Địa chỉ chi tiết *</label>
            <input v-model="form.address" type="text" class="input" required placeholder="Số nhà, tên đường..." />
            <p v-if="errors.address" class="text-red-500 text-sm mt-1">{{ errors.address[0] }}</p>
          </div>

          <button type="submit" class="btn btn-primary w-full h-11" :disabled="loading">
            <span v-if="!loading">Tiếp theo</span>
            <span v-else>Đang xử lý...</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useRouter, useRuntimeConfig } from '#app';
import { useToast } from '~/composables/useToast';
import RegisterSteps from '@/components/RegisterSteps.vue';

const { toast } = useToast();
const router = useRouter();
const config = useRuntimeConfig();
const api = config.public.apiBaseUrl;

// Form data
const form = reactive({
  store_name: '',
  phone_number: '',
  province_id: '',
  district_id: '',
  ward_id: '',
  address: '',
});

// Dữ liệu cho dropdown
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

// Trạng thái
const loading = ref(false);
const loadingDistricts = ref(false);
const loadingWards = ref(false);
const errors = ref({});

// Load dữ liệu từ localStorage nếu có
onMounted(async () => {
  const saved = localStorage.getItem('register_step1');
  if (saved) {
    const savedData = JSON.parse(saved);
    console.log('Loaded from localStorage in step1:', savedData); // Debug
    Object.assign(form, {
      store_name: savedData.store_name || '',
      phone_number: savedData.phone_number || '',
      province_id: savedData.province_id ? Number(savedData.province_id) : '',
      district_id: savedData.district_id ? Number(savedData.district_id) : '',
      ward_id: savedData.ward_id || '',
      address: savedData.address || '',
    });
    if (savedData.province_id && !isNaN(Number(savedData.province_id))) {
      await loadDistricts();
      if (savedData.district_id && districts.value.some(d => d.DistrictID === Number(savedData.district_id))) {
        form.district_id = Number(savedData.district_id);
        await loadWards();
        if (savedData.ward_id && wards.value.some(w => w.WardCode === savedData.ward_id)) {
          form.ward_id = savedData.ward_id;
        }
      }
    }
  }
  await loadProvinces();
});

// Tải danh sách tỉnh
const loadProvinces = async () => {
  try {
    const response = await axios.get(`${api}/ghn/provinces`, {
      headers: { Accept: 'application/json' },
    });
    if (response.data && Array.isArray(response.data.data)) {
      provinces.value = response.data.data;
      console.log('Provinces loaded in step1:', provinces.value); // Debug
    } else {
      throw new Error('Invalid provinces data format');
    }
  } catch (error) {
    toast('error', 'Không thể tải danh sách tỉnh/thành phố. Vui lòng thử lại.');
    console.error('Error loading provinces:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status,
    });
    provinces.value = [];
  }
};

// Tải danh sách quận/huyện
const loadDistricts = async () => {
  if (!form.province_id || isNaN(Number(form.province_id))) {
    districts.value = [];
    wards.value = [];
    form.district_id = '';
    form.ward_id = '';
    return;
  }
  loadingDistricts.value = true;
  try {
    const response = await axios.get(`${api}/ghn/districts`, {
      params: { province_id: Number(form.province_id) },
      headers: { Accept: 'application/json' },
    });
    if (response.data && Array.isArray(response.data.data)) {
      districts.value = response.data.data;
      console.log('Districts loaded for province_id', form.province_id, ':', districts.value); // Debug
    } else {
      throw new Error('Invalid districts data format');
    }
    wards.value = [];
    form.ward_id = '';
  } catch (error) {
    toast('error', 'Không thể tải danh sách quận/huyện. Vui lòng thử lại.');
    console.error('Error loading districts:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status,
    });
    districts.value = [];
  } finally {
    loadingDistricts.value = false;
  }
};

// Tải danh sách phường/xã
const loadWards = async () => {
  if (!form.district_id || isNaN(Number(form.district_id))) {
    wards.value = [];
    form.ward_id = '';
    return;
  }
  loadingWards.value = true;
  try {
    const response = await axios.get(`${api}/ghn/wards`, {
      params: { district_id: Number(form.district_id) },
      headers: { Accept: 'application/json' },
    });
    if (response.data && Array.isArray(response.data.data)) {
      wards.value = response.data.data;
      console.log('Wards loaded for district_id', form.district_id, ':', wards.value); // Debug
    } else {
      throw new Error('Invalid wards data format');
    }
  } catch (error) {
    toast('error', 'Không thể tải danh sách phường/xã. Vui lòng thử lại.');
    console.error('Error loading wards:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status,
    });
    wards.value = [];
  } finally {
    loadingWards.value = false;
  }
};

// Submit form
const submitStep1 = async () => {
  loading.value = true;
  errors.value = {};

  // Validation
  if (!form.store_name) {
    errors.value.store_name = ['Vui lòng nhập tên cửa hàng'];
  }
  if (!form.phone_number || !/^[0-9]{10,11}$/.test(form.phone_number)) {
    errors.value.phone_number = ['Số điện thoại phải có 10 hoặc 11 chữ số'];
  }
  if (!form.province_id || isNaN(Number(form.province_id)) || !provinces.value.some(p => p.ProvinceID === Number(form.province_id))) {
    errors.value.province_id = ['Vui lòng chọn tỉnh/thành phố hợp lệ'];
  }
  if (!form.district_id || isNaN(Number(form.district_id)) || !districts.value.some(d => d.DistrictID === Number(form.district_id))) {
    errors.value.district_id = ['Vui lòng chọn quận/huyện hợp lệ'];
  }
  if (!form.ward_id || !wards.value.some(w => w.WardCode === form.ward_id)) {
    errors.value.ward_id = ['Vui lòng chọn phường/xã hợp lệ'];
  }
  if (!form.address) {
    errors.value.address = ['Vui lòng nhập địa chỉ chi tiết'];
  }

  if (Object.keys(errors.value).length > 0) {
    toast('error', 'Vui lòng điền đầy đủ và đúng thông tin.');
    console.log('Validation errors in step1:', errors.value); // Debug
    loading.value = false;
    return;
  }

  try {
    // Chuẩn hóa dữ liệu trước khi lưu
    const formData = {
      store_name: form.store_name,
      phone_number: form.phone_number,
      province_id: Number(form.province_id), // Đảm bảo là số nguyên
      district_id: Number(form.district_id), // Đảm bảo là số nguyên
      ward_id: form.ward_id, // ward_id là chuỗi
      address: form.address,
    };

    // Log dữ liệu trước khi lưu
    console.log('Saving to localStorage in step1:', formData); // Debug
    // Lưu vào localStorage
    localStorage.setItem('register_step1', JSON.stringify(formData));

    toast('success', 'Lưu thông tin bước 1 thành công');
    router.push('/seller/RegisterSellerSteps/step2');
  } catch (error) {
    toast('error', 'Có lỗi xảy ra khi lưu thông tin bước 1');
    console.error('Error saving step 1:', error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}
.btn-primary {
  @apply bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition;
}
</style>