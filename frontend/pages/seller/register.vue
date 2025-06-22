
<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#eaf1fd] to-[#f4f7fd] px-2 py-6">
    <div
      class="max-w-9xl w-full flex flex-col md:flex-row rounded-[28px] overflow-hidden shadow-[0_8px_40px_0_rgba(22,61,124,.10)] border border-gray-200 bg-white mx-auto">
      <!-- Bên trái: Branding -->
      <div class="hidden md:flex flex-col justify-center items-center w-1/2 p-12 bg-transparent">
        <div class="mb-10 flex flex-col items-center">
          <img src="/images/SellerCenter2.png" alt="Passion Logo">
        </div>
        <div class="text-2xl font-bold text-gray-900 mb-2 text-center">
          Đăng ký bán hàng cùng <strong class="text-blue-600 font-extrabold">Passion</strong>
        </div>
        <div class="text-gray-500 text-base text-center max-w-[300px]">
          Tham gia cộng đồng bán hàng lớn nhất Việt Nam.<br>
          Tiếp cận hàng triệu khách hàng tiềm năng.
        </div>
      </div>
      <!-- Bên phải: Form -->
      <div class="w-full md:w-1/2 flex flex-col justify-center p-8 sm:p-16 bg-white">
        <div class="mx-auto w-full md:w-[80%]">
          <div class="mb-6">
            <div class="text-3xl font-bold text-gray-900 mb-1">Đăng ký ngay</div>
            <div class="text-gray-500 text-base">Tạo tài khoản bán hàng của bạn</div>
          </div>
          <div class="grid grid-cols-2 gap-2 mb-6 rounded-[12px] bg-[#f3f6fa] p-1.5">
            <button type="button"
              class="py-2 rounded-[10px] font-semibold text-base transition-all duration-200 shadow-sm"
              :class="sellerType === 'personal' ? 'bg-white text-blue-600 shadow border border-blue-500' : 'bg-transparent text-gray-700 border border-transparent hover:bg-white'"
              @click="sellerType = 'personal'">
              <span class="inline-flex items-center gap-2">Cá nhân/Nhỏ lẻ</span>
            </button>
            <button type="button"
              class="py-2 rounded-[10px] font-semibold text-base transition-all duration-200 shadow-sm"
              :class="sellerType === 'business' ? 'bg-white text-blue-600 shadow border border-blue-500' : 'bg-transparent text-gray-700 border border-transparent hover:bg-white'"
              @click="sellerType = 'business'">
              <span class="inline-flex items-center gap-2">Doanh nghiệp</span>
            </button>
          </div>

          <!-- FORM ĐĂNG KÝ -->
          <form @submit.prevent="handleSubmit" class="space-y-3">
            <!-- Tên cửa hàng -->
            <div>
              <label class="block font-semibold text-sm mb-1.5 text-gray-700">Tên cửa hàng</label>
              <input type="text"
                class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                v-model="form.store_name" placeholder="Nhập tên shop hoặc tên hiển thị" 
                :class="{ 'border-red-500': errors.store_name }">
              <p v-if="errors.store_name" class="text-sm text-red-600 mt-1.5 flex items-center">
                {{ errors.store_name }}
              </p>
            </div>

            <!-- Cá nhân -->
            <template v-if="sellerType === 'personal'">
              <!-- Số điện thoại -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Số điện thoại</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.phone_number" placeholder="Nhập số điện thoại" 
                  :class="{ 'border-red-500': errors.phone_number }">
                <p v-if="errors.phone_number" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.phone_number }}
                </p>
              </div>

              <!-- Số CMND/CCCD -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Số CMND/CCCD</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.identity_card_number" placeholder="Nhập số CMND/CCCD" 
                  :class="{ 'border-red-500': errors.identity_card_number }">
                <p v-if="errors.identity_card_number" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.identity_card_number }}
                </p>
              </div>

              <!-- Ảnh CCCD/CMND -->
              <div class="mb-2">
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Ảnh CCCD/CMND <span
                    class="text-gray-500 font-normal">(2 mặt)</span></label>
                <label
                  class="flex items-center gap-3 px-3 py-2 rounded-lg border border-gray-200 bg-[#f8fafc] cursor-pointer transition hover:border-[#1BA0E2] hover:bg-[#f1f8fd]">
                  <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a2 2 0 10-2.828-2.828z" />
                  </svg>
                  <span class="flex-1 text-sm text-gray-400">Chọn 2 ảnh CCCD/CMND (mặt trước & mặt sau)</span>
                  <span
                    class="ml-3 text-[#fff] bg-[#1BA0E2] px-3 py-1 rounded font-semibold text-sm hover:bg-[#1780B6] transition">Chọn file</span>
                  <input type="file" class="hidden" accept="image/*" multiple @change="onCccdFiles">
                </label>
                <div class="mt-2 grid grid-cols-2 gap-3">
                  <div v-for="(preview, index) in cccdPreviews" :key="index" class="flex flex-col items-center">
                    <img :src="preview" class="max-h-32 rounded-lg border border-gray-200 shadow" :alt="'CCCD mặt ' + (index === 0 ? 'trước' : 'sau')" />
                    <span class="mt-1 text-xs text-gray-500">{{ index === 0 ? 'Mặt trước' : 'Mặt sau' }}</span>
                  </div>
                </div>
                <!-- // validator -->
               <p v-if="errors.cccdPreviews" class="text-sm text-red-600 mt-1.5 flex items-center">{{ errors.cccdPreviews }}</p>
              </div>

              <!-- Ngày sinh -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Ngày sinh</label>
                <input type="date"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.date_of_birth"  :class="{ 'border-red-500': errors.date_of_birth }">
                <p v-if="errors.date_of_birth" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.date_of_birth }}
                </p>
              </div>

              <!-- Địa chỉ cá nhân -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Địa chỉ cá nhân</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.personal_address" placeholder="Nhập địa chỉ cá nhân" 
                  :class="{ 'border-red-500': errors.personal_address }">
                <p v-if="errors.personal_address" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.personal_address }}
                </p>
              </div>

              <!-- Mô tả ngắn -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Mô tả ngắn về shop (tuỳ chọn)</label>
                <textarea
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.bio" placeholder="Mô tả ngắn về cửa hàng..."></textarea>
              </div>

              <!-- Tài liệu bổ sung -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Tài liệu xác thực bổ sung (tuỳ chọn)</label>
                <label
                  class="flex items-center gap-3 px-3 py-2 rounded-lg border border-gray-200 bg-[#f8fafc] cursor-pointer transition hover:border-[#1BA0E2] hover:bg-[#f1f8fd]">
                  <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a2 2 0 10-2.828-2.828z" />
                  </svg>
                  <span class="flex-1 text-sm truncate text-gray-700" v-if="documentFile">{{ documentFile.name }}</span>
                  <span class="flex-1 text-sm text-gray-400" v-else>Chọn file ảnh/PDF bổ sung (nếu có)</span>
                  <span
                    class="ml-3 text-[#fff] bg-[#1BA0E2] px-3 py-1 rounded font-semibold text-sm hover:bg-[#1780B6] transition">Chọn file</span>
                  <input type="file" class="hidden" accept="image/*,application/pdf" @change="onDocumentFile">
                </label>
                <div v-if="documentPreview" class="mt-2">
                  <img v-if="isImage(documentPreview)" :src="documentPreview" class="max-h-32 rounded-lg border border-gray-200 shadow" alt="Tài liệu" />
                  <div v-else class="flex items-center gap-2 text-sm text-gray-600 mt-1">
                    <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ documentPreview }}</span>
                  </div>
                </div>
              </div>
            </template>

            <!-- Doanh nghiệp -->
            <template v-if="sellerType === 'business'">
              <!-- Mã số thuế -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Mã số thuế</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.tax_code" placeholder="Nhập mã số thuế" 
                  :class="{ 'border-red-500': errors.tax_code }">
                <p v-if="errors.tax_code" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.tax_code }}
                </p>
              </div>

              <!-- Tên công ty -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Tên công ty</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.company_name" placeholder="Nhập tên công ty" 
                  :class="{ 'border-red-500': errors.company_name }">
                <p v-if="errors.company_name" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.company_name }}
                </p>
              </div>

              <!-- Địa chỉ công ty -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Địa chỉ công ty</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.company_address" placeholder="Nhập địa chỉ công ty" 
                  :class="{ 'border-red-500': errors.company_address }">
                <p v-if="errors.company_address" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.company_address }}
                </p>
              </div>

              <!-- Tên người đại diện pháp lý -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Tên người đại diện pháp lý</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.representative_name" placeholder="Nhập tên người đại diện pháp lý" 
                  :class="{ 'border-red-500': errors.representative_name }">
                <p v-if="errors.representative_name" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.representative_name }}
                </p>
              </div>

              <!-- Số điện thoại đại diện -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Số điện thoại đại diện</label>
                <input type="text"
                  class="w-full border border-gray-200 rounded-[9px] px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base bg-white outline-none transition-all placeholder-gray-400"
                  v-model="form.representative_phone" placeholder="Nhập SĐT người đại diện" 
                  :class="{ 'border-red-500': errors.representative_phone }">
                <p v-if="errors.representative_phone" class="text-sm text-red-600 mt-1.5 flex items-center">
                  {{ errors.representative_phone }}
                </p>
              </div>

              <!-- Giấy phép kinh doanh -->
              <div>
                <label class="block font-semibold text-sm mb-1.5 text-gray-700">Giấy phép kinh doanh</label>
                <label
                  class="flex items-center gap-3 px-3 py-2 rounded-lg border border-gray-200 bg-[#f8fafc] cursor-pointer transition hover:border-[#1BA0E2] hover:bg-[#f1f8fd]">
                  <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a2 2 0 10-2.828-2.828z" />
                  </svg>
                  <span class="flex-1 text-sm truncate text-gray-700" v-if="form.business_license">{{ form.business_license.name }}</span>
                  <span class="flex-1 text-sm text-gray-400" v-else>Chọn file giấy phép kinh doanh</span>
                  <span
                    class="ml-3 text-[#fff] bg-[#1BA0E2] px-3 py-1 rounded font-semibold text-sm hover:bg-[#1780B6] transition">Chọn file</span>
                  <input type="file" class="hidden" accept="image/*,application/pdf" @change="onBusinessLicenseFile">
                </label>
                <div v-if="businessLicensePreview" class="mt-2">
                  <img v-if="isImage(businessLicensePreview)" :src="businessLicensePreview" class="max-h-32 rounded-lg border border-gray-200 shadow" alt="Giấy phép kinh doanh" />
                  <div v-else class="flex items-center gap-2 text-sm text-gray-600 mt-1">
                    <svg class="w-5 h-5 text-[#1BA0E2]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ businessLicensePreview }}</span>
                  </div>
                </div>
              </div>
            </template>

            <!-- Nút submit -->
            <button type="submit" :disabled="loading"
              class="w-full text-white font-bold py-3 rounded-[11px] mt-2 text-lg transition-all duration-300 shadow disabled:opacity-60 disabled:cursor-not-allowed"
              :style="{ background: loading ? '#1BA0E2CC' : (isHover ? '#1780B6' : '#1BA0E2') }"
              @mouseenter="isHover = true" @mouseleave="isHover = false">
              <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              {{ loading ? 'Đang đăng ký...' : 'Đăng ký ngay' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import axios from 'axios';

const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;

const sellerType = ref('personal');
const isHover = ref(false);
const loading = ref(false);
const errors = reactive({});
const isUpgrading = ref(false); // Biến xác định trạng thái nâng cấp
const cccdPreviews = ref([]);
const documentFile = ref(null);
const documentPreview = ref('');
const businessLicensePreview = ref('');


const form = reactive({
  store_name: '',
  seller_type: 'personal',
  phone_number: '',
  identity_card_number: '',
  date_of_birth: '',
  personal_address: '',
  document: null,
  bio: '',
  tax_code: '',
  company_name: '',
  company_address: '',
  representative_name: '',
  representative_phone: '',
  business_license: null
});

// Kiểm tra trạng thái Seller khi component được mount
onMounted(async () => {
  await checkSellerStatus();
});

async function checkSellerStatus() {
  try {
    const token = localStorage.getItem('access_token');
    if (!token) return;

    const res = await axios.get(`${API}/sellers/me`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    const seller = res?.data?.data;
    if (seller && seller.seller_type === 'personal') {
      isUpgrading.value = true;
      sellerType.value = 'business';
      form.store_name = seller.store_name; // Tái sử dụng store_name
      form.seller_type = 'business';
    }
  } catch (error) {
    console.error('Error checking seller status:', error);
  }
}

watch(sellerType, (newType) => {
  form.seller_type = newType;
  resetFormData();
});

function resetFormData() {
  Object.assign(errors, {});
  cccdPreviews.value = [];
  documentFile.value = null;
  documentPreview.value = '';
  businessLicensePreview.value = '';

  if (sellerType.value === 'business') {
    form.phone_number = '';
    form.identity_card_number = '';
    form.date_of_birth = '';
    form.personal_address = '';
    form.document = null;
    form.bio = '';
  } else if (!isUpgrading.value) {
    form.tax_code = '';
    form.company_name = '';
    form.company_address = '';
    form.representative_name = '';
    form.representative_phone = '';
    form.business_license = null;
  }
}

function isImage(file) {
  return file && file.startsWith('data:image');
}

function onCccdFiles(event) {
  const files = event.target.files;
  cccdPreviews.value = [];
  if (files.length > 0) {
    form.document = files[0];
    cccdPreviews.value.push(URL.createObjectURL(files[0]));
    if (files[1]) {
      cccdPreviews.value.push(URL.createObjectURL(files[1]));
    }
  }
}

function onDocumentFile(event) {
  const file = event.target.files[0];
  if (file) {
    documentFile.value = file;
    form.document = file;
    documentPreview.value = file.type.startsWith('image/') ? URL.createObjectURL(file) : file.name;
  }
}

function onBusinessLicenseFile(event) {
  const file = event.target.files[0];
  if (file) {
    form.business_license = file;
    businessLicensePreview.value = file.type.startsWith('image/') ? URL.createObjectURL(file) : file.name;
  }
}

function validateForm() {
  Object.assign(errors, {});
  const f = form;

  if (!isUpgrading.value) {
    if (!f.store_name) {
      errors.store_name = 'Tên cửa hàng là bắt buộc.';
    } else if (f.store_name.length > 255) {
      errors.store_name = 'Tên cửa hàng không được vượt quá 255 ký tự.';
    }
  }


    // seller_type check
  if (!['personal', 'business'].includes(f.seller_type)) {
    errors.seller_type = 'Loại người bán không hợp lệ.';
  }


  if (f.seller_type === 'personal' && !isUpgrading.value) {
    if (!f.phone_number || !/^[0-9]{10,15}$/.test(f.phone_number)) {
      errors.phone_number = 'Số điện thoại không hợp lệ (10–15 chữ số).';
    }
    if (!f.identity_card_number || f.identity_card_number.length > 20) {
      errors.identity_card_number = 'Số CMND/CCCD là bắt buộc và tối đa 20 ký tự.';
    }
    if (!f.date_of_birth) {
      errors.date_of_birth = 'Ngày sinh là bắt buộc.';
    }
    if (!f.personal_address) {
      errors.personal_address = 'Địa chỉ cá nhân là bắt buộc.';
    }
    if (!cccdPreviews.value.length || cccdPreviews.value.length !== 2) {
      errors.cccdPreviews = 'Vui lòng chọn đúng 2 ảnh CCCD (mặt trước và mặt sau).';
    }
  } else if (f.seller_type === 'business') {
    if (!f.tax_code) errors.tax_code = 'Mã số thuế là bắt buộc.';
    if (!f.company_name) errors.company_name = 'Tên công ty là bắt buộc.';
    if (!f.company_address) errors.company_address = 'Địa chỉ công ty là bắt buộc.';
    if (!f.representative_name) errors.representative_name = 'Tên người đại diện là bắt buộc.';
    if (!f.representative_phone || !/^[0-9]{10,15}$/.test(f.representative_phone)) {
      errors.representative_phone = 'Số điện thoại người đại diện không hợp lệ.';
    }
    if (!f.business_license) errors.business_license = 'Giấy phép kinh doanh là bắt buộc.';
  }

  return Object.keys(errors).length === 0;
}

async function handleSubmit() {
  if (!validateForm()) return;
  loading.value = true;
  Object.assign(errors, {});

  const token = localStorage.getItem('access_token');
  if (!token) {
    alert('Bạn chưa đăng nhập!');
    loading.value = false;
    return;
  }

  try {
    const meRes = await axios.get(`${API}/me`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    const user = meRes?.data?.data;
    if (!user || !user.id) {
      alert('Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.');
      loading.value = false;
      return;
    }

    const f = form;
    const formData = new FormData();
    formData.append('seller_type', f.seller_type);
    formData.append('user_id', user.id);

    if (isUpgrading.value) {
      // Gửi dữ liệu cho nâng cấp
      formData.append('tax_code', f.tax_code);
      formData.append('company_name', f.company_name);
      formData.append('company_address', f.company_address);
      formData.append('representative_name', f.representative_name);
      formData.append('representative_phone', f.representative_phone);
      if (f.business_license) formData.append('business_license', f.business_license);
    } else {
      // Gửi dữ liệu cho đăng ký mới
      formData.append('store_name', f.store_name);
      if (f.seller_type === 'personal') {
        formData.append('phone_number', f.phone_number);
        formData.append('identity_card_number', f.identity_card_number);
        formData.append('date_of_birth', f.date_of_birth);
        formData.append('personal_address', f.personal_address);
        if (f.document) formData.append('document', f.document);
        if (f.bio) formData.append('bio', f.bio);
      } else {
        formData.append('tax_code', f.tax_code);
        formData.append('company_name', f.company_name);
        formData.append('company_address', f.company_address);
        formData.append('representative_name', f.representative_name);
        formData.append('representative_phone', f.representative_phone);
        if (f.business_license) formData.append('business_license', f.business_license);
      }
    }

    const response = await axios.post(`${API}/sellers/register`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`
      }
    });

    alert(response.data.message || 'Thành công!');
    resetFormData();
  } catch (error) {
    const res = error.response;
    Object.assign(errors, res?.data?.errors || {});
    const message = res?.data?.message || res?.data?.error || 'Thất bại!';
    const detail = Object.values(errors).flat().join('\n');
    alert(detail ? `${message}\n\n${detail}` : message);
    console.error('Error:', error);
  } finally {
    loading.value = false;
  }
}
</script>


