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

      <!-- Body -->
      <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 border-b">
        <!-- Avatar + Basic Info -->
        <div class="col-span-1 flex flex-col items-center text-center">
          <img
            :src="getAvatarUrl(seller.avatar)"
            alt="Avatar"
            class="w-28 h-28 rounded-full object-cover border mb-4"
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
            <p>{{ seller.email || '—' }}</p>
          </div>
          <div>
            <label class="font-medium">Ngày sinh / thành lập:</label>
            <p>{{ seller.date_of_birth }}</p>
          </div>
          <div class="sm:col-span-2">
            <label class="font-medium">CMND/CCCD / Mã số thuế:</label>
            <p>{{ seller.identity_card_number }}</p>
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
        v-if="seller.seller_type === 'Doanh Nghiệp' && seller.business_info"
        class="p-6 border-t bg-gray-50 grid sm:grid-cols-2 gap-6 text-sm text-gray-700"
      >
        <h3 class="text-base font-semibold col-span-2 text-gray-800 border-b pb-2">Thông tin doanh nghiệp</h3>

        <div>
          <label class="font-medium">Tên công ty:</label>
          <p>{{ seller.business_info.company_name }}</p>
        </div>
        <div>
          <label class="font-medium">Mã số thuế:</label>
          <p>{{ seller.business_info.tax_code }}</p>
        </div>
        <div>
          <label class="font-medium">Địa chỉ công ty:</label>
          <p>{{ seller.business_info.company_address }}</p>
        </div>
        <div>
          <label class="font-medium">Số giấy phép kinh doanh:</label>
          <p>{{ seller.business_info.business_license }}</p>
        </div>
        <div>
          <label class="font-medium">Người đại diện:</label>
          <p>{{ seller.business_info.representative_name }}</p>
        </div>
        <div>
          <label class="font-medium">SĐT người đại diện:</label>
          <p>{{ seller.business_info.representative_phone }}</p>
        </div>
      </div>
    </div>
  </div>
</template>



<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';




const seller = ref({
  id: 101,
  store_name: 'Công ty TNHH TM ABC',
  store_slug: 'cong-ty-abc',
  seller_type: 'Doanh Nghiệp',
  phone_number: '0909123456',
  email: 'contact@abc.vn',
  identity_card_number: '0312345678', // hoặc mã số thuế
  // ngày thành lập
  date_of_birth: '2005-05-12',
  // địa chỉ cá nhân
  personal_address: 'Tầng 10, Tòa nhà XYZ, Quận 1, TP.HCM',
  website: 'https://abc.vn',
  verification_status: 'approved',
  avatar: null, 
  business_info: {
    tax_code: '1234567890',
    company_name: 'Công ty TNHH ABC',
    company_address: '123 Nguyễn Trãi, Q1, TP.HCM',
    business_license: 'GP-2024-XYZ',
    representative_name: 'Nguyễn Văn A',
    representative_phone: '0909888777'
  }
}); 



const statusLabel = {
  pending: 'Đang chờ xác minh',
  approved: 'Đã xác minh',
  rejected: 'Bị từ chối'
}

function statusColor(status) {
  switch (status) {
    case 'approved':
      return 'bg-green-100 text-green-700'
    case 'pending':
      return 'bg-yellow-100 text-yellow-700'
    case 'rejected':
      return 'bg-red-100 text-red-700'
    default:
      return 'bg-gray-100 text-gray-600'
  }
}

function getAvatarUrl(avatar) {
  return avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(seller.value.store_name)
}

function editProfile() {
  // Điều hướng đến form chỉnh sửa
  // navigateTo(`/seller/profile/edit`)
  alert('Đi đến chỉnh sửa hồ sơ.')
}

definePageMeta({
  layout: 'default-seller'
})
</script>
