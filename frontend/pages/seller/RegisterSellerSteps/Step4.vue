<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen bg-white relative">
    <div class="absolute top-0 left-0 right-0 z-10 px-6 pt-6 bg-white">
      <RegisterSteps :currentStep="3" />
    </div>
    <!-- Cột trái: Ảnh -->
    <div class="hidden lg:flex items-center justify-center">
      <img src="/images/SellerCenter2.png" alt="CCCD" class="max-h-[500px] rounded-xl shadow-md" />
    </div>

    <!-- Cột phải: Form -->
    <div class="flex items-center justify-center px-8 py-12">
      <div class="w-full max-w-xl">
        <div class="mb-8 pt-20">
          <h1 class="text-2xl font-bold text-blue-700">Xác minh danh tính</h1>
          <p class="text-gray-600">Vui lòng cung cấp thông tin và ảnh CCCD của bạn</p>
        </div>

        <form @submit.prevent="submitStep4" class="space-y-5">
          <div class="bg-blue-50 text-blue-600 px-4 py-3 rounded text-sm">
            Thông tin cá nhân của bạn được bảo mật.
          </div>

          <!-- Số CCCD -->
          <div>
            <label class="block mb-1 font-medium">Số CCCD *</label>
            <div class="relative">
              <i class="fas fa-id-card absolute left-3 top-1/2 -translate-y-1/2 text-blue-500"></i>
              <input
                v-model="form.identity_card_number"
                type="text"
                class="input pl-10"
                placeholder="Nhập số CCCD (12 chữ số)"
                maxlength="12"
                @input="validateIdentityCardNumber"
              />
            </div>
            <p v-if="errors.identity_card_number" class="text-red-500 text-sm mt-1">{{ errors.identity_card_number }}</p>
          </div>

          <!-- Ngày sinh -->
          <div>
            <label class="block mb-1 font-medium">Ngày sinh *</label>
            <div class="relative">
              <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-blue-500"></i>
              <input v-model="form.date_of_birth" type="date" class="input pl-10" @change="validateDateOfBirth" />
            </div>
            <p v-if="errors.date_of_birth" class="text-red-500 text-sm mt-1">{{ errors.date_of_birth }}</p>
          </div>

          <!-- Địa chỉ -->
          <div>
            <label class="block mb-1 font-medium">Địa chỉ cá nhân *</label>
            <div class="relative">
              <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-blue-500"></i>
              <input
                v-model="form.personal_address"
                type="text"
                class="input pl-10"
                placeholder="Nhập địa chỉ thường trú"
                @input="validatePersonalAddress"
              />
            </div>
            <p v-if="errors.personal_address" class="text-red-500 text-sm mt-1">{{ errors.personal_address }}</p>
          </div>

          <!-- Ảnh mặt trước -->
          <div>
            <label class="block mb-1 font-medium">Ảnh mặt trước CCCD *</label>
            <div
              v-if="frontPreview"
              class="relative flex items-center gap-3 bg-gray-50 border rounded px-4 py-2"
            >
              <img :src="frontPreview" alt="Front" class="w-10 h-10 object-cover rounded" />
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-800 truncate">{{ frontFileName }}</p>
                <p class="text-xs text-gray-500">{{ frontFileSize }}</p>
              </div>
              <button @click="removeFront" class="text-gray-500 hover:text-red-500">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div
              v-else
              class="border border-dashed border-gray-300 rounded-md p-4 text-center text-sm text-gray-500"
            >
              <label class="cursor-pointer">
                <i class="fas fa-upload text-blue-500 mr-2"></i>
                Kéo thả ảnh vào đây hoặc <span class="text-blue-600 underline">chọn file</span>
                <input
                  type="file"
                  ref="frontFile"
                  class="hidden"
                  accept="image/*"
                  @change="handleFrontChange"
                />
              </label>
            </div>
            <p v-if="errors.id_card_front_url" class="text-red-500 text-sm mt-1">{{ errors.id_card_front_url }}</p>
          </div>

          <!-- Ảnh mặt sau -->
          <div>
            <label class="block mb-1 font-medium">Ảnh mặt sau CCCD *</label>
            <div
              v-if="backPreview"
              class="relative flex items-center gap-3 bg-gray-50 border rounded px-4 py-2"
            >
              <img :src="backPreview" alt="Back" class="w-10 h-10 object-cover rounded" />
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-800 truncate">{{ backFileName }}</p>
                <p class="text-xs text-gray-500">{{ backFileSize }}</p>
              </div>
              <button @click="removeBack" class="text-gray-500 hover:text-red-500">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div
              v-else
              class="border border-dashed border-gray-300 rounded-md p-4 text-center text-sm text-gray-500"
            >
              <label class="cursor-pointer">
                <i class="fas fa-upload text-blue-500 mr-2"></i>
                Kéo thả ảnh vào đây hoặc <span class="text-blue-600 underline">chọn file</span>
                <input
                  type="file"
                  ref="backFile"
                  class="hidden"
                  accept="image/*"
                  @change="handleBackChange"
                />
              </label>
            </div>
            <p v-if="errors.id_card_back_url" class="text-red-500 text-sm mt-1">{{ errors.id_card_back_url }}</p>
          </div>

          <button
            type="submit"
            class="btn btn-primary w-full h-11"
            :disabled="loading || !isFormValid"
          >
            <span v-if="loading">Đang xử lý...</span>
            <span v-else>Tiếp tục</span>
          </button>

          <div class="text-center mt-3">
            <button type="button" @click="goBackStep" class="text-blue-600 text-sm underline">
              ← Quay lại bước trước
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

// Load lại dữ liệu cũ nếu có
onMounted(() => {
  const saved = localStorage.getItem('register_step4');
  if (saved) {
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
  }
});

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

    toast('success', 'Thông tin bước 4 đã được lưu. Chuyển sang bước xác nhận.');
    router.push('/seller/RegisterSellerSteps/Submit');
  } catch (err) {
    console.error('Lỗi khi xử lý ảnh:', err);
    toast('error', 'Đã xảy ra lỗi khi xử lý ảnh. Vui lòng thử lại.');
    errors.value.form = 'Đã xảy ra lỗi khi xử lý ảnh. Vui lòng thử lại.';
  } finally {
    loading.value = false;
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
  frontFile.value.value = '';
  errors.value.id_card_front_url = 'Vui lòng tải lên ảnh mặt trước CCCD.';
};

const removeBack = () => {
  backPreview.value = null;
  backFileName.value = '';
  backFileSize.value = '';
  backFile.value.value = '';
  errors.value.id_card_back_url = 'Vui lòng tải lên ảnh mặt sau CCCD.';
};

const goBackStep = () => {
  router.push('/seller/RegisterSellerSteps/step3');
};
</script>

<style scoped>
.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
  padding-left: 2.5rem !important;
}

.btn-primary {
  @apply bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed;
}
</style>