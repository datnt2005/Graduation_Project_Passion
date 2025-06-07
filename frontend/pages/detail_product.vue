<template>

    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
            integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>

    <body class="bg-white text-gray-900">
        <main class="max-w-7xl mx-auto p-4 sm:p-6 md:p-8">
            <!-- Top product section -->
            <section class="bg-white border border-gray-200 rounded-md p-4 md:p-6 mb-8">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Left images -->
                    <div class="flex flex-col items-center md:items-start gap-3 md:w-1/3">
                        <img :src="productImages[currentImage]" :alt="productImagesAlt[currentImage]"
                            class="w-full max-w-[300px] rounded-md border border-gray-300" height="400" width="300" />
                        <div class="flex gap-2 w-full max-w-[300px] overflow-x-auto">
                            <img v-for="(img, idx) in productImages" :key="idx" :src="img" :alt="productImagesAlt[idx]"
                                class="rounded-md border border-gray-300 cursor-pointer" height="60" width="60"
                                :class="{ 'ring-2 ring-red-400': idx === currentImage }" @click="currentImage = idx" />
                        </div>
                    </div>
                    <!-- Right product info -->
                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <h1 class="text-lg font-semibold text-gray-900">
                                Samsung Note20 ultra 5G
                            </h1>
                            <p class="text-sm text-gray-600 mb-1">
                                Galaxy Note 20 Ultra 256 GB 1 tháng
                            </p>
                            <p class="text-2xl font-bold text-red-600 mb-2">
                                5.500.000 ₫
                            </p>
                            <p class="text-xs text-gray-500 mb-2">
                                Rẻ hơn - Có trả góp
                            </p>
                            <!-- Thay thế đoạn flex-wrap bằng flex-col và gap-4 để mỗi nhóm xuống dòng và cách nhau -->
                            <div class="flex flex-col gap-4 mb-4 text-xs text-gray-700">
                                <!-- Ram -->
                                <div>
                                    <span class="font-semibold">Ram:</span>
                                    <button v-for="ram in ramOptions" :key="ram"
                                        class="border border-gray-300 rounded px-2 py-0.5 text-gray-700 mr-2"
                                        :class="selectedRam === ram ? 'bg-blue-500 text-white border-blue-500' : ''"
                                        @click="selectedRam = ram" type="button">
                                        {{ ram }}
                                    </button>
                                </div>
                                <!-- Rom -->
                                <div>
                                    <span class="font-semibold">Rom:</span>
                                    <button v-for="rom in romOptions" :key="rom"
                                        class="border border-gray-300 rounded px-2 py-0.5 text-gray-700 mr-2"
                                        :class="selectedRom === rom ? 'bg-blue-500 text-white border-blue-500' : ''"
                                        @click="selectedRom = rom" type="button">
                                        {{ rom }}
                                    </button>
                                </div>
                                <!-- Color -->
                                <div>
                                    <span class="font-semibold">Color:</span>
                                    <button v-for="color in colorOptions" :key="color"
                                        class="border border-gray-300 rounded px-2 py-0.5 text-gray-700 mr-2"
                                        :class="selectedColor === color ? 'bg-blue-500 text-white border-blue-500' : ''"
                                        @click="selectedColor = color" type="button">
                                        {{ color }}
                                    </button>
                                </div>
                                <!-- Bảo hành -->
                                <div>
                                    <span class="font-semibold">Bảo hành:</span>
                                    <button v-for="warranty in warrantyOptions" :key="warranty"
                                        class="border border-gray-300 rounded px-2 py-0.5 text-gray-700 mr-2"
                                        :class="selectedWarranty === warranty ? 'bg-blue-500 text-white border-blue-500' : ''"
                                        @click="selectedWarranty = warranty" type="button">
                                        {{ warranty }}
                                    </button>
                                </div>
                            </div>
                            <div class="flex gap-4 mb-2">
                                <button
                                    class="text-xs font-semibold text-red-600 border border-red-600 rounded px-4 py-1 bg-white transition hover:bg-red-600 hover:text-white"
                                    type="button">
                                    Mua ngay
                                </button>
                                <button
                                    class="text-xs font-semibold text-red-600 border border-red-600 rounded px-4 py-1 bg-white transition hover:bg-red-600 hover:text-white"
                                    type="button">
                                    Thêm vào giỏ hàng
                                </button>
                            </div>
                            <div class="flex flex-col gap-1 text-xs text-gray-700 mb-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <span class="text-blue-500">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                    <span>
                                        Phường 9, Quận 3, Tp Hồ Chí Minh
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <span class="text-yellow-500">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                    <span>
                                        Cập nhật 5 giờ trước
                                    </span>
                                </label>
                            </div>

                        </div>
                        <div class="flex items-center gap-3 bg-gray-100 p-3 rounded-md">
                            <img alt="Avatar of user Phan Minh Tuấn" class="w-10 h-10 rounded-full" height="40"
                                src="https://storage.googleapis.com/a1aa/image/bbe47371-7ae2-4341-d2fb-d02436f6367e.jpg"
                                width="40" />
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">
                                    Phan Minh Tuấn
                                </p>
                                <p class="text-xs text-gray-600">
                                    Đã đăng bán 6 sản phẩm - Đánh giá 4.8/5
                                </p>
                            </div>
                            <div class="text-yellow-400 font-semibold text-lg">
                                4.8
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Description and details -->
            <section class="mb-8">
                <h2 class="text-sm font-semibold mb-2">
                    Mô tả chi tiết
                </h2>
                <p class="text-xs text-gray-700 mb-4 leading-relaxed" :class="{ 'line-clamp-2': isCollapsed }"
                    v-show="!isCollapsed || showFull">
                    Samsung note20 ultra 5G ram 12G ổ cứng 256 G. Máy đầy đủ tính năng. Ngoại hình đẹp 99%. Màn chính
                    kim. Giá chỉ 5tr6, với các trạng thái như mới, chưa trầy xước, chưa tróc sơn, vân tay.
                    <br />
                    Xem máy tại 55 D1 Tô Quang Bửu, Phường 15, Quận 8, Hồ Chí Minh.
                    <br />
                    Có ship COD toàn quốc, được kiểm tra, trả nghiệm trước khi nhận hàng.
                    <br />
                    Máy được test, bảo hành theo quy định.
                    <br />
                    Thanh toán: tiền mặt, chuyển khoản, trả góp lãi suất 0%.
                </p>
                <button
                    class="text-xs text-gray-700 border border-gray-300 rounded px-3 py-1 hover:bg-gray-100 transition"
                    type="button" @click="isCollapsed = !isCollapsed">
                    {{ isCollapsed ? 'Xem thêm' : 'Thu gọn' }}
                </button>
            </section>
            <!-- Phone number reveal -->
            <section class="mb-8 text-xs text-gray-700">
                <p class="mb-2 font-semibold">
                    Nhấn để hiển thị số :
                    <span class="font-bold">
                        0374********
                    </span>
                </p>
            </section>
            <!-- Table with two columns -->

            <!-- Button and checkbox -->
            <section class="mb-8 flex items-center justify-between text-xs text-gray-700">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="w-4 h-4" type="checkbox" />
                    <span>
                        Bạn có sản phẩm / dịch vụ tương tự?
                    </span>
                </label>
                <button
                    class="text-orange-500 font-semibold border border-orange-500 rounded px-4 py-1 hover:bg-orange-50 transition"
                    type="button">
                    Đăng Tin
                </button>
            </section>
            <!-- Related products -->
            <section class="mb-8">
                <h3 class="text-center text-sm font-semibold mb-4">
                    Sản Phẩm Liên Quan
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-4xl mx-auto">
                    <div class="text-center text-xs">
                        <img alt="Sony Xperia 5 Mini 64gb 2n đẹp Hỗ trợ 0đ 120x160"
                            class="mx-auto mb-1 rounded border border-gray-300" height="160"
                            src="https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg"
                            width="120" />
                        <p class="text-gray-700">
                            Sony xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ
                        </p>
                        <p class="text-red-600 font-semibold">
                            3.700.000
                        </p>
                    </div>
                    <div class="text-center text-xs">
                        <img alt="Sony Xperia 5 Mini 64gb 2n đẹp Hỗ trợ 0đ 120x160"
                            class="mx-auto mb-1 rounded border border-gray-300" height="160"
                            src="https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg"
                            width="120" />
                        <p class="text-gray-700">
                            Sony xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ
                        </p>
                        <p class="text-red-600 font-semibold">
                            2.700.000
                        </p>
                    </div>
                    <div class="text-center text-xs">
                        <img alt="Sony Xperia 5 Mini 64gb 2n đẹp Hỗ trợ 0đ 120x160"
                            class="mx-auto mb-1 rounded border border-gray-300" height="160"
                            src="https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg"
                            width="120" />
                        <p class="text-gray-700">
                            Sony xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ
                        </p>
                        <p class="text-red-600 font-semibold">
                            2.700.000
                        </p>
                    </div>
                    <div class="text-center text-xs">
                        <img alt="Sony Xperia 5 Mini 64gb 2n đẹp Hỗ trợ 0đ 120x160"
                            class="mx-auto mb-1 rounded border border-gray-300" height="160"
                            src="https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg"
                            width="120" />
                        <p class="text-gray-700">
                            Sony xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ
                        </p>
                        <p class="text-red-600 font-semibold">
                            2.700.000
                        </p>
                    </div>
                </div>
                <p class="text-xs text-right text-blue-600 mt-2 cursor-pointer">
                    Xem Tất Cả
                </p>
            </section>
            <!-- Customer reviews -->
            <section class="mb-8 max-w-4xl mx-auto">
                <h3 class="text-sm font-semibold mb-4">
                    Khách hàng đánh giá
                </h3>
                <form action="/api/ratings" method="POST" class="flex flex-col sm:flex-row gap-4 mb-4" id="ratingForm">
                    <!-- Nếu dùng Laravel Blade -->
                    <!-- @csrf -->

                    <div class="flex-1 text-xs">
                        <p class="font-semibold mb-1">Tổng quan</p>
                        <div class="flex items-center gap-1 mb-1">
                            <span class="text-yellow-400 font-bold text-lg">4.5</span>
                            <span class="text-yellow-400 text-lg">★★★★★</span>
                        </div>
                        <p class="text-gray-500 mb-2">(5 đánh giá)</p>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2"><span>5</span>
                                <div class="w-full bg-gray-200 rounded h-2">
                                    <div class="bg-yellow-400 h-2 rounded" style="width: 20%"></div>
                                </div><span>1</span>
                            </div>
                            <div class="flex items-center gap-2"><span>4</span>
                                <div class="w-full bg-gray-200 rounded h-2">
                                    <div class="bg-yellow-400 h-2 rounded" style="width: 15%"></div>
                                </div><span>1</span>
                            </div>
                            <div class="flex items-center gap-2"><span>3</span>
                                <div class="w-full bg-gray-200 rounded h-2">
                                    <div class="bg-yellow-400 h-2 rounded" style="width: 10%"></div>
                                </div><span>1</span>
                            </div>
                            <div class="flex items-center gap-2"><span>2</span>
                                <div class="w-full bg-gray-200 rounded h-2">
                                    <div class="bg-yellow-400 h-2 rounded" style="width: 5%"></div>
                                </div><span>0</span>
                            </div>
                            <div class="flex items-center gap-2"><span>1</span>
                                <div class="w-full bg-gray-200 rounded h-2">
                                    <div class="bg-yellow-400 h-2 rounded" style="width: 0%"></div>
                                </div><span>0</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 text-xs">
                        <p class="font-semibold mb-1">Bình luận - xem đánh giá ★★★★★</p>

                        <!-- Chọn sao bằng icon -->
                        <div class="flex items-center gap-1 mb-2">
                            <label class="text-gray-700 font-medium">Chọn sao:</label>
                            <div id="starRating" class="flex cursor-pointer text-xl text-gray-300">
                                <!-- Các sao -->
                                <span data-value="1" class="star">★</span>
                                <span data-value="2" class="star">★</span>
                                <span data-value="3" class="star">★</span>
                                <span data-value="4" class="star">★</span>
                                <span data-value="5" class="star">★</span>
                            </div>
                        </div>

                        <!-- Input ẩn lưu số sao -->
                        <input type="hidden" name="rating" id="ratingInput" required>

                        <!-- Nội dung bình luận -->
                        <textarea name="comment" class="w-full border border-gray-300 rounded p-2 resize-none" rows="6"
                            placeholder="Nhập nội dung đánh giá..." required></textarea>

                        <!-- Input ẩn để truyền product_id và user_id -->
                        <input type="hidden" name="product_id" value="123">
                        <input type="hidden" name="user_id" value="456">

                        <button type="submit"
                            class="mt-2 px-3 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100 transition">
                            Gửi
                        </button>
                    </div>
                </form>
                
                <!-- Filter buttons -->
                <div class="flex flex-wrap gap-2 mb-4 text-xs">
                    <button class="border border-gray-300 rounded px-3 py-1 hover:bg-gray-100 transition">
                        Mới nhất
                    </button>
                    <button class="border border-gray-300 rounded px-3 py-1 hover:bg-gray-100 transition">
                        Mới nhất
                    </button>
                    <button class="border border-gray-300 rounded px-3 py-1 hover:bg-gray-100 transition">
                        Mới nhất
                    </button>
                    <button class="border border-gray-300 rounded px-3 py-1 hover:bg-gray-100 transition">
                        Mới nhất
                    </button>
                </div>
                <!-- Reviews list -->
                <div class="space-y-4 text-xs text-gray-700">
                    <!-- Review 1 -->
                    <div class="bg-gray-100 rounded p-3">
                        <div class="flex items-center gap-3 mb-2">
                            <img alt="Avatar of Nguyen Anh Tuan" class="w-10 h-10 rounded-full" height="40"
                                src="https://storage.googleapis.com/a1aa/image/34160c1a-ddaa-4f95-98ff-c2598c702304.jpg"
                                width="40" />
                            <div>
                                <p class="font-semibold">
                                    Nguyen Anh Tuan
                                </p>
                                <p>
                                    Đã viết 9 đánh giá
                                </p>
                            </div>
                            <div class="ml-auto flex items-center gap-1 text-yellow-400">
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                            </div>
                        </div>
                        <p class="mb-1 font-semibold">
                            Hài lòng
                        </p>
                        <p class="mb-1 text-green-600 font-semibold">
                            Đã mua hàng
                        </p>
                        <p class="mb-1">
                            Giao hàng rất nhanh, sản phẩm đúng mô tả
                        </p>
                        <div class="flex items-center gap-4 text-gray-500 text-xs">
                            <span>
                                <i class="fas fa-thumbs-up">
                                </i>
                                5 Đã nhận
                            </span>
                            <span>
                                <i class="fas fa-heart">
                                </i>
                                1 Lượt cảm ơn
                            </span>
                        </div>
                    </div>
                    <!-- Review 2 -->
                    <div class="bg-gray-100 rounded p-3">
                        <div class="flex items-center gap-3 mb-2">
                            <img alt="Avatar of Nguyen Anh Tuan" class="w-10 h-10 rounded-full" height="40"
                                src="https://storage.googleapis.com/a1aa/image/34160c1a-ddaa-4f95-98ff-c2598c702304.jpg"
                                width="40" />
                            <div>
                                <p class="font-semibold">
                                    Nguyen Anh Tuan
                                </p>
                                <p>
                                    Đã viết 9 đánh giá
                                </p>
                            </div>
                            <div class="ml-auto flex items-center gap-1 text-yellow-400">
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                            </div>
                        </div>
                        <p class="mb-1 font-semibold">
                            Hài lòng
                        </p>
                        <p class="mb-1 text-green-600 font-semibold">
                            Đã mua hàng
                        </p>
                        <p class="mb-1">
                            Giao hàng rất nhanh, sản phẩm đúng mô tả
                        </p>
                        <div class="flex items-center gap-4 text-gray-500 text-xs">
                            <span>
                                <i class="fas fa-thumbs-up">
                                </i>
                                5 Đã nhận
                            </span>
                            <span>
                                <i class="fas fa-heart">
                                </i>
                                1 Lượt cảm ơn
                            </span>
                        </div>
                    </div>
                    <!-- Review 3 -->
                    <div class="bg-gray-100 rounded p-3">
                        <div class="flex items-center gap-3 mb-2">
                            <img alt="Avatar of Nguyen Anh Tuan" class="w-10 h-10 rounded-full" height="40"
                                src="https://storage.googleapis.com/a1aa/image/34160c1a-ddaa-4f95-98ff-c2598c702304.jpg"
                                width="40" />
                            <div>
                                <p class="font-semibold">
                                    Nguyen Anh Tuan
                                </p>
                                <p>
                                    Đã viết 9 đánh giá
                                </p>
                            </div>
                            <div class="ml-auto flex items-center gap-1 text-yellow-400">
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                            </div>
                        </div>
                        <p class="mb-1 font-semibold">
                            Hài lòng
                        </p>
                        <p class="mb-1 text-green-600 font-semibold">
                            Đã mua hàng
                        </p>
                        <p class="mb-1">
                            Giao hàng rất nhanh, sản phẩm đúng mô tả
                        </p>
                        <div class="flex items-center gap-4 text-gray-500 text-xs">
                            <span>
                                <i class="fas fa-thumbs-up">
                                </i>
                                5 Đã nhận
                            </span>
                            <span>
                                <i class="fas fa-heart">
                                </i>
                                1 Lượt cảm ơn
                            </span>
                        </div>
                    </div>
                    <!-- Review 4 -->
                    <div class="bg-gray-100 rounded p-3">
                        <div class="flex items-center gap-3 mb-2">
                            <img alt="Avatar of Nguyen Anh Tuan" class="w-10 h-10 rounded-full" height="40"
                                src="https://storage.googleapis.com/a1aa/image/34160c1a-ddaa-4f95-98ff-c2598c702304.jpg"
                                width="40" />
                            <div>
                                <p class="font-semibold">
                                    Nguyen Anh Tuan
                                </p>
                                <p>
                                    Đã viết 9 đánh giá
                                </p>
                            </div>
                            <div class="ml-auto flex items-center gap-1 text-yellow-400">
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                                <i class="fas fa-star">
                                </i>
                            </div>
                        </div>
                        <p class="mb-1 font-semibold">
                            Hài lòng
                        </p>
                        <p class="mb-1 text-green-600 font-semibold">
                            Đã mua hàng
                        </p>
                        <p class="mb-1">
                            Giao hàng rất nhanh, sản phẩm đúng mô tả
                        </p>
                        <div class="flex items-center gap-4 text-gray-500 text-xs">
                            <span>
                                <i class="fas fa-thumbs-up">
                                </i>
                                5 Đã nhận
                            </span>
                            <span>
                                <i class="fas fa-heart">
                                </i>
                                1 Lượt cảm ơn
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Pagination -->
                <nav aria-label="Pagination"
                    class="mt-6 flex justify-center items-center gap-3 text-xs text-gray-700 select-none">
                    <button aria-label="Previous page" class="p-1 rounded hover:bg-gray-200 transition" type="button">
                        <i class="fas fa-chevron-left">
                        </i>
                    </button>
                    <button aria-current="page" class="w-7 h-7 rounded bg-gray-200 text-gray-900 font-semibold"
                        type="button">
                        1
                    </button>
                    <button class="w-7 h-7 rounded hover:bg-gray-200 transition" type="button">
                        2
                    </button>
                    <button class="w-7 h-7 rounded hover:bg-gray-200 transition" type="button">
                        3
                    </button>
                    <button class="w-7 h-7 rounded hover:bg-gray-200 transition" type="button">
                        4
                    </button>
                    <button class="w-7 h-7 rounded hover:bg-gray-200 transition" type="button">
                        5
                    </button>
                </nav>
            </section>
        </main>
    </body>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const ramOptions = ['8GB', '12GB', '16GB']
const romOptions = ['128GB', '256GB', '512GB']
const colorOptions = ['Đen', 'Bạc', 'Vàng']
const warrantyOptions = ['12 tháng', '18 tháng']

const selectedRam = ref(null)
const selectedRom = ref(null)
const selectedColor = ref(null)
const selectedWarranty = ref(null)

const productImages = [
    'https://storage.googleapis.com/a1aa/image/7ad18199-6b36-4171-e823-9cec3d8bde9f.jpg',
    'https://storage.googleapis.com/a1aa/image/c63c6593-7a16-4b2b-b1ea-ec7d1b11003e.jpg',
    'https://storage.googleapis.com/a1aa/image/8eaf1eab-cd53-4bff-45c8-c143d44e1c15.jpg',
    'https://storage.googleapis.com/a1aa/image/3dbab879-76d8-4e84-9737-f049ded0520f.jpg',
    'https://storage.googleapis.com/a1aa/image/4b9472fd-7cf0-4bc4-cb5b-57b24d34bf33.jpg'
]
const productImagesAlt = [
    "Samsung Note20 Ultra 5G smartphone front view with rose gold color and stylus pen",
    "Side view 1 of Samsung Note20 Ultra 5G smartphone in rose gold",
    "Side view 2 of Samsung Note20 Ultra 5G smartphone in rose gold",
    "Side view 3 of Samsung Note20 Ultra 5G smartphone in rose gold",
    "Side view 4 of Samsung Note20 Ultra 5G smartphone in rose gold"
]
const currentImage = ref(0)
let intervalId = null

const isCollapsed = ref(true)

onMounted(() => {
    intervalId = setInterval(() => {
        currentImage.value = (currentImage.value + 1) % productImages.length
    }, 3000)
})

onBeforeUnmount(() => {
    clearInterval(intervalId)
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
<script>
    const stars = document.querySelectorAll("#starRating .star");
    const ratingInput = document.getElementById("ratingInput");

    stars.forEach(star => {
        star.addEventListener("click", () => {
            const rating = parseInt(star.dataset.value);
            ratingInput.value = rating;

            // Tô màu các sao đã chọn
            stars.forEach(s => {
                s.classList.toggle("text-yellow-400", parseInt(s.dataset.value) <= rating);
                s.classList.toggle("text-gray-300", parseInt(s.dataset.value) > rating);
            });
        });
    });
</script>
<style>
    #starRating .star:hover,
    #starRating .star:hover ~ .star {
        color: #facc15; /* màu vàng nhạt khi hover */
    }
</style>

