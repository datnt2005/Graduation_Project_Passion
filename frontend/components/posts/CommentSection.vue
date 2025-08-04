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
        <div v-for="(media, i) in keptMedia" :key="'kept'+i" class="relative w-16 h-16 border rounded overflow-hidden group">
          <img v-if="media.type === 'image'" :src="media.url" class="w-full h-full object-cover" />
          <video v-else :src="media.url" class="w-full h-full object-cover" controls />
          <button type="button" class="absolute top-0 right-0 bg-black/60 text-white text-[10px] px-1 rounded"
            @click="removeKeptMedia(i)">×</button>
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
        :class="[
          filterStar === '' ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-gray-300',
          'px-3 py-1.5 border rounded hover:bg-gray-100 transition'
        ]">
        Tất cả
      </button>
      <button v-for="n in [5,4,3,2,1]" :key="n"
        @click="filterStar = n; currentPage = 1"
        :class="[
          filterStar === n ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-gray-300',
          'px-3 py-1.5 border rounded hover:bg-gray-100 transition'
        ]">
        {{ n }} Sao ({{ getStarCount(n) }})
      </button>
    </div>

    <!-- Danh sách bình luận -->
    <div v-for="c in paginatedComments" :key="c.id" class="bg-white rounded-md p-4 border text-sm text-gray-800 relative mb-4">
      <!-- Debug inline để kiểm tra -->
      <div class="flex items-start gap-3 mb-2">
        <!-- Avatar -->
        <div>
          <img v-if="c.user?.avatar" :src="c.user.avatar?.startsWith('http') ? c.user.avatar : `${mediaBase}${c.user.avatar}`" alt="avatar"
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
        <div v-if="props.currentUserId && c.user_id && String(c.user_id) === String(props.currentUserId)" class="relative ml-auto menu-wrapper">
          <button @click="toggleMenu(c.id)" class="p-1 hover:bg-gray-100 rounded">
            <i class="fas fa-ellipsis-h text-gray-500"></i>
          </button>
          <div v-if="showMenu === c.id"
            class="absolute right-0 mt-2 bg-white border rounded shadow-md text-xs z-10 min-w-[120px] overflow-hidden">
            <button @click="edit(c)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">✏️ Sửa</button>
            <button @click="confirmDelete(c.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">🗑️ Xoá</button>
          </div>
        </div>
      </div>
      <!-- Nội dung bình luận -->
      <div class="text-gray-700 whitespace-pre-line mb-2">{{ c.content }}</div>
      <!-- Media -->
      <div v-if="c.media && c.media.length" class="flex gap-2 flex-wrap mt-2">
        <template v-for="m in c.media" :key="m.id">
          <img v-if="m.type === 'image'" :src="m.url?.startsWith('http') ? m.url : `${mediaBase}${m.url}`" class="w-20 h-20 object-cover rounded border" />
          <video v-else-if="m.type === 'video'" :src="m.url?.startsWith('http') ? m.url : `${mediaBase}${m.url}`" controls class="w-28 h-20 rounded border" />
        </template>
      </div>
      <!-- Interaction -->
      <div class="flex items-center gap-4 text-gray-600 text-sm">
        <!-- Like -->
        <button @click="likeComment(c)" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1">
          <i :class="['fas fa-thumbs-up', commentLikes.get(c.id)?.isLiked ? 'text-blue-600' : 'text-gray-500']"></i>
          <span>{{ commentLikes.get(c.id)?.likeCount || 0 }}</span>
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
        class="mt-3 ml-6 border-l-4 border-[#1BA0E2] pl-4 py-2 bg-blue-50 rounded-md text-sm">
        <div class="flex items-center gap-2 mb-1">
          <i class="fas fa-user-shield text-[#1BA0E2]"></i>
          <span class="font-semibold text-[#1BA0E2]">Passion</span>
          <span class="text-gray-400 text-xs">• Đã phản hồi</span>
        </div>
        <p class="text-gray-700 leading-snug">{{ c.admin_reply }}</p>
      </div>
      <!-- User Replies -->
      <div v-if="c.replies && c.replies.length" class="mt-3 ml-6">
        <div v-for="reply in c.replies" :key="reply.id" class="border-l-2 border-gray-300 pl-4 py-2 mb-2">
          <!-- Debug inline cho reply -->
          <div class="flex items-start gap-3">
            <div>
              <img v-if="reply.user?.avatar" :src="reply.user.avatar?.startsWith('http') ? reply.user.avatar : `${mediaBase}${reply.user.avatar}`" alt="avatar"
                class="w-8 h-8 rounded-full object-cover border" />
              <div v-else class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold">
                {{ reply.user?.name?.charAt(0).toUpperCase() || 'U' }}
              </div>
            </div>
            <div class="flex-1">
              <p class="font-semibold text-sm">{{ reply.user?.name || 'Ẩn danh' }}</p>
              <p class="text-gray-700 text-sm whitespace-pre-line">{{ reply.content }}</p>
              <div class="flex items-center gap-2 text-xs text-gray-400 mt-1">
                <span>{{ formatDate(reply.created_at) }}</span>
              </div>
              <!-- Reply Media -->
              <div v-if="reply.media && reply.media.length" class="flex gap-2 flex-wrap mt-2">
                <template v-for="m in reply.media" :key="m.id">
                  <img v-if="m.type === 'image'" :src="m.url?.startsWith('http') ? m.url : `${mediaBase}${m.url}`" class="w-16 h-16 object-cover rounded border" />
                  <video v-else-if="m.type === 'video'" :src="m.url?.startsWith('http') ? m.url : `${mediaBase}${m.url}`" controls class="w-20 h-16 rounded border" />
                </template>
              </div>
              <!-- Like for Reply -->
              <div class="flex items-center gap-4 text-gray-600 text-sm mt-2">
                <button @click="likeComment(reply)" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1">
                  <i :class="['fas fa-thumbs-up', commentLikes.get(reply.id)?.isLiked ? 'text-blue-600' : 'text-gray-500']"></i>
                  <span>{{ commentLikes.get(reply.id)?.likeCount || 0 }}</span>
                </button>
              </div>
            </div>
            <!-- Menu ba chấm cho reply -->
            <div v-if="props.currentUserId && reply.user_id && String(reply.user_id) === String(props.currentUserId)" class="relative ml-auto menu-wrapper">
              <button @click="toggleMenu(reply.id)" class="p-1 hover:bg-gray-100 rounded">
                <i class="fas fa-ellipsis-h text-gray-500"></i>
              </button>
              <div v-if="showMenu === reply.id"
                class="absolute right-0 mt-2 bg-white border rounded shadow-md text-xs z-10 min-w-[120px] overflow-hidden">
                <button @click="edit(reply)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">✏️ Sửa</button>
                <button @click="confirmDelete(reply.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">🗑️ Xoá</button>
              </div>
            </div>
          </div>
        </div>
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

    <!-- Dialog xác nhận xóa -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showConfirmDialog" class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfirmDialog"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
            <div 
              class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            >
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg 
                      class="h-6 w-6 text-red-600" 
                      xmlns="http://www.w3.org/2000/svg" 
                      fill="none" 
                      viewBox="0 0 24 24" 
                      stroke="currentColor"
                    >
                      <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" 
                      />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                      {{ confirmDialogTitle }}
                    </h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        {{ confirmDialogMessage }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                  type="button"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="handleConfirmAction"
                >
                  Xác nhận
                </button>
                <button
                  type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="closeConfirmDialog"
                >
                  Hủy
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </section>
</template>

<script setup>
import { ref, onMounted, nextTick, computed, watch, onBeforeUnmount } from 'vue'
import { useToast } from '~/composables/useToast'
import { useRuntimeConfig } from '#app'

const { toast } = useToast()
const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

const props = defineProps({
  slug: {
    type: String,
    required: true
  },
  currentUserId: {
    type: [Number, String, null],
    default: null
  }
})

// State
const comments = ref([])
const commentContent = ref('')
const userRating = ref(0)
const editingId = ref(null)
const submitting = ref(false)
const averageRating = ref(0)
const showMenu = ref(null)
const showReplyForm = ref(null)
const replyContent = ref('')
const filterStar = ref('')
const currentPage = ref(1)
const itemsPerPage = 5
const postId = ref(null)
const commentLikes = ref(new Map())
const commentSection = ref(null)
const showConfirmDialog = ref(false)
const confirmDialogTitle = ref('')
const confirmDialogMessage = ref('')
const pendingDeleteId = ref(null)

// Media
const previewImages = ref([])
const rawImages = ref([])
const previewVideos = ref([])
const rawVideos = ref([])
const keptMedia = ref([])

// Debug
const debugInfo = ref({})

// Lấy postId từ slug
const fetchPostIdBySlug = async () => {
  if (!props.slug) {
    console.error('Không có slug được cung cấp', { time: new Date().toLocaleString('vi-VN') })
    toast('error', 'Không tìm thấy bài viết')
    return
  }
  try {
    const res = await $fetch(`${apiBase}/posts/slug/${props.slug}`, {
      headers: { 'Accept': 'application/json' },
      cache: 'no-store'
    })
    postId.value = res.data?.idz
    debugInfo.value.postId = postId.value
    if (!postId.value) {
      throw new Error('Không tìm thấy ID bài viết')
    }
  } catch (err) {
    console.error('Lỗi khi lấy postId từ slug:', err, { time: new Date().toLocaleString('vi-VN') })
    toast('error', 'Không thể tải bài viết')
    postId.value = null
  }
}

// Format date
const formatDate = (dateStr) => {
  if (!dateStr || typeof dateStr !== 'string') return '-'
  const date = new Date(dateStr)
  if (isNaN(date)) return '-'
  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}

// Load danh sách bình luận
const fetchComments = async () => {
  if (!postId.value) {
    console.warn('Không có postId để lấy bình luận', { time: new Date().toLocaleString('vi-VN') })
    return
  }
  try {
    const token = localStorage.getItem('access_token')
    debugInfo.value.token = !!token
    debugInfo.value.currentUserId = props.currentUserId
    const res = await $fetch(`${apiBase}/posts/${postId.value}/comments`, {
      headers: { 
        'Accept': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {})
      },
      cache: 'no-store'
    })
    comments.value = (res.data || []).map(c => ({
      ...c,
      user_id: c.user_id ? String(c.user_id) : null,
      isLiked: c.isLiked ?? false,
      likes_count: c.likes_count || 0,
      replies: (c.replies || []).map(r => ({
        ...r,
        user_id: r.user_id ? String(r.user_id) : null,
        isLiked: r.isLiked ?? false,
        likes_count: r.likes_count || 0
      }))
    }))
    debugInfo.value.comments = comments.value.map(c => ({
      id: c.id,
      user_id: c.user_id,
      isOwnComment: String(c.user_id) === String(props.currentUserId)
    }))

    // Khởi tạo trạng thái Like
    commentLikes.value.clear()
    if (token) {
      const processComments = async (commentList) => {
        for (const c of commentList) {
          try {
            const res = await $fetch(`${apiBase}/posts/${postId.value}/comments/${c.id}/liked`, {
              headers: { Authorization: `Bearer ${token}` },
              cache: 'no-store'
            })
            commentLikes.value.set(c.id, {
              isLiked: res.liked ?? false,
              likeCount: c.likes_count
            })
            if (c.replies && c.replies.length) {
              await processComments(c.replies)
            }
          } catch (err) {
            console.error(`Lỗi khi kiểm tra trạng thái Like cho bình luận ${c.id}:`, err, { time: new Date().toLocaleString('vi-VN') })
            commentLikes.value.set(c.id, { isLiked: false, likeCount: c.likes_count })
          }
        }
      }
      await processComments(comments.value)
    } else {
      const setDefaultLikes = (commentList) => {
        commentList.forEach(c => {
          commentLikes.value.set(c.id, { isLiked: false, likeCount: c.likes_count })
          if (c.replies && c.replies.length) {
            setDefaultLikes(c.replies)
          }
        })
      }
      setDefaultLikes(comments.value)
    }
    // Tính trung bình rating
    if (comments.value.length) {
      const sum = comments.value.reduce((acc, c) => acc + (c.rating || 0), 0)
      averageRating.value = sum / comments.value.length
    } else {
      averageRating.value = 0
    }
  } catch (err) {
    console.error('Lỗi khi lấy bình luận:', err, { time: new Date().toLocaleString('vi-VN') })
    comments.value = []
    averageRating.value = 0
    toast('error', 'Không thể tải bình luận')
  }
}

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
  keptMedia.value = []
}

// Sửa bình luận
const edit = (c) => {
  editingId.value = c.id
  commentContent.value = c.content
  userRating.value = c.rating || 0
  resetMedia()
  keptMedia.value = c.media ? c.media.map(m => ({ id: m.id, type: m.type, url: m.url })) : []
}

// Submit bình luận
const submitComment = async () => {
  if (!postId.value) {
    toast('error', 'Không tìm thấy bài viết')
    return
  }
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
      url = `${apiBase}/posts/${postId.value}/comments/${editingId.value}`
      method = 'POST'
      body = new FormData()
      body.append('content', commentContent.value)
      body.append('rating', userRating.value)
      body.append('_method', 'PUT')
      keptMedia.value.forEach((media, i) => body.append(`kept_images[${i}]`, media.id))
      rawImages.value.forEach((file, i) => body.append(`images[${i}]`, file))
      rawVideos.value.forEach((file, i) => body.append(`videos[${i}]`, file))
      headers = { Authorization: `Bearer ${token}` }
    } else {
      url = `${apiBase}/posts/${postId.value}/comments`
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
    toast('success', editingId.value ? 'Đã cập nhật bình luận' : 'Đã gửi bình luận')
    await fetchComments()
  } catch (err) {
    console.error('Lỗi khi gửi bình luận:', err, { time: new Date().toLocaleString('vi-VN') })
    toast('error', err?.data?.message || err?.message || 'Lỗi gửi bình luận')
  } finally {
    submitting.value = false
  }
}

// Xóa bình luận
const confirmDelete = (id) => {
  pendingDeleteId.value = id
  confirmDialogTitle.value = 'Xóa bình luận'
  confirmDialogMessage.value = 'Bạn có chắc chắn muốn xóa bình luận này? Hành động này không thể hoàn tác.'
  showConfirmDialog.value = true
  showMenu.value = null
}

const handleConfirmAction = async () => {
  if (!postId.value || !pendingDeleteId.value) {
    console.error('Invalid postId or commentId', { postId: postId.value, commentId: pendingDeleteId.value, time: new Date().toLocaleString('vi-VN') })
    toast('error', 'Không tìm thấy bài viết hoặc bình luận')
    closeConfirmDialog()
    return
  }
  const token = localStorage.getItem('access_token')
  if (!token) {
    console.error('No access token found', { time: new Date().toLocaleString('vi-VN') })
    toast('error', 'Bạn cần đăng nhập để xóa bình luận')
    closeConfirmDialog()
    return
  }
  try {
    const response = await $fetch(`${apiBase}/posts/${postId.value}/comments/${pendingDeleteId.value}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token}` },
      cache: 'no-store'
    })
    await fetchComments()
    toast('success', 'Đã xóa bình luận')    
  } catch (err) {
    console.error('Lỗi khi xóa bình luận:', err, { status: err.status, response: err.data, time: new Date().toLocaleString('vi-VN') })
    if (err.status === 404) {
      toast('error', 'Bình luận không tồn tại hoặc đã bị xóa')
    } else if (err.status === 403) {
      toast('error', 'Bạn không có quyền xóa bình luận này')
    } else {
      toast('error', err?.data?.message || err?.message || 'Không thể xóa bình luận')
    }
  } finally {
    closeConfirmDialog()
  }
}

const closeConfirmDialog = () => {
  showConfirmDialog.value = false
  confirmDialogTitle.value = ''
  confirmDialogMessage.value = ''
  pendingDeleteId.value = null
}

// Toggle menu ba chấm
const toggleMenu = (id) => {
  showMenu.value = showMenu.value === id ? null : id
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.menu-wrapper')) {
    showMenu.value = null
  }
}

// Trả lời
const toggleReplyForm = (id) => {
  showReplyForm.value = showReplyForm.value === id ? null : id
  replyContent.value = ''
}

const submitReply = async (comment) => {
  if (!postId.value) {
    toast('error', 'Không tìm thấy bài viết')
    return
  }
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
    const res = await $fetch(`${apiBase}/posts/${postId.value}/comments/${comment.id}/reply`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
      body: JSON.stringify({ content: replyContent.value }),
      cache: 'no-store'
    })
    toast('success', 'Đã gửi trả lời!')
    showReplyForm.value = null
    replyContent.value = ''
    await fetchComments()
  } catch (err) {
    console.error('Lỗi khi gửi trả lời:', err, { time: new Date().toLocaleString('vi-VN') })
    toast('error', err?.data?.message || err?.message || 'Lỗi khi gửi trả lời')
  }
}

// Like bình luận
const likeComment = async (comment) => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    toast('info', 'Vui lòng đăng nhập để thích bình luận.')
    return
  }
  if (!postId.value) {
    toast('error', 'Không tìm thấy bài viết')
    return
  }

  try {
    const isCurrentlyLiked = commentLikes.value.get(comment.id)?.isLiked || false
    const url = `${apiBase}/posts/${postId.value}/comments/${comment.id}/${isCurrentlyLiked ? 'unlike' : 'like'}`
    const res = await $fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
      cache: 'no-store'
    })
    if (!res || !res.hasOwnProperty('likes')) {
      throw new Error('Phản hồi API không hợp lệ hoặc thiếu trường likes')
    }
    commentLikes.value.set(comment.id, {
      isLiked: !isCurrentlyLiked,
      likeCount: res.likes
    })
    const updateCommentLikes = (commentList) => {
      for (let c of commentList) {
        if (c.id === comment.id) {
          c.likes_count = res.likes
        }
        if (c.replies && c.replies.length) {
          updateCommentLikes(c.replies)
        }
      }
    }
    updateCommentLikes(comments.value)
  } catch (err) {
    console.error('Lỗi khi like/unlike:', err, { time: new Date().toLocaleString('vi-VN') })
    toast('error', err?.data?.message || err?.message || 'Lỗi khi thích bình luận')
  }
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

// Media handler
const onImageChange = (e) => {
  const files = Array.from(e.target.files).filter(f => f.type.startsWith('image/'))
  if (files.length + rawImages.value.length + keptMedia.value.filter(m => m.type === 'image').length > 5) {
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

const onVideoChange = (e) => {
  const files = Array.from(e.target.files).filter(f => f.type.startsWith('video/'))
  if (files.length + rawVideos.value.length + keptMedia.value.filter(m => m.type === 'video').length > 5) {
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

const removeKeptMedia = (idx) => {
  keptMedia.value.splice(idx, 1)
}

onMounted(async () => {
  document.addEventListener('click', handleClickOutside)
  await fetchPostIdBySlug()
  if (postId.value) {
    await fetchComments()
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

watch(() => props.slug, async (newSlug, oldSlug) => {
  if (newSlug !== oldSlug) {
    await fetchPostIdBySlug()
    if (postId.value) {
      await fetchComments()
    }
  }
})

watch(() => props.currentUserId, async (newId, oldId) => {
  if (newId !== oldId && postId.value) {
    await fetchComments()
  }
})
</script>

<style scoped>
.menu-wrapper { 
  position: relative; 
  visibility: visible !important; 
  display: block !important; 
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter, .fade-leave-to { opacity: 0; }
textarea:focus, input:focus { outline: none; box-shadow: 0 0 0 2px #3b82f6; }
</style>