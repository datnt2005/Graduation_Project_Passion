<template>
  <div class="bg-gray-50 min-h-screen flex items-center justify-center px-4 py-12 font-sans">
    <div class="bg-white border border-gray-200 rounded-2xl max-w-md w-full p-8 flex flex-col items-center shadow-lg animate-fade-in">
      <!-- Redesigned SVG Icon -->
      <div class="border-2 border-blue-500 bg-white w-16 h-16 rounded-full flex items-center justify-center mb-6">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="circleGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#1E40AF;stop-opacity:1" />
            </linearGradient>
          </defs>
          <circle cx="24" cy="24" r="22" fill="url(#circleGradient)" opacity="0.9" stroke="#3B82F6" stroke-width="2"/>
          <path class="checkmark" d="M16 24.5L22 30.5L34 18.5" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <!-- Title -->
      <h1 class="text-2xl font-bold text-blue-600 uppercase mb-2 tracking-tight text-center">
        Đăng ký bán hàng thành công!
      </h1>
      <div class="w-16 border-b-2 border-blue-500 mb-6"></div>
      <p class="text-gray-700 text-base text-center font-medium mb-6 leading-relaxed">
        Hồ sơ của bạn đã được gửi lên hệ thống.<br>
        <span class="font-semibold text-blue-600">Chúng tôi sẽ kiểm duyệt trong vòng 1-2 ngày làm việc.</span>
      </p>
      <div class="w-full border border-blue-200 bg-blue-50 text-blue-600 px-5 py-3 rounded-lg mb-6 flex items-center gap-3">
        <font-awesome-icon :icon="['fas', 'clock']" class="w-5 h-5" />
        <span>
          Hệ thống sẽ thông báo kết quả duyệt hồ sơ qua <b>Email</b> của bạn.
        </span>
      </div>
      <ul class="w-full text-gray-600 text-sm mb-8 space-y-2">
        <li><span class="font-bold text-blue-600">Quy trình duyệt:</span></li>
        <li>• Hồ sơ được kiểm tra tự động & thủ công.</li>
        <li>• Nếu cần bổ sung, Passion sẽ liên hệ qua email/SĐT.</li>
        <li>• Sau khi duyệt xong, bạn sẽ nhận thông báo hướng dẫn đăng nhập Seller Center.</li>
      </ul>
      <button
        class="w-full border-2 border-blue-500 text-blue-500 font-semibold rounded-lg py-3 uppercase hover:bg-blue-50 focus:ring-2 focus:ring-blue-300 transition duration-200"
        @click="goToHome"
        aria-label="Quay về trang chủ"
      >
        Về trang chủ
      </button>
      <router-link to="/seller/dashboard" class="w-full text-center text-blue-500 hover:underline text-sm mt-4">
        Xem hướng dẫn bán hàng
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

function goToHome() {
  router.push('/')
}
</script>

<style scoped>
/* Fade-in animation for the card */
.animate-fade-in {
  animation: fade-in 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Checkmark animation */
.checkmark {
  stroke-dasharray: 100;
  stroke-dashoffset: 100;
  animation: draw-check 0.5s ease-out forwards;
  animation-delay: 0.3s;
}

@keyframes draw-check {
  to {
    stroke-dashoffset: 0;
  }
}

/* Improve button focus state */
button:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .max-w-md {
    max-width: 90%;
  }
  h1 {
    font-size: 1.25rem;
  }
  button {
    padding: 0.75rem;
  }
}
</style>