<template>
  <main class="max-w-7xl mx-auto p-4 sm:p-6 md:p-8">
    <!-- Top product section -->
    <section class="bg-white border border-gray-200 rounded-md p-4 md:p-6 mb-8">
      <div class="flex flex-col md:flex-row gap-6">
        <!-- Product Image Gallery -->
        <ProductImageGallery :images="productImages" :alts="productImagesAlt" v-model:current-image="currentImage" />

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
                <form @submit.prevent="submitReview" class="flex flex-col sm:flex-row gap-4 mb-4" id="ratingForm">
                    <div class="flex-1 text-xs">
                        <p class="font-semibold mb-1">Tổng quan</p>
                        <div class="flex items-center gap-1 mb-1">
                            <span class="text-yellow-400 font-bold text-lg">{{ averageRating.toFixed(1) }}</span>
                            <span class="text-yellow-400 text-lg">★★★★★</span>
                        </div>
                        <p class="text-gray-500 mb-2">({{ reviews.length }} đánh giá)</p>
                    </div>

                    <div class="flex-1 text-xs">
                        <p class="font-semibold mb-1">Bình luận - xem đánh giá ★★★★★</p>

                        <div class="flex items-center gap-1 mb-2">
                            <label class="text-gray-700 font-medium">Chọn sao:</label>
                            <div class="flex cursor-pointer text-xl text-gray-300">
                                <span v-for="star in 5" :key="star"
                                    :class="{ 'text-yellow-400': star <= (hoverRating || review.rating) }"
                                    @click="review.rating = star" @mouseover="hoverRating = star"
                                    @mouseleave="hoverRating = 0">★</span>
                            </div>
                        </div>

                        <textarea v-model="review.content" class="w-full border border-gray-300 rounded p-2 resize-none"
                            rows="6" placeholder="Nhập nội dung đánh giá..." required></textarea>

                        <button type="submit"
                            class="mt-2 px-3 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100 transition">
                            {{ review.id ? 'Cập nhật' : 'Gửi' }}
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
                <div class="reviews-list">
                    <div v-for="r in reviews" :key="r.id"
                        class="review-item border p-3 rounded mb-3 flex items-start gap-3 relative">
                        <!-- Avatar cố định -->
                        <img src="https://jbagy.me/wp-content/uploads/2025/03/hinh-anh-cute-avatar-vo-tri-3.jpg"
                            alt="avatar" class="w-10 h-10 rounded-full object-cover border border-gray-300">

                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <strong>{{ r.user?.name || 'Người dùng ẩn danh' }}</strong>
                                    <div class="text-sm">
                                        <template v-for="star in 5" :key="star">
                                            <span
                                                :class="star <= r.rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                                        </template>
                                        ({{ r.rating }})
                                    </div>
                                </div>

                                <!-- Dropdown 3 chấm -->
                                <div class="relative" @click="r.showMenu = !r.showMenu">
                                    <button class="text-gray-500 hover:text-gray-700">⋮</button>
                                    <div v-if="r.showMenu"
                                        class="absolute right-0 top-6 bg-white border rounded shadow-md z-10 text-sm w-24">
                                        <button @click="editReview(r); r.showMenu = false"
                                            class="block w-full text-left px-3 py-2 hover:bg-gray-100 text-blue-600">Sửa</button>
                                        <button @click="deleteReview(r.id); r.showMenu = false"
                                            class="block w-full text-left px-3 py-2 hover:bg-gray-100 text-red-600">Xóa</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Nội dung đánh giá -->
                            <p class="mt-2 text-gray-700">{{ r.content }}</p>
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
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import ProductImageGallery from '../components/shared/ProductImageGallery.vue';
import ProductOptions from '../components/shared/ProductOptions.vue';
import RelatedProductItem from '../components/shared/RelatedProductItem.vue';
import ReviewItem from '../components/shared/ReviewItem.vue';

// Product Data
const product = {
  name: 'Samsung Note20 Ultra 5G',
  description: 'Galaxy Note 20 Ultra 256 GB 1 tháng',
  price: '5.500.000',
  location: 'Phường 9, Quận 3, Tp Hồ Chí Minh',
  updatedAt: '5 giờ trước',
  phone: '0374********',
  fullDescription: `
    Samsung note20 ultra 5G ram 12G ổ cứng 256 G. Máy đầy đủ tính năng. Ngoại hình đẹp 99%. Màn chính kim. Giá chỉ 5tr6, với các trạng thái như mới, chưa trầy xước, chưa tróc sơn, vân tay.
    Xem máy tại 55 D1 Tô Quang Bửu, Phường 15, Quận 8, Hồ Chí Minh.
    Có ship COD toàn quốc, được kiểm tra, trả nghiệm trước khi nhận hàng.
    Máy được test, bảo hành theo quy định.
    Thanh toán: tiền mặt, chuyển khoản, trả góp lãi suất 0%.
  `,
};

// Seller Data
const seller = {
  name: 'Phan Minh Tuấn',
  stats: 'Đã đăng bán 6 sản phẩm - Đánh giá 4.8/5',
  rating: 4.8,
  avatar: 'https://storage.googleapis.com/a1aa/image/bbe47371-7ae2-4341-d2fb-d02436f6367e.jpg',
  alt: 'Avatar of user Phan Minh Tuấn',
};

// Options
const options = {
  ram: ['8GB', '12GB', '16GB'],
  rom: ['128GB', '256GB', '512GB'],
  color: ['Đen', 'Bạc', 'Vàng'],
  warranty: ['12 tháng', '18 tháng'],
};

const selectedOptions = ref({
  ram: null,
  rom: null,
  color: null,
  warranty: null,
});

// Images
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

const currentImage = ref(0);
const isCollapsed = ref(true);

// Related Products
const relatedProducts = [
  { id: 1, name: 'Sony Xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ', price: '3.700.000', image: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg' },
  { id: 2, name: 'Sony Xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ', price: '2.700.000', image: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg' },
  { id: 3, name: 'Sony Xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ', price: '2.700.000', image: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg' },
  { id: 4, name: 'Sony Xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ', price: '2.700.000', image: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg' },
  { id: 5, name: 'Sony Xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ', price: '2.700.000', image: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg' },
  { id: 6, name: 'Sony Xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ', price: '2.700.000', image: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg' },
  { id: 7, name: 'Sony Xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ', price: '2.700.000', image: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg' },
  { id: 8, name: 'Sony Xperia 5 Mini 64gb 2n đẹp - Hỗ trợ 0đ', price: '2.700.000', image: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg' },
];

const showAll = ref(false);
const displayProducts = computed(() => {
  return showAll.value ? relatedProducts : relatedProducts.slice(0, 4);
});

const reviews = {
  summary: {
    rating: 4.5,
    count: 4,
    ratings: [
      { stars: 5, percentage: 80, count: 3 },
      { stars: 4, percentage: 20, count: 1 },
      { stars: 3, percentage: 0, count: 0 },
      { stars: 2, percentage: 0, count: 0 },
      { stars: 1, percentage: 0, count: 0 },
    ],
  },
  list: [
    {
      id: 1,
      user: 'Nguyen Anh Tuan',
      avatar: 'https://storage.googleapis.com/a1aa/image/34160c1a-ddaa-4f95-98ff-c2598c702304.jpg',
      rating: 5,
      comment: 'Giao hàng rất nhanh, sản phẩm đúng mô tả',
      reply: "Cảm ơn bạn đã mua sản phẩm",
      purchased: true,
      likes: 5,
      images: [
        'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg',
        'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg'
      ],
      color: 'Đỏ',
      usageTime: '2 tuần',
      date: '05/06/2025',
      joined: '01/2024',
      totalReviews: 3
    },
    {
      id: 2,
      user: 'Le Thi Hoa',
      avatar: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg',
      rating: 4,
      comment: 'Ổn áp nhưng giao hàng hơi chậm.',
      purchased: true,
      likes: 3,
      images: [],
      color: 'Xanh',
      usageTime: '1 tháng',
      date: '03/06/2025',
      joined: '09/2023',
      totalReviews: 5
    },
    {
      id: 3,
      user: 'Tran Van B',
      avatar: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg',
      rating: 5,
      comment: 'Rất ưng ý, sẽ ủng hộ thêm!',
      purchased: true,
      likes: 7,
      images: ['https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg'],
      color: 'Vàng',
      usageTime: '3 ngày',
      date: '01/06/2025',
      joined: '05/2024',
      totalReviews: 1
    },
    {
      id: 4,
      user: 'Pham Minh Chau',
      avatar: 'https://storage.googleapis.com/a1aa/image/9610552e-6c3d-49fd-7ecc-045dcdc2a920.jpg',
      rating: 5,
      comment: 'Shop đóng gói cẩn thận, hàng đẹp.',
      purchased: true,
      likes: 6,
      images: [],
      color: 'Trắng',
      usageTime: '5 ngày',
      date: '31/05/2025',
      joined: '02/2024',
      totalReviews: 2
    }
  ]
};

// Pagination for Reviews
const currentPage = ref(1);
const itemsPerPage = 3;
const totalPages = computed(() =>
  Math.ceil(reviews.list.length / itemsPerPage)
);

const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return reviews.list.slice(start, end);
});

const reviewSection = ref(null);

// Scroll mượt tới vùng đánh giá
const scrollToReview = () => {
  reviewSection.value?.scrollIntoView({ behavior: 'smooth' });
};

// Khi đổi trang, vừa đổi số trang vừa scroll lên
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    scrollToReview();
  }
};

const isFavorite = ref(false);

function toggleFavorite() {
  isFavorite.value = !isFavorite.value;
  const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
  if (isFavorite.value) {
    // Thêm vào danh sách nếu chưa có
    if (!favorites.find(p => p.id === product.id)) {
      favorites.push(product);
    }
  } else {
    // Gỡ bỏ
    const index = favorites.findIndex(p => p.id === product.id);
    if (index > -1) {
      favorites.splice(index, 1);
    }
  }
  localStorage.setItem('favorites', JSON.stringify(favorites));
}

// Image Carousel
let intervalId = null;
onMounted(() => {
    intervalId = setInterval(() => {
        currentImage.value = (currentImage.value + 1) % productImages.length
    }, 3000)
})

onBeforeUnmount(() => {
    clearInterval(intervalId)
})


// Xử lý đánh giá 
const productId = 1
const currentUserId = 3

const hoverRating = ref(0)

const reviews = ref([])
const averageRating = ref(0)

const review = ref({
    id: null,
    product_id: productId,
    user_id: currentUserId,
    rating: 0,
    content: '',
})

function loadReviews() {
    fetch(`http://127.0.0.1:8000/api/reviews?product_id=${productId}`)
        .then(res => res.json())
        .then(data => {
            reviews.value = data
            if (data.length > 0) {
                const total = data.reduce((acc, cur) => acc + cur.rating, 0)
                averageRating.value = total / data.length
            } else {
                averageRating.value = 0
            }
        })
}

function submitReview() {
    if (review.value.rating === 0) {
        alert('Vui lòng chọn số sao đánh giá');
        return;
    }

    const isEdit = !!review.value.id;
    const method = isEdit ? 'PUT' : 'POST';
    const url = isEdit
        ? `http://127.0.0.1:8000/api/reviews/${review.value.id}`
        : `http://127.0.0.1:8000/api/reviews`;

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(review.value),
    })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err });
            }
            return res.json();
        })
        .then(() => {
            loadReviews();
            resetForm();
            alert(isEdit ? 'Cập nhật đánh giá thành công' : 'Gửi đánh giá thành công');
        })
        .catch(err => {
            let errorMessage = 'Lỗi khi gửi đánh giá';
            if (err.errors) {
                errorMessage = Object.values(err.errors).flat().join('\n');
            } else if (err.message) {
                errorMessage = err.message;
            }
            alert(errorMessage);
        });
}



function editReview(r) {
    review.value = { ...r } // gán lại để hiển thị lên form
}

reviews.value = reviews.value.map(r => ({
    ...r,
    showMenu: false
}));


function deleteReview(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) return;

    fetch(`http://127.0.0.1:8000/api/reviews/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: currentUserId })
    })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err });
            }
            return res.json();
        })
        .then(() => {
            loadReviews();
            alert('Xóa đánh giá thành công');
        })
        .catch(err => {
            let errorMessage = err?.message || 'Xóa thất bại';
            if (err?.errors) {
                errorMessage = Object.values(err.errors).flat().join('\n');
            }
            alert(errorMessage);
        });
}




function resetForm() {
    review.value = {
        id: null,
        product_id: productId,
        user_id: currentUserId,
        rating: 0,
        content: '',
    }
}

onMounted(() => {
    loadReviews()
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
#starRating .star:hover~.star {
    color: #facc15;
    /* màu vàng nhạt khi hover */
}
</style>


.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

</style>
