<template>
  <div class="max-w-[600px] mx-auto">
    <!-- HERO + SEARCH -->
    <div class="bg-[#1BA0E2] text-white py-4 px-4 text-center mt-4 rounded-t-md">
      <h4 class="text-2xl md:text-3xl font-semibold mb-4">CHÚNG TÔI CÓ THỂ GIÚP GÌ CHO BẠN</h4>
    </div>

    <!-- NGƯỜI BÁN / NGƯỜI MUA -->
   <div class="grid grid-cols-2 gap-4 my-6 px-4">
  <div class="border p-4 rounded text-center hover:shadow-md transition text-sm">
    <div class="text-2xl mb-1">🏠</div>
    <h3 class="text-base font-semibold mb-1">Tôi là người bán</h3>
    <p class="text-gray-600 text-xs">Mẹo vặt, hướng dẫn giúp bán hàng nhanh chóng và tiện lợi trên Pasion</p>
  </div>
  <div class="border p-4 rounded text-center hover:shadow-md transition text-sm">
    <div class="text-2xl mb-1">🛒</div>
    <h3 class="text-base font-semibold mb-1">Tôi là người mua</h3>
    <p class="text-gray-600 text-xs">Mẹo vặt, hướng dẫn giúp mua hàng nhanh chóng và tiện lợi trên Pasion</p>
  </div>
</div>

    <!-- FAQ -->
    <div class="bg-[#1BA0E2] text-white py-6 px-4 rounded">
  <div class="grid grid-cols-1 gap-4">
    <div>
      <h2 class="text-xl font-bold mb-4">CÂU HỎI THƯỜNG GẶP</h2>
      <ul class="space-y-2 text-sm">
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Tôi cần làm gì để thay đổi thông tin cá nhân (SĐT, Email, ...)?
          </a>
        </li>
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Tôi có thể theo dõi đơn hàng ở đâu?
          </a>
        </li>
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Làm sao để đổi mật khẩu tài khoản?
          </a>
        </li>
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Pasion có chính sách đổi trả không?
          </a>
        </li>
        <li>
          <a href="#" class="block hover:underline hover:text-gray-200 transition">
            › Cách liên hệ với bộ phận hỗ trợ?
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>


    <!-- FORM LIÊN HỆ -->
    <div class="bg-gray-100 py-10 px-4 rounded-b-md mt-4 mb-4">
      <div class="text-center">
        <h2 class="text-2xl font-bold mb-2">Trợ giúp</h2>
        <p class="text-gray-600 mb-6">Gửi thông tin liên hệ hoặc trợ giúp tại đây.</p>
      </div>
      <form class="space-y-4 text-left">
        <input type="text" placeholder="Tên của bạn" class="w-full px-4 py-2 rounded border" />
        <input type="email" placeholder="Email" class="w-full px-4 py-2 rounded border" />
        <input type="text" placeholder="Số điện thoại" class="w-full px-4 py-2 rounded border" />
        <input type="text" placeholder="Chủ đề" class="w-full px-4 py-2 rounded border" />
        <textarea rows="4" placeholder="Nội dung" class="w-full px-4 py-2 rounded border"></textarea>
        <button
          type="submit"
          class="bg-[#1BA0E2] text-white px-6 py-2 rounded hover:bg-blue-600 transition w-full"
        >
          Gửi
        </button>
      </form>
    </div>
  </div>
</template>
