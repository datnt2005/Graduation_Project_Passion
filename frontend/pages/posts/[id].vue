<template>
  <main class="flex-1 p-6 sm:p-10 bg-white max-w-6xl mx-auto rounded-lg shadow flex flex-col md:flex-row gap-8">
    <!-- Main content -->
    <div class="flex-1 min-w-0">
      <div v-if="loading" class="text-center text-gray-400 py-16 text-lg flex flex-col items-center gap-2">
        <span class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mb-2"></span>
        Đang tải bài viết...
      </div>
      <div v-else-if="!post" class="text-center text-gray-500 py-16 text-lg">Không tìm thấy bài viết.</div>
      <div v-else>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4 leading-tight">{{ post.title }}</h1>
        <div class="flex flex-wrap gap-4 text-xs text-gray-500 mb-6 items-center">
          <span class="flex items-center gap-1">
            <i class="i-mdi-account-circle-outline text-base"></i>
            {{ post.user?.name || '---' }}
          </span>
          <span class="flex items-center gap-1">
            <i class="i-mdi-calendar-month-outline text-base"></i>
            {{ formatDate(post.created_at) }}
          </span>
          <span class="flex items-center gap-1">
            <i class="i-mdi-eye-outline text-base"></i>
            {{ post.views || 0 }} lượt xem
          </span>
          <span v-if="post.category?.name" class="flex items-center gap-1">
            <i class="i-mdi-tag-outline text-base"></i>
            {{ post.category.name }}
          </span>
        </div>
        <img
          v-if="post.thumbnail_url"
          :src="post.thumbnail_url"
          alt="Thumbnail"
          class="w-full max-h-96 object-cover rounded-lg mb-8 shadow"
        />
        <div class="prose prose-blue max-w-none mb-8 text-base leading-relaxed" v-html="post.content"></div>
        <div v-if="post.tags && post.tags.length" class="mt-6 flex flex-wrap items-center gap-2">
          <span class="font-semibold text-gray-700">Tags:</span>
          <span
            v-for="tag in post.tags"
            :key="tag"
            class="inline-block bg-blue-100 text-blue-700 rounded px-2 py-1 text-xs"
          >
            #{{ tag }}
          </span>
        </div>

        <!-- Comment & Rating Section -->
        <div class="mt-12">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Bình luận & Đánh giá</h2>
          <!-- Rating -->
          <div class="flex items-center gap-2 mb-4">
            <span class="font-semibold text-gray-700">Đánh giá của bạn:</span>
            <template v-for="star in 5" :key="star">
              <button
                @click="userRating = star"
                class="focus:outline-none"
                :aria-label="`Đánh giá ${star} sao`"
              >
                <svg
                  :class="userRating >= star ? 'text-yellow-400' : 'text-gray-300'"
                  class="w-7 h-7 inline-block"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.454a1 1 0 00-1.175 0l-3.38 2.454c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z"/>
                </svg>
              </button>
            </template>
            <span v-if="averageRating" class="ml-4 text-sm text-gray-500">(Trung bình: {{ averageRating.toFixed(1) }}/5)</span>
          </div>
          <!-- Comment Form -->
          <form @submit.prevent="submitComment" class="mb-8" enctype="multipart/form-data">
            <textarea
              v-model="commentContent"
              rows="3"
              class="w-full border rounded p-2 mb-2 focus:ring focus:border-blue-400"
              placeholder="Nhập bình luận của bạn..."
              required
            ></textarea>
            <input type="file" @change="onImageChange" accept="image/*" class="mb-2" />
            <button
              type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
              :disabled="submitting"
            >
              Gửi bình luận
            </button>
          </form>
          <!-- Comment List -->
          <div v-if="comments.length" class="space-y-6">
            <div v-for="c in comments" :key="c.id" class="border-b pb-4">
              <div class="flex items-center gap-2 mb-1">
                <span class="font-semibold text-gray-800">{{ c.user_name || 'Ẩn danh' }}</span>
                <span class="text-xs text-gray-400">{{ formatDate(c.created_at) }}</span>
                <span v-if="c.rating" class="flex items-center ml-2">
                  <svg v-for="star in 5" :key="star" class="w-4 h-4" fill="currentColor"
                    :class="star <= c.rating ? 'text-yellow-400' : 'text-gray-300'" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.454a1 1 0 00-1.175 0l-3.38 2.454c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z"/>
                  </svg>
                </span>
              </div>
              <div class="text-gray-700 whitespace-pre-line">{{ c.content }}</div>
              <img v-if="c.image_url" :src="c.image_url" class="mt-2 max-w-xs rounded shadow" />
              <div v-if="c.admin_reply" class="mt-2 p-2 bg-gray-50 border-l-4 border-blue-400 text-sm text-gray-700">
                <span class="font-semibold text-blue-700">Phản hồi từ quản trị viên:</span> {{ c.admin_reply }}
              </div>
              <div v-if="editingCommentId === c.id" class="mt-2">
                <textarea
                  v-model="editContent"
                  rows="2"
                  class="w-full border rounded p-2 mb-2"
                  placeholder="Nhập nội dung bình luận..."
                  required
                ></textarea>
                <button
                  @click="saveEditComment(c)"
                  class="bg-blue-500 text-white px-2 py-1 rounded mr-2"
                >
                  Lưu
                </button>
                <button
                  @click="editingCommentId = null"
                  class="text-gray-500 px-2 py-1"
                >
                  Hủy
                </button>
              </div>
              <div v-else>
                <button
                  v-if="c.user_id === currentUserId"
                  @click="startEditComment(c)"
                  class="text-blue-500 text-xs mt-2 hover:underline mr-2"
                >Sửa</button>
                <button
                  v-if="c.user_id === currentUserId"
                  @click="deleteComment(c.id)"
                  class="text-red-500 text-xs mt-2 hover:underline"
                >Xóa</button>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-400">Chưa có bình luận nào.</div>
        </div>
        <!-- End Comment & Rating Section -->
      </div>
    </div>
    <!-- Related posts sidebar -->
    <aside class="w-full md:w-80 flex-shrink-0">
      <div class="bg-gray-50 rounded-lg shadow p-4">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Bài viết liên quan</h2>
        <div v-if="relatedLoading" class="text-gray-400 text-sm">Đang tải...</div>
        <div v-else-if="!relatedPosts.length" class="text-gray-400 text-sm">Không có bài viết liên quan.</div>
        <div v-else class="space-y-4">
          <NuxtLink
            v-for="item in relatedPosts"
            :key="item.id"
            :to="`/posts/${item.id}`"
            class="flex gap-3 items-center hover:bg-blue-50 rounded transition p-2 -m-2"
          >
            <img
              v-if="item.thumbnail_url"
              :src="item.thumbnail_url"
              alt="thumb"
              class="w-16 h-12 object-cover rounded"
            />
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-gray-800 text-sm truncate">{{ item.title }}</div>
              <div class="text-xs text-gray-500 truncate">{{ formatDate(item.created_at) }}</div>
            </div>
          </NuxtLink>
        </div>
      </div>
    </aside>
  </main>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const post = ref(null)
const loading = ref(true)

const relatedPosts = ref([])
const relatedLoading = ref(true)

// Bình luận & đánh giá
const comments = ref([])
const commentContent = ref('')
const userRating = ref(0)
const submitting = ref(false)
const averageRating = ref(0)
const commentImage = ref(null)
const currentUserId = ref(2) // hoặc lấy từ auth
const editingCommentId = ref(null)
const editContent = ref('')

const fetchComments = async () => {
  try {
    const res = await $fetch(`http://localhost:8000/api/posts/${route.params.id}/comments`)
    comments.value = res.data || []
    // Tính điểm trung bình
    if (comments.value.length) {
      const sum = comments.value.reduce((acc, c) => acc + (c.rating || 0), 0)
      averageRating.value = sum / comments.value.length
    } else {
      averageRating.value = 0
    }
  } catch {
    comments.value = []
    averageRating.value = 0
  }
}

function onImageChange(e) {
  commentImage.value = e.target.files[0]
}

const submitComment = async () => {
  if (!commentContent.value.trim()) return
  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('content', commentContent.value)
    formData.append('rating', userRating.value)
    formData.append('post_id', post.value.id)
    if (commentImage.value) formData.append('image', commentImage.value)
    await $fetch(`http://localhost:8000/api/post-comments`, {
      method: 'POST',
      body: formData
    })
    commentContent.value = ''
    userRating.value = 0
    commentImage.value = null
    await fetchComments()
  } finally {
    submitting.value = false
  }
}

const deleteComment = async (id) => {
  if (!confirm('Bạn chắc chắn muốn xóa bình luận này?')) return
  await $fetch(`http://localhost:8000/api/post-comments/${id}`, { method: 'DELETE' })
  await fetchComments()
}

const startEditComment = (comment) => {
  editingCommentId.value = comment.id
  editContent.value = comment.content
}

const saveEditComment = async (comment) => {
  if (!editContent.value.trim()) return
  await $fetch(`http://localhost:8000/api/post-comments/${comment.id}`, {
    method: 'PATCH',
    body: { content: editContent.value }
  })
  editingCommentId.value = null
  editContent.value = ''
  await fetchComments()
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

const fetchPost = async () => {
  loading.value = true
  try {
    const res = await $fetch(`http://localhost:8000/api/posts/${route.params.id}`)
    post.value = res.data
  } catch (e) {
    post.value = null
  } finally {
    loading.value = false
  }
}

const fetchRelatedPosts = async () => {
  relatedLoading.value = true
  try {
    // Lấy các bài viết cùng chuyên mục, loại trừ bài hiện tại
    let url = 'http://localhost:8000/api/posts?status=published'
    if (post.value?.category?.id) {
      url += `&category_id=${post.value.category.id}`
    }
    const res = await $fetch(url)
    relatedPosts.value = res.data.filter(p => p.id !== post.value.id) || []
  } catch {
    relatedPosts.value = []
  } finally {
    relatedLoading.value = false
  }
}

onMounted(() => {
  fetchPost()
  fetchComments()
  fetchRelatedPosts()
})

watch(route.params, () => {
  fetchPost()
  fetchComments()
  fetchRelatedPosts()
})
</script>

<style scoped>
.prose {
  max-width: 100%;
}
</style>