<template>
  <div class="max-w-5xl mx-auto px-4 py-12 mt-4 border border-dashed border-blue-400">
    <h2 class="text-3xl font-bold text-center mb-2">LIÊN HỆ</h2>
    <p class="text-center text-gray-600 mb-8">Gửi thông tin liên hệ hoặc yêu cầu hỗ trợ tại đây.</p>

    <!-- Form và Map -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Form -->
      <form class="space-y-4">
        <input type="text" placeholder="Tên của bạn" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400" />
        <input type="email" placeholder="Email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400" />
        <textarea placeholder="Nội dung" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400"></textarea>
        <button type="submit" class="w-full bg-[#1BA0E2] text-white py-2 rounded-md bg-[#1BA0E3] transition">Gửi</button>
      </form>

      <!-- Google Map -->
      <div>
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.676602582042!2d108.02670031533484!3d12.64770939087733!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31721de8a67aa0a9%3A0xefe0d76be63dab69!2zMTY5IFkgV2FuZywgRWEgVGFtLCBCdW9uIE1hIFRoxrDhu51uZywgxJDhuq9rIEzhuq9rIDYzMTE4LCBWaeG7h3QgTmFt!5e0!3m2!1sen!2s!4v1683627361234!5m2!1sen!2s"
          width="100%" height="320" style="border:0;"  loading="lazy"
          referrerpolicy="no-referrer-when-downgrade" class="rounded-md w-full h-full">
        </iframe>
      </div>
    </div>

    <!-- Contact Info -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-white bg-[#1BA0E2] rounded-md mt-10 p-4 text-sm text-center">
      <div class="flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 8h2a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9a2 2 0 012-2h2m2-4h6a2 2 0 012 2v4H7V6a2 2 0 012-2z" />
        </svg>
        Chat trực tuyến
      </div>
      <div class="flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 12a4 4 0 01-8 0m8 0a4 4 0 00-8 0m0 0v1m0-2V9a4 4 0 018 0v1" />
        </svg>
        Trogiup@Pasion.com
      </div>
      <div class="flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 5h2l.4 2M7 13h10l1.5-6H6.4M7 13L6 21h12l-1-8M7 13l.4-2M10 17h4" />
        </svg>
        0942960548
      </div>
    </div>

    <!-- Footer Links -->
    <div class="text-sm text-center text-gray-500 py-6 mt-4 space-x-4">
      <a href="#" class="hover:underline">Trung tâm trợ giúp</a>
      <a href="#" class="hover:underline">Tôi là người bán</a>
      <a href="#" class="hover:underline">Tôi là người mua</a>
    </div>
  </div>
</template>
