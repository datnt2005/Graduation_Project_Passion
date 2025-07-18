<template>
  <div class="min-h-screen bg-gradient-to-b from-white to-blue-50 px-4 py-10 flex items-start justify-center relative">
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white">
      <RegisterSteps :currentStep="4" />
    </div>
    <div class="w-full max-w-2xl text-center space-y-6 pt-24">
      <!-- Icon + tiêu đề -->
      <div class="flex flex-col items-center gap-2">
        <div class="bg-blue-100 text-blue-600 rounded-full p-3">
          <i class="fas fa-file-alt fa-lg"></i>
        </div>
        <h2 class="text-2xl font-bold text-blue-700">Xác nhận gửi đăng ký</h2>
        <p class="text-gray-600 text-sm">
          Hệ thống sẽ xem xét và duyệt hồ sơ trong vòng
          <span class="font-medium text-blue-600">1–3 ngày làm việc</span>.
        </p>
      </div>

      <!-- Thông tin đăng ký -->
      <div class="bg-white rounded-xl shadow p-5 text-left space-y-4" v-if="step1">
        <h3 class="font-semibold text-gray-700 flex items-center gap-2">
          <i class="fas fa-check-circle text-green-500"></i>
          Thông tin đăng ký
        </h3>
        <div class="bg-gray-50 rounded px-4 py-3 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Tên cửa hàng:</span>
            <span class="font-medium text-gray-700">{{ step1.store_name || 'Chưa nhập' }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Số điện thoại:</span>
            <span class="font-medium text-gray-700">{{ step1.phone_number || 'Chưa nhập' }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Địa chỉ lấy hàng:</span>
            <span class="font-medium text-gray-700">
              {{ step1.address || 'Chưa nhập' }}, {{ getWardName(step1.ward_id) }}, {{ getDistrictName(step1.district_id) }}, {{ getProvinceName(step1.province_id) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Quy trình xử lý -->
      <div class="bg-white rounded-xl shadow p-5 text-left space-y-4">
        <h3 class="font-semibold text-gray-700 flex items-center gap-2">
          <i class="fas fa-stream text-blue-500"></i>
          Quy trình xử lý
        </h3>
        <div class="space-y-4 pl-6">
          <div class="flex items-start gap-3">
            <div class="text-green-500"><i class="fas fa-check-circle"></i></div>
            <div>
              <div class="font-medium text-gray-800">Gửi đăng ký</div>
              <div class="text-sm text-gray-500">Hoàn thành thông tin cơ bản</div>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <div class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold">
              2
            </div>
            <div>
              <div class="font-medium text-gray-800">Xem xét hồ sơ</div>
              <div class="text-sm text-gray-500">1–2 ngày làm việc</div>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <div class="bg-gray-300 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold">
              3
            </div>
            <div>
              <div class="font-medium text-gray-800">Thông báo kết quả</div>
              <div class="text-sm text-gray-500">Qua email hoặc hệ thống</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Thông báo lỗi chi tiết -->
      <div v-if="!isFormValid && !errorMessage" class="text-red-600 font-medium text-sm pt-2">
        Vui lòng kiểm tra lại thông tin ở các bước trước. Một số trường bắt buộc đang thiếu hoặc không hợp lệ.
      </div>

      <!-- Nút hành động -->
      <div class="flex justify-center gap-4 pt-2">
        <button
          @click="goBackStep"
          class="px-6 py-2 rounded-lg border border-blue-500 text-blue-600 font-medium hover:bg-blue-50"
          :disabled="loading"
        >
          <i class="fas fa-arrow-left mr-1"></i> Quay lại
        </button>
        <button
          @click="submit"
          :disabled="loading || !isFormValid"
          class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 flex items-center justify-center min-w-[150px] disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg
            v-if="loading"
            class="animate-spin h-4 w-4 mr-2 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"
            />
          </svg>
          <i v-else class="fas fa-paper-plane mr-1"></i>
          <span>{{ loading ? 'Đang gửi...' : 'Gửi đăng ký' }}</span>
        </button>
      </div>
      <p class="text-xs text-gray-500 text-center max-w-sm mx-auto">
        Khi nhấn <span class="font-medium">Gửi đăng ký</span>, bạn đồng ý với
        <a href="/seller/terms-of-service" target="_blank" class="text-blue-600 underline hover:text-blue-800"
          >Điều khoản</a
        >
        và
        <a href="/seller/privacy-policy" target="_blank" class="text-blue-600 underline hover:text-blue-800"
          >Chính sách</a
        >
        của chúng tôi.
      </p>
      <!-- Lỗi nếu có -->
      <div v-if="errorMessage" class="text-red-600 font-medium text-sm pt-2">
        {{ errorMessage }}
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
    formData.append('province_id', step1.value.province_id ? Number(step1.value.province_id) : ''); // Đảm bảo là số
    formData.append('district_id', step1.value.district_id ? Number(step1.value.district_id) : ''); // Đảm bảo là số
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

    // Log FormData
    const formDataLog = {};
    for (const [key, value] of formData.entries()) {
      formDataLog[key] = value;
    }
    console.log('FormData gửi lên:', formDataLog); // Debug

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
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700;
}

.btn-outline {
  @apply border border-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-100;
}
</style>