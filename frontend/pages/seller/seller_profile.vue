  <template>
    <div class="bg-gray-50 min-h-screen py-8 px-4 sm:px-8">
      <div class="max-w-5xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gray-800 px-6 py-5 text-white flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-bold">Hồ Sơ Người Bán</h2>
            <p class="text-sm text-blue-200">Thông tin chi tiết doanh nghiệp / cá nhân bán hàng</p>
          </div>
          <button
            @click="editProfile"
            class="bg-white text-blue-700 font-medium px-4 py-2 rounded-md hover:bg-blue-100 transition"
          >
            Chỉnh sửa hồ sơ
          </button>
        </div>
        <!-- Render toàn bộ nếu đã có seller -->
        <template v-if="seller">
          <!-- Body -->
          <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 border-b">
            <!-- Avatar + Basic Info -->
            <div class="col-span-1 flex flex-col items-center text-center">
              <img
                :src="seller.user?.avatar_url"
                alt="Avatar"
                class="w-28 h-28 rounded-full object-cover border mb-4 cursor-pointer"
                @click="openImagePreview(seller.user?.avatar_url)"
              />

              <h3 class="text-lg font-semibold text-gray-800">{{ seller.store_name }}</h3>
              <p class="text-sm text-gray-500 italic">{{ seller.store_slug }}</p>
              <span class="mt-2 px-3 py-1 text-xs font-medium rounded-full"
                :class="statusColor(seller.verification_status)">
                {{ statusLabel[seller.verification_status] }}
              </span>
            </div>

            <!-- General Info -->
            <div class="col-span-2 grid sm:grid-cols-2 gap-4 text-sm text-gray-700">
              <div>
                <label class="font-medium">Loại người bán:</label>
                <p>{{ seller.seller_type }}</p>
              </div>
              <div>
                <label class="font-medium">Số điện thoại:</label>
                <p>{{ seller.phone_number }}</p>
              </div>
              <div>
                <label class="font-medium">Email liên hệ:</label>
                <p>{{ seller.user?.email || '—' }}</p>
              </div>
              <div>
                <label class="font-medium">Ngày sinh / thành lập:</label>
                <p>{{ seller.date_of_birth }}</p>
              </div>
              <div class="sm:col-span-2">
                <label class="font-medium">CMND/CCCD / Mã số thuế:</label>
                <p>{{ seller.identity_card_number }}</p>
              </div>
            <div class="mt-4">
              <label class="font-medium">Ảnh CCCD (2 mặt):</label>
              <div class="flex gap-6 mt-2"> <!-- Dùng flex ở đây để 2 ảnh nằm ngang -->
                <!-- Mặt trước -->
                <div class="flex flex-col items-start">
                  <p class="text-sm text-gray-500 mb-1">Mặt trước:</p>
                  <img
                    v-if="getCccdImage(seller, 'front')"
                    :src="getCccdImage(seller, 'front')"
                    alt="CCCD Mặt trước"
                   class="w-48 rounded border shadow cursor-pointer"
                    @click="openImagePreview(getCccdImage(seller, 'front'))"
                  />
                  <p v-else class="text-gray-400 text-sm">Không có ảnh mặt trước</p>
                </div>

                <!-- Mặt sau -->
                <div class="flex flex-col items-start">
                  <p class="text-sm text-gray-500 mb-1">Mặt sau:</p>
                  <img
                    v-if="getCccdImage(seller, 'back')"
                    :src="getCccdImage(seller, 'back')"
                    alt="CCCD Mặt sau"
                    class="w-48 rounded border shadow cursor-pointer"
                    @click="openImagePreview(getCccdImage(seller, 'back'))"
                  />
                  <p v-else class="text-gray-400 text-sm">Không có ảnh mặt sau</p>
                </div>
              </div>
            </div>


            </div>
          </div>

          <!-- Address & Other -->
          <div class="p-6 grid sm:grid-cols-2 gap-6 text-sm text-gray-700">
            <div>
              <label class="font-medium">Địa chỉ cá nhân / công ty:</label>
              <p>{{ seller.personal_address }}</p>
            </div>
            <div>
              <label class="font-medium">Website / Fanpage (nếu có):</label>
              <p>{{ seller.website || '—' }}</p>
            </div>
          </div>
          <!-- Business Seller Info -->
          <div
            v-if="seller.seller_type === 'business' && seller.business"
            class="p-6 border-t bg-gray-50 grid sm:grid-cols-2 gap-6 text-sm text-gray-700"
          >
            <h3 class="text-base font-semibold col-span-2 text-gray-800 border-b pb-2">Thông tin doanh nghiệp</h3>
            <div>
              <label class="font-medium">Tên công ty:</label>
              <p>{{ seller.business.company_name }}</p>
            </div>
            <div>
              <label class="font-medium">Mã số thuế:</label>
              <p>{{ seller.business.tax_code }}</p>
            </div>
            <div>
              <label class="font-medium">Địa chỉ công ty:</label>
              <p>{{ seller.business.company_address }}</p>
            </div>
          <div>
            <label class="font-medium">Giấy phép kinh doanh:</label>
         <img
            v-if="getDocumentImage(seller, 'business')"
            :src="mediaBase + getDocumentImage(seller, 'business')"
            alt="Giấy phép kinh doanh"
              class="mt-2  max-w-xs w-full h-auto rounded border shadow cursor-pointer"
            @click="openImagePreview(mediaBase + getDocumentImage(seller, 'business'))"
          />
            <p v-else class="text-gray-400">Chưa có giấy phép</p>
          </div>
            <div>
              <label class="font-medium">Người đại diện:</label>
              <p>{{ seller.business.representative_name }}</p>
            </div>
            <div>
              <label class="font-medium">SĐT người đại diện:</label>
              <p>{{ seller.business.representative_phone }}</p>
            </div>
          </div>
        </template>
        <div v-else class="text-center text-gray-400 py-10">Đang tải hồ sơ người bán...</div>
      </div>
    </div>
   <!-- Overlay preview ảnh to -->
<div
  v-if="previewImage"
  class="fixed inset-0 z-50 bg-black bg-opacity-70 flex justify-center items-center"
  @click.self="closeImagePreview"
>
  <img
    :src="previewImage"
    alt="Preview"
    class="max-w-[90vw] max-h-[85vh] object-contain rounded shadow-lg border-4 border-white"
  />
</div>

  </template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2'
const config = useRuntimeConfig();
const seller = ref(null);
const API = config.public.apiBaseUrl;
const mediaBase = (config.public.mediaBaseUrl || 'http://localhost:8000').replace(/\/?$/, '/');


onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token');

    if (!token) {
      toast('error', 'Vui lòng đăng nhập để tiếp tục!');
      return;
    }

    const response = await axios.get(`${API}/sellers/seller/me`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    console.log('response', response);

    seller.value = response.data.seller;
  } catch (error) {
    console.error('Lỗi khi tải hồ sơ người bán:', error);
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

const previewImage = ref(null);

const openImagePreview = (imgUrl) => {
  previewImage.value = imgUrl;
};

const closeImagePreview = () => {
  previewImage.value = null;
};

const getDocumentImage = (seller, type) => {
  if (type === 'business' && seller?.business?.business_license) {
    return seller.business.business_license;
  }
  if (type === 'personal' && seller?.document) {
    return seller.document;
  }
  return ''; // hoặc return null nếu bạn đã kiểm tra v-if kỹ
};

const getCccdImage = (seller, side) => {
  if (side === 'front' && seller?.cccd_front) {
    return mediaBase + seller.cccd_front;
  }
  if (side === 'back' && seller?.cccd_back) {
    return mediaBase + seller.cccd_back;
  }
  return null;
};


const statusLabel = {
  pending: 'Đang chờ xác minh',
  verified: 'Đã xác minh',
  rejected: 'Bị từ chối'
}

function statusColor(status) {
  switch (status) {
    case 'verified':
      return 'bg-green-100 text-green-700'
    case 'pending':
      return 'bg-yellow-100 text-yellow-700'
    case 'rejected':
      return 'bg-red-100 text-red-700'
    default:
      return 'bg-gray-100 text-gray-600'
  }
}

function getAvatarUrl(avatarUrl) {
  return avatarUrl || mediaBase + 'avatars/default.jpg';
}
function editProfile() {
  // Điều hướng đến form chỉnh sửa
  toast('success', 'Đi đến chỉnh sửa hồ sơ.')
  navigateTo(`/seller/seller_profile_edit`)
}

definePageMeta({
  layout: 'default-seller'
})
</script>

