<template>
  <main class="bg-white p-8 rounded shadow max-w-7xl mx-auto mt-6">
    <!-- Banner -->
    <div class="text-center mb-10">
      <h1 class="text-3xl font-bold text-[#1BA0E2]">Bán hàng cùng Passion</h1>
      <p class="mt-2 text-gray-600">Biến đam mê của bạn thành thu nhập ổn định với nền tảng thương mại điện tử Passion.</p>
      <img src="/images/seller.png" alt="Bán hàng cùng Passion" class="mx-auto mt-6 rounded-lg shadow-md w-full max-w-3xl">
      <button
        @click="handleRegisterClick"
        class="inline-block mt-6 bg-[#1BA0E2] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#1591cc] transition"
        :disabled="checkingAuth"
      >
        {{ checkingAuth ? 'Đang kiểm tra...' : 'Đăng ký ngay' }}
      </button>
      <p v-if="errorMessage" class="text-red-600 text-sm mt-2">{{ errorMessage }}</p>
    </div>

    <!-- Lợi ích -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
      <div class="text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Khách hàng" class="mx-auto h-20">
        <h3 class="font-bold text-lg mt-4 text-[#1BA0E2]">Tiếp cận khách hàng đam mê</h3>
        <p class="text-gray-600 text-sm mt-1">Kết nối với cộng đồng người tiêu dùng yêu thích sản phẩm độc đáo, sáng tạo.</p>
      </div>
      <div class="text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" alt="Hỗ trợ" class="mx-auto h-20">
        <h3 class="font-bold text-lg mt-4 text-[#1BA0E2]">Hỗ trợ khởi nghiệp dễ dàng</h3>
        <p class="text-gray-600 text-sm mt-1">Đội ngũ Passion sẵn sàng đồng hành cùng bạn trên từng bước phát triển.</p>
      </div>
      <div class="text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/2331/2331970.png" alt="Hệ thống" class="mx-auto h-20">
        <h3 class="font-bold text-lg mt-4 text-[#1BA0E2]">Hệ thống vận hành tối ưu</h3>
        <p class="text-gray-600 text-sm mt-1">Quản lý đơn hàng, vận chuyển và thanh toán dễ dàng – tất cả trong một.</p>
      </div>
    </div>

    <!-- Hướng dẫn -->
    <div class="mt-16 text-center">
      <h2 class="text-2xl font-bold mb-4 text-[#1BA0E2]">Chỉ 4 bước để bắt đầu bán hàng</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-left mt-6">
        <div class="p-4 bg-[#E6F4FB] rounded-lg">
          <h3 class="font-semibold mb-1 text-[#1BA0E2]">1. Tạo tài khoản nhà bán</h3>
          <p class="text-sm text-gray-600">Điền thông tin cơ bản để bắt đầu hành trình cùng Passion.</p>
        </div>
        <div class="p-4 bg-[#E6F4FB] rounded-lg">
          <h3 class="font-semibold mb-1 text-[#1BA0E2]">2. Chọn phương thức vận chuyển</h3>
          <p class="text-sm text-gray-600">Lựa chọn các phương thức vận chuyển phù hợp cho cửa hàng.</p>
        </div>
        <div class="p-4 bg-[#E6F4FB] rounded-lg">
          <h3 class="font-semibold mb-1 text-[#1BA0E2]">3. Cung cấp thông tin xác minh</h3>
          <p class="text-sm text-gray-600">Hoàn thiện hồ sơ với thông tin cá nhân hoặc doanh nghiệp.</p>
        </div>
        <div class="p-4 bg-[#E6F4FB] rounded-lg">
          <h3 class="font-semibold mb-1 text-[#1BA0E2]">4. Xác nhận và gửi đăng ký</h3>
          <p class="text-sm text-gray-600">Kiểm tra và gửi hồ sơ để bắt đầu bán hàng.</p>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from '~/composables/useToast';

const router = useRouter();
const { toast } = useToast();
const checkingAuth = ref(false);
const errorMessage = ref('');

const handleRegisterClick = async () => {
  checkingAuth.value = true;
  errorMessage.value = '';

  try {
    const token = localStorage.getItem('access_token');
    if (!token) {
      errorMessage.value = 'Vui lòng đăng nhập để bắt đầu đăng ký.';
      toast('error', 'Bạn cần đăng nhập để đăng ký bán hàng.');
      setTimeout(() => {
        router.push('/login');
      }, 2000); // Delay để người dùng thấy thông báo
      return;
    }

    // Giả sử có API kiểm tra token hợp lệ
    // Nếu không có API này, bạn có thể bỏ qua bước kiểm tra hoặc dùng cách khác
    // Ví dụ: Gửi yêu cầu thử đến một endpoint bảo mật
    // await axios.get(`${api}/auth/check`, { headers: { Authorization: `Bearer ${token}` } });

    router.push('/seller/RegisterSellerSteps/step1');
  } catch (err) {
    console.error('Lỗi khi kiểm tra đăng nhập:', err);
    errorMessage.value = 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.';
    toast('error', 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.');
    setTimeout(() => {
      router.push('/login');
    }, 2000);
  } finally {
    checkingAuth.value = false;
  }
};
</script>

<style scoped>
/* Giữ nguyên style hiện tại */
</style>