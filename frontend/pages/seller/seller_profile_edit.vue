<template>
  <div class="bg-gray-50 min-h-screen py-8 px-4 sm:px-8">
    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-lg p-6 space-y-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Chỉnh sửa hồ sơ người bán</h2>

      <!-- Thông tin cơ bản -->
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-medium mb-1">Tên cửa hàng</label>
          <input v-model="form.store_name" type="text" class="input" />
        </div>
        <div>
          <label class="block font-medium mb-1">Slug cửa hàng</label>
          <input v-model="form.store_slug" type="text" class="input" />
        </div>
        <div>
          <label class="block font-medium mb-1">Số điện thoại</label>
          <input v-model="form.phone_number" type="text" class="input" />
        </div>
        <div>
          <label class="block font-medium mb-1">Ngày sinh / thành lập</label>
          <input v-model="form.date_of_birth" type="date" class="input" />
        </div>
        <div class="sm:col-span-2">
          <label class="block font-medium mb-1">Địa chỉ cá nhân / công ty</label>
          <input v-model="form.personal_address" type="text" class="input" />
        </div>
        <div class="sm:col-span-2">
          <label class="block font-medium mb-1">CMND/CCCD / Mã số thuế</label>
          <input v-model="form.identity_card_number" type="text" class="input" />
        </div>

      <!-- CCCD mặt trước -->
      <div class="sm:col-span-1">
        <label class="block font-medium mb-1">Ảnh CCCD mặt trước</label>
        <input type="file" @change="(e) => handleFileUpload(e, 'cccd_front')" class="input" />
        <img
          v-if="form.cccd_front_preview"
          :src="form.cccd_front_preview"
          class="mt-2 max-w-xs border rounded"
          alt="CCCD mặt trước"
        />
      </div>

      <!-- CCCD mặt sau -->
      <div class="sm:col-span-1">
        <label class="block font-medium mb-1">Ảnh CCCD mặt sau</label>
        <input type="file" @change="(e) => handleFileUpload(e, 'cccd_back')" class="input" />
        <img
          v-if="form.cccd_back_preview"
          :src="form.cccd_back_preview"
          class="mt-2 max-w-xs border rounded"
          alt="CCCD mặt sau"
        />
      </div>
      </div>

      <!-- Nếu là doanh nghiệp -->
      <div v-if="form.seller_type === 'business'" class="border-t pt-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-800">Thông tin doanh nghiệp</h3>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-medium mb-1">Tên công ty</label>
            <input v-model="form.business.company_name" type="text" class="input" />
          </div>
          <div>
            <label class="block font-medium mb-1">Mã số thuế</label>
            <input v-model="form.business.tax_code" type="text" class="input" />
          </div>
          <div class="sm:col-span-2">
            <label class="block font-medium mb-1">Địa chỉ công ty</label>
            <input v-model="form.business.company_address" type="text" class="input" />
          </div>
          <div class="sm:col-span-2">
            <label class="block font-medium mb-1">Người đại diện</label>
            <input v-model="form.business.representative_name" type="text" class="input" />
          </div>
          <div class="sm:col-span-2">
            <label class="block font-medium mb-1">SĐT người đại diện</label>
            <input v-model="form.business.representative_phone" type="text" class="input" />
          </div>
          <div class="sm:col-span-2">
            <label class="block font-medium mb-1">Ảnh giấy phép kinh doanh</label>
            <input type="file" @change="handleFileUpload" class="input" />
            <img
              v-if="form.business.business_license_preview"
              :src="form.business.business_license_preview"
              class="mt-2 max-w-xs border rounded"
              alt="Giấy phép"
            />
          </div>
        </div>
      </div>

      <!-- Nút lưu -->
      <div class="pt-4">
        <button @click="submitForm" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
          Lưu thay đổi
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

// lấy dữ liệu seller hiệu tạo 
onMounted(async () => {
  const { data } = await axios.get(`${API}/sellers/seller/me`, {
    headers: { Authorization: `Bearer ${token}` }
  });

  //  console.log('✅ Seller:', data.seller); // 👈 thêm dòng này
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
      'Content-Type': 'multipart/form-data'
    }
  });

  toast('success', 'Hồ sơ đã được cập nhật!');
  router.push('/seller/seller_profile');
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
