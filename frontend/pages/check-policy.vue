<template>
  <div class="max-w-7xl mx-auto px-4 py-10 bg-white">
    <div class="text-sm text-gray-500 py-2 rounded">
      <NuxtLink to="/" class="text-gray-400">Trang chủ</NuxtLink>
      <span class="mx-1">›</span>
      <span class="text-black font-medium">Chính sách</span>
    </div>
    <div class="border-b pb-4">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Chính Sách Kiểm Hàng</h1>
      <p class="text-gray-600">Cập nhật lần cuối: 18/07/2025</p>
    </div>

    <div class="mt-8 space-y-8">
      <!-- Giới thiệu -->
      <section>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Giới thiệu về chính sách kiểm hàng</h2>
        <p class="text-gray-600 leading-relaxed">
          Passion cam kết đảm bảo chất lượng sản phẩm thông qua quy trình kiểm hàng nghiêm ngặt. Vui lòng tham khảo chi
          tiết dưới đây để hiểu rõ quy trình và trách nhiệm của bạn khi nhận hàng.
        </p>
      </section>

      <!-- Quy trình kiểm hàng -->
      <section>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Quy trình kiểm hàng</h2>
        <div class="bg-gray-50 p-4 rounded-lg">
          <ul class="list-disc pl-5 text-gray-600 space-y-2">
            <li>Kiểm tra sản phẩm trước khi đóng gói để đảm bảo không hư hỏng hoặc thiếu sót.</li>
            <li>Khách hàng có quyền kiểm tra hàng hóa trước khi thanh toán (áp dụng cho COD).</li>
            <li>Nhân viên giao hàng sẽ hỗ trợ mở gói và kiểm tra nhanh chóng.</li>
          </ul>
        </div>
      </section>

      <!-- Lưu ý khi kiểm hàng -->
      <section>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Lưu ý khi kiểm hàng</h2>
        <div class="bg-gray-50 p-4 rounded-lg">
          <ul class="list-disc pl-5 text-gray-600 space-y-2">
            <li>Chỉ kiểm tra bên ngoài bao bì hoặc sản phẩm khi có mặt nhân viên giao hàng.</li>
            <li>Không mở niêm phong nếu không có sự đồng ý của nhân viên giao hàng.</li>
            <li>Báo cáo ngay nếu phát hiện sản phẩm bị lỗi hoặc không đúng mô tả.</li>
          </ul>
        </div>
      </section>

      <!-- Trách nhiệm của khách hàng -->
      <section>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Trách nhiệm của khách hàng</h2>
        <div class="bg-gray-50 p-4 rounded-lg">
          <p class="text-gray-600">
            Khách hàng cần kiểm tra kỹ sản phẩm tại chỗ và ký xác nhận giao hàng. Sau khi nhận, Passion không chịu trách
            nhiệm nếu sản phẩm bị lỗi do vận chuyển mà không được báo cáo ngay.
          </p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { useHead } from '#imports'

useHead({
  title: 'Chính sách kiểm hàng',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})
</script>

<style scoped>
a {
  color: #0068a2;
}
</style>