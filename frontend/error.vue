<template>
  <Header />

  <div class="flex flex-col items-center text-center px-10 py-20 pt-10 pb-8 text-base">
    <!-- Ảnh lỗi -->
    <div class="w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg mb-6 animate-fade-in-slide delay-100">
      <img
        src="/images/404.png"
        alt="Lỗi 404"
        class="w-full h-auto max-h-[350px] mx-auto object-contain drop-shadow-md"
      />
    </div>

    <!-- Tiêu đề -->
    <h1 class="text-2xl font-bold text-[#1BA0E2] animate-fade-in-slide delay-200">
      Trang không tồn tại
    </h1>

    <!-- Mô tả -->
    <p class="text-gray-600 mt-2 max-w-md animate-fade-in-slide delay-300">
      Có vẻ như liên kết bạn truy cập không hợp lệ hoặc đã bị xoá. Vui lòng quay lại trang chủ.
    </p>

    <!-- Nút -->
   <button
  @click="goBack"
  class="mt-6 px-6 py-3 bg-[#1BA0E2] text-white font-semibold rounded-full shadow hover:bg-[#1591cc] transition animate-fade-in-slide delay-500"
>
  Quay lại trang trước
</button>

  </div>

  <Footer />
</template>

<script setup>
import Header from '~/components/shared/layouts/Header.vue'
import Footer from '~/components/shared/layouts/Footer.vue'
import { useRouter } from 'vue-router'
const router = useRouter()

const goBack = () => {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/')
  }
}

</script>

<style scoped>
@keyframes fade-in-slide {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-slide {
  animation: fade-in-slide 0.6s ease-out both;
}

.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-500 { animation-delay: 0.5s; }
</style>