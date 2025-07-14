<template>

  <div class="bg-[#f5f5f5] text-[#222222]">
    <div class="max-w-[1200px] mx-auto px-2">
      <!-- Top banner -->
      <div class="flex flex-wrap justify-center md:justify-between gap-4 mb-6">
        <!-- Banner trái - Slide -->
        <div class="flex-1 min-w-[280px] max-w-full relative overflow-hidden rounded-xl shadow-md">
          <div ref="banner" id="bannerSlides" class="flex transition-transform duration-700 ease-in-out">
            <img src="../img1.png" alt="Slide 1" class="w-full flex-shrink-0 object-contain" />
            <img src="../img2.png" alt="Slide 2" class="w-full flex-shrink-0 object-contain" />
            <img src="../img3.png" alt="Slide 3" class="w-full flex-shrink-0 object-contain" />
          </div>
          <div id="dotsNav">
            <button v-for="(_, i) in 3" :key="i" ref="dots" @click="index = i">{{ i }}</button>
          </div>
        </div>

        <!-- Banner phải -->
        <div class="hidden sm:flex flex-col gap-4 min-w-[250px] max-w-[360px]">
          <img src="../img2.png" alt="Tiki small banner 1" class="w-full h-auto object-contain rounded-xl shadow-md" />
          <img src="../img3.png" alt="Tiki small banner 2" class="w-full h-auto object-contain rounded-xl shadow-md" />
        </div>
      </div>

      <!-- Navigation bar -->
      <nav
        class="bg-white border border-t-0 border-b-gray-300 flex justify-around text-[10px] sm:text-[12px] md:text-[14px] py-3 mb-6 shadow-sm">
        <a class="flex flex-col items-center text-[#1BA0E2] font-semibold hover:text-[#1BA0E2] transition duration-200"
          href="#">
          <i class="fas fa-home text-[18px] sm:text-[22px]"></i>
          <span class="mt-1">Mới Cập Nhật</span>
        </a>
        <a class="flex flex-col items-center text-gray-600 hover:text-[#1BA0E2] transition duration-200" href="#">
          <i class="fas fa-truck text-[18px] sm:text-[22px]"></i>
          <span class="mt-1">Vận Chuyển</span>
        </a>
        <a class="flex flex-col items-center text-gray-600 hover:text-[#1BA0E2] transition duration-200" href="#">
          <i class="fas fa-tags text-[18px] sm:text-[22px]"></i>
          <span class="mt-1">Ưu Đãi</span>
        </a>
        <a class="flex flex-col items-center text-gray-600 hover:text-[#1BA0E2] transition duration-200" href="#">
          <i class="fas fa-star text-[18px] sm:text-[22px]"></i>
          <span class="mt-1">Shop Yêu Thích</span>
        </a>
        <a class="flex flex-col items-center text-gray-600 hover:text-[#1BA0E2] transition duration-200" href="#">
          <i class="fas fa-gift text-[18px] sm:text-[22px]"></i>
          <span class="mt-1">Quà Tặng</span>
        </a>
      </nav>

      <!-- Danh mục -->
      <div class="max-w-7xl mx-auto mt-6 bg-white">
        <div class="border-b border-gray-200 px-4 py-3">
          <h2 class="text-[#1BA0E2] text-base font-semibold uppercase tracking-wide">DANH MỤC</h2>
        </div>
        <div class="overflow-x-auto scrollbar-hide">
          <div class="min-w-[900px] grid grid-cols-10 gap-x-6 gap-y-4 px-4 py-4">
            <div class="flex flex-col items-center text-center text-[12px] text-gray-800">
              <div class="bg-gray-100 rounded-full p-3 mb-2">
                <img alt="Blue polo shirt on white circular background" class="w-16 h-16 object-contain" height="64"
                  src="https://storage.googleapis.com/a1aa/image/16c912a6-b48b-46e5-19b5-5ce8430c0c6d.jpg" width="64" />
              </div>
              Thời Trang Nam
            </div>
          </div>
        </div>
      </div>
      <!-- Flash Sale Section -->
      <div class="mt-6 bg-white border border-gray-200 rounded-md p-3">
        <div class="flex justify-between items-center mb-3">
          <div class="flex items-center space-x-2">
            <div class="text-[1BA0E2] font-bold text-sm uppercase">
              FLASH SALE
            </div>
            <div class="flex space-x-1 text-xs text-gray-600">
              <i class="fas fa-bolt">
              </i>
              <i class="fas fa-bolt">
              </i>
              <i class="fas fa-bolt">
              </i>
              <i class="fas fa-bolt">
              </i>
            </div>
          </div>
          <a class="text-[1BA0E2] text-xs font-semibold hover:underline" href="#">
            Xem tất cả
          </a>
        </div>
        <div class="grid grid-cols-5 gap-3">
          <div class="text-center text-xs">
            <img alt="Flash sale product 5 image" class="mx-auto mb-1 rounded" height="120"
              src="https://storage.googleapis.com/a1aa/image/b2250288-d350-4848-3dd3-57bb2d5b16f8.jpg" width="120" />
            <div class="text-red-600 font-bold">
              ₫1.867.000
            </div>
            <div class="line-through text-gray-400 text-[10px]">
              ₫2.000.000
            </div>
          </div>
          <div class="text-center text-xs">
            <img alt="Flash sale product 5 image" class="mx-auto mb-1 rounded" height="120"
              src="https://storage.googleapis.com/a1aa/image/b2250288-d350-4848-3dd3-57bb2d5b16f8.jpg" width="120" />
            <div class="text-red-600 font-bold">
              ₫1.867.000
            </div>
            <div class="line-through text-gray-400 text-[10px]">
              ₫2.000.000
            </div>
          </div>
          <div class="text-center text-xs">
            <img alt="Flash sale product 5 image" class="mx-auto mb-1 rounded" height="120"
              src="https://storage.googleapis.com/a1aa/image/b2250288-d350-4848-3dd3-57bb2d5b16f8.jpg" width="120" />
            <div class="text-red-600 font-bold">
              ₫1.867.000
            </div>
            <div class="line-through text-gray-400 text-[10px]">
              ₫2.000.000
            </div>
          </div>
          <div class="text-center text-xs">
            <img alt="Flash sale product 5 image" class="mx-auto mb-1 rounded" height="120"
              src="https://storage.googleapis.com/a1aa/image/b2250288-d350-4848-3dd3-57bb2d5b16f8.jpg" width="120" />
            <div class="text-red-600 font-bold">
              ₫1.867.000
            </div>
            <div class="line-through text-gray-400 text-[10px]">
              ₫2.000.000
            </div>
          </div>
          <div class="text-center text-xs">
            <img alt="Flash sale product 5 image" class="mx-auto mb-1 rounded" height="120"
              src="https://storage.googleapis.com/a1aa/image/b2250288-d350-4848-3dd3-57bb2d5b16f8.jpg" width="120" />
            <div class="text-red-600 font-bold">
              ₫1.867.000
            </div>
            <div class="line-through text-gray-400 text-[10px]">
              ₫2.000.000
            </div>
          </div>
        </div>
      </div>
      <!-- Shopee Mall Section -->
      <div class="max-w-[1280px] mx-auto p-4">
        <!-- Top bar -->
        <div
          class="bg-white flex flex-wrap justify-between gap-2 items-center text-[14px] font-normal text-[#1BA0E2] border border-[#f0f0f0] rounded-sm px-4 py-2">
          <div class="flex flex-wrap items-center gap-4">
            <span class="font-semibold cursor-pointer">PASSION MALL</span>
            <span class="border-l border-gray-300 pl-4 flex items-center gap-1 text-[#333]">
              <i class="fas fa-undo-alt text-[#1BA0E2]"></i>
              <span>Trả Hàng 15 Ngày</span>
            </span>
            <span class="border-l border-gray-300 pl-4 flex items-center gap-1 text-[#333]">
              <i class="fas fa-shield-alt text-[#1BA0E2]"></i>
              <span>Chính Hãng 100%</span>
            </span>
            <span class="border-l border-gray-300 pl-4 flex items-center gap-1 text-[#333]">
              <i class="fas fa-truck text-[#1BA0E2]"></i>
              <span>Miễn Phí Giao</span>
            </span>
          </div>
          <div class="text-[#1BA0E2] flex items-center cursor-pointer">
            <span>Xem Tất Cả</span>
            <i class="fas fa-chevron-right ml-1"></i>
          </div>
        </div>

        <!-- Main content -->
        <div class="bg-white mt-4 rounded-sm flex flex-wrap md:flex-nowrap overflow-x-auto scrollbar-hide">
          <!-- Left big image -->
          <div class="flex-shrink-0 w-full md:w-[400px]">
            <img alt="Banner" class="w-full h-full object-cover rounded-l-sm"
              src="https://storage.googleapis.com/a1aa/image/2485a31c-da6a-487d-95ea-67ac0d005dff.jpg" />
          </div>
          <!-- Right grid -->
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 p-4 flex-grow">
            <div class="flex flex-col items-center space-y-2">
              <img alt="Product" class="w-[80px] h-[120px] object-contain"
                src="https://storage.googleapis.com/a1aa/image/e0c42720-0a5d-49e0-d79f-3ba67292fe0c.jpg" />
              <span class="text-[#1BA0E2] text-sm font-medium">Ưu đãi đến 50%</span>
            </div>
            <div class="flex flex-col items-center space-y-2">
              <img alt="Product" class="w-[80px] h-[120px] object-contain"
                src="https://storage.googleapis.com/a1aa/image/e0c42720-0a5d-49e0-d79f-3ba67292fe0c.jpg" />
              <span class="text-[#1BA0E2] text-sm font-medium">Ưu đãi đến 50%</span>
            </div>
            <div class="flex flex-col items-center space-y-2">
              <img alt="Product" class="w-[80px] h-[120px] object-contain"
                src="https://storage.googleapis.com/a1aa/image/e0c42720-0a5d-49e0-d79f-3ba67292fe0c.jpg" />
              <span class="text-[#1BA0E2] text-sm font-medium">Ưu đãi đến 50%</span>
            </div>
            <div class="flex flex-col items-center space-y-2">
              <img alt="Product" class="w-[80px] h-[120px] object-contain"
                src="https://storage.googleapis.com/a1aa/image/e0c42720-0a5d-49e0-d79f-3ba67292fe0c.jpg" />
              <span class="text-[#1BA0E2] text-sm font-medium">Ưu đãi đến 50%</span>
            </div>
            <div class="flex flex-col items-center space-y-2">
              <img alt="Product" class="w-[80px] h-[120px] object-contain"
                src="https://storage.googleapis.com/a1aa/image/e0c42720-0a5d-49e0-d79f-3ba67292fe0c.jpg" />
              <span class="text-[#1BA0E2] text-sm font-medium">Ưu đãi đến 50%</span>
            </div>
            <div class="flex flex-col items-center space-y-2">
              <img alt="Product" class="w-[80px] h-[120px] object-contain"
                src="https://storage.googleapis.com/a1aa/image/e0c42720-0a5d-49e0-d79f-3ba67292fe0c.jpg" />
              <span class="text-[#1BA0E2] text-sm font-medium">Ưu đãi đến 50%</span>
            </div>
            <div class="flex flex-col items-center space-y-2">
              <img alt="Product" class="w-[80px] h-[120px] object-contain"
                src="https://storage.googleapis.com/a1aa/image/e0c42720-0a5d-49e0-d79f-3ba67292fe0c.jpg" />
              <span class="text-[#1BA0E2] text-sm font-medium">Ưu đãi đến 50%</span>
            </div>
            <div class="flex flex-col items-center space-y-2">
              <img alt="Product" class="w-[80px] h-[120px] object-contain"
                src="https://storage.googleapis.com/a1aa/image/e0c42720-0a5d-49e0-d79f-3ba67292fe0c.jpg" />
              <span class="text-[#1BA0E2] text-sm font-medium">Ưu đãi đến 50%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Best Seller Section -->
      <div class="max-w-[1200px] mx-auto bg-white">
        <div class="flex justify-between items-center px-4 py-3 border-b border-[#f0f0f0]">
          <h2 class="text-[#1BA0E2] text-sm font-semibold uppercase tracking-wide">
            TÌM KIẾM HÀNG ĐẦU
          </h2>
          <a class="text-[#1BA0E2] text-sm font-normal hover:underline flex items-center" href="#">
            Xem Tất Cả
            <i class="fas fa-chevron-right ml-1 text-xs">
            </i>
          </a>
        </div>
        <div class="flex overflow-x-auto scrollbar-hide space-x-4 px-4 py-4">
          <!-- Item 1 -->
          <div class="flex-shrink-0 w-[140px]">
            <div class="relative">
              <img alt="Áo Babydoll Nữ Tay Bèo - woman wearing white shirt and black skirt"
                class="w-full h-[120px] object-cover" height="120"
                src="https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg" width="140" />
              <div
                class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
                TOP
              </div>
              <div
                class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1">
                Bán 105k+ / tháng
              </div>
            </div>
            <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
              Áo Babydoll Nữ Tay Bèo
            </p>
          </div>
          <!-- Item 1 -->
          <div class="flex-shrink-0 w-[140px]">
            <div class="relative">
              <img alt="Áo Babydoll Nữ Tay Bèo - woman wearing white shirt and black skirt"
                class="w-full h-[120px] object-cover" height="120"
                src="https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg" width="140" />
              <div
                class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
                TOP
              </div>
              <div
                class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1">
                Bán 105k+ / tháng
              </div>
            </div>
            <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
              Áo Babydoll Nữ Tay Bèo
            </p>
          </div>
          <!-- Item 1 -->
          <div class="flex-shrink-0 w-[140px]">
            <div class="relative">
              <img alt="Áo Babydoll Nữ Tay Bèo - woman wearing white shirt and black skirt"
                class="w-full h-[120px] object-cover" height="120"
                src="https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg" width="140" />
              <div
                class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
                TOP
              </div>
              <div
                class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1">
                Bán 105k+ / tháng
              </div>
            </div>
            <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
              Áo Babydoll Nữ Tay Bèo
            </p>
          </div>
          <!-- Item 1 -->
          <div class="flex-shrink-0 w-[140px]">
            <div class="relative">
              <img alt="Áo Babydoll Nữ Tay Bèo - woman wearing white shirt and black skirt"
                class="w-full h-[120px] object-cover" height="120"
                src="https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg" width="140" />
              <div
                class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
                TOP
              </div>
              <div
                class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1">
                Bán 105k+ / tháng
              </div>
            </div>
            <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
              Áo Babydoll Nữ Tay Bèo
            </p>
          </div>
          <!-- Item 1 -->
          <div class="flex-shrink-0 w-[140px]">
            <div class="relative">
              <img alt="Áo Babydoll Nữ Tay Bèo - woman wearing white shirt and black skirt"
                class="w-full h-[120px] object-cover" height="120"
                src="https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg" width="140" />
              <div
                class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
                TOP
              </div>
              <div
                class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1">
                Bán 105k+ / tháng
              </div>
            </div>
            <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
              Áo Babydoll Nữ Tay Bèo
            </p>
          </div>
          <!-- Item 1 -->
          <div class="flex-shrink-0 w-[140px]">
            <div class="relative">
              <img alt="Áo Babydoll Nữ Tay Bèo - woman wearing white shirt and black skirt"
                class="w-full h-[120px] object-cover" height="120"
                src="https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg" width="140" />
              <div
                class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
                TOP
              </div>
              <div
                class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1">
                Bán 105k+ / tháng
              </div>
            </div>
            <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
              Áo Babydoll Nữ Tay Bèo
            </p>
          </div>
          <!-- Item 1 -->
          <div class="flex-shrink-0 w-[140px]">
            <div class="relative">
              <img alt="Áo Babydoll Nữ Tay Bèo - woman wearing white shirt and black skirt"
                class="w-full h-[120px] object-cover" height="120"
                src="https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg" width="140" />
              <div
                class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
                TOP
              </div>
              <div
                class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1">
                Bán 105k+ / tháng
              </div>
            </div>
            <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
              Áo Babydoll Nữ Tay Bèo
            </p>
          </div>
          <!-- Item 1 -->
          <div class="flex-shrink-0 w-[140px]">
            <div class="relative">
              <img alt="Áo Babydoll Nữ Tay Bèo - woman wearing white shirt and black skirt"
                class="w-full h-[120px] object-cover" height="120"
                src="https://storage.googleapis.com/a1aa/image/e72371a1-577a-4ed0-ba24-e6463b69f8cc.jpg" width="140" />
              <div
                class="absolute top-2 left-2 bg-[#1BA0E2] text-white text-[10px] font-bold px-1.5 py-[1px] uppercase">
                TOP
              </div>
              <div
                class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white text-[11px] font-semibold text-center py-1">
                Bán 105k+ / tháng
              </div>
            </div>
            <p class="mt-2 text-[#333333] text-sm font-normal leading-tight">
              Áo Babydoll Nữ Tay Bèo
            </p>
          </div>
        </div>
        <button aria-label="Next"
          class="absolute right-2 top-1/2 -translate-y-1/2 bg-white border border-gray-300 rounded-full w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900"
          style="z-index:10">
          <i class="fas fa-chevron-right">
          </i>
        </button>
      </div>

      <!-- Gợi ý hôm nay -->
      <div class="mt-6 bg-white border border-gray-200 rounded-md p-3">
        <div class="text-[#1BA0E2] font-bold text-sm uppercase mb-3">GỢI Ý HÔM NAY</div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-xs text-gray-700">
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
          <div class="border border-gray-200 rounded p-1 hover:shadow-md transition-shadow">
            <img alt="Product 8 image" class="w-full rounded mb-1" height="150"
              src="https://storage.googleapis.com/a1aa/image/3d613f75-5771-47a6-554b-c13cccb12e6f.jpg" width="150" />
            <div class="font-semibold text-red-600 text-sm truncate">₫130.000</div>
            <div class="line-through text-gray-400 text-[10px] truncate">₫150.000</div>
            <div class="text-[10px] text-gray-500 truncate">Đã bán 40+</div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <div class="text-center text-gray-500 text-xs py-6 hover:underline">
        Xem Thêm
      </div>

    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';

const banner = ref(null);
const dots = ref([]);
let index = 0;

onMounted(() => {
  const total = banner.value.children.length;

  function updateSlide() {
    banner.value.style.transform = `translateX(-${index * 100}%)`;
    dots.value.forEach((dot, i) => {
      if (i === index) {
        dot.classList.add("bg-[#1BA0E2]", "opacity-100");
        dot.classList.remove("bg-white", "opacity-70");
      } else {
        dot.classList.remove("bg-[#1BA0E2]", "opacity-100");
        dot.classList.add("bg-white", "opacity-70");
      }
    });
  }

  setInterval(() => {
    index = (index + 1) % total;
    updateSlide();
  }, 5000);

  updateSlide();
});
</script>
