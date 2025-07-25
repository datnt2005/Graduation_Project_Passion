<template>
  <div class="bg-gray-50 min-h-screen py-10 px-4 sm:px-8">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-xl p-8 space-y-8">
      <!-- Group: Thông tin cửa hàng -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
          <i class="fas fa-store text-blue-500"></i> Thông tin cửa hàng
        </h2>

        <div class="grid sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tên cửa hàng</label>
            <input v-model="form.store_name" type="text" class="input" placeholder="Nhập tên cửa hàng" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
            <input v-model="form.phone_number" type="text" class="input" placeholder="0123456789" readonly />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ lấy hàng</label>
            <input v-model="form.pickup_address" type="text" class="input" placeholder="Nhập địa chỉ chi tiết" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Giới thiệu</label>
            <textarea v-model="form.bio" rows="3" class="input" placeholder="Giới thiệu ngắn gọn về cửa hàng hoặc bản thân"></textarea>
          </div>
        </div>
      </div>

   

      <!-- Button -->
      <div class="text-right pt-4">
        <button
          @click="submitForm"
          :disabled="isSubmitting"
          class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md shadow flex items-center gap-2 disabled:opacity-50"
        >
          <span v-if="!isSubmitting"><i class="fas fa-save"></i> Lưu thay đổi</span>
          <span v-else class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
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
import Swal from 'sweetalert2';
const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const mediaBase = (config.public.mediaBaseUrl || 'http://localhost:8000/storage/').replace(/\/?$/, '/');
const token = localStorage.getItem('access_token');
const router = useRouter();

const form = ref({
  store_name: '',
  phone_number: '',
  pickup_address: '',
  bio: '',
  document: null,
  document_preview: ''
});
const isSubmitting = ref(false);

const toast = (icon, title) => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    customClass: { popup: 'text-sm rounded-md shadow-md' }
  });
};

onMounted(async () => {
  try {
    const { data } = await axios.get(`${API}/sellers/seller/me`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    const seller = data.seller || {};
    form.value = {
      store_name: seller.store_name || '',
      phone_number: seller.phone_number || '',
      pickup_address: seller.pickup_address || '',
      bio: seller.bio || '',
      document: null,
      document_preview: seller.document ? mediaBase + seller.document : ''
    };
  } catch (e) {
    toast('error', 'Không thể tải thông tin');
  }
});

function handleFileUpload(e) {
  const file = e.target.files[0];
  if (file) {
    form.value.document = file;
    form.value.document_preview = URL.createObjectURL(file);
  }
}

async function submitForm() {
  isSubmitting.value = true;
  try {
    const payload = new FormData();
    payload.append('store_name', form.value.store_name || '');
    payload.append('phone_number', form.value.phone_number || '');
    payload.append('pickup_address', form.value.pickup_address || '');
    payload.append('bio', form.value.bio || '');
    if (form.value.document instanceof File) {
      payload.append('document', form.value.document);
    }

    await axios.post(`${API}/sellers/update`, payload, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });

    toast('success', 'Hồ sơ đã được cập nhật!');
    router.push('/seller/seller_profile');
  } catch (error) {
    const errors = error.response?.data?.errors || {};
    const firstError = Object.values(errors)?.[0]?.[0] || 'Có lỗi xảy ra';
    toast('error', firstError);
  } finally {
    isSubmitting.value = false;
  }
}
definePageMeta({
  layout: 'default-seller'
});
</script>

<style scoped>
.input {
  @apply w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200;
}
</style>