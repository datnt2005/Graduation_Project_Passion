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
        <p class="font-semibold mb-1">
          {{ editingReviewId ? 'Chỉnh sửa đánh giá của bạn' : 'Bình luận - xem đánh giá' }}
        </p>

        <!-- Rating -->
        <div class="flex items-center gap-1 mb-2">
          <span v-for="star in 5" :key="star" class="cursor-pointer text-yellow-500 text-lg"
            @click="newReviewRating = star">
            <span v-if="newReviewRating >= star">★</span>
            <span v-else class="text-gray-300">☆</span>
          </span>
        </div>

        <input type="file" accept="image/*" multiple @change="handleImageUpload" class="block mt-2 text-xs" />

        <!-- Xem trước ảnh -->
        <div class="flex gap-2 flex-wrap mt-2" v-if="uploadedImages.length">
          <div v-for="(file, i) in uploadedImages" :key="i" class="relative w-14 h-14 group">
            <img :src="file.isExisting ? file.url : URL.createObjectURL(file)"
              class="w-full h-full object-cover rounded" />
            <button type="button"
              class="absolute top-0 right-0 bg-black/60 text-white text-[10px] px-1 rounded hidden group-hover:block"
              @click="removeImage(i)">
              ×
            </button>
          </div>
        </div>

        <textarea v-model="newReviewComment" class="w-full border border-gray-300 rounded p-2 resize-none" rows="6"
          placeholder="Viết cảm nhận của bạn..."></textarea>

        <div class="flex items-center gap-2 mt-2">
          <button type="submit" class="px-3 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100 transition">
            {{ editingReviewId ? 'Cập nhật' : 'Gửi' }}
          </button>
          <button v-if="editingReviewId" type="button" class="text-gray-600 text-xs hover:underline"
            @click="cancelEdit">
            Huỷ chỉnh sửa
          </button>
        </div>
      </form>

    </div>
    <!-- Bộ lọc -->
    <div class="flex flex-wrap sm:flex-nowrap justify-between items-center text-xs mb-4 gap-2">
      <h4 class="font-semibold">Bộ lọc đánh giá</h4>
      <div class="flex flex-wrap gap-2 items-center">
        <label class="text-gray-600">Sắp xếp:</label>
        <select v-model="sortBy" class="border border-gray-300 rounded px-2 py-1 min-w-[100px]">
          <option value="newest">Mới nhất</option>
          <option value="oldest">Cũ nhất</option>
        </select>

        <label class="text-gray-600">Sao:</label>
        <select v-model="filterStar" class="border border-gray-300 rounded px-2 py-1 min-w-[100px]">
          <option value="">Tất cả sao</option>
          <option v-for="n in 5" :key="n" :value="n">{{ n }} sao</option>
        </select>
      </div>
    </div>

    <!-- Danh sách đánh giá -->
    <transition-group name="fade" tag="div" class="space-y-4">
      <ReviewItem v-for="review in paginatedReviews" :key="review.id" :review="review" @edit-review="startEdit"
        @delete-review="deleteReview" />
    </transition-group>

    <!-- Pagination -->
    <nav class="mt-6 flex justify-center items-center gap-3 text-xs text-gray-700 select-none">
      <button class="p-1 rounded hover:bg-gray-200 transition" :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button v-for="page in totalPages" :key="page"
        :class="{ 'bg-gray-200 text-gray-900 font-semibold': page === currentPage }"
        class="w-7 h-7 rounded hover:bg-gray-200 transition" @click="goToPage(page)">
        {{ page }}
      </button>
      <button class="p-1 rounded hover:bg-gray-200 transition" :disabled="currentPage === totalPages"
        @click="goToPage(currentPage + 1)">
        <i class="fas fa-chevron-right"></i>
      </button>
    </nav>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import ReviewItem from './ReviewItem.vue';
import { watch } from 'vue';
import { nextTick } from 'vue'


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

const uploadedImages = ref([]); // ← Biến lưu ảnh đang hiển thị trong form
// ← Biến lưu ID ảnh cũ bị xóa
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files);
  uploadedImages.value = files;
};

// ======= BIẾN BẢO MẬT ĐƯỜNG LINK =======
const config = useRuntimeConfig();
const apiBase = config.public.apiBaseUrl;
const mediaBase = config.public.mediaBaseUrl;

const userId = ref(null);
const token = ref(null); // ban đầu null

const fetchUser = async () => {
  try {
    if (!token.value) return;

    const res = await fetch(`${apiBase}/me`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
      },
    });

    if (!res.ok) throw new Error('Lỗi khi lấy thông tin người dùng');

    const json = await res.json();
    if (json && json.data) {
      userId.value = json.data.id;
    } else {
      throw new Error('Không có dữ liệu người dùng');
    }
  } catch (err) {
    await showError('Lỗi lấy thông tin người dùng', err.message);
  }
};

onMounted(async () => {
  token.value = localStorage.getItem('access_token');

  await fetchUser();
  await fetchReviews();
});

// Filter + sort
const sortBy = ref('newest') // 'newest' | 'oldest'
const filterStar = ref('') // '' hoặc số 1-5

const filteredAndSortedReviews = computed(() => {
  let list = [...reviews.value.list]
  if (filterStar.value) {
    list = list.filter(r => r.rating === Number(filterStar.value))
  }
  if (sortBy.value === 'newest') {
    list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  } else {
    list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
  }
  return list
})

// ======= PHÂN TRANG =======
const currentPage = ref(1);
const itemsPerPage = 3;

watch([sortBy, filterStar], async () => {
  currentPage.value = 1;

  await nextTick(); // đợi DOM cập nhật lại sau khi sort/filter
  if (reviewSection.value) {
    reviewSection.value.scrollIntoView({ behavior: 'smooth' });
  }
});

const totalPages = computed(() =>
  Math.ceil(filteredAndSortedReviews.value.length / itemsPerPage)
);

const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredAndSortedReviews.value.slice(start, start + itemsPerPage);
});


// ======= KIỂM TRA ĐÃ ĐÁNH GIÁ CHƯA =======
const hasUserReviewed = computed(() => {
  return reviews.value.list.some((r) => r.user_id === userId.value);
});

// ======= LẤY DANH SÁCH ĐÁNH GIÁ =======
const fetchReviews = async () => {
  try {
    const res = await fetch(`${apiBase}/reviews?product_id=1`);
    if (!res.ok) throw new Error('Lỗi khi lấy đánh giá');
    reviews.value = await res.json();
  } catch (err) {
    await showError('Lỗi lấy đánh giá', err.message);
  }
};

const startEdit = (review) => {
  editingReviewId.value = review.id;
  newReviewRating.value = review.rating;
  newReviewComment.value = review.content;

  // Reset trước
  uploadedImages.value = [];
  deletedImages.value = [];

  // Gán lại ảnh cũ
  if (Array.isArray(review.images)) {
    uploadedImages.value = review.images.map((img) => ({
      isExisting: true,
      url: img.url,
      original: {
        id: img.id,
        url: img.url,
      }
    }));
  }
};

const deletedImages = ref([]);
const removeImage = (index) => {
  const file = uploadedImages.value[index];
  if (file.isExisting && file.original?.id) {
    deletedImages.value.push(file.original.id);
  }
  uploadedImages.value.splice(index, 1);
};


// Trong submitReview
if (deletedImages.value.length) {
  deletedImages.value.forEach(id => formData.append('deleted_images[]', id));
}


// ======= GỬI/CẬP NHẬT ĐÁNH GIÁ =======
const submitReview = async () => {
  if (!token.value) {
    return Swal.fire({ icon: 'warning', title: 'Bạn cần đăng nhập để gửi đánh giá' });
  }

  if (!editingReviewId.value && hasUserReviewed.value) {
    return Swal.fire({ icon: 'info', title: 'Bạn đã đánh giá sản phẩm này rồi!' });
  }

  const formData = new FormData();
  formData.append('rating', newReviewRating.value);
  formData.append('content', newReviewComment.value);

  uploadedImages.value.forEach(file => {
    if (!file.isExisting) {
      formData.append('images[]', file);
    }
  });

  if (editingReviewId.value) {
    formData.append('_method', 'PUT'); //  TRỌNG TÂM: spoof method PUT

    const keptIds = uploadedImages.value
      .filter(img => img.isExisting && img.original?.id)
      .map(img => img.original.id);

    keptIds.forEach(id => formData.append('kept_images[]', id));
  }

  const url = editingReviewId.value
    ? `${apiBase}/reviews/${editingReviewId.value}`
    : `${apiBase}/reviews?product_id=${productId.value}`;

  try {
    const res = await fetch(url, {
      method: 'POST', //  luôn dùng POST
      headers: { Authorization: `Bearer ${token.value}` },
      body: formData,
    });

    const data = await res.json();

    if (!res.ok) {
      const firstError = Object.values(data.errors || {})[0]?.[0];
      return showError('Lỗi', firstError || data.message || 'Có lỗi xảy ra');
    }

    await Toast.fire({ icon: 'success', title: editingReviewId.value ? 'Cập nhật thành công' : 'Đã gửi đánh giá' });

    newReviewRating.value = 0;
    newReviewComment.value = '';
    editingReviewId.value = null;
    uploadedImages.value = [];

    await fetchReviews();
  } catch (err) {
    showError('Lỗi gửi đánh giá', err.message);
  }
};


// ======= XOÁ ĐÁNH GIÁ =======
const deleteReview = async (id) => {
  if (!token.value) return;

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
    const res = await fetch(`${apiBase}/reviews/${id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token.value}`, // ✅ dùng token.value
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