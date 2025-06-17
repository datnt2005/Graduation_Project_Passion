<template>
  <div class="bg-white rounded-md p-4 border text-sm text-gray-800 relative">

    <!-- Header -->
    <div class="flex items-start gap-3 mb-3">
      <!-- Avatar -->
      <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold">
        {{ review.user.charAt(0).toUpperCase() }}
      </div>

      <!-- Info -->
      <div class="flex-1">
        <p class="font-semibold text-base">{{ review.user }}</p>
      </div>

      <!-- Ellipsis Menu -->
      <div class="relative ml-auto">
        <button @click="toggleMenu" class="p-1 hover:bg-gray-100 rounded">
          <i class="fas fa-ellipsis-h text-gray-500"></i>
        </button>
        <div v-if="showMenu"
          class="absolute right-0 mt-2 bg-white border rounded shadow-md text-xs z-10 min-w-[100px] overflow-hidden">
         <!-- Đừng truyền review.id nếu bạn cần edit full -->
<button @click="$emit('edit-review', review)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">
  ✏️ Sửa
</button>

          <button @click="$emit('delete-review', review.id)"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">
            🗑️ Xoá
          </button>
        </div>
      </div>
    </div>

    <!-- Stars -->
    <div class="flex items-center gap-1 text-yellow-400 mb-2">
      <i v-for="i in review.rating" :key="i" class="fas fa-star"></i>
    </div>

    <!-- Badge -->
    <p v-if="review.purchased" class="text-green-600 font-semibold text-sm mb-2 flex items-center gap-1">
      <i class="fas fa-check-circle"></i> Đã mua hàng
    </p>

    <!-- Content -->
    <p class="mb-2 text-base text-gray-700">{{ review.content }}</p>

    <!-- Admin Reply -->
    <div v-if="review.reply" class="mt-3 ml-6 border-l-4 border-[#1BA0E2] pl-4 py-2 bg-blue-50 rounded-md text-sm m-5">
      <div class="flex items-center gap-2 mb-1">
        <i class="fas fa-user-shield text-[#1BA0E2]"></i>
        <span class="font-semibold text-[#1BA0E2]">Passion</span>
        <span class="text-gray-400 text-xs">• Đã phản hồi</span>
      </div>
      <p class="text-gray-700 leading-snug">{{ review.reply.content }}</p>
    </div>

    <!-- Images -->
    <img v-for="(img, index) in review.images" :key="index" :src="img.url" class="w-20 h-20 object-cover rounded border"
      alt="Ảnh sản phẩm" />

    <!-- Metadata -->
    <p class="text-sm text-gray-500 mb-1">Màu: {{ review.color }}</p>
    <p class="text-sm text-gray-500 mb-2">
      Đánh giá vào {{ formatDate(review.created_at) }}
    </p>


    <!-- Interaction -->
    <div class="flex flex-col gap-2 text-gray-600 text-sm">
      <div class="flex items-center gap-4">
        <!-- Like -->
        <button @click="likeReview" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1">
          <i :class="['fas fa-thumbs-up', isLiked ? 'text-blue-600' : 'text-gray-500']"></i>
          <span>{{ likeCount }}</span>
        </button>

        <!-- Comment -->
        <button class="flex items-center gap-1 hover:text-blue-600 transition" @click="showReplyForm = !showReplyForm">
          <i class="fas fa-comment"></i>
          <span>Bình luận</span>
        </button>

        <!-- Share -->
        <button class="flex items-center gap-1 hover:text-blue-600 transition ml-auto">
          <i class="fas fa-share-alt"></i>
          <span>Chia sẻ</span>
        </button>
      </div>

      <!-- Reply Form -->
      <transition name="fade">
        <div v-if="showReplyForm" class="flex items-start border border-black-500 p-2 rounded-md mt-2">
          <div class="text-2xl mr-2 text-gray-400">😊</div>
          <div class="flex-1 relative">
            <input type="text" v-model="replyContent"
              class="w-full border border-blue-500 rounded-full px-4 py-2 pr-10 outline-none"
              placeholder="Viết câu trả lời" />
            <button class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
              @click="submitReply">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </transition>
    </div>
  </div>

</template>

<script setup>
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import { useRuntimeConfig } from '#app';

const { review } = defineProps(['review'])

const config = useRuntimeConfig()
const apiBase = config.public.apiBaseUrl
const mediaBase = config.public.mediaBaseUrl

const showReplyForm = ref(false)
const replyContent = ref('')
const isLiked = ref(false)
const likeCount = ref(review.likes_count || 0)
const showMenu = ref(false)

const toggleMenu = () => {
  showMenu.value = !showMenu.value
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const emit = defineEmits(['edit-review', 'delete-review'])

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2000,
  timerProgressBar: true,
})

const likeReview = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    Toast.fire({
      icon: 'info',
      title: 'Vui lòng đăng nhập để thích đánh giá.'
    })
    return
  }

  try {
    const url = `${apiBase}/reviews/${review.id}/${isLiked.value ? 'unlike' : 'like'}`
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`
      }
    })
    const data = await res.json()

    if (!res.ok) throw new Error(data.message)

    isLiked.value = !isLiked.value
    likeCount.value = data.likes
  } catch (err) {
    Toast.fire({
      icon: 'error',
      title: err.message
    })
  }
}

onMounted(async () => {
  const token = localStorage.getItem('access_token')
  if (!token) return

  try {
    const res = await fetch(`${apiBase}/reviews/${review.id}/liked`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    const data = await res.json()
    isLiked.value = data.liked
  } catch (err) {
    console.error('Lỗi kiểm tra like:', err)
  }
})

const submitReply = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    Toast.fire({
      icon: 'info',
      title: 'Vui lòng đăng nhập để phản hồi.'
    })
    return
  }

  try {
    const res = await fetch(`${apiBase}/reviews/${review.id}/reply`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        content: replyContent.value,
      }),
    })

    const data = await res.json()

    if (!res.ok) {
      throw new Error(data.message || 'Đã xảy ra lỗi khi phản hồi.')
    }

    review.reply = data.reply
    replyContent.value = ''
    showReplyForm.value = false

    Toast.fire({
      icon: 'success',
      title: 'Phản hồi thành công!'
    })
  } catch (err) {
    console.error(err)
    Toast.fire({
      icon: 'error',
      title: 'Chỉ người bán sản phẩm này mới được phản hồi.'
    })
  }
}
</script>


<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
