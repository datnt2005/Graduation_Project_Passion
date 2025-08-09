<template>
  <section ref="reviewSection" class="w-full mb-12 py-6 bg-gray-50">
    <h1 class="text-sm font-semibold mb-4">Khách hàng đánh giá</h1>
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-6">
        <!-- Tổng quan -->
        <div class="flex-1">
          <p class="text-sm font-semibold mb-3 text-gray-800">Tổng quan đánh giá</p>
          <div v-memo="[reviews.summary.rating, reviews.summary.count]" class="flex items-center gap-2 mb-2">
            <span class="text-yellow-500 font-bold text-3xl">{{ reviews.summary.rating.toFixed(1) }}</span>
            <div class="flex items-center text-yellow-400 text-lg">
              <template v-for="i in 5" :key="i">
                <i v-if="i <= Math.floor(reviews.summary.rating)" class="fas fa-star"></i>
                <i v-else-if="i - reviews.summary.rating <= 0.5" class="fas fa-star-half-alt"></i>
                <i v-else class="far fa-star text-gray-300"></i>
              </template>
            </div>
          </div>
          <p class="text-xs text-gray-500 mb-4">({{ reviews.summary.count }} đánh giá)</p>
          <div v-memo="[fullRatings]" class="space-y-2 text-xs">
            <div v-for="rating in fullRatings" :key="rating.stars" class="flex items-center gap-2">
              <span class="w-10 flex items-center gap-1 text-gray-700">
                <i class="fas fa-star text-yellow-400 text-sm"></i> {{ rating.stars }}
              </span>
              <div class="flex-1 h-2 bg-gray-200 rounded">
                <div class="h-2 bg-yellow-400 rounded" :style="{ width: rating.percentage + '%' }"></div>
              </div>
              <span class="w-6 text-right text-gray-600">{{ rating.count }}</span>
            </div>
          </div>
        </div>
        <!-- Form đánh giá -->
        <div v-if="token" class="flex-1 space-y-4">
          <form @submit.prevent="submitReview">
            <div class="flex justify-between items-center">
              <h2 class="font-semibold text-sm text-gray-800">
                {{ editingReviewId ? 'Chỉnh sửa đánh giá của bạn' : 'Gửi đánh giá sản phẩm' }}
              </h2>
              <button v-if="editingReviewId" type="button" class="text-xs text-gray-500 hover:text-red-500" @click="cancelEdit">
                Hủy
              </button>
            </div>
            <!-- Rating -->
            <div class="space-y-1">
              <label class="block text-xs text-gray-600 font-medium">Chọn số sao</label>
              <div class="flex items-center gap-1">
                <span v-for="star in 5" :key="star" class="cursor-pointer text-xl" @click="newReviewRating = star">
                  <span :class="newReviewRating >= star ? 'text-yellow-500' : 'text-gray-300'">★</span>
                </span>
                <span class="text-xs ml-2 text-gray-500">({{ newReviewRating }} / 5)</span>
              </div>
            </div>
            <!-- Upload hình ảnh & video -->
            <div class="flex flex-col gap-2">
              <div class="flex gap-2 flex-wrap">
                <label for="review-upload" class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded bg-white cursor-pointer hover:bg-gray-50 text-xs">
                  <i class="fas fa-camera text-gray-500"></i>
                  <span>Chọn ảnh</span>
                  <input id="review-upload" type="file" accept="image/*" multiple class="hidden" @change="handleImageUpload" />
                </label>
                <label for="review-video-upload" class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded bg-white cursor-pointer hover:bg-gray-50 text-xs">
                  <i class="fas fa-video text-gray-500"></i>
                  <span>Chọn video</span>
                  <input id="review-video-upload" type="file" accept="video/*" multiple class="hidden" @change="handleVideoUpload" />
                </label>
              </div>
              <div v-if="uploadedImages.length" class="flex gap-2 flex-wrap">
                <div v-for="(file, i) in uploadedImages" :key="i" class="relative w-16 h-16 border rounded overflow-hidden group">
                  <img :src="getImagePreview(file)" :alt="'Ảnh đánh giá ' + i" class="w-full h-full object-cover" loading="lazy" />
                  <button type="button" class="absolute top-0 right-0 bg-black/60 text-white text-[10px] px-1 rounded hidden group-hover:block" @click="removeImage(i)">
                    ×
                  </button>
                </div>
              </div>
              <div v-if="uploadedVideos.length" class="flex gap-2 flex-wrap">
                <div v-for="(video, i) in uploadedVideos" :key="'video-' + i" class="relative w-28 h-20 border rounded overflow-hidden group">
                  <video :src="getVideoPreview(video)" class="w-full h-full object-cover" controls loading="lazy"></video>
                  <button type="button" class="absolute top-0 right-0 bg-black/60 text-white text-[10px] px-1 rounded hidden group-hover:block" @click="removeVideo(i)">
                    ×
                  </button>
                </div>
              </div>
            </div>
            <!-- Nội dung -->
            <div class="space-y-1">
              <label class="block text-xs text-gray-600 font-medium">Nội dung đánh giá</label>
              <textarea v-model="newReviewComment" rows="5" class="w-full border border-gray-300 rounded p-2 text-sm resize-none focus:outline-none focus:ring focus:ring-blue-100" placeholder="Viết cảm nhận của bạn về sản phẩm..." aria-label="Nội dung đánh giá"></textarea>
            </div>
            <!-- Submit -->
            <div class="flex justify-end">
              <button type="submit" class="px-4 py-2 text-sm bg-[#1BA0E2] hover:bg-[#1790cc] text-white rounded transition" :disabled="!isReviewFormValid">
                {{ editingReviewId ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
              </button>
            </div>
          </form>
        </div>
        <div v-else class="flex-1 space-y-4">
          <p class="text-sm text-gray-600">Bạn cần đăng nhập để gửi đánh giá.</p>
          <button @click="openLoginModal" class="text-blue-500 hover:underline">Đăng nhập ngay</button>
        </div>
      </div>
    </div>
    <!-- Bộ lọc -->
    <div class="flex flex-wrap gap-2 my-6 items-center text-sm font-medium text-gray-700">
      <button @click="resetFilters" :class="[filterStar === '' && !filterByMedia ? 'text-red-600 border-red-600' : 'text-gray-600 border-gray-300', 'px-3 py-1.5 border rounded hover:bg-gray-100 transition']">
        Tất cả
      </button>
      <button v-for="n in [5, 4, 3, 2, 1]" :key="n" @click="filterStar = n; filterByMedia = false" :class="[filterStar === n ? 'text-red-600 border-red-600' : 'text-gray-600 border-gray-300', 'px-3 py-1.5 border rounded hover:bg-gray-100 transition']">
        {{ n }} Sao ({{ getStarCount(n) }})
      </button>
      <button @click="filterByMedia = !filterByMedia; if (filterByMedia) filterStar = ''" :class="[filterByMedia ? 'text-red-600 border-red-600' : 'text-gray-600 border-gray-300', 'px-3 py-1.5 border rounded hover:bg-gray-100 transition']">
        Có hình ảnh/Video ({{ getMediaCount() }})
      </button>
      <button @click="sortBy = 'newest'" :class="[sortBy === 'newest' ? 'text-red-600 border-red-600' : 'text-gray-600 border-gray-300', 'px-3 py-1.5 border rounded hover:bg-gray-100 transition text-xs']">
        Mới nhất
      </button>
      <button @click="sortBy = 'oldest'" :class="[sortBy === 'oldest' ? 'text-red-600 border-red-600' : 'text-gray-600 border-gray-300', 'px-3 py-1.5 border rounded hover:bg-gray-100 transition text-xs']">
        Cũ nhất
      </button>
    </div>
    <!-- Danh sách đánh giá -->
    <div v-if="isLoading" class="text-center text-gray-400 py-10">Đang tải dữ liệu...</div>
    <div v-else-if="paginatedReviews.length === 0" class="text-center text-gray-400 py-10">Chưa có đánh giá nào.</div>
    <div v-else class="space-y-4">
      <ReviewItem v-for="review in paginatedReviews" :key="`${review.id}-${refreshKey}`" :review="review" :current-user-id="currentUserId" @edit-review="startEdit" @delete-review="deleteReview" />
    </div>
    <!-- Pagination -->
    <nav v-if="totalPages > 1" class="mt-6 flex justify-center items-center gap-3 text-xs text-gray-700 select-none">
      <button class="p-1 rounded hover:bg-gray-200 transition" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button v-for="page in totalPages" :key="page" :class="{ 'bg-gray-200 text-gray-900 font-semibold': page === currentPage }" class="w-7 h-7 rounded hover:bg-gray-200 transition" @click="goToPage(page)">
        {{ page }}
      </button>
      <button class="p-1 rounded hover:bg-gray-200 transition" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
        <i class="fas fa-chevron-right"></i>
      </button>
    </nav>
  </section>
  <AuthModal :show="showModal" :initial-mode="modalMode" @close="showModal = false" @login-success="handleLoginSuccess" />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useHead } from '#app'
import { useRuntimeConfig } from '#app'
import { useToast } from '~/composables/useToast'
import ReviewItem from './ReviewItem.vue'
import AuthModal from '~/components/shared/AuthModal.vue'

const { toast, showError, showConfirm } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl
const modalMode = ref('login')
const showModal = ref(false)

const props = defineProps({
  productId: { type: Number, required: true },
  productName: { type: String, default: 'Sản phẩm' }
})

const reviewSection = ref(null)
const token = ref(localStorage.getItem('access_token'))
const userId = ref(null)
const isLoading = ref(true)
const reviews = ref({ summary: { rating: 0, count: 0, ratings: {} }, list: [] })
const newReviewRating = ref(0)
const newReviewComment = ref('')
const editingReviewId = ref(null)
const uploadedImages = ref([])
const uploadedVideos = ref([])
const deletedImages = ref([])
const deletedVideos = ref([])
const sortBy = ref('newest')
const filterStar = ref('')
const filterByMedia = ref(false)
const currentPage = ref(1)
const itemsPerPage = 3
const refreshKey = ref(0)
const urlObjects = ref([])

// SEO metadata
useHead({

  script: [
    {
      type: 'application/ld+json',
      children: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Product',
        name: props.productName,
        aggregateRating: {
          '@type': 'AggregateRating',
          ratingValue: reviews.value.summary.rating.toFixed(1),
          reviewCount: reviews.value.summary.count
        },
        review: reviews.value.list.map(review => ({
          '@type': 'Review',
          author: { '@type': 'Person', name: review.user?.name || 'Khách' },
          datePublished: review.created_at,
          reviewRating: { '@type': 'Rating', ratingValue: review.rating },
          reviewBody: review.content
        }))
      })
    }
  ]
})

function openLoginModal() {
  console.log('openLoginModal called')
  modalMode.value = 'login'
  showModal.value = true
}

const currentUserId = computed(() => userId.value || null)
const isReviewFormValid = computed(() => newReviewRating.value > 0 && newReviewComment.value.trim().length > 0)
const fullRatings = computed(() => {
  const rawRatings = reviews.value.summary.ratings || {}
  const totalCount = reviews.value.summary.count || 0
  return [5, 4, 3, 2, 1].map(star => {
    const count = rawRatings[star] || 0
    const percentage = totalCount > 0 ? Math.round((count / totalCount) * 100) : 0
    return { stars: star, count, percentage }
  })
})
const filteredAndSortedReviews = computed(() => {
  let list = [...reviews.value.list]
  if (filterStar.value) list = list.filter(r => r.rating === Number(filterStar.value))
  if (filterByMedia.value) list = list.filter(r => (r.images?.length || 0) > 0 || (r.videos?.length || 0) > 0)
  return sortBy.value === 'newest'
    ? list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    : list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
})
const totalPages = computed(() => Math.ceil(filteredAndSortedReviews.value.length / itemsPerPage))
const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredAndSortedReviews.value.slice(start, start + itemsPerPage)
})
const hasUserReviewed = computed(() => reviews.value.list.some(r => r.user_id === userId.value))
const getStarCount = (star) => reviews.value.list.filter(r => r.rating === star).length
const getMediaCount = () => reviews.value.list.filter(r => (r.images?.length || 0) > 0 || (r.videos?.length || 0) > 0).length

const getImagePreview = (file) => {
  if (file.isExisting) return file.url
  if (file.file instanceof File) {
    const url = URL.createObjectURL(file.file)
    urlObjects.value.push(url)
    return url
  }
  return null
}
const getVideoPreview = (file) => {
  if (file.isExisting) return file.url
  if (file.file instanceof File) {
    const url = URL.createObjectURL(file.file)
    urlObjects.value.push(url)
    return url
  }
  return null
}
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files || []).map(file => ({ isExisting: false, file }))
  uploadedImages.value.push(...files)
}
const handleVideoUpload = (event) => {
  const files = Array.from(event.target.files || []).map(file => ({ isExisting: false, file }))
  uploadedVideos.value.push(...files)
}
const removeImage = (index) => {
  const file = uploadedImages.value[index]
  if (file.isExisting && file.original?.id) deletedImages.value.push(file.original.id)
  uploadedImages.value.splice(index, 1)
}
const removeVideo = (index) => {
  const file = uploadedVideos.value[index]
  if (file.isExisting && file.original?.id) deletedVideos.value.push(file.original.id)
  uploadedVideos.value.splice(index, 1)
}
const resetFilters = () => {
  filterStar.value = ''
  filterByMedia.value = false
  currentPage.value = 1
}
const fetchUser = async () => {
  if (!token.value) return
  try {
    const cacheKey = 'user_data'
    const cached = localStorage.getItem(cacheKey)
    if (cached) {
      const { id, expires } = JSON.parse(cached)
      if (expires > Date.now()) {
        userId.value = id
        return
      }
    }
    const res = await fetch(`${apiBase}/me`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    if (!res.ok) throw new Error('Lỗi khi lấy thông tin người dùng')
    const json = await res.json()
    userId.value = json.data?.id || null
    localStorage.setItem(cacheKey, JSON.stringify({ id: userId.value, expires: Date.now() + 3600 * 1000 }))
  } catch (err) {
    showError('Lỗi lấy thông tin người dùng', err.message)
  }
}
const fetchReviews = async () => {
  isLoading.value = true
  try {
    const cacheKey = `reviews_${props.productId}`
    const cached = localStorage.getItem(cacheKey)
    if (cached) {
      const { data, expires } = JSON.parse(cached)
      if (expires > Date.now()) {
        reviews.value = data
        isLoading.value = false
        return
      }
    }
    const res = await fetch(`${apiBase}/reviews?product_id=${props.productId}&page=${currentPage.value}&per_page=${itemsPerPage}`)
    if (!res.ok) throw new Error('Lỗi khi lấy đánh giá')
    const data = await res.json()
    const summary = data.data?.summary || { rating: 0, count: 0, ratings: {} }
    const list = data.data?.list || []
    reviews.value = {
      summary: { ...summary, ratings: summary.ratings || {} },
      list: structuredClone(list)
    }
    localStorage.setItem(cacheKey, JSON.stringify({ data: reviews.value, expires: Date.now() + 3600 * 1000 }))
  } catch (err) {
    showError('Lỗi lấy đánh giá', err.message)
  } finally {
    isLoading.value = false
  }
}
const submitReview = async () => {
  if (!token.value) {
    openLoginModal()
    return
  }
  if (!isReviewFormValid.value) {
    return toast('warning', 'Vui lòng chọn số sao và nhập nội dung đánh giá')
  }
  if (!editingReviewId.value && hasUserReviewed.value) {
    return toast('info', 'Bạn đã đánh giá sản phẩm này rồi!')
  }

  const formData = new FormData()
  formData.append('product_id', props.productId)
  formData.append('rating', newReviewRating.value)
  formData.append('content', newReviewComment.value)
  uploadedImages.value.forEach(file => {
    if (!file.isExisting) formData.append('images[]', file.file)
  })
  uploadedVideos.value.forEach(video => {
    if (!video.isExisting) formData.append('videos[]', video.file)
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
    deletedImages.value.forEach(id => formData.append('deleted_images[]', id))
    deletedVideos.value.forEach(id => formData.append('deleted_videos[]', id))
  }

  const url = editingReviewId.value
    ? `${apiBase}/reviews/${editingReviewId.value}`
    : `${apiBase}/reviews`

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: formData
    })
    const data = await res.json()
    if (!res.ok) {
      const errorMessage =
        Object.values(data.errors || {})[0]?.[0] ||
        data.message ||
        'Có lỗi xảy ra khi gửi đánh giá'
      return toast('error', errorMessage)
    }

    toast('success', editingReviewId.value ? 'Cập nhật thành công' : 'Đã gửi đánh giá')
    newReviewRating.value = 0
    newReviewComment.value = ''
    editingReviewId.value = null
    uploadedImages.value = []
    uploadedVideos.value = []
    deletedImages.value = []
    deletedVideos.value = []
    localStorage.removeItem(`reviews_${props.productId}`)
    await fetchReviews()
    refreshKey.value++
    await nextTick()
    reviewSection.value?.scrollIntoView({ behavior: 'smooth' })
  } catch (err) {
    showError('Lỗi gửi đánh giá', err.message)
  }
}
const deleteReview = async (id) => {
  if (!token.value) return
  const confirmResult = await showConfirm('Bạn có chắc chắn?', 'Đánh giá sẽ bị xóa và không thể khôi phục', 'Xóa')
  if (!confirmResult.isConfirmed) return
  try {
    const res = await fetch(`${apiBase}/reviews/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    if (!res.ok) throw new Error('Xóa không thành công')
    toast('success', 'Đã xóa đánh giá')
    localStorage.removeItem(`reviews_${props.productId}`)
    await fetchReviews()
  } catch (err) {
    showError('Xóa không thành công', err.message)
  }
}
const startEdit = (review) => {
  editingReviewId.value = review.id
  newReviewRating.value = review.rating
  newReviewComment.value = review.content
  uploadedImages.value = (review.images || []).map(img => ({ isExisting: true, url: img.url, original: { id: img.id, url: img.url } }))
  uploadedVideos.value = (review.videos || []).map(vid => ({ isExisting: true, url: vid.url, original: { id: vid.id, url: vid.url } }))
  deletedImages.value = []
  deletedVideos.value = []
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
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchReviews()
    reviewSection.value?.scrollIntoView({ behavior: 'smooth' })
  }
}
const handleLoginSuccess = async () => {
  token.value = localStorage.getItem('access_token')
  await fetchUser()
  showModal.value = false
}

onUnmounted(() => {
  urlObjects.value.forEach(url => URL.revokeObjectURL(url))
  urlObjects.value = []
})

watch([sortBy, filterStar, filterByMedia], () => {
  currentPage.value = 1
  fetchReviews()
})

onMounted(async () => {
  if (token.value) await fetchUser()
  await fetchReviews()
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>