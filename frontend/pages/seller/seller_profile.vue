<template>
  <div class="bg-[#eaeff0] min-h-screen p-6">
    <div ref="sellerProfile" class="max-w-6xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center bg-white p-5 rounded-xl shadow">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Hồ Sơ Người Bán</h2>
          <p class="text-sm text-gray-500">Thông tin chi tiết doanh nghiệp / cá nhân bán hàng</p>
        </div>
        <div class="flex gap-2">
          <button @click="editProfile" class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">
            Chỉnh sửa
          </button>
          <button @click="printToPDF" class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">
            Xuất PDF
          </button>
        </div>
      </div>

      <!-- Main content -->
      <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6" v-if="seller">
        <!-- Bên trái: Thông tin -->
        <div class="space-y-6">
          <!-- Thông tin cơ bản -->
          <div class="bg-white rounded-xl p-6 shadow space-y-6">
            <div class="flex items-center gap-4">
              <img
                :src="seller.user?.avatar?.startsWith('https://') 
                  ? seller.user.avatar 
                  : `https://pub-3fc809b4396849cba1c342a5b9f50be9.r2.dev/${seller.user?.avatar || 'default.jpg'}`"
                class="w-20 h-20 rounded-full border object-cover"
                alt="avatar"
              />
              <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ seller.store_name }}</h3>
                <p class="text-sm text-gray-500">@{{ seller.store_slug }}</p>
                <span class="mt-1 inline-block px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                  Đã xác thực
                </span>
              </div>
            </div>

            <!-- Các trường -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-8 text-sm text-gray-700 flex-1">
              <div class="flex gap-2">
                <span class="text-gray-500 w-28">Số điện thoại:</span>
                <span class="font-semibold text-gray-800">{{ seller.phone_number }}</span>
              </div>
              <div class="flex gap-2">
                <span class="text-gray-500 w-20">Email:</span>
                <span class="font-semibold text-gray-800">{{ seller.user?.email || '—' }}</span>
              </div>

              <div class="flex gap-2">
                <span class="text-gray-500 w-28">Ngày sinh:</span>
                <span class="font-semibold text-gray-800">{{ seller.date_of_birth }}</span>
              </div>
              <div class="flex gap-2">
                <span class="text-gray-500 w-20">CCCD:</span>
                <span class="font-semibold text-gray-800">{{ seller.identity_card_number }}</span>
              </div>
              
              <div class="flex gap-2 col-span-2">
                <span class="text-gray-500 w-28">Địa chỉ lấy hàng:</span>
                <span class="font-semibold text-gray-800">{{ seller.personal_address || '—' }}</span>
              </div>
              <div class="flex gap-2 col-span-2">
                <span class="text-gray-500 w-28">Website:</span>
                <span class="font-semibold text-blue-600 underline">
                  <a class="font-semibold text-blue-600" href="#" >Passion</a>
                </span>
              </div>
                   
              <div class="flex gap-2 col-span-2">
                <span class="text-gray-500 w-28">Giới Thiệu:</span>
                <span class="font-semibold text-gray-800">{{ seller.bio || '—' }}</span>
              </div>
            </div>
          </div>

          <!-- Thông tin doanh nghiệp -->
          <div class="bg-white rounded-xl p-6 shadow space-y-4">
            <template v-if="seller.seller_type === 'business'">
              <h3 class="text-base font-semibold text-gray-800">🏢 Thông tin doanh nghiệp</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                  <p class="text-gray-500">Tên công ty</p>
                  <p class="font-medium">{{ seller.business_name || '—' }}</p>
                </div>
                <div>
                  <p class="text-gray-500">Mã số thuế</p>
                  <p class="font-medium">{{ seller.tax_code || '—' }}</p>
                </div>
                <div>
                  <p class="text-gray-500">Email doanh nghiệp</p>
                  <p class="font-medium">{{ seller.business_email || '—' }}</p>
                </div>
                <div>
                  <p class="text-gray-500">Địa chỉ công ty</p>
                  <p class="font-medium">{{ seller.business?.company_address || seller.pickup_address || '—' }}</p>
                </div>
                <div>
                  <p class="text-gray-500">Người đại diện</p>
                  <p class="font-medium">{{ seller.business?.representative_name || seller.user?.name || '—' }}</p>
                </div>
                <div>
                  <p class="text-gray-500">SĐT người đại diện</p>
                  <p class="font-medium">{{ seller.business?.representative_phone || seller.phone_number || '—' }}</p>
                </div>
              </div>
            </template>
            <template v-else>
              <p class="text-sm text-gray-500 italic">Người này chưa đăng ký thông tin doanh nghiệp.</p>
            </template>
          </div>
        </div>

        <!-- Bên phải: Hình ảnh -->
        <div class="bg-white rounded-xl p-6 shadow space-y-4 text-sm text-gray-700">
          <h3 class="text-base font-semibold text-gray-800">📄 Giấy tờ tùy thân</h3>
          <!-- CCCD mặt trước -->
          <div class="space-y-1">
            <p class="text-gray-500">CCCD - Mặt trước</p>
            <div
              class="w-full aspect-[4/3] bg-gray-100 border rounded flex items-center justify-center text-gray-400 cursor-pointer"
              @click="getCccdImage(seller, 'front') && openImagePreview(getCccdImage(seller, 'front'))">
              <img v-if="getCccdImage(seller, 'front')" :src="getCccdImage(seller, 'front')"
                class="object-contain max-h-52 rounded" alt="CCCD trước" />
              <span v-else class="text-sm italic">Chưa có ảnh</span>
            </div>
          </div>

          <!-- CCCD mặt sau -->
          <div class="space-y-1">
            <p class="text-gray-500">CCCD - Mặt sau</p>
            <div
              class="w-full aspect-[4/3] bg-gray-100 border rounded flex items-center justify-center text-gray-400 cursor-pointer"
              @click="getCccdImage(seller, 'back') && openImagePreview(getCccdImage(seller, 'back'))">
              <img v-if="getCccdImage(seller, 'back')" :src="getCccdImage(seller, 'back')"
                class="object-contain max-h-52 rounded" alt="CCCD sau" />
              <span v-else class="text-sm italic">Chưa có ảnh</span>
            </div>
          </div>

          <!-- GPKD -->
          <div class="space-y-1">
            <p class="text-gray-500">Giấy phép kinh doanh</p>
            <div
              class="w-full aspect-[4/3] bg-gray-100 border rounded flex items-center justify-center text-gray-400 cursor-pointer"
              @click="getDocumentImage(seller, 'business') && openImagePreview(getDocumentImage(seller, 'business'))">
              <img v-if="getDocumentImage(seller, 'business')" :src="getDocumentImage(seller, 'business')"
                class="object-contain max-h-52 rounded" alt="GPKD" />
              <span v-else class="text-sm italic">Chưa có ảnh</span>
            </div>
          </div>

        </div>
      </div>

      <!-- Loading fallback -->
     <!-- Skeleton loading -->
<div v-else class="animate-pulse grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
  <!-- Trái -->
  <div class="space-y-6">
    <div class="bg-white p-6 rounded-xl shadow space-y-6">
      <div class="flex items-center gap-4">
        <div class="w-20 h-20 rounded-full bg-gray-300"></div>
        <div class="space-y-2 flex-1">
          <div class="h-4 w-1/2 bg-gray-300 rounded"></div>
          <div class="h-3 w-1/3 bg-gray-200 rounded"></div>
          <div class="h-5 w-24 bg-gray-300 rounded"></div>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-8">
        <div class="h-4 bg-gray-200 rounded col-span-1"></div>
        <div class="h-4 bg-gray-200 rounded col-span-1"></div>
        <div class="h-4 bg-gray-200 rounded col-span-1"></div>
        <div class="h-4 bg-gray-200 rounded col-span-1"></div>
        <div class="h-4 bg-gray-200 rounded col-span-2"></div>
        <div class="h-4 bg-gray-200 rounded col-span-2"></div>
        <div class="h-4 bg-gray-200 rounded col-span-2"></div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow space-y-4">
      <div class="h-4 w-40 bg-gray-300 rounded"></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="h-3 w-full bg-gray-200 rounded"></div>
        <div class="h-3 w-full bg-gray-200 rounded"></div>
        <div class="h-3 w-full bg-gray-200 rounded"></div>
        <div class="h-3 w-full bg-gray-200 rounded"></div>
        <div class="h-3 w-full bg-gray-200 rounded"></div>
        <div class="h-3 w-full bg-gray-200 rounded"></div>
      </div>
    </div>
  </div>

  <!-- Phải -->
  <div class="bg-white p-6 rounded-xl shadow space-y-4">
    <div class="h-4 w-32 bg-gray-300 rounded"></div>
    <div class="space-y-4">
      <div class="w-full aspect-[4/3] bg-gray-200 rounded"></div>
      <div class="w-full aspect-[4/3] bg-gray-200 rounded"></div>
      <div class="w-full aspect-[4/3] bg-gray-200 rounded"></div>
    </div>
  </div>
</div>

    </div>

    <!-- Overlay xem ảnh -->
    <div v-if="previewImage" class="fixed inset-0 z-50 bg-black bg-opacity-70 flex justify-center items-center"
      @click.self="closeImagePreview">
      <img :src="previewImage" class="max-w-[90vw] max-h-[85vh] object-contain rounded shadow-lg border-4 border-white"
        alt="Preview" />
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { navigateTo } from '#app';
import { secureAxios } from '@/utils/secureAxios'

const config = useRuntimeConfig();
const API = config.public.apiBaseUrl;
const mediaBaseUrl = (config.public.mediaBaseUrl || 'http://localhost:8000').replace(/\/?$/, '/');

const seller = ref(null);
const sellerProfile = ref(null);
const previewImage = ref(null);
let html2pdf = null; // import động chỉ khi ở client

// Load dữ liệu khi mounted
onMounted(async () => {
  if (process.client) {
    // Import html2pdf.js chỉ khi chạy trên trình duyệt
    const module = await import('html2pdf.js');
    html2pdf = module.default;
  }

  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      toast('error', 'Vui lòng đăng nhập để tiếp tục!');
      return;
    }

  const { data } = await secureAxios(`${API}/sellers/seller/me`, {}, ['seller'])


    seller.value = data.seller;
  } catch (error) {
    console.error('Lỗi khi tải hồ sơ người bán:', error);
  }
});

// Mở ảnh xem trước
const openImagePreview = (url) => {
  previewImage.value = url;
};
const closeImagePreview = () => {
  previewImage.value = null;
};

// Lấy ảnh CCCD
const getCccdImage = (seller, side) => {
  const path =
    side === 'front' ? seller.id_card_front_url : seller.id_card_back_url
  if (!path) return null
  return path.startsWith('http') ? path : `${mediaBaseUrl}${path}`
}

// Ảnh giấy tờ
const getDocumentImage = (seller) => {
  const path = seller?.identity_card_file
  if (!path) return null
  return path.startsWith('http') ? path : `${mediaBaseUrl}${path}`
}



const statusColor = (status) => {
  switch (status) {
    case 'verified': return 'bg-green-100 text-green-700';
    case 'pending': return 'bg-yellow-100 text-yellow-700';
    case 'rejected': return 'bg-red-100 text-red-700';
    default: return 'bg-gray-100 text-gray-600';
  }
};

// Thông báo toast
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
    didOpen: (el) => {
      el.addEventListener('mouseenter', () => Swal.stopTimer());
      el.addEventListener('mouseleave', () => Swal.resumeTimer());
    },
  });
};

// In PDF
const printToPDF = () => {
  if (!process.client || !html2pdf) {
    toast('error', 'Chức năng chỉ dùng được trên trình duyệt!');
    return;
  }

  if (!seller.value) {
    toast('error', 'Hồ sơ người bán chưa được tải!');
    return;
  }

  const element = sellerProfile.value;
  if (!element) {
    toast('error', 'Không tìm thấy nội dung cần in PDF!');
    return;
  }

  const opt = {
    margin: [10, 10, 10, 10],
    filename: `HoSoNguoiBan_${seller.value.store_name}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };

  html2pdf().set(opt).from(element).save()
    .catch((err) => {
      console.error('Lỗi khi tạo PDF:', err);
      toast('error', 'Không thể tạo file PDF!');
    });
};

// Chuyển sang trang chỉnh sửa
const editProfile = async () => {
  
  await navigateTo('/seller/seller_profile_edit');
};

// Layout của trang
definePageMeta({
  layout: 'default-seller'
});
</script>