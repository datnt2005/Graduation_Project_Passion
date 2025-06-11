<template>
    <section ref="reviewSection" class="w-full mb-12 py-6 bg-gray-50">
        <h3 class="text-sm font-semibold mb-4">Khách hàng đánh giá</h3>
        <div class="flex flex-col sm:flex-row gap-4 mb-4">
            <!-- Tổng quan -->
            <div class="flex-1 text-xs">
                <p class="font-semibold mb-1">Tổng quan</p>
                <div class="flex items-center gap-1 mb-1">
                    <span class="text-yellow-400 font-bold text-lg">{{ reviews.summary.rating }}</span>
                    <span class="text-yellow-400 text-lg">★★★★★</span>
                </div>
                <p class="text-gray-500 mb-2">({{ reviews.summary.count }} đánh giá)</p>
                <div class="space-y-1">
                    <div v-for="rating in reviews.summary.ratings" :key="rating.stars" class="flex items-center gap-2">
                        <span>{{ rating.stars }}</span>
                        <div class="w-full bg-gray-200 rounded h-2">
                            <div class="bg-yellow-400 h-2 rounded" :style="{ width: rating.percentage + '%' }"></div>
                        </div>
                        <span>{{ rating.count }}</span>
                    </div>
                </div>
            </div>

            <!-- Form gửi hoặc sửa đánh giá -->
            <form @submit.prevent="submitReview" class="flex-1 text-xs">
                <p class="font-semibold mb-1">{{ editingReviewId ? 'Chỉnh sửa đánh giá của bạn' : 'Bình luận - xem đánh giá' }}</p>

                <!-- Rating -->
                <div class="flex items-center gap-1 mb-2">
                    <span v-for="star in 5" :key="star" class="cursor-pointer text-yellow-500 text-lg" @click="newReviewRating = star">
                        <span v-if="newReviewRating >= star">★</span>
                        <span v-else class="text-gray-300">☆</span>
                    </span>
                </div>

                <textarea v-model="newReviewComment" class="w-full border border-gray-300 rounded p-2 resize-none" rows="6" placeholder="Viết cảm nhận của bạn..."></textarea>

                <div class="flex items-center gap-2 mt-2">
                    <button type="submit" class="px-3 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100 transition">
                        {{ editingReviewId ? 'Cập nhật' : 'Gửi' }}
                    </button>
                    <button v-if="editingReviewId" type="button" class="text-gray-600 text-xs hover:underline" @click="cancelEdit">
                        Huỷ chỉnh sửa
                    </button>
                </div>
            </form>
        </div>

        <!-- Danh sách đánh giá -->
        <transition-group name="fade" tag="div" class="space-y-4">
            <ReviewItem v-for="review in paginatedReviews" :key="review.id" :review="review" @edit-review="editReview" @delete-review="deleteReview" />
        </transition-group>

        <!-- Pagination -->
        <nav class="mt-6 flex justify-center items-center gap-3 text-xs text-gray-700 select-none">
            <button class="p-1 rounded hover:bg-gray-200 transition" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <button v-for="page in totalPages" :key="page" :class="{ 'bg-gray-200 text-gray-900 font-semibold': page === currentPage }"
                class="w-7 h-7 rounded hover:bg-gray-200 transition" @click="goToPage(page)">
                {{ page }}
            </button>

            <button class="p-1 rounded hover:bg-gray-200 transition" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </nav>
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import ReviewItem from './ReviewItem.vue';

// ======= THÔNG BÁO TOAST THÀNH CÔNG =======
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2000,
  timerProgressBar: true,
});

// ======= THÔNG BÁO LỖI CHUNG =======
const showError = async (title = 'Lỗi', message = 'Đã xảy ra lỗi.') => {
  await Swal.fire({
    icon: 'error',
    title,
    text: message,
  });
};

// ======= BIẾN =======
const reviews = ref({
  summary: { rating: 0, count: 0, ratings: [] },
  list: [],
});

const newReviewRating = ref(0);
const newReviewComment = ref('');
const editingReviewId = ref(null);
const reviewSection = ref(null);

const userId = Number(localStorage.getItem('user_id'));
const token = localStorage.getItem('access_token');

// ======= PHÂN TRANG =======
const currentPage = ref(1);
const itemsPerPage = 3;

const totalPages = computed(() =>
  Math.ceil(reviews.value.list.length / itemsPerPage)
);

const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return reviews.value.list.slice(start, start + itemsPerPage);
});

// ======= KIỂM TRA NGƯỜI DÙNG ĐÃ ĐÁNH GIÁ CHƯA =======
const hasUserReviewed = computed(() => {
  return reviews.value.list.some((r) => r.user_id === userId);
});

// ======= LẤY DANH SÁCH ĐÁNH GIÁ =======
const fetchReviews = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/reviews?product_id=1');
    if (!res.ok) throw new Error('Lỗi khi lấy đánh giá');
    reviews.value = await res.json();
  } catch (err) {
    await showError('Lỗi lấy đánh giá', err.message);
  }
};

// ======= GỬI HOẶC CẬP NHẬT ĐÁNH GIÁ =======
const submitReview = async () => {
  if (!token) {
    return Swal.fire({
      icon: 'warning',
      title: 'Bạn cần đăng nhập để gửi đánh giá',
    });
  }

  // Nếu không đang chỉnh sửa mà đã đánh giá rồi => chặn lại
  if (!editingReviewId.value && hasUserReviewed.value) {
    return Swal.fire({
      icon: 'info',
      title: 'Bạn đã đánh giá sản phẩm này rồi!',
    });
  }

  const payload = {
    rating: newReviewRating.value,
    content: newReviewComment.value,
  };

  const method = editingReviewId.value ? 'PUT' : 'POST';
  const url = editingReviewId.value
    ? `http://127.0.0.1:8000/api/reviews/${editingReviewId.value}`
    : `http://127.0.0.1:8000/api/reviews?product_id=1`;

  try {
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });

    const data = await res.json();

    if (!res.ok) {
      let message = data.message || 'Đã xảy ra lỗi.';

      if (res.status === 422 && data.errors) {
        const firstError = Object.values(data.errors)[0]?.[0];
        message = firstError || message;
      }

      return await showError('Lỗi', message);
    }

    await Toast.fire({
      icon: 'success',
      title: editingReviewId.value ? 'Cập nhật thành công' : 'Đã gửi đánh giá',
    });

    // Reset form
    newReviewRating.value = 0;
    newReviewComment.value = '';
    editingReviewId.value = null;

    await fetchReviews();
  } catch (err) {
    await showError('Không thể gửi đánh giá', err.message);
  }
};

// ======= XOÁ ĐÁNH GIÁ =======
const deleteReview = async (id) => {
  if (!token) return;

  const confirmResult = await Swal.fire({
    title: 'Bạn có chắc chắn?',
    text: 'Đánh giá sẽ bị xoá và không thể khôi phục',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Xoá',
    cancelButtonText: 'Huỷ',
  });

  if (!confirmResult.isConfirmed) return;

  try {
    const res = await fetch(`http://127.0.0.1:8000/api/reviews/${id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    if (!res.ok) throw new Error('Xoá không thành công');

    await Toast.fire({
      icon: 'success',
      title: 'Đã xoá đánh giá',
    });

    await fetchReviews();
  } catch (err) {
    await showError('Xoá không thành công', err.message);
  }
};

// ======= CHỈNH SỬA =======
const editReview = (review) => {
  newReviewRating.value = review.rating;
  newReviewComment.value = review.content;
  editingReviewId.value = review.id;
};

// ======= HUỶ CHỈNH SỬA =======
const cancelEdit = () => {
  newReviewRating.value = 0;
  newReviewComment.value = '';
  editingReviewId.value = null;
};

// ======= CUỘN TỚI PHẦN ĐÁNH GIÁ =======
const scrollToReview = () => {
  reviewSection.value?.scrollIntoView({ behavior: 'smooth' });
};

// ======= CHUYỂN TRANG =======
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    scrollToReview();
  }
};

// ======= LẤY DỮ LIỆU BAN ĐẦU =======
onMounted(fetchReviews);
</script>


