<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-gradient-to-br from-white to-blue-50">
    <!-- Header với stepper -->
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white shadow-sm">
      <RegisterSteps :currentStep="3" />
    </div>

    <!-- Cột trái: Ảnh -->
    <div class="hidden lg:flex flex-col items-center justify-center p-8 ">
      <img 
        src="/images/SellerCenter2.png" 
        alt="Xác minh danh tính" 
        class="max-h-[500px] rounded-xl shadow-lg transition-all duration-500 hover:scale-105" 
      />
      <div class="mt-8 text-center max-w-md">
        <h2 class="text-xl font-bold text-blue-800 mb-2">Xác minh danh tính</h2>
        <p class="text-blue-700 opacity-80">Bảo mật thông tin cá nhân và xác minh danh tính để đảm bảo tính tin cậy của tài khoản</p>
      </div>
    </div>

    <!-- Cột phải: Form -->
<div class="flex items-center justify-center px-8 pt-[100px] pb-[100px]">
      <div class="w-full max-w-xl bg-white p-8 rounded-2xl shadow-lg">
        <div class="mb-8">
          <div class="flex items-center gap-3 mb-4">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full p-3">
              <i class="fas fa-id-card text-xl"></i>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-800">Xác minh danh tính</h1>
              <p class="text-gray-600 text-sm">Cung cấp thông tin và ảnh CCCD của bạn</p>
            </div>
          </div>
        </div>

        <form @submit.prevent="submitStep4" class="space-y-6">
          <!-- Thông báo bảo mật -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
              <div class="text-blue-600">
                <i class="fas fa-shield-alt"></i>
              </div>
              <div class="text-sm text-blue-800">
                <p class="font-medium mb-1">Thông tin được bảo mật</p>
                <p class="text-blue-700">Thông tin cá nhân của bạn được bảo vệ.</p>
              </div>
            </div>
          </div>

          <!-- Số CCCD -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-id-card mr-3 text-purple-600"></i>
              Số CCCD <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                <i class="fas fa-hashtag"></i>
              </span>
              <input
                v-model="form.identity_card_number"
                type="text"
                class="input pl-10"
                placeholder="Nhập số CCCD (12 chữ số)"
                maxlength="12"
                @input="validateIdentityCardNumber"
              />
            </div>
            <p v-if="errors.identity_card_number" class="error-message">{{ errors.identity_card_number }}</p>
          </div>

          <!-- Ngày sinh -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-calendar-alt mr-3 text-green-600"></i>
              Ngày sinh <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                <i class="fas fa-birthday-cake"></i>
              </span>
              <input 
                v-model="form.date_of_birth" 
                type="date" 
                class="input pl-10" 
                @change="validateDateOfBirth" 
              />
            </div>
            <p v-if="errors.date_of_birth" class="error-message">{{ errors.date_of_birth }}</p>
          </div>

          <!-- Địa chỉ -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-map-marker-alt mr-3 text-red-600"></i>
              Địa chỉ cá nhân <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                <i class="fas fa-home"></i>
              </span>
              <input
                v-model="form.personal_address"
                type="text"
                class="input pl-10"
                placeholder="Nhập địa chỉ thường trú"
                @input="validatePersonalAddress"
              />
            </div>
            <p v-if="errors.personal_address" class="error-message">{{ errors.personal_address }}</p>
          </div>

          <!-- Upload ảnh -->
          <div class="bg-gray-50 rounded-xl p-6 space-y-6">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-4">
              <i class="fas fa-images text-blue-600"></i>
              Ảnh CCCD
            </h3>

            <!-- Ảnh mặt trước -->
            <div class="form-group">
              <label class="form-label">
                <i class="fas fa-id-card mr-3 text-blue-600"></i>
                Ảnh mặt trước CCCD <span class="text-red-500">*</span>
              </label>
              
              <!-- File đã chọn -->
              <div
                v-if="frontPreview"
                class="file-preview"
              >
                <div class="flex items-center gap-3">
                  <img :src="frontPreview" alt="Front" class="w-12 h-12 object-cover rounded-lg border" />
                  <div class="flex-1">
                    <p class="font-medium text-gray-800 truncate">{{ frontFileName }}</p>
                    <p class="text-xs text-green-600">
                      <i class="fas fa-check-circle mr-1"></i>
                      {{ frontFileSize }} - Đã tải lên
                    </p>
                  </div>
                  <button 
                    @click="removeFront" 
                    class="text-gray-400 hover:text-red-500 transition-colors p-2"
                    type="button"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>

              <!-- Upload area -->
              <div
                v-else
                class="upload-area"
                @click="$refs.frontFile.click()"
                @dragover.prevent
                @drop.prevent="handleFrontDrop"
              >
                <div class="text-center">
                  <div class="bg-blue-100 text-blue-600 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-camera text-2xl"></i>
                  </div>
                  <p class="text-gray-700 font-medium mb-1">Tải ảnh mặt trước CCCD</p>
                  <p class="text-sm text-gray-500">
                    Kéo thả ảnh vào đây hoặc <span class="text-blue-600 underline cursor-pointer">chọn file</span>
                  </p>
                  <p class="text-xs text-gray-400 mt-2">
                    JPG, PNG tối đa 5MB
                  </p>
                </div>
              </div>

              <input
                type="file"
                ref="frontFile"
                class="hidden"
                accept="image/*"
                @change="handleFrontChange"
              />
              <p v-if="errors.id_card_front_url" class="error-message">{{ errors.id_card_front_url }}</p>
            </div>

            <!-- Ảnh mặt sau -->
            <div class="form-group">
              <label class="form-label">
                <i class="fas fa-id-card mr-3 text-blue-600"></i>
                Ảnh mặt sau CCCD <span class="text-red-500">*</span>
              </label>
              
              <!-- File đã chọn -->
              <div
                v-if="backPreview"
                class="file-preview"
              >
                <div class="flex items-center gap-3">
                  <img :src="backPreview" alt="Back" class="w-12 h-12 object-cover rounded-lg border" />
                  <div class="flex-1">
                    <p class="font-medium text-gray-800 truncate">{{ backFileName }}</p>
                    <p class="text-xs text-green-600">
                      <i class="fas fa-check-circle mr-1"></i>
                      {{ backFileSize }} - Đã tải lên
                    </p>
                  </div>
                  <button 
                    @click="removeBack" 
                    class="text-gray-400 hover:text-red-500 transition-colors p-2"
                    type="button"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>

              <!-- Upload area -->
              <div
                v-else
                class="upload-area"
                @click="$refs.backFile.click()"
                @dragover.prevent
                @drop.prevent="handleBackDrop"
              >
                <div class="text-center">
                  <div class="bg-blue-100 text-blue-600 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-camera text-2xl"></i>
                  </div>
                  <p class="text-gray-700 font-medium mb-1">Tải ảnh mặt sau CCCD</p>
                  <p class="text-sm text-gray-500">
                    Kéo thả ảnh vào đây hoặc <span class="text-blue-600 underline cursor-pointer">chọn file</span>
                  </p>
                  <p class="text-xs text-gray-400 mt-2">
                    JPG, PNG tối đa 5MB
                  </p>
                </div>
              </div>

              <input
                type="file"
                ref="backFile"
                class="hidden"
                accept="image/*"
                @change="handleBackChange"
              />
              <p v-if="errors.id_card_back_url" class="error-message">{{ errors.id_card_back_url }}</p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row justify-center gap-4 pt-6">
            <button 
              type="button" 
              @click="goBackStep" 
              class="btn-outline flex items-center justify-center gap-2 px-8 py-3 text-base"
            >
              <i class="fas fa-arrow-left"></i>
              <span>Quay lại</span>
            </button>
            <button 
              type="submit" 
              class="btn-primary flex items-center justify-center gap-2 px-8 py-3 text-base"
              :disabled="loading || !isFormValid"
            >
              <span v-if="!loading">Tiếp tục</span>
              <span v-else class="flex items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý
              </span>
              <i v-if="!loading" class="fas fa-arrow-right"></i>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from '~/composables/useToast';
import RegisterSteps from '@/components/RegisterSteps.vue';

const router = useRouter();
const loading = ref(false);
const errors = ref({});
const { toast } = useToast();

const frontPreview = ref(null);
const backPreview = ref(null);
const frontFileName = ref('');
const backFileName = ref('');
const frontFileSize = ref('');
const backFileSize = ref('');
const frontFile = ref(null);
const backFile = ref(null);

const form = ref({
  identity_card_number: '',
  date_of_birth: '',
  personal_address: '',
});

// Kiểm tra dữ liệu hợp lệ trước khi gửi
const isFormValid = computed(() => {
  return (
    form.value.identity_card_number &&
    /^[0-9]{12}$/.test(form.value.identity_card_number) &&
    form.value.date_of_birth &&
    form.value.personal_address &&
    frontPreview.value &&
    backPreview.value
  );
});

// Validate số CCCD
const validateIdentityCardNumber = () => {
  if (!form.value.identity_card_number) {
    errors.value.identity_card_number = 'Vui lòng nhập số CCCD.';
  } else if (!/^[0-9]{12}$/.test(form.value.identity_card_number)) {
    errors.value.identity_card_number = 'Số CCCD phải gồm 12 chữ số.';
  } else {
    errors.value.identity_card_number = '';
  }
};

// Validate ngày sinh
const validateDateOfBirth = () => {
  if (!form.value.date_of_birth) {
    errors.value.date_of_birth = 'Vui lòng chọn ngày sinh.';
  } else {
    const today = new Date();
    const dob = new Date(form.value.date_of_birth);
    if (dob >= today) {
      errors.value.date_of_birth = 'Ngày sinh không hợp lệ.';
    } else {
      errors.value.date_of_birth = '';
    }
  }
};

// Validate địa chỉ
const validatePersonalAddress = () => {
  if (!form.value.personal_address) {
    errors.value.personal_address = 'Vui lòng nhập địa chỉ cá nhân.';
  } else if (form.value.personal_address.length < 5) {
    errors.value.personal_address = 'Địa chỉ cá nhân phải có ít nhất 5 ký tự.';
  } else {
    errors.value.personal_address = '';
  }
};

// Hàm convert file -> base64
const readFileAsBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = () => reject(new Error('Không thể đọc file ảnh.'));
    reader.readAsDataURL(file);
  });
};

// Handle drag and drop
const handleFrontDrop = (e) => {
  const files = e.dataTransfer.files;
  if (files.length > 0) {
    const file = files[0];
    if (file.type.match(/^image\/(jpeg|jpg|png)$/)) {
      handleFrontChange({ target: { files: [file] } });
    } else {
      toast('error', 'Chỉ chấp nhận file JPG, PNG.');
    }
  }
};

const handleBackDrop = (e) => {
  const files = e.dataTransfer.files;
  if (files.length > 0) {
    const file = files[0];
    if (file.type.match(/^image\/(jpeg|jpg|png)$/)) {
      handleBackChange({ target: { files: [file] } });
    } else {
      toast('error', 'Chỉ chấp nhận file JPG, PNG.');
    }
  }
};

// Preview và lưu ảnh mặt trước
const handleFrontChange = async (e) => {
  const file = e.target.files[0];
  if (file) {
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      errors.value.id_card_front_url = 'Chỉ chấp nhận định dạng JPG hoặc PNG.';
      toast('error', 'Chỉ chấp nhận định dạng JPG hoặc PNG.');
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      errors.value.id_card_front_url = 'Kích thước ảnh tối đa là 5MB.';
      toast('error', 'Kích thước ảnh tối đa là 5MB.');
      return;
    }
    try {
      frontFileName.value = file.name;
      frontFileSize.value = (file.size / 1024 / 1024).toFixed(2) + ' MB';
      const base64 = await readFileAsBase64(file);
      frontPreview.value = base64;
      errors.value.id_card_front_url = '';
      toast('success', 'Đã tải ảnh mặt trước thành công!');
    } catch (err) {
      errors.value.id_card_front_url = 'Lỗi khi tải ảnh mặt trước. Vui lòng thử lại.';
      toast('error', 'Lỗi khi tải ảnh mặt trước. Vui lòng thử lại.');
    }
  }
};

// Preview và lưu ảnh mặt sau
const handleBackChange = async (e) => {
  const file = e.target.files[0];
  if (file) {
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      errors.value.id_card_back_url = 'Chỉ chấp nhận định dạng JPG hoặc PNG.';
      toast('error', 'Chỉ chấp nhận định dạng JPG hoặc PNG.');
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      errors.value.id_card_back_url = 'Kích thước ảnh tối đa là 5MB.';
      toast('error', 'Kích thước ảnh tối đa là 5MB.');
      return;
    }
    try {
      backFileName.value = file.name;
      backFileSize.value = (file.size / 1024 / 1024).toFixed(2) + ' MB';
      const base64 = await readFileAsBase64(file);
      backPreview.value = base64;
      errors.value.id_card_back_url = '';
      toast('success', 'Đã tải ảnh mặt sau thành công!');
    } catch (err) {
      errors.value.id_card_back_url = 'Lỗi khi tải ảnh mặt sau. Vui lòng thử lại.';
      toast('error', 'Lỗi khi tải ảnh mặt sau. Vui lòng thử lại.');
    }
  }
};

// Xoá ảnh preview
const removeFront = () => {
  frontPreview.value = null;
  frontFileName.value = '';
  frontFileSize.value = '';
  if (frontFile.value) frontFile.value.value = '';
  errors.value.id_card_front_url = 'Vui lòng tải lên ảnh mặt trước CCCD.';
  toast('info', 'Đã xóa ảnh mặt trước.');
};

const removeBack = () => {
  backPreview.value = null;
  backFileName.value = '';
  backFileSize.value = '';
  if (backFile.value) backFile.value.value = '';
  errors.value.id_card_back_url = 'Vui lòng tải lên ảnh mặt sau CCCD.';
  toast('info', 'Đã xóa ảnh mặt sau.');
};

// Gửi form
const submitStep4 = async () => {
  // Validate trước khi gửi
  validateIdentityCardNumber();
  validateDateOfBirth();
  validatePersonalAddress();
  
  if (!frontPreview.value) errors.value.id_card_front_url = 'Vui lòng tải lên ảnh mặt trước CCCD.';
  if (!backPreview.value) errors.value.id_card_back_url = 'Vui lòng tải lên ảnh mặt sau CCCD.';
  
  if (!isFormValid.value) {
    toast('error', 'Vui lòng điền đầy đủ và đúng thông tin.');
    return;
  }
  
  loading.value = true;
  errors.value = {};
  
  try {
    let frontBase64 = frontPreview.value;
    let backBase64 = backPreview.value;
    
    if (frontFile.value?.files[0]) {
      frontBase64 = await readFileAsBase64(frontFile.value.files[0]);
    }
    if (backFile.value?.files[0]) {
      backBase64 = await readFileAsBase64(backFile.value.files[0]);
    }
    
    localStorage.setItem(
      'register_step4',
      JSON.stringify({
        ...form.value,
        frontImageBase64: frontBase64,
        backImageBase64: backBase64,
      })
    );
    
    toast('success', 'Đã lưu thông tin xác minh danh tính thành công!');
    
    // Simulate API call delay
    await new Promise(resolve => setTimeout(resolve, 500));
    
    router.push('/seller/RegisterSellerSteps/Submit');
  } catch (err) {
    console.error('Lỗi khi xử lý ảnh:', err);
    toast('error', 'Đã xảy ra lỗi khi xử lý ảnh. Vui lòng thử lại.');
    errors.value.form = 'Đã xảy ra lỗi khi xử lý ảnh. Vui lòng thử lại.';
  } finally {
    loading.value = false;
  }
};

const goBackStep = () => {
  router.push('/seller/RegisterSellerSteps/step3');
};

// Load lại dữ liệu cũ nếu có
onMounted(() => {
  const saved = localStorage.getItem('register_step4');
  if (saved) {
    try {
      const data = JSON.parse(saved);
      form.value.identity_card_number = data.identity_card_number || '';
      form.value.date_of_birth = data.date_of_birth || '';
      form.value.personal_address = data.personal_address || '';
      frontPreview.value = data.frontImageBase64 || null;
      backPreview.value = data.backImageBase64 || null;
      frontFileName.value = data.frontImageBase64 ? 'cccd-front.png' : '';
      backFileName.value = data.backImageBase64 ? 'cccd-back.png' : '';
      
      // Validate lại dữ liệu khi load
      validateIdentityCardNumber();
      validateDateOfBirth();
      validatePersonalAddress();
    } catch (error) {
      console.error('Error loading saved data:', error);
    }
  }
});
</script>

<style scoped>
.form-group {
  @apply mb-4;
}

.form-label {
  @apply block mb-2 font-medium text-gray-700 text-sm;
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

.upload-area {
  @apply w-full h-40 flex flex-col items-center justify-center bg-gray-50 text-gray-500 rounded-xl border-2 border-dashed border-gray-300 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-300;
}

.file-preview {
  @apply w-full border border-gray-200 bg-gray-50 rounded-xl p-4 shadow-sm;
}

.btn-primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed;
}

.btn-outline {
  @apply border-2 border-gray-300 text-gray-700 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2;
}

.error-message {
  @apply text-red-500 text-xs mt-1 ml-1;
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.form-group {
  animation: fadeIn 0.4s ease-out forwards;
}

.form-group:nth-child(2) { animation-delay: 0.1s; }
.form-group:nth-child(3) { animation-delay: 0.2s; }
.form-group:nth-child(4) { animation-delay: 0.3s; }

/* Responsive */
@media (max-width: 640px) {
  .btn-primary, .btn-outline {
    @apply w-full;
  }
}
</style>
