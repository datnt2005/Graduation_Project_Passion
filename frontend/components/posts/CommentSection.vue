<template>
  <section class="mt-12" ref="commentSection">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Bình luận & Đánh giá</h2>
    <div class="flex items-center gap-2 mb-4">
      <span class="font-semibold text-gray-700">Trung bình:</span>
      <span class="text-yellow-500 font-bold text-2xl">{{ averageRating.toFixed(1) }}</span>
      <div class="flex items-center text-yellow-400 text-lg">
        <template v-for="i in 5" :key="i">
          <i v-if="i <= Math.floor(averageRating)" class="fas fa-star"></i>
          <i v-else-if="i - averageRating <= 0.5" class="fas fa-star-half-alt"></i>
          <i v-else class="far fa-star text-gray-300"></i>
        </template>
      </div>
      <span class="ml-2 text-xs text-gray-500">({{ comments.length }} đánh giá)</span>
    </div>

    <!-- Form đánh giá -->
    <form @submit.prevent="submitComment" class="bg-white border rounded p-4 mb-6 space-y-3">
      <div class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">Chọn sao:</span>
        <span v-for="star in 5" :key="star" class="cursor-pointer text-xl" @click="userRating = star">
          <span :class="userRating >= star ? 'text-yellow-500' : 'text-gray-300'">★</span>
        </span>
        <span class="text-xs ml-2 text-gray-500">({{ userRating }} / 5)</span>
      </div>

      <!-- Thêm vào trong <form> trước textarea -->
      <div class="flex gap-2 mb-2">
        <label class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded bg-white cursor-pointer hover:bg-gray-50 text-xs">
          <i class="fas fa-camera"></i>
          <span>Chọn ảnh</span>
          <input type="file" accept="image/*" multiple class="hidden" @change="onImageChange" />
        </label>
        <label class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded bg-white cursor-pointer hover:bg-gray-50 text-xs">
          <i class="fas fa-video"></i>
          <span>Chọn video</span>
          <input type="file" accept="video/*" multiple class="hidden" @change="onVideoChange" />
        </label>
      </div>
      <!-- Hiển thị preview -->
      <div class="flex gap-2 flex-wrap mb-2">
        <div v-for="(img, i) in previewImages" :key="'img'+i" class="relative w-16 h-16 border rounded overflow-hidden group">
          <img :src="img" class="w-full h-full object-cover" />
          <button type="button" class="absolute top-0 right-0 bg-black/60 text-white text-[10px] px-1 rounded"
            @click="removeImage(i)">×</button>
        </div>
        <div v-for="(vid, i) in previewVideos" :key="'vid'+i" class="relative w-16 h-16 border rounded overflow-hidden group">
          <video :src="vid" class="w-full h-full object-cover" controls />
          <button type="button" class="absolute top-0 right-0 bg-black/60 text-white text-[10px] px-1 rounded"
            @click="removeVideo(i)">×</button>
        </div>
      </div>

      <textarea v-model="commentContent" rows="3" class="w-full border rounded p-2 focus:ring" required placeholder="Nhập bình luận..."></textarea>
      <div class="flex justify-end">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded" :disabled="submitting">
          {{ editingId ? 'Cập nhật' : 'Gửi' }}
        </button>
        <button v-if="editingId" type="button" @click="cancelEdit" class="ml-2 text-xs text-gray-500 hover:text-red-500">Huỷ</button>
      </div>
    </form>

    <!-- Bộ lọc -->
    <div class="flex flex-wrap gap-2 my-4 items-center text-sm font-medium text-gray-700">
      <button @click="filterStar = ''; currentPage = 1"
        :class=" [
          filterStar === '' ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-gray-300',
          'px-3 py-1.5 border rounded hover:bg-gray-100 transition'
        ]">
        Tất cả
      </button>
      <button v-for="n in [5,4,3,2,1]" :key="n"
        @click="filterStar = n; currentPage = 1"
        :class=" [
          filterStar === n ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-gray-300',
          'px-3 py-1.5 border rounded hover:bg-gray-100 transition'
        ]">
        {{ n }} Sao ({{ getStarCount(n) }})
      </button>
    </div>

    <!-- Danh sách bình luận -->
    <div v-for="c in paginatedComments" :key="c.id" class=" bg-white rounded-md p-4 border text-sm text-gray-800 relative mb-4">
      <!-- Report Dialog -->
      <ReportDialog v-if="showReportDialog === c.id" :target-id="c.id" type="post_comment" @close="showReportDialog = null" />

      <div class="flex items-start gap-3 mb-2">
        <!-- Avatar -->
        <div>
          <img v-if="c.user?.avatar" :src="c.user.avatar" alt="avatar"
            class="w-10 h-10 rounded-full object-cover border" />
          <div v-else class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold">
            {{ c.user?.name?.charAt(0).toUpperCase() || 'U' }}
          </div>
        </div>
        <!-- Info -->
        <div class="flex-1">
          <p class="font-semibold text-base">{{ c.user?.name || 'Ẩn danh' }}</p>
          <div class="flex items-center gap-2 text-xs text-gray-400">
            <span>{{ formatDate(c.created_at) }}</span>
            <span v-if="c.rating" class="flex items-center ml-2">
              <span v-for="star in 5" :key="star" class="text-yellow-400 text-xs">
                <i :class="star <= c.rating ? 'fas fa-star' : 'far fa-star text-gray-300'"></i>
              </span>
            </span>
          </div>
        </div>
        <!-- Menu ba chấm -->
        <div class="relative ml-auto menu-wrapper">
          <button @click="toggleMenu(c.id)" class="p-1 hover:bg-gray-100 rounded">
            <i class="fas fa-ellipsis-h text-gray-500"></i>
          </button>
          <div v-if="showMenu === c.id"
            class="absolute right-0 mt-2 bg-white border rounded shadow-md text-xs z-10 min-w-[120px] overflow-hidden">
            <template v-if="c.user_id === currentUserId">
              <button @click="edit(c)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">✏️ Sửa</button>
              <button @click="deleteComment(c.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">🗑️ Xoá</button>
            </template>
            <template v-else>
              <button @click="handleReport(c.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-orange-600">🚩 Báo cáo</button>
            </template>
          </div>
        </div>
      </div>
      <!-- Nội dung hoặc form chỉnh sửa -->
      <div v-if="editingId === c.id" class="mt-2">
        <textarea
          v-model="commentContent"
          rows="2"
          class="w-full border rounded p-2 mb-2"
          placeholder="Nhập nội dung bình luận..."
          required
        ></textarea>
        <button
          @click="submitComment"
          class="bg-blue-500 text-white px-2 py-1 rounded mr-2"
        >
          Lưu
        </button>
        <button
          @click="cancelEdit"
          class="text-gray-500 px-2 py-1"
        >
          Hủy
        </button>
      </div>
      <div v-else>
        <div class="text-gray-700 whitespace-pre-line mb-2">{{ c.content }}</div>
      </div>
      <!-- Interaction -->
      <div class="flex items-center gap-4 text-gray-600 text-sm">
        <!-- Like -->
        <button @click="likeComment(c)" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1">
          <i :class="['fas fa-thumbs-up', c.isLiked ? 'text-blue-600' : 'text-gray-500']"></i>
          <span>{{ c.likes_count || 0 }}</span>
        </button>
        <!-- Reply -->
        <button class="flex items-center gap-1 hover:text-blue-600 transition" @click="toggleReplyForm(c.id)">
          <i class="fas fa-comment"></i>
          <span>Trả lời</span>
        </button>
      </div>
      <!-- Reply Form -->
      <transition name="fade">
        <div v-if="showReplyForm === c.id" class="flex items-start border border-blue-500 p-2 rounded-md mt-2">
          <div class="text-2xl mr-2 text-gray-400">😊</div>
          <div class="flex-1 relative">
            <input type="text" v-model="replyContent" class="w-full border border-blue-500 rounded-full px-4 py-2 pr-10 outline-none"
              placeholder="Viết câu trả lời" />
            <button class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
              @click="submitReply(c)">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </transition>
      <!-- Admin Reply -->
      <div v-if="c.admin_reply"
        class="mt-3 ml-6 border-l-4 border-[#1BA0E2] pl-4 py-2 bg-blue-50 rounded-md text-sm m-5">
        <div class="flex items-center gap-2 mb-1">
          <i class="fas fa-user-shield text-[#1BA0E2]"></i>
          <span class="font-semibold text-[#1BA0E2]">Passion</span>
          <span class="text-gray-400 text-xs">• Đã phản hồi</span>
        </div>
        <p class="text-gray-700 leading-snug">{{ c.admin_reply }}</p>
      </div>

      <!-- Trong danh sách bình luận, sau phần nội dung -->
      <div v-if="c.media && c.media.length" class="flex gap-2 flex-wrap mt-2">
        <template v-for="m in c.media" :key="m.id">
          <img v-if="m.type === 'image'" :src="m.url" class="w-20 h-20 object-cover rounded border" />
          <video v-else-if="m.type === 'video'" :src="m.url" controls class="w-28 h-20 rounded border" />
        </template>
      </div>
    </div>

    <!-- Pagination -->
    <nav v-if="totalPages > 1" class="mt-6 flex justify-center items-center gap-3 text-xs text-gray-700 select-none">
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
import { ref, onMounted, nextTick, computed } from 'vue'
import { useToast } from '~/composables/useToast'
import { useRuntimeConfig } from '#app'
import ReportDialog from '~/components/shared/ReportDialog.vue'

const props = defineProps({
  postId: Number,
  currentUserId: Number
})

const { toast } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl

// State
const comments = ref([])
const commentContent = ref('')
const userRating = ref(0)
const editingId = ref(null)
const submitting = ref(false)
const averageRating = ref(0)
const showMenu = ref(null)
const showReportDialog = ref(null)
const showReplyForm = ref(null)
const replyContent = ref('')
const filterStar = ref('')
const currentPage = ref(1)
const itemsPerPage = 5

// Media quản lý file gốc & preview
const previewImages = ref([])
const rawImages = ref([])
const previewVideos = ref([])
const rawVideos = ref([])

// Load danh sách bình luận
const fetchComments = async () => {
  try {
    const res = await $fetch(`${apiBase}/posts/${props.postId}/comments`)
    comments.value = (res.data || []).map(c => ({
      ...c,
      isLiked: c.isLiked || false
    }))
    if (comments.value.length) {
      const sum = comments.value.reduce((acc, c) => acc + (c.rating || 0), 0)
      averageRating.value = sum / comments.value.length
    } else averageRating.value = 0
    checkAllLiked()
  } catch {
    comments.value = []
    averageRating.value = 0
  }
}

const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('vi-VN')

// Reset form
const cancelEdit = () => {
  editingId.value = null
  commentContent.value = ''
  userRating.value = 0
  resetMedia()
}
const resetMedia = () => {
  previewImages.value = []
  rawImages.value = []
  previewVideos.value = []
  rawVideos.value = []
}

// Khi sửa bình luận
const edit = (c) => {
  editingId.value = c.id
  commentContent.value = c.content
  userRating.value = c.rating || 0
  resetMedia()
}

// Submit bình luận (tạo mới hoặc cập nhật)
const submitComment = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    toast('error', 'Bạn cần đăng nhập để gửi bình luận')
    return
  }
  if (!commentContent.value.trim()) {
    toast('error', 'Vui lòng nhập nội dung')
    return
  }

  submitting.value = true
  try {
    let url, method, body, headers
    if (editingId.value) {
      url = `${apiBase}/posts/${props.postId}/comments/${editingId.value}`
      method = 'PUT'
      body = new FormData()
      body.append('content', commentContent.value)
      body.append('rating', userRating.value)
      rawImages.value.forEach((file, i) => body.append(`images[${i}]`, file))
      rawVideos.value.forEach((file, i) => body.append(`videos[${i}]`, file))
      headers = { Authorization: `Bearer ${token}` }
    } else {
      url = `${apiBase}/posts/${props.postId}/comments`
      method = 'POST'
      body = new FormData()
      body.append('content', commentContent.value)
      body.append('rating', userRating.value)
      rawImages.value.forEach((file, i) => body.append(`images[${i}]`, file))
      rawVideos.value.forEach((file, i) => body.append(`videos[${i}]`, file))
      headers = { Authorization: `Bearer ${token}` }
    }

    const res = await $fetch(url, { method, headers, body })
    cancelEdit()
    await fetchComments()
    toast('success', editingId.value ? 'Đã cập nhật' : 'Đã gửi bình luận')
  } catch (err) {
    toast('error', err?.data?.message || err?.message || 'Lỗi gửi bình luận')
  } finally {
    submitting.value = false
  }
}

// Xoá bình luận
const deleteComment = async (id) => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    toast('error', 'Bạn cần đăng nhập để xoá bình luận')
    return
  }
  if (!confirm('Xoá bình luận?')) return
  try {
    await $fetch(`${apiBase}/posts/${props.postId}/comments/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token}` }
    })
    await fetchComments()
    toast('success', 'Đã xoá')
  } catch {
    toast('error', 'Không thể xoá')
  }
}

// Hiện/tắt menu 3 chấm
const toggleMenu = (id) => {
  showMenu.value = showMenu.value === id ? null : id
  document.addEventListener('click', handleClickOutside)
}
const handleClickOutside = (event) => {
  if (!event.target.closest('.menu-wrapper')) {
    showMenu.value = null
    document.removeEventListener('click', handleClickOutside)
  }
}

// Báo cáo
const handleReport = (id) => {
  const comment = comments.value.find(c => c.id === id)
  if (comment && comment.user.id === props.currentUserId) {
    toast('error', 'Bạn không thể báo cáo bình luận của chính mình')
    return
  }
  showReportDialog.value = id
  showMenu.value = null
}

// Trả lời
const toggleReplyForm = (id) => {
  showReplyForm.value = showReplyForm.value === id ? null : id
  replyContent.value = ''
}
const submitReply = async (comment) => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    toast('info', 'Vui lòng đăng nhập để trả lời.')
    return
  }
  if (!replyContent.value.trim()) {
    toast('error', 'Vui lòng nhập nội dung trả lời.')
    return
  }
  try {
    const res = await fetch(`${apiBase}/posts/${props.postId}/comments/${comment.id}/reply`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
      body: JSON.stringify({ content: replyContent.value }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Đã xảy ra lỗi khi trả lời.')
    toast('success', 'Đã gửi trả lời!')
    showReplyForm.value = null
    replyContent.value = ''
    await fetchComments()
  } catch (err) {
    toast('error', err.message || 'Chỉ tác giả hoặc quản trị viên mới được trả lời.')
  }
}

// Like bình luận
const likeComment = async (comment) => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    toast('info', 'Vui lòng đăng nhập để thích bình luận.')
    return
  }
  try {
    const url = `${apiBase}/posts/${props.postId}/comments/${comment.id}/${comment.isLiked ? 'unlike' : 'like'}`
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` }
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message)
    comment.isLiked = !comment.isLiked
    comment.likes_count = data.likes // Cập nhật số lượt like từ API
  } catch (err) {
    toast('error', err.message)
  }
}

// Kiểm tra like cho tất cả
const checkAllLiked = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) return
  await Promise.all(comments.value.map(async (c) => {
    try {
      const res = await fetch(`${apiBase}/posts/${props.postId}/comments/${c.id}/liked`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      const data = await res.json()
      c.isLiked = data.liked
    } catch {}
  }))
}

// Bộ lọc, phân trang
const getStarCount = (star) => comments.value.filter(c => c.rating === star).length

const filteredComments = computed(() => {
  let list = [...comments.value]
  if (filterStar.value) list = list.filter(c => c.rating === Number(filterStar.value))
  return list
})

const totalPages = computed(() => Math.ceil(filteredComments.value.length / itemsPerPage))
const paginatedComments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredComments.value.slice(start, start + itemsPerPage)
})

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    nextTick(() => commentSection.value?.scrollIntoView({ behavior: 'smooth' }))
  }
}

// --- MEDIA HANDLER ---
// Ảnh
const onImageChange = (e) => {
  const files = Array.from(e.target.files).filter(f => f.type.startsWith('image/'))
  if (files.length + rawImages.value.length > 5) {
    toast('error', 'Tối đa 5 ảnh')
    return
  }
  files.forEach(img => {
    rawImages.value.push(img)
    const reader = new FileReader()
    reader.onload = (ev) => previewImages.value.push(ev.target.result)
    reader.readAsDataURL(img)
  })
}
const removeImage = (idx) => {
  previewImages.value.splice(idx, 1)
  rawImages.value.splice(idx, 1)
}
// Video
const onVideoChange = (e) => {
  const files = Array.from(e.target.files).filter(f => f.type.startsWith('video/'))
  if (files.length + rawVideos.value.length > 5) {
    toast('error', 'Tối đa 5 video')
    return
  }
  files.forEach(vid => {
    rawVideos.value.push(vid)
    const reader = new FileReader()
    reader.onload = (ev) => previewVideos.value.push(ev.target.result)
    reader.readAsDataURL(vid)
  })
}
const removeVideo = (idx) => {
  previewVideos.value.splice(idx, 1)
  rawVideos.value.splice(idx, 1)
}

onMounted(() => fetchComments())
</script>


<style scoped>
.menu-wrapper { position: relative; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter, .fade-leave-to { opacity: 0; }
textarea:focus { outline: none; box-shadow: 0 0 0 2px #3b82f6; }
</style>
