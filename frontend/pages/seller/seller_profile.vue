<template>
  <div class="bg-gray-50 min-h-screen py-10 px-4 sm:px-8">
    <div class="max-w-6xl mx-auto bg-white rounded-xl border border-gray-200 shadow-sm">

      <!-- Header -->
      <div class="flex justify-between items-start p-6 border-b border-gray-100">
        <div>
          <h2 class="text-2xl font-semibold text-gray-900">Hồ Sơ Người Bán</h2>
          <p class="text-sm text-gray-500 mt-1">Thông tin chi tiết doanh nghiệp / cá nhân bán hàng</p>
        </div>
        <button @click="editProfile"
          class="flex items-center gap-1 text-sm text-gray-700 hover:text-blue-600 border border-gray-300 hover:border-blue-500 rounded-md px-3 py-1.5 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536a2 2 0 012.828 0l1.172 1.172a2 2 0 010 2.828L13 15l-4 1 1-4z" />
          </svg>
          Chỉnh sửa
        </button>
      </div>

      <!-- Info Section -->
      <div class="grid sm:grid-cols-3 gap-6 p-6 text-sm text-gray-800">
        <!-- Avatar + Store Info -->
        <div class="col-span-1 flex flex-col items-center sm:items-start text-center sm:text-left">
          <img :src="getAvatarUrl(seller.avatar)" alt="Avatar" class="w-24 h-24 rounded-full object-cover border" />
          <p class="text-base font-medium mt-4">{{ seller.store_name }}</p>
          <p class="text-gray-500">@{{ seller.store_slug }}</p>
          <span
            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 3l7.5 4.5v5.25c0 4.28-3.11 8.14-7.5 9-4.39-.86-7.5-4.72-7.5-9V7.5L12 3z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
            </svg>
            Đã xác thực
          </span>

        </div>
        <!-- Contact + Info -->
        <div class="col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-gray-500">Loại người bán</label>
            <p class="mt-1 font-medium">{{ seller.seller_type }}</p>
          </div>
          <div>
            <label class="text-gray-500">Số điện thoại</label>
            <p class="mt-1 font-medium">{{ seller.phone_number }}</p>
          </div>
          <div>
            <label class="text-gray-500">Email liên hệ</label>
            <p class="mt-1 font-medium">{{ seller.email }}</p>
          </div>
          <div>
            <label class="text-gray-500">Ngày sinh / thành lập</label>
            <p class="mt-1 font-medium">{{ seller.date_of_birth }}</p>
          </div>
          <div class="sm:col-span-2">
            <label class="text-gray-500">CMND/CCCD / Mã số thuế</label>
            <p class="mt-1 font-medium">{{ seller.identity_card_number }}</p>
          </div>
        </div>
      </div>

      <!-- Địa chỉ & Website -->
      <div class="grid sm:grid-cols-2 gap-6 px-6 pb-6 text-sm text-gray-800">
        <div>
          <label class="text-gray-500">Địa chỉ cá nhân / công ty</label>
          <p class="mt-1 font-medium">{{ seller.personal_address }}</p>
        </div>
        <div>
          <a href="{ seller.website }" target="_blank" rel="noopener noreferrer">
            <label class="text-gray-500">Website / Fanpage</label>
            <p class="mt-1 font-medium text-blue-600 hover:underline cursor-pointer">
              {{ seller.website || '—' }}
            </p>
          </a>
        </div>
      </div>

      <!-- Thông tin doanh nghiệp -->
      <div v-if="seller.seller_type === 'Doanh Nghiệp' && seller.business_info"
        class="bg-gray-50 border-t border-gray-100 px-6 py-6 text-sm text-gray-800">
        <h3 class="text-base font-semibold mb-4 text-gray-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 10h18M9 21h6m-6 0a3 3 0 01-3-3v-4a3 3 0 013-3h6a3 3 0 013 3v4a3 3 0 01-3 3" />
          </svg>
          Thông tin doanh nghiệp
        </h3>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="text-gray-500">Tên công ty</label>
            <p class="mt-1 font-medium">{{ seller.business_info.company_name }}</p>
          </div>
          <div>
            <label class="text-gray-500">Mã số thuế</label>
            <p class="mt-1 font-medium">{{ seller.business_info.tax_code }}</p>
          </div>
          <div>
            <label class="text-gray-500">Địa chỉ công ty</label>
            <p class="mt-1 font-medium">{{ seller.business_info.company_address }}</p>
          </div>
          <div>
            <label class="text-gray-500">Số giấy phép kinh doanh</label>
            <p class="mt-1 font-medium">{{ seller.business_info.business_license }}</p>
          </div>
          <div>
            <label class="text-gray-500">Người đại diện</label>
            <p class="mt-1 font-medium">{{ seller.business_info.representative_name }}</p>
          </div>
          <div>
            <label class="text-gray-500">SĐT người đại diện</label>
            <p class="mt-1 font-medium">{{ seller.business_info.representative_phone }}</p>
          </div>
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
