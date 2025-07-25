<template>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-white to-blue-50 px-4 py-2">
<div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full text-center min-h-[550px] flex flex-col justify-between">
      <!-- Icon thành công -->
      <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <!-- Tiêu đề -->
      <h1 class="text-2xl font-bold text-green-600 mb-2">Gửi đăng ký thành công!</h1>
      <p class="text-gray-700 text-sm mb-4">
        Hồ sơ đăng ký bán hàng của bạn đã được gửi đi. Chúng tôi sẽ xem xét và phản hồi trong vòng
        <span class="font-medium text-blue-600">1–3 ngày làm việc</span>
        qua email hoặc thông báo hệ thống.
      </p>

      <!-- Thông tin thêm -->
      <div class="grid grid-cols-2 gap-3 my-5 text-sm">
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-green-700 flex flex-col items-center">
          <i class="fas fa-envelope text-lg mb-1"></i>
          <div>Email thông báo</div>
          <div class="text-xs text-gray-500">Sẽ được gửi khi có kết quả</div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-blue-700 flex flex-col items-center">
          <i class="fas fa-clock text-lg mb-1"></i>
          <div>Thời gian xử lý</div>
          <div class="text-xs text-gray-500">1–3 ngày làm việc</div>
        </div>
      </div>

      <!-- Nút chính -->
      <button @click="goHome" class="w-full py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition mb-3">
        <i class="fas fa-home mr-2"></i>Về trang chủ
      </button>

      <!-- Hỗ trợ -->
      <button class="w-full py-2 rounded-lg border border-gray-200 text-gray-700 text-sm hover:bg-gray-100 transition">
     
        <NuxtLink to="/support">   <i class="fas fa-headset mr-2"></i>Liên hệ hỗ trợ</NuxtLink>
      </button>

    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const goHome = () => {
  router.push('/')
}
</script>
