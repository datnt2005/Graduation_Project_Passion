<template>
  <main class="bg-white text-gray-800 mt-4 mb-4">

    <!-- Giới thiệu -->
    <section class="py-12 px-4 md:px-20">
      <div class="grid md:grid-cols-2 gap-8 items-center">
        <div>
          <h2 class="text-2xl font-bold mb-4">Giới thiệu về Passion</h2>
          <p class="text-gray-700 text-lg leading-relaxed">
            Passion là nền tảng thương mại điện tử đa ngành, nơi kết nối hàng triệu người mua với hàng ngàn người bán trên khắp Việt Nam. Không chỉ là nơi mua sắm đa dạng, Passion còn cho phép người dùng dễ dàng trở thành người bán, khởi tạo gian hàng và phát triển thương hiệu cá nhân.
          </p>
        </div>
        <img src="https://cdn-img.upanhlaylink.com/img/image_20250527d785fa616f5da6aa56ce93f3ecf34d12.jpg" alt="Giới thiệu Passion" class="rounded-xl shadow-md w-full" />
      </div>
    </section>

    <!-- Tầm nhìn & Sứ mệnh -->
    <section class="bg-gray-100 py-12 px-4 md:px-20">
      <div class="grid md:grid-cols-2 gap-10">
        <div class="flex items-start gap-4">
          <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 4v16m8-8H4"></path>
          </svg>
          <div>
            <h3 class="text-xl font-bold mb-2">Tầm nhìn</h3>
            <p class="text-gray-700 text-lg">
              Trở thành nền tảng thương mại điện tử hàng đầu Việt Nam, nơi ai cũng có thể bắt đầu kinh doanh dễ dàng.
            </p>
          </div>
        </div>
        <div class="flex items-start gap-4">
          <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M5 13l4 4L19 7"></path>
          </svg>
          <div>
            <h3 class="text-xl font-bold mb-2">Sứ mệnh</h3>
            <p class="text-gray-700 text-lg">
              Cung cấp môi trường kinh doanh minh bạch, tiện lợi và hỗ trợ toàn diện cho người mua và người bán.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Giá trị cốt lõi -->
    <section class="py-12 px-4 md:px-20">
      <h2 class="text-2xl font-bold mb-4">Giá trị cốt lõi</h2>
      <ul class="list-disc list-inside text-gray-700 text-lg space-y-2">
        <li>Đa dạng sản phẩm – phục vụ mọi nhu cầu</li>
        <li>Dễ dàng trở thành người bán</li>
        <li>Giao diện thân thiện, dễ sử dụng</li>
        <li>Hệ thống hỗ trợ người dùng tận tâm</li>
      </ul>
    </section>

    <!-- Giá trị kinh doanh -->
    <section class="bg-gray-100 py-12 px-4 md:px-20">
      <h2 class="text-2xl font-bold mb-4">Giá trị kinh doanh</h2>
      <p class="text-gray-700 text-lg">
        Passion tạo ra giá trị bền vững bằng cách kết nối thị trường, giảm rào cản khởi nghiệp và tối ưu trải nghiệm người dùng.
      </p>
    </section>

    <!-- Ngành hàng nổi bật -->
    <section class="py-12 px-4 md:px-20">
      <h2 class="text-2xl font-bold mb-4">Ngành hàng nổi bật</h2>
      <div class="grid md:grid-cols-3 gap-6 text-gray-700 text-lg">
        <div class="bg-white shadow-lg rounded-xl p-6 border">
          <img src="https://cdn-img.upanhlaylink.com/img/image_20250527f0565522b18541ed5fd33781865eb70b.jpg" alt="Thời trang" class="mb-4 rounded-md" />
          <h3 class="font-semibold text-xl mb-2">Thời trang & Phụ kiện</h3> 
          <p>Đa dạng từ local brand đến quốc tế.</p>
        </div>
        <div class="bg-white shadow-lg rounded-xl p-6 border">
          <img src="https://cdn-img.upanhlaylink.com/img/image_20250527047ea929e69a1039186215c3439f5d8c.jpg" alt="Điện tử" class="mb-4 rounded-md" />
          <h3 class="font-semibold text-xl mb-2">Điện tử & Công nghệ</h3>
          <p>Sản phẩm chính hãng, cập nhật liên tục.</p>
        </div>
        <div class="bg-white shadow-lg rounded-xl p-6 border">
          <img src="https://cdn-img.upanhlaylink.com/img/image_20250527e196e04165191fcf91257649fd93e1b5.jpg"  alt="Mỹ phẩm" class="mb-4 rounded-md" />
          <h3 class="font-semibold text-xl mb-2">Mỹ phẩm & Làm đẹp</h3>
          <p>Chất lượng cao, giá cả cạnh tranh.</p>
        </div>
      </div>
    </section>

    <!-- Trở thành người bán -->
    <section class="bg-yellow-50 py-12 px-4 md:px-20">
      <div class="grid md:grid-cols-2 gap-10 items-center">
        <div>
          <h2 class="text-2xl font-bold mb-4">Trở thành người bán trên Passion</h2>
          <p class="text-gray-700 text-lg mb-6">
            Với chỉ vài bước đơn giản, bạn có thể mở gian hàng, đăng sản phẩm và bắt đầu kinh doanh ngay hôm nay.
          </p>
          <NuxtLink to="" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-6 rounded-lg transition">
            Đăng ký ngay
          </NuxtLink>
        </div>
        <img src="https://cdn-img.upanhlaylink.com/img/image_20250527510475fcab156211ddae7d0b9a5546d6.jpg" alt="Trở thành người bán" class="rounded-xl shadow-md" />
      </div>
    </section>

    <!-- Hành trình phát triển -->
    <section class="bg-gray-100 py-12 px-4 md:px-20">
      <h2 class="text-2xl font-bold mb-4">Hành trình phát triển (2023 - 2025)</h2>
      <div class="space-y-4 text-gray-700 text-lg">
        <div><strong>2023:</strong> Ra mắt nền tảng với 1000 sản phẩm.</div>
        <div><strong>2024:</strong> Mở rộng cộng đồng người bán, vượt mốc 50.000 seller.</div>
        <div><strong>2025:</strong> Ứng dụng AI vào quản lý và mở rộng ra thị trường Đông Nam Á.</div>
      </div>
    </section>

  </main>
</template>

<script setup>
import { useHead } from '#imports'

useHead({
  title: 'Giới thiệu Passion',
  meta: [
    { name: 'description', content: 'Liên hệ với chúng tôi để được hỗ trợ nhanh chóng và hiệu quả. Passion luôn sẵn sàng giúp đỡ bạn.' }
  ]
})
</script>

<style scoped>
/* Thêm hiệu ứng nhẹ nếu cần */
</style>
