<template>
  <div class="bg-gray-50 min-h-screen py-8 px-4 sm:px-8">
    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-lg p-6 space-y-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Chỉnh sửa hồ sơ người bán</h2>

      <template v-if="loaded">
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-medium mb-1">Tên cửa hàng</label>
            <input v-model="form.store_name" type="text" class="input" />
            <p v-if="errors.store_name" class="text-red-500 text-xs mt-1">{{ errors.store_name[0] }}</p>
          </div>
          <div>
            <label class="block font-medium mb-1">Slug cửa hàng</label>
            <input v-model="form.store_slug" type="text" class="input" />
            <p v-if="errors.store_slug" class="text-red-500 text-xs mt-1">{{ errors.store_slug[0] }}</p>
          </div>
          <div>
            <label class="block font-medium mb-1">Số điện thoại</label>
            <input v-model="form.phone_number" type="text" class="input" />
            <p v-if="errors.phone_number" class="text-red-500 text-xs mt-1">{{ errors.phone_number[0] }}</p>
          </div>
          <div>
            <label class="block font-medium mb-1">Ngày sinh / thành lập</label>
            <input v-model="form.date_of_birth" type="date" class="input" />
            <p v-if="errors.date_of_birth" class="text-red-500 text-xs mt-1">{{ errors.date_of_birth[0] }}</p>
          </div>
          <div class="sm:col-span-2">
            <label class="block font-medium mb-1">Địa chỉ cá nhân / công ty</label>
            <input v-model="form.personal_address" type="text" class="input" />
            <p v-if="errors.personal_address" class="text-red-500 text-xs mt-1">{{ errors.personal_address[0] }}</p>
          </div>
          <div class="sm:col-span-2">
            <label class="block font-medium mb-1">CMND/CCCD / Mã số thuế</label>
            <input v-model="form.identity_card_number" type="text" class="input" />
            <p v-if="errors.identity_card_number" class="text-red-500 text-xs mt-1">{{ errors.identity_card_number[0] }}</p>
          </div>

          <!-- CCCD mặt trước -->
          <div>
            <label class="block font-medium mb-1">Ảnh CCCD mặt trước</label>
            <input type="file" @change="(e) => handleFileUpload(e, 'cccd_front')" class="input" />
            <img v-if="form.cccd_front_preview" :src="form.cccd_front_preview" class="mt-2 max-w-xs border rounded" />
          </div>

          <!-- CCCD mặt sau -->
          <div>
            <label class="block font-medium mb-1">Ảnh CCCD mặt sau</label>
            <input type="file" @change="(e) => handleFileUpload(e, 'cccd_back')" class="input" />
            <img v-if="form.cccd_back_preview" :src="form.cccd_back_preview" class="mt-2 max-w-xs border rounded" />
          </div>
        </div>

        <!-- Nếu là doanh nghiệp -->
        <div v-if="form.seller_type === 'business'" class="border-t pt-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-800">Thông tin doanh nghiệp</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium mb-1">Tên công ty</label>
              <input v-model="form.business.company_name" type="text" class="input" />
              <p v-if="errors['business.company_name']" class="text-red-500 text-xs mt-1">{{ errors['business.company_name'][0] }}</p>
            </div>
            <div>
              <label class="block font-medium mb-1">Mã số thuế</label>
              <input v-model="form.business.tax_code" type="text" class="input" />
              <p v-if="errors['business.tax_code']" class="text-red-500 text-xs mt-1">{{ errors['business.tax_code'][0] }}</p>
            </div>
            <div class="sm:col-span-2">
              <label class="block font-medium mb-1">Địa chỉ công ty</label>
              <input v-model="form.business.company_address" type="text" class="input" />
              <p v-if="errors['business.company_address']" class="text-red-500 text-xs mt-1">{{ errors['business.company_address'][0] }}</p>
            </div>
            <div class="sm:col-span-2">
              <label class="block font-medium mb-1">Người đại diện</label>
              <input v-model="form.business.representative_name" type="text" class="input" />
              <p v-if="errors['business.representative_name']" class="text-red-500 text-xs mt-1">{{ errors['business.representative_name'][0] }}</p>
            </div>
            <div class="sm:col-span-2">
              <label class="block font-medium mb-1">SĐT người đại diện</label>
              <input v-model="form.business.representative_phone" type="text" class="input" />
              <p v-if="errors['business.representative_phone']" class="text-red-500 text-xs mt-1">{{ errors['business.representative_phone'][0] }}</p>
            </div>
            <div class="sm:col-span-2">
              <label class="block font-medium mb-1">Ảnh giấy phép kinh doanh</label>
              <input type="file" @change="(e) => handleFileUpload(e, 'business_license')" class="input" />
              <img v-if="form.business.business_license_preview" :src="form.business.business_license_preview" class="mt-2 max-w-xs border rounded" />
            </div>
          </div>
        </div>

        <div class="pt-6">
          <button @click="submitForm" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Lưu thay đổi</button>
        </div>
      </template>
      <template v-else>
        <div class="text-center text-gray-500">Loading...</div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';

const router = useRouter();
const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const mediaBase = (config.public.mediaBaseUrl || 'http://localhost:8000/storage/').replace(/\/?$/, '/');

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

const errors = ref({});
const loaded = ref(false);

const toast = (icon, title) => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    timer: 1500,
    showConfirmButton: false,
    timerProgressBar: true
  });
};

onMounted(async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    toast('error', 'Vui lòng đăng nhập trước');
    return router.push('/login');
  }

  try {
    const { data } = await axios.get(`${API}/sellers/seller/me`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    form.value = { ...data.seller };
    form.value.business = data.seller.business || {};
    form.value.cccd_front_preview = data.seller.cccd_front ? getFileUrl(data.seller.cccd_front) : '';
    form.value.cccd_back_preview = data.seller.cccd_back ? getFileUrl(data.seller.cccd_back) : '';
    form.value.cccd_front = null;
    form.value.cccd_back = null;
    form.value.business.business_license_preview = data.seller.business?.business_license ? getFileUrl(data.seller.business.business_license) : '';

    loaded.value = true;
  } catch {
    toast('error', 'Tải dữ liệu thất bại.');
    router.push('/login');
  }
});

function getFileUrl(path) {
  return `${mediaBase}${path}`;
}

function handleFileUpload(e, field) {
  const file = e.target.files[0];
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

async function submitForm() {
  const token = localStorage.getItem('access_token');
  if (!token) return router.push('/login');

  errors.value = {}; // reset errors
  const payload = new FormData();

  // Gán các trường cơ bản
  for (const key in form.value) {
    if (['business', 'cccd_front_preview', 'cccd_back_preview'].includes(key)) continue;
    payload.append(key, form.value[key]);
  }

  // Gán file ảnh
  if (form.value.cccd_front instanceof File) {
    payload.append('cccd_front', form.value.cccd_front);
  }
  if (form.value.cccd_back instanceof File) {
    payload.append('cccd_back', form.value.cccd_back);
  }

  // ✅ Gán các trường business đúng kiểu Laravel cần
  for (const key in form.value.business) {
    if (key === 'business_license_preview') continue;
    const value = form.value.business[key];
    if (value instanceof File) {
      payload.append(`business[${key}]`, value);
    } else {
      payload.append(`business[${key}]`, value ?? '');
    }
  }

  try {
    await axios.post(`${API}/sellers/update`, payload, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });
    toast('success', 'Cập nhật thành công!');
    router.push('/seller/seller_profile');
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {};
      toast('error', 'Vui lòng kiểm tra lại thông tin.');
    } else {
      toast('error', 'Có lỗi xảy ra khi lưu.');
    }
  }
}

definePageMeta({ layout: 'default-seller' });
</script>

<style scoped>
.input {
  @apply w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200;
}
</style>
