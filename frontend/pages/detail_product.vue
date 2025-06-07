<template>
  <main class="max-w-7xl mx-auto p-4 sm:p-6 md:p-8">
    <!-- Top product section -->
    <section class="bg-white border border-gray-200 rounded-md p-4 md:p-6 mb-8">
      <div class="flex flex-col md:flex-row gap-6">
        <!-- Product Image Gallery -->
        <ProductImageGallery
          :images="productImages"
          :alts="productImagesAlt"
          v-model:current-image="currentImage"
        />

        <!-- Product Info -->
        <div class="flex-1 flex flex-col justify-between">
          <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ product.name }}</h1>
            <p class="text-sm text-gray-600 mb-1">{{ product.description }}</p>
            <!-- Changed text-red-600 to text-gray-900 for price if red is unwanted -->
            <p class="text-2xl font-bold text-gray-900 mb-2">{{ product.price }} ₫</p>
            <p class="text-xs text-gray-500 mb-2">Rẻ hơn - Có trả góp</p>

            <!-- Product Options -->
            <ProductOptions
              :options="options"
              v-model:selected="selectedOptions"
            />

            <!-- Action Buttons -->
            <div class="flex gap-4 mb-2">
              <!-- Changed text-red-600 to text-blue-600 for buttons -->
              <button
                class="text-xs font-semibold text-blue-600 border border-blue-600 rounded px-4 py-1 bg-white transition hover:bg-blue-600 hover:text-white"
                type="button"
                aria-label="Mua ngay sản phẩm"
              >
                Mua ngay
              </button>
              <NuxtLink to="/cart"
                class="text-xs font-semibold text-blue-600 border border-blue-600 rounded px-4 py-1 bg-white transition hover:bg-blue-600 hover:text-white"
                type="button"
                aria-label="Thêm sản phẩm vào giỏ hàng"
              >
                Thêm vào giỏ hàng
              </NuxtLink>
              <button
                class="text-xs font-semibold text-pink-600 border border-pink-600 rounded px-4 py-1 bg-white transition hover:bg-pink-600 hover:text-white flex items-center gap-1"
                type="button"
                aria-label="Yêu thích sản phẩm"
                @click="toggleFavorite"
              >
                <i :class="isFavorite ? 'fas fa-heart' : 'far fa-heart'"></i>
                <span>{{ isFavorite ? 'Đã yêu thích' : 'Yêu thích' }}</span>
              </button>
            </div>

            <!-- Location and Update Time -->
            <div class="flex flex-col gap-1 text-xs text-gray-700 mb-2">
              <div class="flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-blue-500"></i>
                <span>{{ product.location }}</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="fas fa-clock text-yellow-500"></i>
                <span>Cập nhật {{ product.updatedAt }}</span>
              </div>
            </div>
          </div>

          <!-- Seller Info -->
          <div class="flex items-center gap-3 bg-gray-100 p-3 rounded-md">
            <img
              :alt="seller.alt"
              :src="seller.avatar"
              class="w-10 h-10 rounded-full"
              height="40"
              width="40"
              loading="lazy"
            />
            <div class="flex-1">
              <p class="text-sm font-semibold text-gray-900">{{ seller.name }}</p>
              <p class="text-xs text-gray-600">{{ seller.stats }}</p>
            </div>
            <div class="text-yellow-400 font-semibold text-lg">{{ seller.rating }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Description -->
    <section class="mb-8">
      <h2 class="text-[20px] font-semibold mb-2">Mô tả chi tiết</h2>
      <p
        class="text-[16px] text-gray-700 mb-4 leading-relaxed"
        :class="{ 'line-clamp-2': isCollapsed }"
      >
        {{ product.fullDescription }}
      </p>
      <button
        class="text-xs text-gray-700 border border-gray-300 rounded px-3 py-1 hover:bg-gray-100 transition"
        type="button"
        @click="isCollapsed = !isCollapsed"
        :aria-expanded="!isCollapsed"
      >
        {{ isCollapsed ? 'Xem thêm' : 'Thu gọn' }}
      </button>
    </section>

    <!-- Phone Number -->
    <section class="mb-8 text-xs text-gray-700">
      <p class="mb-2 font-semibold">
        Nhấn để hiển thị số: <span class="font-bold">{{ product.phone }}</span>
      </p>
    </section>

    <!-- Related Products -->
    <section class="w-full mb-12 py-6 bg-gray-50">
      <h3 class="text-center text-2xl font-bold text-gray-800 mb-6 tracking-wide">
        Sản Phẩm Liên Quan
      </h3>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 max-w-6xl mx-auto px-4">
        <RelatedProductItem
          v-for="item in displayProducts"
          :key="item.id"
          :product="item"
        />
      </div>
      <div v-if="relatedProducts.length > 4" class="max-w-6xl mx-auto px-4 mt-6 flex justify-end">
        <button
          class="text-sm text-blue-600 cursor-pointer hover:underline hover:text-blue-800 transition-colors duration-300"
          @click="showAll = !showAll"
          :aria-expanded="showAll"
        >
          {{ showAll ? 'Thu gọn' : 'Xem Tất Cả' }}
        </button>
      </div>
    </section>

    <!-- Customer Reviews -->
    <section class="w-full mb-12 py-6 bg-gray-50">
      <h3 class="text-sm font-semibold mb-4">Khách hàng đánh giá</h3>
      <div class="flex flex-col sm:flex-row gap-4 mb-4">
        <!-- Review Summary -->
        <div class="flex-1 text-xs">
          <p class="font-semibold mb-1">Tổng quan</p>
          <div class="flex items-center gap-1 mb-1">
            <span class="text-yellow-400 font-bold text-lg">{{ reviews.summary.rating }}</span>
            <span class="text-yellow-400 text-lg">★★★★★</span>
          </div>
          <p class="text-gray-500 mb-2">({{ reviews.summary.count }} đánh giá)</p>
          <div class="space-y-1">
            <div
              v-for="rating in reviews.summary.ratings"
              :key="rating.stars"
              class="flex items-center gap-2"
            >
              <span>{{ rating.stars }}</span>
              <div class="w-full bg-gray-200 rounded h-2">
                <div
                  class="bg-yellow-400 h-2 rounded"
                  :style="{ width: rating.percentage + '%' }"
                ></div>
              </div>
              <span>{{ rating.count }}</span>
            </div>
          </div>
        </div>

        <!-- Comment Form -->
        <div class="flex-1 text-xs">
          <p class="font-semibold mb-1">Bình luận - xem đánh giá ★★★★★</p>
          <textarea
            class="w-full border border-gray-300 rounded p-2 resize-none"
            rows="6"
            aria-label="Viết bình luận của bạn"
          ></textarea>
          <button
            class="mt-2 px-3 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100 transition"
            type="button"
            aria-label="Gửi bình luận"
          >
            Gửi
          </button>
        </div>
      </div>

      <!-- Review List -->
      <div class="space-y-4">
       <ReviewItem
          v-for="review in paginatedReviews"
          :key="review.id"
          :review="review"
        />
      </div>

      <!-- Pagination -->
      <nav
        aria-label="Phân trang đánh giá"
        class="mt-6 flex justify-center items-center gap-3 text-xs text-gray-700 select-none"
      >
        <button
          aria-label="Trang trước"
          class="p-1 rounded hover:bg-gray-200 transition"
          type="button"
          :disabled="currentPage === 1"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <button
          v-for="page in totalPages"
          :key="page"
          :aria-current="page === currentPage ? 'page' : undefined"
          class="w-7 h-7 rounded hover:bg-gray-200 transition"
          :class="{ 'bg-gray-200 text-gray-900 font-semibold': page === currentPage }"
          type="button"
          @click="currentPage = page"
        >
          {{ page }}
        </button>
        <button
          aria-label="Trang sau"
          class="p-1 rounded hover:bg-gray-200 transition"
          type="button"
          :disabled="currentPage === totalPages"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </nav>
    </section>
  </main>
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
  'https://storage.googleapis.com/a1aa/image/4b9472fd-7cf0-4bc4-cb5b-57b24d34bf33.jpg',
];

const productImagesAlt = [
  'Samsung Note20 Ultra 5G smartphone front view with rose gold color and stylus pen',
  'Side view 1 of Samsung Note20 Ultra 5G smartphone in rose gold',
  'Side view 2 of Samsung Note20 Ultra 5G smartphone in rose gold',
  'Side view 3 of Samsung Note20 Ultra 5G smartphone in rose gold',
  'Side view 4 of Samsung Note20 Ultra 5G smartphone in rose gold',
];

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

// Reviews
const reviews = {
  summary: {
    rating: 4.5,
    count: 5,
    ratings: [
      { stars: 5, percentage: 20, count: 1 },
      { stars: 4, percentage: 15, count: 1 },
      { stars: 3, percentage: 10, count: 1 },
      { stars: 2, percentage: 5, count: 0 },
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
      purchased: true,
      likes: 5,
      thanks: 1,
    },
    {
      id: 2,
      user: 'Nguyen Anh Tuan',
      avatar: 'https://storage.googleapis.com/a1aa/image/34160c1a-ddaa-4f95-98ff-c2598c702304.jpg',
      rating: 5,
      comment: 'Giao hàng rất nhanh, sản phẩm đúng mô tả',
      purchased: true,
      likes: 5,
      thanks: 1,
    },
    {
      id: 3,
      user: 'Nguyen Anh Tuan',
      avatar: 'https://storage.googleapis.com/a1aa/image/34160c1a-ddaa-4f95-98ff-c2598c702304.jpg',
      rating: 5,
      comment: 'Giao hàng rất nhanh, sản phẩm đúng mô tả',
      purchased: true,
      likes: 5,
      thanks: 1,
    },
    {
      id: 4,
      user: 'Nguyen Anh Tuan',
      avatar: 'https://storage.googleapis.com/a1aa/image/34160c1a-ddaa-4f95-98ff-c2598c702304.jpg',
      rating: 5,
      comment: 'Giao hàng rất nhanh, sản phẩm đúng mô tả',
      purchased: true,
      likes: 5,
      thanks: 1,
    },
  ],
};

// Pagination for Reviews
const currentPage = ref(1);
const itemsPerPage = 3;
const totalPages = computed(() => Math.ceil(reviews.list.length / itemsPerPage));
const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return reviews.list.slice(start, end);
});

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
    currentImage.value = (currentImage.value + 1) % productImages.length;
  }, 3000);
});

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>