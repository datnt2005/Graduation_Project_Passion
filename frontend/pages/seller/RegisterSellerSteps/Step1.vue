<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-gradient-to-br from-white to-blue-50 relative">
    <!-- Header với stepper -->
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 mt-6 mb-6 bg-white shadow-sm">
      <RegisterSteps :currentStep="0" />
    </div>

    <!-- Phần hình ảnh bên trái -->
    <div class="hidden lg:flex flex-col items-center justify-center p-8">
      <img 
        src="/images/SellerCenter2.png" 
        alt="Đăng ký bán hàng" 
        class="max-h-[500px] rounded-xl shadow-lg transition-all duration-500 hover:scale-105" 
      />
      <div class="mt-8 text-center max-w-md">
        <h2 class="text-xl font-bold text-blue-800 mb-2">Bán hàng cùng chúng tôi</h2>
        <p class="text-blue-700 opacity-80">Tiếp cận hàng triệu khách hàng và phát triển doanh nghiệp của bạn với nền tảng thương mại điện tử hàng đầu</p>
      </div>
    </div>

    <!-- Phần form bên phải -->
    <div class="flex items-center justify-center px-8 py-8 pt-32 lg:pt-28">
      <div style="margin-top: 180px" class="w-full max-w-xl bg-white p-8 rounded-2xl shadow-lg ">
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Tạo tài khoản bán hàng</h1>
          <p class="text-gray-600">Bắt đầu hành trình kinh doanh trực tuyến của bạn</p>
        </div>

        <form @submit.prevent="submitStep1" class="space-y-6">
          <!-- Tên cửa hàng -->
          <div class="form-group">
            <label class="form-label">Tên cửa hàng <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                <i class="fas fa-store"></i>
              </span>
              <input 
                v-model="form.store_name" 
                type="text" 
                class="input pl-10" 
                placeholder="Nhập tên cửa hàng của bạn"
                required 
              />
            </div>
            <p v-if="errors.store_name" class="error-message">{{ errors.store_name[0] }}</p>
          </div>

          <!-- Số điện thoại -->
          <div class="form-group">
            <label class="form-label">Số điện thoại <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                <i class="fas fa-phone"></i>
              </span>
              <input 
                v-model="form.phone_number" 
                type="text" 
                class="input pl-10" 
                placeholder="Nhập số điện thoại liên hệ"
                required 
              />
            </div>
            <p v-if="errors.phone_number" class="error-message">{{ errors.phone_number[0] }}</p>
          </div>

          <!-- Địa chỉ -->
          <div  class="bg-blue-50 p-4 rounded-lg">
            <h3 class="font-medium text-blue-800 mb-3 flex items-center">
              <i class="fas fa-map-marker-alt mr-3"></i> Địa chỉ cửa hàng
            </h3>
            
            <!-- Tỉnh/Thành phố -->
            <div class="form-group">
              <label class="form-label">Tỉnh/Thành phố <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                  <i class="fas fa-city"></i>
                </span>
                <select 
                  v-model="form.province_id" 
                  class="input pl-10 appearance-none" 
                  @change="loadDistricts" 
                  required
                >
                  <option value="" disabled>Chọn tỉnh/thành phố</option>
                  <option v-for="province in provinces" :key="province.ProvinceID" :value="province.ProvinceID">
                    {{ province.ProvinceName }}
                  </option>
                </select>
              </div>
              <p v-if="errors.province_id" class="error-message">{{ errors.province_id[0] }}</p>
            </div>

            <!-- Quận/Huyện -->
            <div class="form-group">
              <label class="form-label">Quận/Huyện <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                  <i class="fas fa-building"></i>
                </span>
                <select 
                  v-model="form.district_id" 
                  class="input pl-10 appearance-none" 
                  @change="loadWards" 
                  required 
                  :disabled="!form.province_id || loadingDistricts"
                >
                  <option value="" disabled>{{ loadingDistricts ? 'Đang tải...' : 'Chọn quận/huyện' }}</option>
                  <option v-for="district in districts" :key="district.DistrictID" :value="district.DistrictID">
                    {{ district.DistrictName }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <i v-if="loadingDistricts" class="fas fa-spinner fa-spin text-blue-500"></i>
                  <i v-else class="fas fa-chevron-down text-gray-400"></i>
                </div>
              </div>
              <p v-if="errors.district_id" class="error-message">{{ errors.district_id[0] }}</p>
            </div>

            <!-- Phường/Xã -->
            <div class="form-group">
              <label class="form-label">Phường/Xã <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                  <i class="fas fa-home"></i>
                </span>
                <select 
                  v-model="form.ward_id" 
                  class="input pl-10 appearance-none" 
                  required 
                  :disabled="!form.district_id || loadingWards"
                >
                  <option value="" disabled>{{ loadingWards ? 'Đang tải...' : 'Chọn phường/xã' }}</option>
                  <option v-for="ward in wards" :key="ward.WardCode" :value="ward.WardCode">
                    {{ ward.WardName }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <i v-if="loadingWards" class="fas fa-spinner fa-spin text-blue-500"></i>
                  <i v-else class="fas fa-chevron-down text-gray-400"></i>
                </div>
              </div>
              <p v-if="errors.ward_id" class="error-message">{{ errors.ward_id[0] }}</p>
            </div>

            <!-- Địa chỉ chi tiết -->
            <div class="form-group">
              <label class="form-label">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                  <i class="fas fa-map-signs"></i>
                </span>
                <input 
                  v-model="form.address" 
                  type="text" 
                  class="input pl-10" 
                  required 
                  placeholder="Số nhà, tên đường..."
                />
              </div>
              <p v-if="errors.address" class="error-message">{{ errors.address[0] }}</p>
            </div>
          </div>

          <!-- Button -->
          <div class="pt-4">
            <button 
              type="submit" 
              class="btn-primary w-full h-12 flex items-center justify-center gap-2 text-base" 
              :disabled="loading"
            >
              <span v-if="!loading">Tiếp theo</span>
              <span v-else class="flex items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý
              </span>
              <i class="fas fa-arrow-right"></i>
            </button>
          </div>
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
const loadFromLocalStorage = () => {
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
      loadDistricts();
      if (savedData.district_id && districts.value.some(d => d.DistrictID === Number(savedData.district_id))) {
        form.district_id = Number(savedData.district_id);
        loadWards();
        if (savedData.ward_id && wards.value.some(w => w.WardCode === savedData.ward_id)) {
          form.ward_id = savedData.ward_id;
        }
      }
    }
  }
};

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

onMounted(() => {
  loadFromLocalStorage();
  loadProvinces();
});
</script>

<style scoped>
.form-group {
  @apply mb-4;
}

.form-label {
  @apply block mb-1.5 font-medium text-gray-700 text-sm;
}

.input {
  @apply w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200;
}

.input.pl-10 {
  @apply pl-12;
}

.input:hover {
  @apply border-gray-400;
}

.input:disabled {
  @apply bg-gray-100 cursor-not-allowed;
}

.btn-primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed;
}

.error-message {
  @apply text-red-500 text-xs mt-1 ml-1;
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.form-group {
  animation: fadeIn 0.3s ease-out forwards;
  animation-delay: calc(var(--index, 0) * 0.05s);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .btn-primary {
    @apply h-12;
  }
 
}



</style>
