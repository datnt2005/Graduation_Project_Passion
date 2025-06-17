<template>
    <main class="bg-[#f5f7fa] font-sans text-sm text-[#222222]">
         <div class="max-w-[1200px] mx-auto p-6 space-y-6">
        <!-- Top product section -->
        <section class="bg-white border border-gray-200 rounded-md p-4 md:p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Product Image Gallery -->
                <ProductImageGallery :images="productImages" :alts="productImagesAlt"
                    v-model:current-image="currentImage" />

                <!-- Product Info -->
                <div class="flex-1 space-y-4">
                <!-- Title -->
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-normal leading-tight">
                    {{ product.name }}  
                    </h1>
                    <button @click="toggleFavorite" class="text-red-500 text-xl focus:outline-none">
                    <i :class="[isFavorite ? 'fas' : 'far', 'fa-heart']"></i>
                    </button>
                </div>
                <!-- Rating and report -->
               <div class="flex items-center text-xs text-[#222222] space-x-2">
                <span>{{ product.rating }}</span>
                <div class="flex space-x-0.5 text-[#fdd835]">
                    <i v-for="n in product.stars" :key="n" class="fas fa-star"></i>
                    <i v-for="n in (5 - product.stars)" :key="'empty-' + n" class="far fa-star"></i>
                </div>
                </div>
                <!-- Seller Info -->
                <div class="flex justify-between items-center bg-white border rounded-sm p-4">
                <div class="flex items-center space-x-4">
                    <img src="https://cf.shopee.vn/file/7bdab1db90b84a85844658f0938a34d1_tn" alt="Shop avatar" class="w-16 h-16 rounded-full border" />
                    <div>
                    <h2 class="font-semibold text-lg">Thời Trang Trẻ09</h2>
                    <p class="text-sm text-gray-500">Online 11 Giờ Trước</p>
                    <div class="flex space-x-2 mt-2">
                        
                        <button class="border px-3 py-1 rounded text-sm flex items-center">
                        <i class="fas fa-store mr-1"></i> Xem Shop
                        </button>
                    </div>
                    </div>
                </div>
                <div class="text-right text-sm">
                    <p class="text-gray-500">Đánh Giá</p>
                    <p class="text-red-500 font-semibold">3k</p>
                    <p class="text-gray-500 mt-2">Sản Phẩm</p>
                    <p class="text-red-500 font-semibold">51</p>
                </div>
                </div>

                <!-- Price and discount -->
               <div class="bg-[#fef0ef] p-4 rounded-sm flex items-center space-x-4">
                <div class="text-[#f15a24] text-3xl font-semibold flex items-center space-x-1">
                    <span>₫</span>
                    <span>{{ product.price.toLocaleString() }}</span>
                </div>
                <div class="text-gray-400 line-through text-sm">
                    ₫{{ product.originalPrice.toLocaleString() }}
                </div>
                <div class="text-[#f15a24] text-xs font-semibold bg-[#fddede] rounded px-1 py-0.5">
                    -{{ product.discountPercent }}%
                </div>
                </div>
             
                  <ProductOptions @update:selected="handleSelectedOptions" />
                 
               <div class="flex space-x-4 mt-6">
                <button
                    class="flex items-center justify-center border border-[#0d5cb6] text-[#0d5cb6] bg-white rounded-md w-48 h-11 text-sm font-semibold hover:bg-[#e6f0fb] transition-colors duration-200"
                >
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Thêm Vào Giỏ Hàng
                </button>
                <button
                    class="bg-[#0d5cb6] text-white rounded-md w-48 h-11 text-sm font-semibold hover:bg-[#084d9d] transition-colors duration-200"
                >
                    Mua Ngay
                </button>
                </div>

                </div>
            </div>
        </section>

        <!-- Description -->
        <section class="mb-8">
            <h2 class="text-[20px] font-semibold mb-2">Mô tả chi tiết</h2>
            <p class="text-[16px] text-gray-700 mb-4 leading-relaxed" :class="{ 'line-clamp-2': isCollapsed }">
                {{ product.fullDescription }}
            </p>
            <button class="text-xs text-gray-700 border border-gray-300 rounded px-3 py-1 hover:bg-gray-100 transition"
                type="button" @click="isCollapsed = !isCollapsed" :aria-expanded="!isCollapsed">
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
                <RelatedProductItem v-for="item in displayProducts" :key="item.id" :product="item" />
            </div>
            <div v-if="relatedProducts.length > 4" class="max-w-6xl mx-auto px-4 mt-6 flex justify-end">
                <button
                    class="text-sm text-blue-600 cursor-pointer hover:underline hover:text-blue-800 transition-colors duration-300"
                    @click="showAll = !showAll" :aria-expanded="showAll">
                    {{ showAll ? 'Thu gọn' : 'Xem Tất Cả' }}
                </button>
            </div>
        </section>

        <!-- Customer Reviews -->
<<<<<<< HEAD
        <section ref="reviewSection" class="w-full mb-12 py-6 bg-gray-50">
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
                        <div v-for="rating in reviews.summary.ratings" :key="rating.stars"
                            class="flex items-center gap-2">
                            <span>{{ rating.stars }}</span>
                            <div class="w-full bg-gray-200 rounded h-2">
                                <div class="bg-yellow-400 h-2 rounded" :style="{ width: rating.percentage + '%' }">
                                </div>
                            </div>
                            <span>{{ rating.count }}</span>
                        </div>
                    </div>
                </div>

                <!-- Comment Form -->
                <form @submit.prevent="submitReview" class="flex-1 text-xs">
                    <p class="font-semibold mb-1">
                        {{ editingReviewId ? 'Chỉnh sửa đánh giá của bạn' : 'Bình luận - xem đánh giá' }}
                    </p>

                    <!-- Rating stars -->
                    <div class="flex items-center gap-1 mb-2">
                        <span v-for="star in 5" :key="star" class="cursor-pointer text-yellow-500 text-lg"
                            @click="newReviewRating = star">
                            <span v-if="newReviewRating >= star">★</span>
                            <span v-else class="text-gray-300">☆</span>
                        </span>
                    </div>

                    <!-- Comment textarea -->
                    <textarea v-model="newReviewComment" class="w-full border border-gray-300 rounded p-2 resize-none"
                        rows="6" aria-label="Viết bình luận của bạn" placeholder="Viết cảm nhận của bạn...">
  </textarea>

                    <!-- Submit + Cancel -->
                    <div class="flex items-center gap-2 mt-2">
                        <button class="px-3 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100 transition"
                            type="submit" aria-label="Gửi bình luận">
                            {{ editingReviewId ? 'Cập nhật' : 'Gửi' }}
                        </button>

                        <button v-if="editingReviewId" type="button" class="text-gray-600 text-xs hover:underline"
                            @click="cancelEdit">
                            Huỷ chỉnh sửa
                        </button>
                    </div>
                </form>


            </div>


            <!-- Review List -->
            <transition-group name="fade" tag="div" class="space-y-4">
                <ReviewItem v-for="review in paginatedReviews" :key="review.id" :review="review"
                    @edit-review="editReview" @delete-review="deleteReview" />

            </transition-group>

            <!-- Pagination -->
            <nav aria-label="Phân trang đánh giá"
                class="mt-6 flex justify-center items-center gap-3 text-xs text-gray-700 select-none">
                <button aria-label="Trang trước" class="p-1 rounded hover:bg-gray-200 transition" type="button"
                    :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <!-- Các nút số trang -->
                <button v-for="page in totalPages" :key="page" :aria-current="page === currentPage ? 'page' : undefined"
                    class="w-7 h-7 rounded hover:bg-gray-200 transition"
                    :class="{ 'bg-gray-200 text-gray-900 font-semibold': page === currentPage }" type="button"
                    @click="goToPage(page)">
                    {{ page }}
                </button>

                <!-- Nút "Trang sau" -->
                <button aria-label="Trang sau" class="p-1 rounded hover:bg-gray-200 transition" type="button"
                    :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </nav>
        </section>
    </div>
=======
        <ProductReviews />
>>>>>>> 537c724c34ae742b86897289086712a3b14c6266
    </main>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import ProductImageGallery from '../components/shared/ProductImageGallery.vue';
import ProductOptions from '../components/shared/ProductOptions.vue';
import RelatedProductItem from '../components/shared/products/RelatedProductItem.vue';
import ProductReviews from '../components/shared/ProductReviews.vue';

// Product Data
const product = {
  phone: '0374********',
  name: 'Áo phông nam nữ Premium Cotton BBR dệt họa tiết kẻ sọc caro toàn thân vàng bò/ xanh than Hot 2025',
  rating: 4.1,
  stars: 4,
  price: 1500,
  originalPrice: 15000,
  discountPercent: 90,
  fullDescription: `
Samsung note20 ultra 5G ram 12G ổ cứng 256 G. Máy đầy đủ tính năng. Ngoại hình đẹp 99%. Màn chính kim. Giá chỉ 5tr6, với các trạng thái như mới, chưa trầy xước, chưa tróc sơn, vân tay.
Xem máy tại 55 D1 Tô Quang Bửu, Phường 15, Quận 8, Hồ Chí Minh.
Có ship COD toàn quốc, được kiểm tra, trả nghiệm trước khi nhận hàng.
Máy được test, bảo hành theo quy định.
Thanh toán: tiền mặt, chuyển khoản, trả góp lãi suất 0%.
`
}



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



<<<<<<< HEAD
// Khai báo biến reactive cho rating và comment
const newReviewRating = ref(0);
const newReviewComment = ref('');
const editingReviewId = ref(null);

const editReview = (review) => {
    newReviewRating.value = review.rating;
    newReviewComment.value = review.content;
    editingReviewId.value = review.id;
};

const cancelEdit = () => {
    editingReviewId.value = null;
    newReviewRating.value = 0;
    newReviewComment.value = '';
};


// Hàm submit đánh giá
const submitReview = async () => {
    const token = localStorage.getItem('access_token');
    if (!token) {
        alert("Bạn cần đăng nhập để gửi đánh giá");
        return;
    }

    const payload = {
        rating: newReviewRating.value,
        content: newReviewComment.value
    };

    const url = editingReviewId.value
        ? `http://127.0.0.1:8000/api/reviews/${editingReviewId.value}`
        : `http://127.0.0.1:8000/api/reviews?product_id=1`;

    const method = editingReviewId.value ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${token}`
            },
            body: JSON.stringify(payload)
        });

        if (!res.ok) throw new Error('Lỗi khi gửi dữ liệu');

        alert(editingReviewId.value ? 'Đã cập nhật đánh giá' : 'Đã gửi đánh giá');

        // Reset form
        newReviewRating.value = 0;
        newReviewComment.value = '';
        editingReviewId.value = null;

        // Reload reviews
        await fetchReviews();
    } catch (err) {
        alert("Không thể gửi đánh giá");
        console.error(err);
    }
};

const deleteReview = async (id) => {
    if (!confirm("Bạn có chắc muốn xoá đánh giá này?")) return;

    const token = localStorage.getItem('access_token');
    try {
        const res = await fetch(`http://127.0.0.1:8000/api/reviews/${id}`, {
            method: 'DELETE',
            headers: {
                Authorization: `Bearer ${token}`
            }
        });

        if (!res.ok) throw new Error();

        alert("Đã xoá đánh giá");
        await fetchReviews();
    } catch (err) {
        alert("Xoá không thành công");
    }
};



const fetchReviews = async () => {
    try {
        const res = await fetch('http://127.0.0.1:8000/api/reviews?product_id=1');
        if (!res.ok) throw new Error('Lỗi khi lấy đánh giá');

        const data = await res.json();

        reviews.value = data;
    } catch (err) {
        console.error('Lỗi lấy đánh giá:', err.message);
    }
};


// Pagination for Reviews
const currentPage = ref(1);
const itemsPerPage = 3;
const totalPages = computed(() =>
    Math.ceil(reviews.value.list.length / itemsPerPage)
);

const paginatedReviews = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return reviews.value.list.slice(start, end);
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
    fetchReviews();
    intervalId = setInterval(() => {
        currentImage.value = (currentImage.value + 1) % productImages.length;
    }, 3000);
});

=======
>>>>>>> 537c724c34ae742b86897289086712a3b14c6266
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

