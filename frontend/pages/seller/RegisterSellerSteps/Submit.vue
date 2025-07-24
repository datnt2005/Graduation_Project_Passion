<template>
  <div class="min-h-screen bg-gradient-to-br from-white to-blue-50 px-4 py-10 flex items-start justify-center relative">
    <!-- Header với stepper -->
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white shadow-sm">
      <RegisterSteps :currentStep="4" />
    </div>

    <div class="w-full max-w-4xl pt-32 mt-24">
      <!-- Header Section -->
      <div class="text-center mb-8">
        <div class="flex flex-col items-center gap-4 mb-6">
          <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-full p-4 shadow-lg">
            <i class="fas fa-check-circle text-2xl"></i>
          </div>
          <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Xác nhận gửi đăng ký</h2>
            <p class="text-gray-600 max-w-md mx-auto">
              Hệ thống sẽ xem xét và duyệt hồ sơ trong vòng 
              <span class="font-semibold text-green-600">1–3 ngày làm việc</span>
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Thông tin đăng ký -->
        <div class="bg-white rounded-2xl shadow-lg p-6" v-if="step1">
          <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-3">
            <div class="bg-blue-100 text-blue-600 rounded-lg p-2">
              <i class="fas fa-store"></i>
            </div>
            Thông tin cửa hàng
          </h3>
          
          <div class="space-y-4">
            <div class="info-item">
              <span class="info-label">Tên cửa hàng:</span>
              <span class="info-value">{{ step1.store_name || 'Chưa nhập' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Số điện thoại:</span>
              <span class="info-value">{{ step1.phone_number || 'Chưa nhập' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Địa chỉ:</span>
              <span class="info-value">
                {{ step1.address || 'Chưa nhập' }}<br>
                <span class="text-sm text-gray-500">
                  {{ getWardName(step1.ward_id) }}, {{ getDistrictName(step1.district_id) }}, {{ getProvinceName(step1.province_id) }}
                </span>
              </span>
            </div>
          </div>
        </div>

        <!-- Thông tin vận chuyển và thuế -->
        <div class="space-y-6">
          <!-- Vận chuyển -->
          <div class="bg-white rounded-2xl shadow-lg p-6" v-if="step2">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-3">
              <div class="bg-orange-100 text-orange-600 rounded-lg p-2">
                <i class="fas fa-shipping-fast"></i>
              </div>
              Phương thức vận chuyển
            </h3>
            
            <div class="space-y-2">
              <div v-if="step2.shipping_options?.express" class="flex items-center gap-2 text-sm">
                <i class="fas fa-check text-green-500"></i>
                <span>Giao hàng nhanh (Express)</span>
              </div>
              <div v-if="step2.shipping_options?.standard" class="flex items-center gap-2 text-sm">
                <i class="fas fa-check text-green-500"></i>
                <span>Giao hàng tiêu chuẩn (Standard)</span>
              </div>
              <div v-if="!step2.shipping_options?.express && !step2.shipping_options?.standard" class="text-red-500 text-sm">
                Chưa chọn phương thức vận chuyển
              </div>
            </div>
          </div>

          <!-- Thông tin thuế -->
          <div class="bg-white rounded-2xl shadow-lg p-6" v-if="step3">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-3">
              <div class="bg-purple-100 text-purple-600 rounded-lg p-2">
                <i class="fas fa-file-invoice-dollar"></i>
              </div>
              Thông tin thuế
            </h3>
            
            <div class="space-y-3">
              <div class="info-item">
                <span class="info-label">Loại hình:</span>
                <span class="info-value">
                  {{ step3.seller_type === 'personal' ? 'Cá nhân' : step3.seller_type === 'business' ? 'Doanh nghiệp' : 'Chưa chọn' }}
                </span>
              </div>
              <div class="info-item">
                <span class="info-label">Mã số thuế:</span>
                <span class="info-value">{{ step3.tax_code || 'Chưa nhập' }}</span>
              </div>
              <div v-if="step3.seller_type === 'business'" class="info-item">
                <span class="info-label">Tên doanh nghiệp:</span>
                <span class="info-value">{{ step3.business_name || 'Chưa nhập' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quy trình xử lý -->
      <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
          <div class="bg-indigo-100 text-indigo-600 rounded-lg p-2">
            <i class="fas fa-tasks"></i>
          </div>
          Quy trình xử lý hồ sơ
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Bước 1 -->
          <div class="process-step completed">
            <div class="step-icon">
              <i class="fas fa-check"></i>
            </div>
            <div class="step-content">
              <h4 class="step-title">Gửi đăng ký</h4>
              <p class="step-description">Hoàn thành thông tin cơ bản</p>
              <div class="step-status completed">Đã hoàn thành</div>
            </div>
          </div>

          <!-- Bước 2 -->
          <div class="process-step current">
            <div class="step-icon">
              <span class="step-number">2</span>
            </div>
            <div class="step-content">
              <h4 class="step-title">Xem xét hồ sơ</h4>
              <p class="step-description">Kiểm tra và xác minh thông tin</p>
              <div class="step-status current">1–2 ngày làm việc</div>
            </div>
          </div>

          <!-- Bước 3 -->
          <div class="process-step pending">
            <div class="step-icon">
              <span class="step-number">3</span>
            </div>
            <div class="step-content">
              <h4 class="step-title">Thông báo kết quả</h4>
              <p class="step-description">Qua email hoặc hệ thống</p>
              <div class="step-status pending">Chờ xử lý</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Thông báo lỗi -->
      <div v-if="!isFormValid || errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
          <div class="text-red-600 mt-0.5">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div>
            <h4 class="font-medium text-red-800 mb-1">Cần kiểm tra lại thông tin</h4>
            <p class="text-red-700 text-sm">
              {{ errorMessage || 'Vui lòng kiểm tra lại thông tin ở các bước trước. Một số trường bắt buộc đang thiếu hoặc không hợp lệ.' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row justify-center gap-4 mb-8">
        <button
          @click="goBackStep"
          class="btn-outline flex items-center justify-center gap-2 px-8 py-3 text-base"
          :disabled="loading"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Quay lại</span>
        </button>
        <button
          @click="submit"
          :disabled="loading || !isFormValid"
          class="btn-primary flex items-center justify-center gap-2 px-8 py-3 text-base min-w-[180px]"
        >
          <span v-if="!loading" class="flex items-center gap-2">
            <i class="fas fa-paper-plane"></i>
            Gửi đăng ký
          </span>
          <span v-else class="flex items-center gap-2">
            <i class="fas fa-spinner fa-spin"></i>
            Đang gửi...
          </span>
        </button>
      </div>

      <!-- Terms and conditions -->
      <div class="bg-gray-50 rounded-xl p-4 text-center">
        <p class="text-sm text-gray-600">
          Khi nhấn <span class="font-medium text-gray-800">Gửi đăng ký</span>, bạn đồng ý với
          <a href="/seller/terms-of-service" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
            Điều khoản dịch vụ
          </a>
          và
          <a href="/seller/privacy-policy" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
            Chính sách bảo mật
          </a>
          của chúng tôi.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useRuntimeConfig } from '#app';
import axios from 'axios';
import { useToast } from '~/composables/useToast';
import RegisterSteps from '@/components/RegisterSteps.vue';

const { toast } = useToast();
const router = useRouter();
const config = useRuntimeConfig();
const api = config.public.apiBaseUrl;

const loading = ref(false);
const errorMessage = ref('');
const lastStepWithError = ref('step1');

const step1 = ref(null);
const step2 = ref(null);
const step3 = ref(null);
const step4 = ref(null);

const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

// Load provinces, districts, and wards for display
const loadProvinces = async () => {
  try {
    const response = await axios.get(`${api}/ghn/provinces`, {
      headers: { Accept: 'application/json' },
    });
    provinces.value = response.data.data || [];
    console.log('Provinces loaded in submit.vue:', provinces.value); // Debug
  } catch (error) {
    toast('error', 'Không thể tải danh sách tỉnh/thành phố.');
    console.error('Error loading provinces:', error.response?.data || error.message);
  }
};

const loadDistricts = async (province_id) => {
  if (!province_id) return;
  try {
    const response = await axios.get(`${api}/ghn/districts`, {
      params: { province_id: Number(province_id) },
      headers: { Accept: 'application/json' },
    });
    districts.value = response.data.data || [];
    console.log('Districts loaded in submit.vue:', districts.value); // Debug
  } catch (error) {
    toast('error', 'Không thể tải danh sách quận/huyện.');
    console.error('Error loading districts:', error.response?.data || error.message);
  }
};

const loadWards = async (district_id) => {
  if (!district_id) return;
  try {
    const response = await axios.get(`${api}/ghn/wards`, {
      params: { district_id: Number(district_id) },
      headers: { Accept: 'application/json' },
    });
    wards.value = response.data.data || [];
    console.log('Wards loaded in submit.vue:', wards.value); // Debug
  } catch (error) {
    toast('error', 'Không thể tải danh sách phường/xã.');
    console.error('Error loading wards:', error.response?.data || error.message);
  }
};

const getProvinceName = (province_id) => {
  const province = provinces.value.find(p => p.ProvinceID === Number(province_id));
  return province ? province.ProvinceName : 'Chưa chọn';
};

const getDistrictName = (district_id) => {
  const district = districts.value.find(d => d.DistrictID === Number(district_id));
  return district ? district.DistrictName : 'Chưa chọn';
};

const getWardName = (ward_id) => {
  const ward = wards.value.find(w => w.WardCode === ward_id);
  return ward ? ward.WardName : 'Chưa chọn';
};

// Hàm chuyển đổi base64 thành file
const dataURLtoFile = (dataurl, filename) => {
  const arr = dataurl.split(',');
  const mime = arr[0].match(/:(.*?);/)[1];
  const bstr = atob(arr[1]);
  let n = bstr.length;
  const u8arr = new Uint8Array(n);
  while (n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }
  return new File([u8arr], filename, { type: mime });
};

const submit = async () => {
  if (!isFormValid.value) {
    toast('error', errorMessage.value);
    return;
  }
  
  errorMessage.value = '';
  loading.value = true;
  const token = localStorage.getItem('access_token');
  
  try {
    const formData = new FormData();
    
    // Step 1
    formData.append('store_name', step1.value.store_name || '');
    formData.append('phone_number', step1.value.phone_number || '');
    formData.append('province_id', step1.value.province_id ? Number(step1.value.province_id) : '');
    formData.append('district_id', step1.value.district_id ? Number(step1.value.district_id) : '');
    formData.append('ward_id', step1.value.ward_id || '');
    formData.append('address', step1.value.address || '');
    
    // Step 2
    const shippingOptionsObj = step2.value.shipping_options
      ? Object.keys(step2.value.shipping_options).reduce((obj, key) => {
          if (step2.value.shipping_options[key]) obj[key] = true;
          return obj;
        }, {})
      : {};
    formData.append('shipping_options', JSON.stringify(shippingOptionsObj));
    
    // Step 3
    formData.append('seller_type', step3.value.seller_type || '');
    formData.append('tax_code', step3.value.tax_code || '');
    formData.append('business_name', step3.value.business_name || '');
    formData.append('business_email', step3.value.business_email || '');
    
    // Step 4
    formData.append('identity_card_number', step4.value.identity_card_number || '');
    formData.append('date_of_birth', step4.value.date_of_birth || '');
    formData.append('personal_address', step4.value.personal_address || '');
    
    // ID card images
    if (step4.value.frontImageBase64) {
      const frontFile = dataURLtoFile(step4.value.frontImageBase64, 'cccd-front.png');
      if (frontFile.size > 4 * 1024 * 1024) {
        throw new Error('Ảnh mặt trước CCCD vượt quá 4MB.');
      }
      formData.append('id_card_front_url', frontFile);
    }
    
    if (step4.value.backImageBase64) {
      const backFile = dataURLtoFile(step4.value.backImageBase64, 'cccd-back.png');
      if (backFile.size > 4 * 1024 * 1024) {
        throw new Error('Ảnh mặt sau CCCD vượt quá 4MB.');
      }
      formData.append('id_card_back_url', backFile);
    }
    
    // Business identity file
    if (step3.value.seller_type === 'business' && step3.value.identity_card_file_base64) {
      const businessFile = dataURLtoFile(
        step3.value.identity_card_file_base64,
        step3.value.identity_card_file_name || 'business-file.pdf'
      );
      if (businessFile.size > 4 * 1024 * 1024) {
        throw new Error('Tài liệu xác minh doanh nghiệp vượt quá 4MB.');
      }
      formData.append('identity_card_file', businessFile);
    }
    
    const response = await axios.post(`${api}/sellers/register/full`, formData, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      },
    });
    
    // Clear localStorage
    localStorage.removeItem('register_step1');
    localStorage.removeItem('register_step2');
    localStorage.removeItem('register_step3');
    localStorage.removeItem('register_step4');
    
    toast('success', response.data.message || 'Gửi đăng ký thành công! Vui lòng chờ xác minh trong 1-3 ngày làm việc.');
    router.push('/seller/RegisterSellerSteps/Success');
  } catch (err) {
    console.error('Lỗi khi gửi đăng ký:', err);
    console.log('Validation errors:', err.response?.data?.errors || {});
    
    if (err.response?.status === 422) {
      const errors = err.response.data.errors || {};
      errorMessage.value = 'Có lỗi trong hồ sơ đăng ký. Vui lòng kiểm tra và sửa lại thông tin.';
      
      if (errors.store_name || errors.phone_number || errors.province_id || errors.district_id || errors.ward_id || errors.address) {
        lastStepWithError.value = 'step1';
      } else if (errors.shipping_options) {
        lastStepWithError.value = 'step2';
      } else if (errors.tax_code || errors.business_email || errors.seller_type || errors.identity_card_file) {
        lastStepWithError.value = 'step3';
      } else if (errors.identity_card_number || errors.id_card_front_url || errors.date_of_birth || errors.personal_address || errors.id_card_back_url) {
        lastStepWithError.value = 'step4';
      }
      
      for (const field in errors) {
        const messages = errors[field];
        Array.isArray(messages) ? messages.forEach(msg => toast('error', msg)) : toast('error', messages);
      }
    } else if (err.response?.status === 401) {
      errorMessage.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
      toast('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
      router.push('/login');
    } else {
      errorMessage.value = 'Đã xảy ra lỗi khi gửi đăng ký. Vui lòng thử lại sau.';
      toast('error', 'Đã xảy ra lỗi khi gửi đăng ký. Vui lòng thử lại sau.');
    }
  } finally {
    loading.value = false;
  }
};

const goBackStep = () => {
  router.push(`/seller/RegisterSellerSteps/${lastStepWithError.value}`);
};

onMounted(async () => {
  // Khởi tạo giá trị mặc định để tránh lỗi undefined
  step1.value = JSON.parse(localStorage.getItem('register_step1') || '{}');
  step2.value = JSON.parse(localStorage.getItem('register_step2') || '{}');
  step3.value = JSON.parse(localStorage.getItem('register_step3') || '{}');
  step4.value = JSON.parse(localStorage.getItem('register_step4') || '{}');
  
  console.log('Loaded data in submit.vue:', {
    step1: step1.value,
    step2: step2.value,
    step3: step3.value,
    step4: step4.value,
  }); // Debug
  
  // Load address data for display
  await loadProvinces();
  if (step1.value?.province_id) {
    await loadDistricts(step1.value.province_id);
  }
  if (step1.value?.district_id) {
    await loadWards(step1.value.district_id);
  }
  
  // Kiểm tra token
  if (!localStorage.getItem('access_token')) {
    errorMessage.value = 'Vui lòng đăng nhập để tiếp tục đăng ký.';
    toast('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
    router.push('/login');
  }
});

// Kiểm tra dữ liệu hợp lệ trước khi gửi
const isFormValid = computed(() => {
  const missingFields = [];
  
  // Kiểm tra step1
  if (!step1.value?.store_name) missingFields.push('Tên cửa hàng (Bước 1)');
  if (!step1.value?.phone_number || !/^[0-9]{10,11}$/.test(step1.value.phone_number)) missingFields.push('Số điện thoại (Bước 1)');
  if (!step1.value?.province_id || isNaN(Number(step1.value.province_id))) missingFields.push('Tỉnh/Thành phố (Bước 1)');
  if (!step1.value?.district_id || isNaN(Number(step1.value.district_id))) missingFields.push('Quận/Huyện (Bước 1)');
  if (!step1.value?.ward_id) missingFields.push('Phường/Xã (Bước 1)');
  if (!step1.value?.address) missingFields.push('Địa chỉ chi tiết (Bước 1)');
  
  // Kiểm tra step2
  if (!step2.value?.shipping_options || Object.keys(step2.value.shipping_options || {}).length === 0) missingFields.push('Phương thức vận chuyển (Bước 2)');
  
  // Kiểm tra step3
  if (!step3.value?.seller_type) missingFields.push('Loại hình người bán (Bước 3)');
  if (!step3.value?.tax_code) missingFields.push('Mã số thuế (Bước 3)');
  if (step3.value?.seller_type === 'business' && (!step3.value?.identity_card_file_base64 || !step3.value?.identity_card_file_name)) {
    missingFields.push('Tài liệu xác minh doanh nghiệp (Bước 3)');
  }
  
  // Kiểm tra step4
  if (!step4.value?.identity_card_number || !/^[0-9]{12}$/.test(step4.value.identity_card_number)) missingFields.push('Số CCCD (Bước 4)');
  if (!step4.value?.date_of_birth) missingFields.push('Ngày sinh (Bước 4)');
  if (!step4.value?.personal_address) missingFields.push('Địa chỉ cá nhân (Bước 4)');
  if (!step4.value?.frontImageBase64) missingFields.push('Ảnh mặt trước CCCD (Bước 4)');
  if (!step4.value?.backImageBase64) missingFields.push('Ảnh mặt sau CCCD (Bước 4)');
  
  if (missingFields.length > 0) {
    errorMessage.value = `Thiếu hoặc không hợp lệ: ${missingFields.join(', ')}`;
    console.log('Validation failed:', {
      step1: step1.value,
      step2: step2.value,
      step3: step3.value,
      step4: step4.value,
      missingFields,
    }); // Debug
  } else {
    errorMessage.value = ''; // Reset error message if valid
  }
  
  return missingFields.length === 0;
});
</script>

<style scoped>
.info-item {
  @apply flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-4 py-2 border-b border-gray-100 last:border-b-0;
}

.info-label {
  @apply text-sm text-gray-600 font-medium min-w-[120px];
}

.info-value {
  @apply text-sm text-gray-800 font-medium;
}

.process-step {
  @apply flex flex-col items-center text-center;
}

.step-icon {
  @apply w-12 h-12 rounded-full flex items-center justify-center mb-3 text-white font-semibold;
}

.process-step.completed .step-icon {
  @apply bg-green-500;
}

.process-step.current .step-icon {
  @apply bg-blue-500;
}

.process-step.pending .step-icon {
  @apply bg-gray-300;
}

.step-number {
  @apply text-sm font-bold;
}

.step-content {
  @apply flex-1;
}

.step-title {
  @apply font-semibold text-gray-800 mb-1;
}

.step-description {
  @apply text-sm text-gray-600 mb-2;
}

.step-status {
  @apply text-xs px-2 py-1 rounded-full font-medium;
}

.step-status.completed {
  @apply bg-green-100 text-green-700;
}

.step-status.current {
  @apply bg-blue-100 text-blue-700;
}

.step-status.pending {
  @apply bg-gray-100 text-gray-600;
}

.btn-primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed;
}

.btn-outline {
  @apply border-2 border-gray-300 text-gray-700 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2;
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.bg-white {
  animation: fadeIn 0.4s ease-out forwards;
}

.bg-white:nth-child(2) { animation-delay: 0.1s; }
.bg-white:nth-child(3) { animation-delay: 0.2s; }

/* Responsive */
@media (max-width: 640px) {
  .btn-primary, .btn-outline {
    @apply w-full;
  }
  
  .grid-cols-3 {
    @apply grid-cols-1;
  }
}
</style>
