<template>
  <div class="bg-gray-50 min-h-screen py-10 px-4 sm:px-8">
    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-xl p-8 space-y-8">

      <!-- Group: Thông tin cơ bản -->
      <div>
        <div class="flex items-center gap-2 mb-4">
          <i class="fas fa-info-circle text-blue-500"></i>
          <h2 class="text-lg font-semibold text-gray-800">Thông tin cơ bản</h2>
          <span class="ml-2 text-xs font-medium bg-blue-100 text-blue-700 px-2 py-1 rounded">Bắt buộc</span>
        </div>

        <div class="grid sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              <i class="fas fa-store mr-1"></i> Tên cửa hàng
            </label>
            <input v-model="form.store_name" type="text" placeholder="Nhập tên cửa hàng" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              <i class="fas fa-link mr-1"></i> Slug cửa hàng
            </label>
            <input v-model="form.store_slug" type="text" placeholder="ten-cua-hang" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              <i class="fas fa-phone mr-1"></i> Số điện thoại
            </label>
            <input v-model="form.phone_number" type="text" placeholder="0123456789" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              <i class="fas fa-calendar-alt mr-1"></i> Ngày sinh / thành lập
            </label>
            <input v-model="form.date_of_birth" type="date" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              <i class="fas fa-map-marker-alt mr-1"></i> Địa chỉ cá nhân / công ty
            </label>
            <textarea v-model="form.personal_address" placeholder="Nhập địa chỉ chi tiết" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200"></textarea>
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              <i class="fas fa-id-card mr-1"></i> CMND/CCCD / Mã số thuế
            </label>
            <input v-model="form.identity_card_number" type="text" placeholder="Nhập số CMND/CCCD hoặc mã số thuế" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
          </div>
        </div>
      </div>

      <!-- Group: Tài liệu xác thực -->
      <div>
        <div class="flex items-center gap-2 mb-4">
          <i class="fas fa-file-alt text-blue-500"></i>
          <h2 class="text-lg font-semibold text-gray-800">Tài liệu xác thực</h2>
          <span class="ml-2 text-xs font-medium bg-blue-100 text-blue-700 px-2 py-1 rounded">Bắt buộc</span>
        </div>

        <div class="grid sm:grid-cols-2 gap-6">
          <!-- Mặt trước -->
         <!-- Upload ảnh CCCD mặt trước -->
<div class="border rounded-lg p-3">
  <label class="block text-sm font-medium text-gray-700 mb-2">
    <i class="fas fa-id-card mr-1 text-gray-500"></i> Ảnh CCCD mặt trước
  </label>

  <div v-if="!form.cccd_front_preview" class="border-2 border-dashed border-gray-300 rounded-md p-4 flex flex-col items-center justify-center text-gray-400 text-sm space-y-2 h-44">
    <i class="fas fa-upload text-xl"></i>
    <span>Tải lên hình ảnh</span>
    <input type="file" @change="(e) => handleFileUpload(e, 'cccd_front')" class="block mt-2" />
  </div>

  <div v-else class="relative w-full">
    <img :src="form.cccd_front_preview" alt="Ảnh CCCD" class="rounded-md object-cover w-full max-h-60 border" />
    <button
      type="button"
      class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm"
      @click="removePreview('cccd_front')"
    >
      &times;
    </button>
  </div>
</div>

          <!-- Mặt sau -->
          <!-- Ảnh CCCD mặt sau -->
<div class="border rounded-lg p-3">
  <label class="block text-sm font-medium text-gray-700 mb-2">
    <i class="fas fa-id-card mr-1 text-gray-500"></i> Ảnh CCCD mặt sau
  </label>

  <!-- Trạng thái chưa chọn ảnh -->
  <div v-if="!form.cccd_back_preview" class="border-2 border-dashed border-gray-300 rounded-md p-4 flex flex-col items-center justify-center text-gray-400 text-sm space-y-2 h-44">
    <i class="fas fa-upload text-xl"></i>
    <span>Tải lên hình ảnh</span>
    <input type="file" @change="(e) => handleFileUpload(e, 'cccd_back')" class="block mt-2" />
  </div>

  <!-- Trạng thái đã có ảnh -->
  <div v-else class="relative w-full">
    <img :src="form.cccd_back_preview" alt="Ảnh CCCD mặt sau" class="rounded-md object-cover w-full max-h-60 border" />
    <button
      type="button"
      class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm"
      @click="removePreview('cccd_back')"
    >
      &times;
    </button>
  </div>
</div>

        </div>
      </div>

      <!-- Button -->
     <div class="text-right pt-4">
  <button
    @click="submitForm"
    :disabled="isSubmitting"
    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md shadow flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
  >
    <span v-if="!isSubmitting">
      <i class="fas fa-save"></i> Lưu thay đổi
    </span>
    <span v-else class="flex items-center gap-2">
      <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
      </svg>
      Đang lưu...
    </span>
  </button>
</div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2'
const router = useRouter();
const isSubmitting = ref(false);

const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const mediaBase = (config.public.mediaBaseUrl || 'http://localhost:8000/storage/').replace(/\/?$/, '/');

const token = localStorage.getItem('access_token');

const form = ref({
  store_name: '',
  store_slug: '',
  seller_type: '',
  phone_number: '',
  date_of_birth: '',
  personal_address: '',
  identity_card_number: '',
  cccd_front: '',
  cccd_back: '',
  cccd_front_preview: '',
  cccd_back_preview: '',
  business: {
    company_name: '',
    tax_code: '',
    company_address: '',
    representative_name: '',
    representative_phone: '',
    business_license: '',
    business_license_preview: ''
  }
});



const toast = (icon, title) => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    width: '350px',
    padding: '10px 20px',
    customClass: { popup: 'text-sm rounded-md shadow-md' },
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toastEl) => {
      toastEl.addEventListener('mouseenter', () => Swal.stopTimer());
      toastEl.addEventListener('mouseleave', () => Swal.resumeTimer());
    }
  });
};

function removePreview(field) {
  if (field === 'cccd_front') {
    form.value.cccd_front = null;
    form.value.cccd_front_preview = '';
  } else if (field === 'cccd_back') {
    form.value.cccd_back = null;
    form.value.cccd_back_preview = '';
  }
}



// lấy dữ liệu seller hiệu tạo 
onMounted(async () => {
  const { data } = await axios.get(`${API}/sellers/seller/me`, {
    headers: { Authorization: `Bearer ${token}` }
  });
  // console.log('✅ Front path:', data.seller.cccd_front);
  // console.log('✅ Back path:', data.seller.cccd_back);


  form.value = { ...data.seller };
  form.value.business = data.seller.business || {};

  form.value.cccd_front_preview = data.seller.cccd_front
    ? getFileUrl(data.seller.cccd_front)
    : '';
  form.value.cccd_back_preview = data.seller.cccd_back
    ? getFileUrl(data.seller.cccd_back)
    : '';
  form.value.cccd_front = null;
  form.value.cccd_back = null;

  form.value.business.business_license_preview = data.seller.business?.business_license
    ? getFileUrl(data.seller.business.business_license)
    : '';
});



function getFileUrl(path) {
  return `${mediaBase}${path}`;
}


function handleFileUpload(event, field) {
  const file = event.target.files[0];
  if (!file) return;

  const previewURL = URL.createObjectURL(file);
  if (field === 'cccd_front') {
    form.value.cccd_front = file;
    form.value.cccd_front_preview = previewURL;
  } else if (field === 'cccd_back') {
    form.value.cccd_back = file;
    form.value.cccd_back_preview = previewURL;
  } else if (field === 'business_license') {
    form.value.business.business_license = file;
    form.value.business.business_license_preview = previewURL;
  }
}

// Lưu thay đổi 
async function submitForm() {
  isSubmitting.value = true;

  try {
    const payload = new FormData();

    for (const key in form.value) {
      if (['business', 'cccd_front_preview', 'cccd_back_preview'].includes(key)) continue;
      payload.append(key, form.value[key]);
    }

    if (form.value.cccd_front instanceof File) {
      payload.append('cccd_front', form.value.cccd_front);
    }

    if (form.value.cccd_back instanceof File) {
      payload.append('cccd_back', form.value.cccd_back);
    }

    if (form.value.business) {
      for (const key in form.value.business) {
        if (key !== 'business_license_preview') {
          payload.append(`business[${key}]`, form.value.business[key]);
        }
      }
    }

    await axios.post(`${API}/sellers/update`, payload, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      },
    });

    toast('success', 'Hồ sơ đã được cập nhật!');
    router.push('/seller/seller_profile');

  } catch (error) {
    console.error('Lỗi khi cập nhật hồ sơ:', error);
    toast('error', 'Không thể cập nhật hồ sơ!');
  } finally {
    isSubmitting.value = false;
  }
}


definePageMeta({
  layout: 'default-seller'
})

</script>
<style scoped>
.input {
  @apply w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200;
}

</style>