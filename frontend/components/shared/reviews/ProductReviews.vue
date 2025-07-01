<template>
  <section class="w-full mb-12 py-6 bg-gray-50">
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

        <!-- Nút chọn ảnh đẹp hơn -->
        <label for="review-upload"
          class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded bg-white cursor-pointer hover:bg-gray-50 transition text-xs">
          <i class="fas fa-camera text-gray-500"></i>
          <span>Chọn ảnh</span>
          <input id="review-upload" type="file" accept="image/*" multiple class="hidden" @change="handleImageUpload" />
        </label>
        <!-- Nút chọn video -->
        <label for="review-video-upload"
          class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded bg-white cursor-pointer hover:bg-gray-50 transition text-xs mt-2">
          <i class="fas fa-video text-gray-500"></i>
          <span>Chọn video</span>
          <input id="review-video-upload" type="file" accept="video/*" multiple class="hidden"
            @change="handleVideoUpload" />
        </label>


        <!-- Xem trước ảnh đã chọn -->
        <div class="flex gap-2 flex-wrap mt-2" v-if="uploadedImages.length">
          <div v-for="(file, i) in uploadedImages" :key="i"
            class="relative w-16 h-16 border rounded overflow-hidden group">
            <img v-if="getImagePreview(file)" :src="getImagePreview(file)" class="w-full h-full object-cover" />
            <button type="button"
              class="absolute top-0 right-0 bg-black/60 text-white text-[10px] px-1 rounded hidden group-hover:block"
              @click="removeImage(i)">
              ×
            </button>
          </div>
        </div>
        <!-- Xem trước video đã chọn -->
        <div class="flex gap-2 flex-wrap mt-2" v-if="uploadedVideos.length">
          <div v-for="(video, i) in uploadedVideos" :key="'video-' + i"
            class="relative w-28 h-20 border rounded overflow-hidden group">
            <video :src="getVideoPreview(video)" class="w-full h-full object-cover" controls></video>
            <button type="button"
              class="absolute top-0 right-0 bg-black/60 text-white text-[10px] px-1 rounded hidden group-hover:block"
              @click="removeVideo(i)">
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
    <template v-if="userId !== null">
      <transition-group name="fade" tag="div" class="space-y-4">
        <ReviewItem v-for="review in paginatedReviews" :key="`${review.id}-${refreshKey}`" :review="review"
          :current-user-id="userId" @edit-review="startEdit" @delete-review="deleteReview" />
      </transition-group>
    </template>



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
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import ReviewItem from './ReviewItem.vue'
import { useRuntimeConfig } from '#app'
import { useToast } from '~/composables/useToast'

const { toast, showError, showConfirm } = useToast()

const props = defineProps({
  productId: {
    type: Number,
    required: true
  }
})

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl
const refreshKey = ref(0)


const token = ref(null)
const userId = ref(null)

const currentUserId = computed(() => userId.value || null)

const reviews = ref({
  summary: { rating: 0, count: 0, ratings: [] },
  list: [],
})

const newReviewRating = ref(0)
const newReviewComment = ref('')
const editingReviewId = ref(null)
const uploadedImages = ref([])
const uploadedVideos = ref([])
const deletedImages = ref([])
const deletedVideos = ref([])
const reviewSection = ref(null)

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files || [])

  const newFiles = files.map(file => ({
    isExisting: false,
    file: file,
  }))

  uploadedImages.value.push(...newFiles)
}

const handleVideoUpload = (event) => {
  const files = Array.from(event.target.files || [])
  const newFiles = files.map(file => ({
    file,
    isExisting: false,
  }))
  uploadedVideos.value.push(...newFiles)
}

const getImagePreview = (file) => {
  if (file.isExisting) return file.url
  if (file.file instanceof File) {
    try {
      return URL.createObjectURL(file.file)
    } catch {
      return null
    }
  }
  return null
}

const getVideoPreview = (file) => {
  if (file.isExisting) return file.url
  if (file.file instanceof File) {
    try {
      return URL.createObjectURL(file.file)
    } catch {
      return null
    }
  }
  return null
}


const fetchUser = async () => {
  try {
    if (!token.value) return

    const res = await fetch(`${apiBase}/me`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
      },
    })

    if (!res.ok) throw new Error('Lỗi khi lấy thông tin người dùng')

    const json = await res.json()
    if (json && json.data) {
      userId.value = json.data.id
    } else {
      throw new Error('Không có dữ liệu người dùng')
    }
  } catch (err) {
    await showError('Lỗi lấy thông tin người dùng', err.message)
  }
}

onMounted(async () => {
  token.value = localStorage.getItem('access_token')
  await fetchUser()
  await fetchReviews()
})

const sortBy = ref('newest')
const filterStar = ref('')

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

const currentPage = ref(1)
const itemsPerPage = 3

watch([sortBy, filterStar], async () => {
  currentPage.value = 1
  await nextTick()
  if (reviewSection.value) {
    reviewSection.value.scrollIntoView({ behavior: 'smooth' })
  }
})

const totalPages = computed(() =>
  Math.ceil(filteredAndSortedReviews.value.length / itemsPerPage)
)

const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredAndSortedReviews.value.slice(start, start + itemsPerPage)
})

const hasUserReviewed = computed(() => {
  return reviews.value.list.some((r) => r.user_id === userId.value)
})

const fetchReviews = async () => {
  try {
    const res = await fetch(`${apiBase}/reviews?product_id=${props.productId}`)
    if (!res.ok) throw new Error('Lỗi khi lấy đánh giá')

    const data = await res.json()

    reviews.value.summary = structuredClone(data.summary)
    reviews.value.list = structuredClone(data.list)

  } catch (err) {
    await showError('Lỗi lấy đánh giá', err.message)
  }
}



const startEdit = (review) => {
  editingReviewId.value = review.id
  newReviewRating.value = review.rating
  newReviewComment.value = review.content
  uploadedImages.value = []
  uploadedVideos.value = []
  deletedImages.value = []
  deletedVideos.value = []

  if (Array.isArray(review.images)) {
    uploadedImages.value = review.images.map((img) => ({
      isExisting: true,
      url: img.url,
      original: {
        id: img.id,
        url: img.url,
      }
    }))
  }

  if (Array.isArray(review.videos)) {
    uploadedVideos.value = review.videos.map((vid) => ({
      isExisting: true,
      url: vid.url,
      original: {
        id: vid.id,
        url: vid.url,
      }
    }))
  }
}


const removeImage = (index) => {
  const file = uploadedImages.value[index]
  if (file.isExisting && file.original?.id) {
    deletedImages.value.push(file.original.id)
  }
  uploadedImages.value.splice(index, 1)
}
const removeVideo = (index) => {
  const file = uploadedVideos.value[index]
  if (file.isExisting && file.original?.id) {
    deletedVideos.value.push(file.original.id)
  }
  uploadedVideos.value.splice(index, 1)
}

const submitReview = async () => {
  if (!token.value) {
    return toast('warning', 'Bạn cần đăng nhập để gửi đánh giá')
  }

  if (!editingReviewId.value && hasUserReviewed.value) {
    return toast('info', 'Bạn đã đánh giá sản phẩm này rồi!')
  }

  const formData = new FormData()
  formData.append('rating', newReviewRating.value)
  formData.append('content', newReviewComment.value)

  uploadedImages.value.forEach(file => {
    if (!file.isExisting) {
      formData.append('images[]', file.file)
    }
  })

  uploadedVideos.value.forEach(video => {
    if (!video.isExisting) {
      formData.append('videos[]', video.file)
    }
  })

  if (editingReviewId.value) {
    formData.append('_method', 'PUT')

    const keptImageIds = uploadedImages.value
      .filter(img => img.isExisting && img.original?.id)
      .map(img => img.original.id)
    keptImageIds.forEach(id => formData.append('kept_images[]', id))

    const keptVideoIds = uploadedVideos.value
      .filter(vid => vid.isExisting && vid.original?.id)
      .map(vid => vid.original.id)
    keptVideoIds.forEach(id => formData.append('kept_videos[]', id))

    deletedVideos.value.forEach(id => formData.append('deleted_videos[]', id))
  }

  deletedImages.value.forEach(id => formData.append('deleted_images[]', id))

  const url = editingReviewId.value
    ? `${apiBase}/reviews/${editingReviewId.value}`
    : `${apiBase}/reviews?product_id=${props.productId}`

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: formData,
    })

    const data = await res.json()

    if (!res.ok) {
      const firstError = Object.values(data.errors || {})[0]?.[0]
      return showError('Lỗi', firstError || data.message || 'Có lỗi xảy ra')
    }

    // Sau khi gửi form thành công
    toast('success', editingReviewId.value ? 'Cập nhật thành công' : 'Đã gửi đánh giá')

    // Reset form
    newReviewRating.value = 0
    newReviewComment.value = ''
    editingReviewId.value = null
    uploadedImages.value = []
    uploadedVideos.value = []
    deletedImages.value = []
    deletedVideos.value = []

    await fetchReviews()
    refreshKey.value++          // ✅ Bắt Vue render lại bằng key mới
    await nextTick()
    reviewSection.value?.scrollIntoView({ behavior: 'smooth' })


  } catch (err) {
    showError('Lỗi gửi đánh giá', err.message)
  }
}


const deleteReview = async (id) => {
  if (!token.value) return

  const confirmResult = await showConfirm('Bạn có chắc chắn?', 'Đánh giá sẽ bị xoá và không thể khôi phục', 'Xoá')

  if (!confirmResult.isConfirmed) return

  try {
    const res = await fetch(`${apiBase}/reviews/${id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token.value}`,
      },
    })

    if (!res.ok) throw new Error('Xoá không thành công')

    toast('success', 'Đã xoá đánh giá')
    await fetchReviews()
  } catch (err) {
    await showError('Xoá không thành công', err.message)
  }
}

const editReview = (review) => {
  newReviewRating.value = review.rating
  newReviewComment.value = review.content
  editingReviewId.value = review.id
}
const cancelEdit = () => {
  editingReviewId.value = null
  newReviewRating.value = 0
  newReviewComment.value = ''

  uploadedImages.value = []
  uploadedVideos.value = []
  deletedImages.value = []
  deletedVideos.value = []
}



const scrollToReview = () => {
  reviewSection.value?.scrollIntoView({ behavior: 'smooth' })
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    scrollToReview()
  }
}
</script>
