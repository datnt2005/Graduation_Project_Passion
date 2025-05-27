<template>
    <div class="max-w-6xl mx-auto px-4 py-12 border border-blue-400 mt-4 border-dashed ">
        <h2 class="text-3xl font-bold text-center mb-2">LIÊN HỆ</h2>
        <p class="text-center text-gray-600 mb-6">Gửi thông tin liên hệ hoặc yêu cầu hỗ trợ tại đây.</p>

        <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
            <!-- Form -->
            <form class="w-full lg:w-1/2 space-y-4">
            <input type="text" placeholder="Tên của bạn" class="w-full px-4 py-2 border border-gray-300 rounded" />
            <input type="email" placeholder="Email" class="w-full px-4 py-2 border border-gray-300 rounded" />
            <textarea placeholder="Nội dung" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded"></textarea>
            <button type="submit" class="bg-white border border-gray-400 rounded px-6 py-2 hover:bg-gray-100">
                Gửi
            </button>
            </form>

            <!-- Map -->
            <div class="w-full lg:w-1/2 flex justify-center">
            
            <div class="w-full lg:w-1/2 flex justify-center">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.676602582042!2d108.02670031533484!3d12.64770939087733!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31721de8a67aa0a9%3A0xefe0d76be63dab69!2zMTY5IFkgV2FuZywgRWEgVGFtLCBCdW9uIE1hIFRoxrDhu51uZywgxJDhuq9rIEzhuq9rIDYzMTE4LCBWaeG7h3QgTmFt!5e0!3m2!1sen!2s!4v1683627361234!5m2!1sen!2s"
            width="320"
            height="320"
            style="border:0;"
        
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            class="rounded  w-200 h-120 object-cover rounded">
        </iframe>
        </div>

            </div>
        </div>
        </div>

        <!-- Contact Info -->
        <div class="bg-white py-6 flex flex-col md:flex-row items-center justify-center gap-6">
        <button class="flex items-center gap-2 bg-[#1BA0E2] text-white px-4 py-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9a2 2 0 012-2h2m2-4h6a2 2 0 012 2v4H7V6a2 2 0 012-2z" />
            </svg>
            Chat trực tuyến
        </button>

        <div class="flex items-center gap-2 bg-[#1BA0E2] text-white px-4 py-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 01-8 0m8 0a4 4 0 00-8 0m0 0v1m0-2V9a4 4 0 018 0v1" />
            </svg>
            Trogiup@Pasion.com
        </div>

        <div class="flex items-center gap-2 bg-[#1BA0E2] text-white px-4 py-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h2l.4 2M7 13h10l1.5-6H6.4M7 13L6 21h12l-1-8M7 13l.4-2M10 17h4" />
            </svg>
            0942960548
        </div>
        </div>

        <!-- Footer Links -->
        <div class="bg-gray-100 text-center py-4 text-sm text-gray-700 flex flex-col md:flex-row justify-center gap-4 mb-4">
        <a href="#">Trung tâm trợ giúp</a>
        <a href="#">Tôi là người bán</a>
        <a href="#">Tôi là người mua</a>
        </div>



</template>